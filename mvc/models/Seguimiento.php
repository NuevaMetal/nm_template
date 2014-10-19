<?php
require_once 'ModelBase.php';
/**
 * Seguimientos entre dos Users
 *
 * @author chema
 */
class Seguimiento extends ModelBase {
	public static $table = "users_seguimientos";
	/*
	 * Miembros
	 */
	public $user_id;
	public $a_quien_id;

	/**
	 * Constructor
	 *
	 * @param string $user_id
	 *        	Identificador del User
	 * @param string $a_quien_id
	 *        	Identificador de aquello a seguir
	 */
	public function __construct($user_id = false, $a_quien_id = false) {
		parent::__construct();
		if ($user_id) {
			$this->user_id = $user_id;
		}
		if ($a_quien_id) {
			$this->a_quien_id = $a_quien_id;
		}
	}

	/**
	 * Guardar un nuevo seguimiento
	 */
	public function save() {
		global $wpdb;
		$existe = $wpdb->get_var("SELECT count(*)
					FROM {$wpdb->prefix}" . static::$table . "
					WHERE user_id = $this->user_id
						AND a_quien_id = $this->a_quien_id");
		if ($existe) {
			throw new Exception(I18n::transu('user.ya_seguido'), 504);
		}
		$result = $wpdb->query($wpdb->prepare("
			INSERT {$wpdb->prefix}" . static::$table . " (user_id, a_quien_id, created_at, updated_at)
			VALUES (%d, %d, null, null);", $this->user_id, $this->a_quien_id));
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
}