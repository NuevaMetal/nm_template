<?php

namespace Models;

use I18n\I18n;
use Libs\Correo;

/**
 *
 * @author chema
 */
class UserPendiente extends ModelBase {
	public static $table = 'users_pendientes';

	const PENDIENTE = 0;

	const ACEPTADO = 1;

	const RECHAZADO = 2;

	/*
	 * Miembros
	 */
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
	public static function getTotal($estado = self::PENDIENTE) {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare('
			SELECT count( * ) AS total
			FROM (
				SELECT user_id
				FROM wp_users_pendientes p
				JOIN wp_users u ON ( p.user_id = u.ID )
				WHERE STATUS = %d
				GROUP BY user_id
			) r', $estado));
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

	/**
	 * Devuelve el User pendiente
	 *
	 * @return User
	 */
	public function getUser() {
		return User::find($this->user_id);
	}

	/**
	 * Devuelve el Editor que aceptó o denegó al user pendiente
	 *
	 * @return User
	 */
	public function getEditor() {
		return User::find($this->editor_id);
	}

	/**
	 * Devuelve el número total de Users pendientes
	 *
	 * @return integer
	 */
	public static function getTotalPendientes() {
		return count(self::getByStatus(UserPendiente::PENDIENTE));
	}

	/**
	 * Devuelve el número total de Users pendientes aceptados
	 *
	 * @return integer
	 */
	public static function getTotalAceptados() {
		return count(self::getByStatus(UserPendiente::ACEPTADO));
	}

	/**
	 * Devuelve el número total de Users pendientes rechazados
	 *
	 * @return integer
	 */
	public static function getTotalRechazados() {
		return count(self::getByStatus(UserPendiente::RECHAZADO));
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
		// Actualizamos su rol a Colaborador
		$user = $this->getUser();
		$user->setRol(User::ROL_COLABORADOR);
		// Cambiamos sus valores en la BBDD
		$result = $wpdb->query($wpdb->prepare('
				UPDATE wp_users_pendientes
				SET editor_id = %d, status = %d, updated_at = now()
				where user_id = %d
				and status = %d', $editor_id, self::ACEPTADO, $this->user_id, self::PENDIENTE));
		// Enviamos correo para informar al usuario que fue aceptado como colaborador
		$plantillaAceptadoColaborador = I18n::trans('emails.aceptado_como_colaborador', [
			'blogname' => get_option('blogname'),
			'user_login' => $user->getLogin()
		]);
		$asunto = I18n::trans('user.aceptado_como_colaborador');
		$enviado = Correo::enviarCorreoGenerico([
			get_option('admin_email'),
			$this->getUser()->getEmail()
		], $asunto, $plantillaAceptadoColaborador);

		if (! $enviado) {
			Utils::info("FALLO al enviar correo generico 'plantillaAceptadoColaborador'");
		}
	}

	/**
	 * No cambia el estado del usuario pendiente y le indicamos el editor que lo rechazó.
	 *
	 * @param integer $editor_id
	 *        	Identificador del editor que rechazó al usuario como colaborador
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

	/**
	 *
	 * @param integer $editor_id
	 * @return boolean
	 */
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
