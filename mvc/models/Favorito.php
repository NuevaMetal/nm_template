<?php

namespace Models;

use Models\Post;
use Models\User;

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

	/**
	 * Creamos las tablas
	 *
	 * @return void
	 */
	private static function _install() {
		global $wpdb;
		// Create table
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}favoritos (
		`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		`post_id` bigint(20) UNSIGNED NOT NULL,
		`user_id` bigint(20) UNSIGNED NOT NULL,
		`status` tinyint(1) NOT NULL DEFAULT '0',
		`count` int(10) NOT NULL DEFAULT '1',
		`created_at` TIMESTAMP NOT NULL DEFAULT 0,
		`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`ID`),
		FOREIGN KEY (`post_id`) REFERENCES `wp_posts`(`ID`),
		FOREIGN KEY (`user_id`) REFERENCES `wp_users`(`ID`)
		)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

		// status: 0-activo, 1-inactivo
		$wpdb->query($query);
	}

	/**
	 * Drop tables
	 *
	 * @return void
	 */
	private static function _uninstall() {
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}favoritos ");
	}
}
