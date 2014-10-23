<?php
require_once 'ModelBase.php';
/**
 * Seguimientos entre dos Users
 *
 * @author chema
 */
class Mensaje extends ModelBase {
	public static $table = "mensajes";
	/*
	 * Miembros
	 */
	public $user_id;
	public $a_quien_id;
	public $mensaje;

	/**
	 * Constructor
	 *
	 * @param string $user_id
	 *        	Identificador del User
	 * @param string $a_quien_id
	 *        	Identificador de aquello a seguir
	 */
	public function __construct($user_id = false, $a_quien_id = false, $mensaje = false) {
		parent::__construct();
		if ($user_id) {
			$this->user_id = $user_id;
		}
		if ($a_quien_id) {
			$this->a_quien_id = $a_quien_id;
		}
		if ($mensaje) {
			$this->mensaje = $mensaje;
		} else {
			$this->mensaje = '';
		}
	}

	/**
	 * Guardar un nuevo mensaje
	 */
	public function save() {
		if (! strlen((string) $this->mensaje)) {
			throw new Exception(I18n::transu('user.mensaje_demasiado_corto'), 500);
		}
		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
			INSERT {$wpdb->prefix}" . static::$table . " (user_id, a_quien_id, mensaje, created_at, updated_at)
			VALUES (%d, %d, %s, null, null);", $this->user_id, $this->a_quien_id, $this->mensaje));
		$this->ID = $wpdb->insert_id;
		return $this;
	}

	/**
	 * Devuelve el User que sigue
	 *
	 * @return User Usuario que sigue
	 */
	public function getUser() {
		return User::find($this->user_id);
	}

	/**
	 * Devuelve el User seguido
	 *
	 * @return User Usuario seguido
	 */
	public function getAQuien() {
		return User::find($this->a_quien_id);
	}

	/**
	 * Crear las tablas de los mensajes entre los usuarios
	 */
	private static function _install() {
		global $wpdb;
		$query = 'CREATE TABLE IF NOT EXISTS wp_mensajes (
ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
user_id bigint(20) UNSIGNED NOT NULL,
a_quien_id bigint(20) UNSIGNED NOT NULL,
mensaje MEDIUMTEXT NOT NULL,
created_at TIMESTAMP NOT NULL DEFAULT 0,
updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE SET NULL,
FOREIGN KEY (a_quien_id) REFERENCES wp_users(ID) ON DELETE SET NULL,
UNIQUE KEY (user_id, created_at)
)ENGINE=MyISAM DEFAULT CHARSET=utf8';
		$wpdb->query($query);
	}
}
