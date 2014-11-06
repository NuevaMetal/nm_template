<?php

namespace Models;

use Models\Post;
use Models\User;

/**
 *
 * @author chema
 */
class UserBaneado extends ModelBase {
	public static $table = "revisiones_ban";

	const BANEADO = 1;

	const DESBANEADO = 2;
	/*
	 * Miembros
	 */
	public $user_id;
	public $editor_id;
	public $status;
	public $num;

	/**
	 * Devuelve el editor que baneó a un User
	 *
	 * @return User
	 */
	public function getEditor() {
		return User::find($this->editor_id);
	}

	/**
	 * Devuelve el User baneado
	 *
	 * @return User
	 */
	public function getUser() {
		return User::find($this->user_id);
	}

	/**
	 * Devuelve el total de users baneados
	 *
	 * @return number
	 */
	public static function getTotalBaneados() {
		return count(self::getBaneados());
	}

	/**
	 * Devuelve la lista de users baneados junto al que lo baneó
	 *
	 * @return array<UserBaneado>
	 */
	public static function getBaneados() {
		global $wpdb;
		$results = $wpdb->get_results($wpdb->prepare('
				SELECT user_id, editor_id, status, created_at
				FROM wp_revisiones_ban
				WHERE status = %d
				GROUP BY user_id, status', self::BANEADO));
		foreach ($results as $k => $_r) {
			$baneado = new UserBaneado();
			$baneado->num = $k + 1;
			$baneado->user_id = $_r->user_id;
			$baneado->editor_id = $_r->editor_id;
			$baneado->status = $_r->status;
			$baneado->created_at = $_r->created_at;
			$baneados[] = $baneado;
		}
		return $baneados;
	}

	/**
	 * Banear al user de las revisiones
	 *
	 * @param integer $editor_id
	 *        	Identificador del editor que lo banea
	 * @throws \Exception
	 */
	public function banearDeLasRevisiones() {
		global $wpdb;
		if ($this->getUser()->isAdmin()) {
			throw new \Exception('No puedes banear a un admin', 402);
		}
		$isBan = $wpdb->get_var($wpdb->prepare('select count(*)
				from wp_revisiones_ban
				where user_id = %d
				and status = %d;', $this->user_id, self::BANEADO));
		if (! $isBan) {
			return $wpdb->query($wpdb->prepare('
				INSERT wp_revisiones_ban (user_id, editor_id, created_at, updated_at)
				VALUES (%d, %d, null, null);', $this->user_id, $this->editor_id));
		}
		return $wpdb->query($wpdb->prepare('
				UPDATE wp_revisiones_ban
				SET user_id = %d, editor_id = %;', $this->user_id, $this->editor_id));
	}

	/**
	 * Desbanear al user de las revisiones
	 *
	 * @param integer $editor_id
	 *        	Identificador del editor que lo banea
	 * @throws \Exception
	 */
	public function desbanearDeLasRevisiones() {
		global $wpdb;
		return $wpdb->query($wpdb->prepare('
				UPDATE wp_revisiones_ban
				SET status = %d
				WHERE user_id = %d', self::DESBANEADO, $this->user_id));
	}

	/**
	 * insall
	 */
	private function _install() {
		$query = 'CREATE TABLE IF NOT EXISTS wp_revisiones_ban (
		`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) UNSIGNED NOT NULL,
		`editor_id` bigint(20) UNSIGNED NOT NULL,
		`status` tinyint(1) NOT NULL DEFAULT "1",
		`created_at` TIMESTAMP NOT NULL DEFAULT 0,
		`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`ID`),
		FOREIGN KEY (`user_id`) REFERENCES `wp_users`(`ID`),
		FOREIGN KEY (`editor_id`) REFERENCES `wp_users`(`ID`)
		)ENGINE=MyISAM  DEFAULT CHARSET=utf8;';

		// status: 1-pendiente, 2-borrada
		// user_id -> User al que se le banean las revisiones
		// editor_id -> User que banean las revisiones al user_id
		// $wpdb->query($query);
	}
}