<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 *
 */
class Favorito extends ModelBase {
	public static $table = "favoritos";
	public $user_id;
	public $post_id;

	/**
	 * Devuelve la lista de favoritos de un User
	 *
	 * @param integer $user_id
	 *        Identificador del usuario
	 * @return array<Favorito>
	 */
	public static function getFavoritosByUserId($user_id = false) {
		$todosFavoritos = self::getTodosFavoritosByUserId($user_id);
		$favoritos = [];
		foreach ($todosFavoritos as $k => $f) {
			$cat_name = strtolower(Utils::getCategoryName($f ['post_id']));
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
	 * Devuelve la lista de todos los favoritos de un User
	 *
	 * @param integer $user_id
	 *        Identificador del usuario
	 * @return array<Favorito>
	 */
	public static function getTodosFavoritosByUserId($user_id = false) {
		if (!$user_id || !is_numeric($user_id)) {
			return null;
		}
		global $wpdb;
		$status = Utils::ACTIVO;
		$tabla = $wpdb->prefix . self::$table;
		$queryPostId = "SELECT post_id FROM $tabla
			WHERE status = $status
			AND user_id = $user_id
			ORDER BY updated_at desc";
		$posts_id = $wpdb->get_col($queryPostId);
		$posts = [];
		foreach ($posts_id as $post_id) {
			$posts [] = ChesterWPCoreDataHelpers::getPost(false, [], $post_id);
		}
		return $posts;
	}

	/**
	 * Devuelve los posts con mejor valoración
	 *
	 * @param number $cant
	 */
	public static function getTopPosts($cant = 4) {
		global $wpdb;
		$tabla = $wpdb->prefix . self::$table;
		$query = "SELECT post_id, COUNT(*) total
				FROM $tabla
				GROUP BY post_id
				order by total desc
				LIMIT $cant";
		$result = $wpdb->get_results($query);
		$posts = [];
		foreach ($result as $k => $r) {
			$postArray = ChesterWPCoreDataHelpers::getPost(false, [], $r->post_id);
			$postArray ['total'] = $r->total;
			$posts [] = $postArray;
		}
		return $posts;
	}

}
