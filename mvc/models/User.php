<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 *
 */
class User extends ModelBase {
	public static $table = "users";

	/**
	 * Número de post favoritos a mostrar en su perfil
	 */
	const NUM_FAV_PERFIL_DEFAULT = 4;

	/**
	 * Número de etiquetas de los posts favoritos a mostrar en su perfil
	 */
	const NUM_ETI_FAV_PERFIL_DEFAULT = 20;

	const ENTRADAS_PUBLICADAS_AJAX = 'entradas-publicadas';

	/**
	 * Devuelve el número total de posts publicados por el User
	 */
	public function getCountPosts() {
		return count_user_posts($this->ID);
	}

	public function getAvatar($tamano = 96, $default = "", $alt = false) {
		return get_avatar($this->ID, $tamano, $default, $alt);
	}

	public function getUrl() {
		return get_the_author_meta('user_url', $this->ID);
	}

	public function getDescription() {
		return get_the_author_meta('description', $this->ID);
	}

	public function getEditUrl() {
		return get_edit_user_link();
	}

	/**
	 * Devuelve una lista de arrays con las etiquetas de las entradas a las que le dio favoritos
	 *
	 * @return array
	 */
	public function getArrayEtiquetasFavoritas($cant = User::NUM_ETI_FAV_PERFIL_DEFAULT) {
		$favoritos = $this->getFavoritos($limit = false, $offset = false, $conCategorias = true);
		$tags = [];
		foreach ($favoritos as $f) {
			if (isset($f ['the_tags'])) {
				foreach ($f ['the_tags'] as $t) {
					if (isset($tags [$t ['name']])) {
						$tags [$t ['name']] ['total']++;
					} else {
						$tags [$t ['name']] = $t;
						$tags [$t ['name']] ['total'] = 1;
					}
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
	 * @param number $limit
	 * @param number $offset
	 * @param boolean $conCategorias
	 * @return array
	 */
	public function getFavoritos($limit = false, $offset = false, $conCategorias = false) {
		global $wpdb;
		$status = Favorito::ACTIVO;
		$tabla = $wpdb->prefix . Favorito::$table;
		$user_id = $this->ID;
		$queryPostId = "SELECT post_id FROM $tabla
						WHERE status = $status
						AND user_id = $user_id
						ORDER BY updated_at desc ";
		if ($limit) {
			$queryPostId .= ' LIMIT ' . $limit;
		}
		if ($offset) {
			$queryPostId .= ' OFFSET ' . $offset;
		}
		$posts_id = $wpdb->get_col($queryPostId);
		$posts = [];
		foreach ($posts_id as $post_id) {
			$posts [] = Post::get($post_id, $dateFormat = false, $conCategorias);
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

	/**
	 * Devuelve el número total de favoritos que han recibido sus entradas
	 *
	 * @return string
	 */
	public function getCountFavoritosRecibidos() {
		global $wpdb;
		$activo = Favorito::ACTIVO;
		return ( int ) $wpdb->get_var("SELECT SUM( p.totales )
			FROM (
				SELECT COUNT(ids.ID) as totales FROM wp_favoritos f,
					(SELECT ID FROM wp_posts
					where post_author = $this->ID) ids
				where f.post_id = ids.ID
				AND f.status = $activo
				GROUP BY f.user_id
			) p");
	}

	/**
	 * De
	 *
	 * @param number $cantidad
	 */
	public function getTotalEntradasPublicadasPorDia($cantidad = 31) {
		global $wpdb;
		$query = 'SELECT DATE(post_date) dia, COUNT(*) total
				FROM wp_posts
				WHERE post_author = ' . $this->ID . '
					AND post_type = "post"
					AND post_status = "publish"
					AND DATE( post_date ) >= DATE( NOW( ) ) -30
				GROUP BY dia
				ORDER BY dia DESC
	 			LIMIT ' . $cantidad;

		$result = $wpdb->get_results($query);
		$result = Analitica::formatearDias($result);
		return $result;
	}

}