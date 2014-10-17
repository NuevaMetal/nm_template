<?php
require_once 'ModelBase.php';
/**
 * Analítica
 *
 * @author chema
 */
class Seguimiento extends ModelBase {
	public static $table = "seguimientos";

	const TIPO_USER = 1;

	/*
	 * Miembros
	 */
	public $user_id;
	public $que_id;
	public $tipo_que;

	/**
	 * Constructor
	 *
	 * @param string $user_id
	 *        	Identificador del User
	 * @param string $que_id
	 *        	Identificador de aquello a seguir
	 * @param string $tipo_que
	 *        	Tipo de aquello a seguir
	 */
	public function __construct($user_id = false, $que_id = false, $tipo_que = false) {
		parent::__construct();
		if ($user_id) {
			$this->user_id = $user_id;
		}
		if ($que_id) {
			$this->que_id = $que_id;
		}
		if ($tipo_que) {
			$this->tipo_que = $tipo_que;
		}
	}

	/**
	 * Guardar un nuevo seguimiento
	 */
	public function save() {
		if ($this->_esTipoPermitido()) {
			global $wpdb;
			$current_user = Utils::getCurrentUser();
			$existe = $wpdb->get_var("SELECT count(*)
					FROM {$wpdb->prefix}" . static::$table . "
					WHERE user_id = $current_user->ID
						AND que_id = $this->que_id
						AND tipo_que = $this->tipo_que;");
			if (! $existe && $current_user) {
				$result = $wpdb->query($wpdb->prepare("
						INSERT {$wpdb->prefix}" . static::$table . " (user_id, que_id, tipo_que, created_at, updated_at)
				VALUES (%d, %d, %d, null, null);", $current_user->ID, $this->que_id, $this->tipo_que));
				$this->ID = $wpdb->insert_id;
				return $this;
			}
		}
		return false;
	}

	/**
	 * Devuelve el User
	 *
	 * @return User Usuario que sigue
	 */
	public function getUser() {
		return User::find($this->user_id);
	}

	/**
	 * Devuelve el objeto al que pertenece el seguimiento
	 *
	 * @return object
	 */
	public function getQue() {
		$model = $this->getModelQue();
		if ($model) {
			return $model::find($this->que_id);
		}
		return null;
	}

	/**
	 * Devuelve el nombre del modelo del 'que'
	 */
	public function getModelQue() {
		return self::getModelByTipo($this->tipo_que);
	}

	/**
	 * Devuelve el nombre del modelo del que conociendo su tipo de seguimiento
	 *
	 * @param integer $tipo
	 *        	Tipo de seguimiento
	 */
	public static function getModelByTipo($tipoQue = false) {
		switch ($tipoQue) {
			case self::TIPO_USER :
				return 'User';
		}
		return null;
	}

	/**
	 * Comprobar si el tipo es un tipo permitido
	 */
	protected function _esTipoPermitido() {
		return in_array($this->tipo_que, $this->_getTiposPermitidos());
	}

	/**
	 * Devuelve la lista de tipos permitidos de seguimientos
	 *
	 * @return array<integer>
	 */
	protected static function _getTiposPermitidos() {
		return [
			self::TIPO_USER
		];
	}
}