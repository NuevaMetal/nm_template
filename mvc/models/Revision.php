<?php

namespace Models;

use Libs\Utils;
use Models\Post;
use Models\User;

/**
 *
 * @author chema
 */
class Revision extends ModelBase {
	public static $table = "revisiones";

	const ESTADO_PENDIENTE = 0;

	const ESTADO_CORREGIDO = 1;

	const ESTADO_BORRADO = 2;

	/*
	 * Miembros
	 */
	public $num;
	public $post_id;
	public $users_id;
	public $status;
	public $total;

	/**
	 * Devuelve el Post de la revisión.
	 *
	 * @return Post
	 */
	public function getPost() {
		return Post::find($this->post_id);
	}

	/**
	 * Devuelve los users que le dieron a la revisión.
	 *
	 * @return User
	 */
	public function getUsers() {
		$users = [];
		foreach ($this->users_id as $user_id) {
			$users[] = User::find($user_id);
		}
		return $users;
	}

	/**
	 * Devuelve el número total de entradas pendientes
	 *
	 * @return intener
	 */
	public static function getTotalPendientes() {
		return count(self::getPendientes());
	}

	/**
	 * Devuelve todas las revisiones en base a su estado
	 *
	 * @param unknown $status
	 */
	private static function _getRevisionesByEstado($estado = false) {
		global $wpdb;
		$sql = 'SELECT DISTINCT r.post_id, r.status, count( * ) total
				FROM wp_revisiones r
				JOIN wp_posts p ON ( r.post_id = p.ID )
				WHERE STATUS = %d
				GROUP BY r.post_id, r.status';

		$results = $wpdb->get_results($wpdb->prepare($sql, $estado));

		foreach ($results as $k => $_r) {
			$r = new Revision();
			$r->num = $k + 1;
			$r->post_id = $_r->post_id;
			$r->status = $_r->status;
			$r->total = $_r->total;
			$r->users_id = self::_getUsersIdByPostId($_r->post_id, $estado);
			$revisiones[] = $r;
		}
		return $revisiones;
	}

	/**
	 * Devuelve la id de los usuarios que le dieron al post
	 *
	 * @param integer $post_id
	 *        	Identificador del Post
	 * @param integer $estado
	 *        	Estado de la revisión
	 */
	private function _getUsersIdByPostId($post_id, $estado) {
		global $wpdb;
		return $wpdb->get_col($wpdb->prepare('
				SELECT user_id FROM wp_revisiones
				WHERE post_id = %d AND status = %d', $post_id, $estado));
	}

	/**
	 * Devuelve true si la revisión está pendiente
	 *
	 * @return boolean
	 */
	public function isPendiente() {
		return $this->status == self::ESTADO_PENDIENTE;
	}
	/**
	 * Devuelve todas las revisiones pendientes
	 *
	 * @return array<Pendiente>
	 */
	public static function getPendientes() {
		return self::_getRevisionesByEstado(Revision::ESTADO_PENDIENTE);
	}

	/**
	 * Devuelve todas las revisiones pendientes
	 *
	 * @return array<Pendiente>
	 */
	public static function getCorregidas() {
		return self::_getRevisionesByEstado(Revision::ESTADO_CORREGIDO);
	}

	/**
	 * Corregir una revisión.
	 * Marca su estado a corregido.
	 *
	 * @param integer $post_id
	 * @param integer $user_id
	 * @return string
	 */
	public static function corregir($post_id) {
		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
				UPDATE {$wpdb->prefix}revisiones
				SET status = 1
				WHERE post_id = %d
				AND status = 0;", $post_id));
		$post_title = '<a href="' . get_permalink($post_id) . '" target="_blank">
							' . get_the_title($post_id) . '</a>';
		return "Corregido el post con título: <strong>$post_title</strong>. ";
	}

	/**
	 * Borrar una revisión.
	 * Cambia su estado a borrado
	 *
	 * @param integer $post_id
	 * @param integer $user_id
	 * @return string
	 */
	public static function pendiente($post_id) {
		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
				UPDATE {$wpdb->prefix}revisiones
				SET status = 0
				WHERE post_id = %d
				AND status = 1;", $post_id));
		$post_title = '<a href="' . get_permalink($post_id) . '" target="_blank">
							' . get_the_title($post_id) . '</a>';
		return "Puesta en revisión el post con título: <strong>'$post_title'</strong>. ";
	}

	/**
	 * Borrar una revisión.
	 * Cambia su estado a borrado
	 *
	 * @param integer $post_id
	 * @param integer $user_id
	 * @return string
	 */
	public static function borrar($post_id) {
		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
				UPDATE {$wpdb->prefix}revisiones
				SET status = 2
				WHERE post_id = %d
				AND status = 1;", $post_id));
		$post_title = '<a href="' . get_permalink($post_id) . '" target="_blank">
							' . get_the_title($post_id) . '</a>';
		return "Borrada la revisión del post con título: <strong>'$post_title'</strong>. ";
	}

	/**
	 * Creamos las tablas
	 *
	 * @return void
	 */
	private function _install() {
		Utils::debug("> RevisionesController->install() ");
		global $wpdb;
		// Create table
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}revisiones (
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

		// status: 1-pendiente, 2-revisada, 3-borrada
		$wpdb->query($query);
	}
}