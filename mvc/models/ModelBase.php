<?php
/**
 * Base abstract para los modelos
 *
 * @author José María Valera Reales <@Chemaclass>
 */
abstract class ModelBase {

	// Columnas de la tabla del modelo
	protected static $columnas = array();
	public $ID;
	public $created_at;
	public $updated_at;
	public function __construct($_id = -1) {
		$this->ID = $_id;
		global $wpdb;
		static::$columnas = $wpdb->get_col_info();
	}

	/**
	 * Devuelve todas los objetos
	 *
	 * @return array<Object>
	 */
	public static function all() {
		global $wpdb;
		$modelo = get_called_class();
		$query = "SELECT * FROM {$wpdb->prefix}{$modelo::$table}";
		$queryResults = $wpdb->get_results($query);
		$result = [];
		foreach ($queryResults as $qr) {
			$a = new $modelo();
			foreach (self::$columnas as $c) {
				$a->$c = $qr->$c;
			}
			$result[] = $a;
		}
		return $result;
	}

	/**
	 * Buscar y devolver el objeto a través de su ID
	 *
	 * @param integer $ID
	 * @return object
	 */
	public static function find($ID = false, $pk = 'ID') {
		if ($ID == null || ! is_numeric($ID)) {
			return null;
		}
		global $wpdb;
		$modelo = get_called_class();
		$query = 'SELECT *
				FROM wp_' . static::$table . '
				WHERE ' . $pk . '= %d';
		$result = [];
		$object = $wpdb->get_row($wpdb->prepare($query, $ID));
		$a = new $modelo();
		if ($object) {
			foreach ($object as $c => $val) {
				$a->$c = $val;
			}
		}
		return $a;
	}
	public function delete() {
		if ($this->ID !== false) {
			global $wpdb;
			$modelo = get_called_class();
			$query = "DELETE
					FROM {$wpdb->prefix}" . static::$table . "
					WHERE ID = $this->ID";
			try {
				return $wpdb->query($query);
			} catch ( Exception $e ) {
				return $e;
			}
		}
		return false;
	}

	/**
	 * Devuelve el primer elemento resultante del where
	 *
	 * @param unknown $columna
	 * @param unknown $que
	 * @param unknown $valor
	 */
	public static function first($columna, $que, $valor) {
		$w = self::where($columna, $que, $valor);
		if ($w && is_array($w)) {
			return $w[0];
		}
		return null;
	}

	/**
	 * Devuelve el resultado del filtrado where a todos los elementos de su tabla
	 *
	 * @param string $columna
	 * @param string $que
	 * @param string $valor
	 */
	public static function where($columna, $que, $valor) {
		global $wpdb;

		$all = self::all();
		$result = [];
		foreach ($all as $item) {
			if (isset($item->$columna)) {
				if (self::_getComparacion($item->$columna, $que, $valor)) {
					$result[] = $item;
				}
			}
		}
		return $result;
	}
	private static function _getComparacion($columna, $que, $valor) {
		switch ($que) {
			case "=" :
				if ($columna == $valor) {
					return true;
				}
				return false;
			case "<" :
				if ($columna < $valor) {
					return true;
				}
				return false;
			case ">" :
				if ($columna > $valor) {
					return true;
				}
				return false;
			case ">=" :
				if ($columna >= $valor) {
					return true;
				}
				return false;
			case "<=" :
				if ($columna <= $valor) {
					return true;
				}
				return false;
		}
		return false;
	}

	/**
	 * __toArray
	 *
	 * @return array
	 */
	public function __toArray() {
		return call_user_func('get_object_vars', $this);
	}

	/**
	 * Crear clave Nonce para las peticiones AJAX
	 *
	 * @param string $tipoNonceString
	 *        	Tipo de Nonce a crear
	 * @return string Clave nonce apartir del tipoNonce
	 */
	protected function crearNonce($tipoNonceString) {
		return wp_create_nonce($tipoNonceString . $this->ID);
	}
}