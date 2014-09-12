<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 *
 */
class Favorito extends ModelBase {

	const ACTIVO = 0;
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
			$postArray = ChesterWPCoreDataHelpers::getPost(false, [], $r->post_id);
			$postArray ['total'] = $r->total;
			$posts [] = $postArray;
		}
		return $posts;
	}

}
