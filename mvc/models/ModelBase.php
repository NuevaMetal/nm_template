<?php
/**
 * Base abstract para los modelos
 *
 * @author José María Valera Reales <@Chemaclass>
 *
 */
abstract class ModelBase {

	// Columnas de la tabla del modelo
	protected static $columnas = array();
	public $ID;
	public $created_at;
	public $updated_at;

	public function __construct() {
		$this->ID = -1;
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
			$result [] = $a;
		}
		return $result;
	}

	/**
	 * Buscar y devolver el objeto a través de su ID
	 *
	 * @param integer $ID
	 * @return object
	 */
	public static function find($ID = false) {
		if ($ID == null || !is_numeric($ID)) {
			return null;
		}
		global $wpdb;
		$modelo = get_called_class();
		$query = "SELECT *
				FROM {$wpdb->prefix}" . static::$table . "
				WHERE ID = $ID";
		$result = [];
		$object = $wpdb->get_row($query);
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
			return $wpdb->query($query);
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
			return $w [0];
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
					$result [] = $item;
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

	public function __toArray() {
		return call_user_func('get_object_vars', $this);
	}

}