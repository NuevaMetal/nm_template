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

	const ESTADO_BORRADO_DEFINITIVO = 2;

	const LEIDO_SI = 1;

	const LEIDO_NO = 0;

	const TAMANO_MAXIMO_TITULO = 64;

	const TAMANO_MAXIMO_MENSAJE_PRIVADO = 400;

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
	public $leido;
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
			throw new Exception(I18n::transu('actividad.mensaje_demasiado_corto'), 500);
		} elseif (! in_array($this->tipo, self::_getTiposPermitidos())) {
			throw new Exception(I18n::transu('actividad.tipo_mensaje_no_permitido'), 500);
		} elseif ($this->_superaTamanoMaximo()) {
			throw new Exception(I18n::transu('actividad.mensaje_demasiado_grande'), 500);
		}

		global $wpdb;
		$result = $wpdb->query($wpdb->prepare("
			INSERT {$wpdb->prefix}" . static::$table . " (user_id, a_quien_id, tipo, respuesta_id, titulo, mensaje, created_at, updated_at)
			VALUES (%d, %d, %d, %d, %s, %s, null, null);", $this->user_id, $this->a_quien_id, $this->tipo, $this->respuesta_id, $this->titulo, $this->mensaje));
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
			throw new Exception(I18n::transu('actividad.mensaje_no_existe'), 504);
		}
		$result = $wpdb->query($wpdb->prepare("
				UPDATE {$wpdb->prefix}" . static::$table . " SET estado = %d
			WHERE ID = %d;", self::ESTADO_BORRADO, $this->ID));
		return $result;
	}

	/**
	 * Establecer el estado del mensaje a borrado
	 *
	 * @throws Exception
	 * @return Mensaje
	 */
	public function borrarDefinitivo() {
		global $wpdb;
		$existe = $wpdb->get_var($wpdb->prepare('
				SELECT count(*)
				FROM wp_mensajes
				WHERE ID = %d', $this->ID));
		if (! $existe) {
			throw new Exception(I18n::transu('actividad.mensaje_no_existe'), 504);
		}
		$result = $wpdb->query($wpdb->prepare('
				UPDATE wp_mensajes
				SET estado = %d
				WHERE ID = %d', self::ESTADO_BORRADO_DEFINITIVO, $this->ID));
		return $result;
	}

	/**
	 * Sobrescribo el find para actualizar el campo leido del mensaje obtenido
	 *
	 * @param integer $id
	 *        	Identificador del Mensaje
	 * @return return Mensaje
	 */
	public static function find($ID) {
		$current_user = Utils::getCurrentUser();
		global $wpdb;
		$wpdb->query($wpdb->prepare("
				UPDATE {$wpdb->prefix}" . static::$table . "
				SET leido = %d
				WHERE ID = %d
				AND leido = %d
				AND a_quien_id = %d", self::LEIDO_SI, $ID, self::LEIDO_NO, $current_user->ID));
		return parent::find($ID);
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
	 * Devuelve el Mensaje de respuesta
	 *
	 * @return Mensaje Mensaje de respuesta
	 */
	public function getRespuesta() {
		if ($this->respuesta_id) {
			return Mensaje::find($this->respuesta_id);
		}
		return null;
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
titulo VARCHAR(100),
mensaje VARCHAR(1000) NOT NULL,
estado tinyint NOT NULL default 1,
leido tinyint NOT NULL default 0,
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
