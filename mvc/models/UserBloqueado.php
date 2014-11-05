<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 */
class UserBloqueado extends ModelBase {
	public static $table = 'users_bloqueados';

	const ESTADO_BORRADO = 0;

	const ESTADO_BLOQUEADO = 1;
	public $user_id;

	/**
	 *
	 * @param int $user_id
	 *        	Identificador del User
	 */
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
	public static function getTotal($que = self::ESTADO_BLOQUEADO) {
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
	public static function getByStatus($status = self::ESTADO_BLOQUEADO) {
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
	public function borrar() {
		global $wpdb;
		$table = $wpdb->prefix . self::$table;
		$estadoBloqueado = self::ESTADO_BLOQUEADO;
		$estadoBorrado = self::ESTADO_BORRADO;
		// Actualizamos su rol a Colaborador
		$user = $this->getUser();
		$user->setRol(User::ROL_COLABORADOR);
		// Cambiamos sus valores en la BBDD
		$result = $wpdb->query("
				UPDATE $table
				SET status = $estadoBorrado, updated_at = now()
				where user_id = $this->user_id
				and status = $estadoBloqueado");
		return $result;
	}
	public function save() {
		global $wpdb;
		$table = $wpdb->prefix . self::$table;
		$estadoBloqueado = self::ESTADO_BLOQUEADO;
		// Primero comprobamos que el user no esté
		$esta = (int) $wpdb->get_var("SELECT COUNT(*)
				FROM $table
				WHERE user_id = $this->user_id
				AND status = $estadoBloqueado;");
		// Segundo, si no está
		if (! $esta && $this->user_id && $this->editor_id) {
			$result = $wpdb->query($wpdb->prepare("
			INSERT INTO $table (user_id, editor_id, status, created_at, updated_at)
			VALUES (%d, %d, %d, null, null);", $this->user_id, $this->editor_id, $estadoBloqueado));
		}
		return $this->ID;
	}

	/**
	 * Crear las tablas
	 *
	 * @return void
	 */
	public static function install() {
		Utils::debug("> UserBloqueadoController->install() ");
		global $wpdb;
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}users_bloqueados (
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
