<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 *
 */
class User extends ModelBase {

	const NUM_FAV_PERFIL_DEFAULT = 4;

	const NUM_ETI_FAV_PERFIL_DEFAULT = 20;
	public static $table = "users";

	/**
	 * Devuelve el número total de posts publicados por el User
	 */
	public function getCountPosts() {
		return count_user_posts($this->ID);
	}

	/**
	 * Devuelve una lista de arrays con las etiquetas de las entradas a las que le dio favoritos
	 *
	 * @return array
	 */
	public function getArrayEtiquetasFavoritas($cant = false) {
		$favoritos = $this->getFavoritos();
		$tags = [];
		foreach ($favoritos as $f) {
			//dd($f);
			foreach ($f ['the_tags'] as $t) {
				if (isset($tags [$t ['name']])) {
					$tags [$t ['name']] ['total']++;
				} else {
					$tags [$t ['name']] = $t;
					$tags [$t ['name']] ['total'] = 1;
				}
			}
		}
		// Ordenamos el array de etiquetas por su cantidad total
		usort($tags, function ($a, $b) {
			return $a ['total'] < $b ['total'];
		});

		if ($cant) {
			return array_slice($tags, 0, $cant);
		}

		return $tags;
	}

	/**
	 * Devuelve la lista de todos los favoritos de un User
	 *
	 * @param integer $user_id
	 *        Identificador del usuario
	 * @return array<Favorito>
	 */
	public function getFavoritos($cant = false) {
		global $wpdb;
		$status = Favorito::ACTIVO;
		$tabla = $wpdb->prefix . Favorito::$table;
		$user_id = $this->ID;
		$queryPostId = "SELECT post_id FROM $tabla
						WHERE status = $status
						AND user_id = $user_id
						ORDER BY updated_at desc ";
		if ($cant && is_numeric($cant)) {
			$queryPostId .= ' LIMIT ' . $cant;
		}
		$posts_id = $wpdb->get_col($queryPostId);
		$posts = [];
		foreach ($posts_id as $post_id) {
			$posts [] = ChesterWPCoreDataHelpers::getPost(false, [], $post_id);
		}
		return $posts;
	}

	/**
	 * Devuelve
	 *
	 * @param string $cant
	 * @return Ambigous <multitype:multitype: , unknown>
	 */
	public function getFavoritosAgrupados($cant = false) {
		$todosFavoritos = $this->getFavoritos($cant);
		$favoritos = [];
		foreach ($todosFavoritos as $k => $f) {
			$cat_name = strtolower(Post::getCategoryName($f ['post_id']));
			if (!isset($favoritos [$cat_name])) {
				$favoritos [$cat_name] = [];
				if ($k == 0 && !isset($favoritos [$cat_name] ['activo'])) {
					$favoritos [$cat_name] ['activo'] = true;
				}
			}
			$favoritos [$cat_name] ['lista'] [] = $f;
		}
		// Añadimos el total
		foreach ($favoritos as &$f) {
			$f ['total_lista'] = count($f ['lista']);
		}
		return $favoritos;
	}

	/**
	 * Devuelve el número total de favoritos que tiene el user
	 *
	 * @return number Total de favoritos que tiene el User
	 */
	public function getCountFavoritos() {
		global $wpdb;
		$activo = Favorito::ACTIVO;
		return ( int ) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "favoritos
				WHERE user_id = $this->ID AND status = $activo;");
	}

}