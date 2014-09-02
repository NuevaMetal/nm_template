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
			$posts [] = self::_getPostArrayByPostId($post_id);
		}
		return $posts;
	}

	private static function _getPostArrayByPostId($post_id) {
		$_p = get_post($post_id);
		$_time = explode(' ', $_p->post_modified);
		$time = $_time [0];
		$postArray = [
			'permalink' => get_permalink($_p->ID),
			'title' => $_p->post_title,
			'time' => $time,
			'author' => get_user_by('id', $_p->post_author)->display_name,
			'author_link' => get_author_posts_url($_p->post_author),
			'excerpt' => Utils::getExcerptById($_p->ID)
		];
		$postArray = Utils::addThumbnailsToPost($postArray, $_p);
		return $postArray;
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
			$postArray = self::_getPostArrayByPostId($r->post_id);
			$postArray ['total'] = $r->total;
			$posts [] = $postArray;
		}
		return $posts;
	}

}
