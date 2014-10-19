<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 */
class Favorito extends ModelBase {

	const ACTIVO = 0;

	const BORRADO = 1;
	public static $table = "favoritos";
	public $user_id;
	public $post_id;

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
			$post = Post::find($r->post_id);
			$post->total = $r->total;
			$posts[] = $post;
		}
		return $posts;
	}

	/**
	 * Devuelve el Post favorito
	 *
	 * @return Post
	 */
	public function getPost() {
		return Post::find($this->post_id);
	}

	/**
	 * Devuelve el User
	 *
	 * @return User
	 */
	public function getUser() {
		return User::find($this->user_id);
	}
}
