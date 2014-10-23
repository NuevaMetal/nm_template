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

	const TAMANO_MAXIMO_MENSAJE_PRIVADO = 10000;

	const TAMANO_MAXIMO_ESTADO = 150;

	/*
	 * Tipos de mensajes
	 */
	const TIPO_MENSAJE_PRIVADO = 1;

	const TIPO_ESTADO = 2;

	/*
	 * Miembros
	 */
	public $user_id;
	public $a_quien_id;
	public $respuesta_id;
	public $mensaje;
	public $estado;
	public $tipo;
	public $updated_at;

	/**
	 * Constructor
	 *
	 * @param string $user_id
	 *        	Identificador del User
	 * @param string $a_quien_id
	 *        	Identificador de aquello a seguir
	 */
	public function __construct($user_id = false, $a_quien_id = false, $mensaje = false, $updated_at = false, $ID = false, $tipo = false) {
		parent::__construct();
		$this->user_id = $user_id;
		$this->a_quien_id = $a_quien_id;
		$this->mensaje = $mensaje;
		$this->updated_at = $updated_at;
		$this->ID = $ID;
		$this->tipo = $tipo;
	}

	/**
	 * Devuelve la fecha en la que se envió el mensaje
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
	 * Devuelve la hora en la que se envió el mensaje
	 */
	public function getTime() {
		$strToTime = strtotime($this->updated_at);
		$hora = date('H', $strToTime);
		$minutos = date('m', $strToTime);
		return "$hora:$minutos";
	}

	/**
	 * Guardar un nuevo mensaje
	 */
	public function save() {
		if (! strlen($this->mensaje)) {
			throw new Exception(I18n::transu('user.mensaje_demasiado_corto'), 500);
		} elseif (! in_array($this->tipo, self::_getTiposPermitidos())) {
			throw new Exception(I18n::transu('user.tipo_mensaje_no_permitido'), 500);
		} elseif ($this->_superaTamanoMaximo()) {
			throw new Exception(I18n::transu('user.mensaje_demasiado_grande'), 500);
		}

		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
			INSERT {$wpdb->prefix}" . static::$table . " (user_id, a_quien_id, tipo, respuesta_id, mensaje, created_at, updated_at)
			VALUES (%d, %d, %d, %d, %s, null, null);", $this->user_id, $this->a_quien_id, $this->tipo, $this->respuesta_id, $this->mensaje));
		$this->ID = $wpdb->insert_id;
		return $this;
	}

	/**
	 * Devuelve true si supera el tamaño máximo, y por tanto debería saltar una excepción
	 *
	 * @return boolean
	 */
	private function _superaTamanoMaximo() {
		switch ($this->tipo) {
			case self::TIPO_MENSAJE_PRIVADO :
				return strlen($this->mensaje) > self::TAMANO_MAXIMO_MENSAJE_PRIVADO;
			case self::TIPO_ESTADO :
				return strlen($this->mensaje) > self::TAMANO_MAXIMO_ESTADO;
		}
		return true;
	}

	/**
	 * Devuelve la lista de tipos de mensajes permitidos
	 *
	 * @return array<integer>
	 */
	private function _getTiposPermitidos() {
		return [
			self::TIPO_ESTADO,
			self::TIPO_MENSAJE_PRIVADO
		];
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
ID bigint( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
user_id bigint( 20 ) UNSIGNED NOT NULL,
a_quien_id bigint( 20 ) UNSIGNED,
respuesta_id bigint( 20 ) UNSIGNED,
mensaje MEDIUMTEXT NOT NULL,
estado tinyint NOT NULL default 1,
tipo tinyint NOT NULL,
created_at TIMESTAMP NOT NULL DEFAULT 0,
updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY ( user_id ) REFERENCES wp_users( ID ) ON DELETE SET NULL,
FOREIGN KEY ( a_quien_id ) REFERENCES wp_users( ID ) ON DELETE SET NULL,
FOREIGN KEY ( respuesta_id ) REFERENCES wp_mensajes( ID ) ON DELETE SET NULL,
UNIQUE KEY ( user_id, created_at )
) ENGINE = MYISAM DEFAULT CHARSET = utf8';
		$wpdb->query($query);
	}
}
