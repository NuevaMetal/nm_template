<?php
require_once 'ModelBase.php';
/**
 * Seguimientos entre dos Users
 *
 * @author chema
 */
class Mensaje extends ModelBase {
	public static $table = "mensajes";

	const ESTADO_ACTIVO = 1;

	const ESTADO_BORRADO = 0;

	const TAMANO_MAXIMO = 10000;

	/*
	 * Miembros
	 */
	public $user_id;
	public $a_quien_id;
	public $mensaje;
	public $estado;
	public $updated_at;

	/**
	 * Constructor
	 *
	 * @param string $user_id
	 *        	Identificador del User
	 * @param string $a_quien_id
	 *        	Identificador de aquello a seguir
	 */
	public function __construct($user_id = false, $a_quien_id = false, $mensaje = false, $updated_at = false, $ID = false) {
		parent::__construct();
		if ($user_id) {
			$this->user_id = $user_id;
		}
		if ($a_quien_id) {
			$this->a_quien_id = $a_quien_id;
		}
		if ($mensaje) {
			$this->mensaje = $mensaje;
		}
		if ($updated_at) {
			$this->updated_at = $updated_at;
		}
		if ($ID) {
			$this->ID = $ID;
		}
	}

	/**
	 * Devuelve la fecha de la actividad
	 */
	public function getDate() {
		// date($dateFormat, strtotime($this->updated_at));
		$strToTime = strtotime($this->updated_at);
		// 1 (para lunes) hasta 7 (para domingo)
		$numDiaSemana = date('N', $strToTime);
		$nombreDia = Utils::getDiaTransByNum($numDiaSemana);
		// Representación numérica de un mes, sin ceros iniciales. 1 hasta 12
		$numMes = date('n', $strToTime);
		$nombremes = Utils::getMesTransByNum($numMes);
		$numDia = date('d', $strToTime); // Número del día
		$ano = date('Y', $strToTime); // Año
		return "$nombreDia, $numDia $nombremes $ano";
	}

	/**
	 * Devuelve la hora en la que se produjo la actividad
	 */
	public function getTime() {
		// date($dateFormat, strtotime($this->updated_at));
		$strToTime = strtotime($this->updated_at);
		$hora = date('H', $strToTime);
		$minutos = date('m', $strToTime);
		return "$hora:$minutos";
	}

	/**
	 * Guardar un nuevo mensaje
	 */
	public function save() {
		$strlen = strlen( $this->mensaje);
		if (! $strlen) {
			throw new Exception(I18n::transu('user.mensaje_demasiado_corto'), 500);
		}elseif( $strlen > self::TAMANO_MAXIMO){
			throw new Exception(I18n::transu('user.mensaje_demasiado_grande'), 500);
		}

		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
			INSERT {$wpdb->prefix}" . static::$table . " (user_id, a_quien_id, mensaje, created_at, updated_at)
			VALUES (%d, %d, %s, null, null);", $this->user_id, $this->a_quien_id, $this->mensaje));
		$this->ID = $wpdb->insert_id;
		return $this;
	}

	/**
	 * Establecer el estado del mensaje a borrado
	 *
	 * @throws Exception
	 * @return Mensaje
	 */
	public function borrar() {
		global $wpdb;
		$existe = $wpdb->get_var("SELECT count(*)
				FROM {$wpdb->prefix}" . static::$table . "
				WHERE ID = $this->ID");
		if (! $existe) {
			throw new Exception(I18n::transu('user.mensaje_no_existe'), 504);
		}
		$result = $wpdb->query($wpdb->prepare("
				UPDATE {$wpdb->prefix}" . static::$table . " SET estado = %d
			WHERE ID = %d;", self::ESTADO_BORRADO, $this->ID));
		return $result;
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
		$query = 'CREATE TABLE IF NOT EXISTS wp_mensajes(
ID bigint( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
user_id bigint( 20 ) UNSIGNED NOT NULL ,
a_quien_id bigint( 20 ) UNSIGNED NOT NULL ,
mensaje MEDIUMTEXT NOT NULL ,
estado tinyint NOT NULL default 1,
created_at TIMESTAMP NOT NULL DEFAULT 0,
updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
FOREIGN KEY ( user_id ) REFERENCES wp_users( ID ) ON DELETE SET NULL ,
FOREIGN KEY ( a_quien_id ) REFERENCES wp_users( ID ) ON DELETE SET NULL ,
UNIQUE KEY ( user_id, created_at )
) ENGINE = MYISAM DEFAULT CHARSET = utf8';
		$wpdb->query($query);
	}
}
