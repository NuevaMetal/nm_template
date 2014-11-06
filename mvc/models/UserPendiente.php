<?php

namespace Models;

/**
 *
 * @author chema
 */
class UserPendiente extends ModelBase {
	public static $table = 'users_pendientes';

	const PENDIENTE = 0;

	const ACEPTADO = 1;

	const RECHAZADO = 2;
	public $user_id;
	public function __construct($user_id = false) {
		parent::__construct();
		if ($user_id) {
			$this->user_id = $user_id;
		}
	}

	/**
	 * Devuelve el número total de usuarios pendientes
	 *
	 * @param integer $que
	 * @return number
	 */
	public static function getTotal($que = self::PENDIENTE) {
		global $wpdb;
		return (int) $wpdb->get_var('
			SELECT count(*) as total
			FROM (SELECT user_id FROM ' . $wpdb->prefix . self::$table . '
					WHERE status = ' . $que . '
					GROUP BY user_id ) r
				');
	}

	/**
	 * Devuelve la lista según el estado
	 *
	 * @param integer $status
	 * @return array
	 */
	public static function getByStatus($status = self::PENDIENTE) {
		foreach (self::all() as $a) {
			if ($a->status == $status) {
				$r[] = $a;
			}
		}
		return $r;
	}
	public function getUser() {
		return User::find($this->user_id);
	}
	public function getEditor() {
		return User::find($this->editor_id);
	}

	/**
	 * Cambia el estado del usuario pendiente, le pone el rol de colaborador
	 * y le indicamos el editor que lo aceptó
	 *
	 * @param integer $editor_id
	 *        	Identificador del editor que aceptó al usuario como colaborador
	 */
	public function aceptarPor($editor_id) {
		if (! $editor_id || ! is_numeric($editor_id)) {
			return false;
		}
		global $wpdb;
		$table = $wpdb->prefix . self::$table;
		$estadoPendiente = self::PENDIENTE;
		$estadoAceptado = self::ACEPTADO;
		// Actualizamos su rol a Colaborador
		$user = $this->getUser();
		$user->setRol(User::ROL_COLABORADOR);
		// Cambiamos sus valores en la BBDD
		$result = $wpdb->query("
				UPDATE $table
				SET editor_id = $editor_id, status = $estadoAceptado, updated_at = now()
				where user_id = $this->user_id
				and status = $estadoPendiente");
	}

	/**
	 * Cambia el estado del usuario pendiente, le pone el rol de colaborador
	 * y le indicamos el editor que lo aceptó
	 *
	 * @param integer $editor_id
	 *        	Identificador del editor que aceptó al usuario como colaborador
	 */
	public function rechazarPor($editor_id) {
		if (! $editor_id || ! is_numeric($editor_id)) {
			return false;
		}
		global $wpdb;
		$table = $wpdb->prefix . self::$table;
		$estadoPendiente = self::PENDIENTE;
		$estadoRechazado = self::RECHAZADO;
		// Actualizamos su rol a Colaborador
		$user = $this->getUser();
		$user->setRol(User::ROL_SUSCRIPTOR);
		// Cambiamos sus valores en la BBDD
		$result = $wpdb->query("
				UPDATE $table
				SET editor_id = $editor_id, status = $estadoRechazado, updated_at = now()
				where user_id = $this->user_id
				and status = $estadoPendiente");
	}
	public function pendienterPor($editor_id) {
		if (! $editor_id || ! is_numeric($editor_id)) {
			return false;
		}
		global $wpdb;
		$table = $wpdb->prefix . self::$table;
		$estadoPendiente = self::PENDIENTE;
		// Actualizamos su rol a Colaborador
		$user = $this->getUser();
		$user->setRol(User::ROL_SUSCRIPTOR);
		// Cambiamos sus valores en la BBDD
		$result = $wpdb->query("
				UPDATE $table
				SET editor_id = $editor_id, status = $estadoPendiente, updated_at = now()
				where user_id = $this->user_id");
	}
	public function save() {
		global $wpdb;
		$table = $wpdb->prefix . self::$table;
		$estadoPendiente = self::PENDIENTE;
		// Primero comprobamos que el user no esté
		$esta = (int) $wpdb->get_var("SELECT COUNT(*)
				FROM $table
				WHERE user_id = $this->user_id
				AND status = $estadoPendiente;");
		// Segundo, si no está
		if (! $esta) {
			$result = $wpdb->query($wpdb->prepare("
			INSERT $table (user_id, editor_id, created_at, updated_at)
			VALUES (%d, null, null, null);", $this->user_id));
		}
		return $this->ID;
	}

	/**
	 * Crear las tablas
	 *
	 * @return void
	 */
	public static function install() {
		Utils::debug("> UsersPendientesController->install() ");
		global $wpdb;
		$query = "CREATE TABLE IF NOT EXISTS wp_users_pendientes (
		`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) UNSIGNED NOT NULL,
		`editor_id` bigint(20) UNSIGNED,
		`status` tinyint(1) NOT NULL DEFAULT '0',
		`created_at` TIMESTAMP NOT NULL DEFAULT 0,
		`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`ID`),
		FOREIGN KEY (`user_id`) REFERENCES `wp_users`(`ID`),
		FOREIGN KEY (`editor_id`) REFERENCES `wp_users`(`ID`)
		)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($query);
	}
}
