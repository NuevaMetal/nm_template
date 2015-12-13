<?php

namespace Models;

/**
 * Base abstract para los modelos
 *
 * @author José María Valera Reales <@Chemaclass>
 */
abstract class ModelBase {

	// Columnas de la tabla del modelo
	protected static $columnas = array();
	// Clave primaria
	protected static $PK = 'ID';

	/*
	 * Miembros
	 */
	public $ID;
	public $created_at;
	public $updated_at;

	/**
	 * Constructor
	 *
	 * @param integer $_id
	 *        	Identificador del modelo
	 */
	public function __construct($ID = 0) {
		$this->ID = $ID;
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
		// Clase hija
		$modelo = get_called_class();
		$queryResults = $wpdb->get_results('SELECT * FROM wp_' . $modelo::$table);
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
	 *        	Identificador del objeto
	 * @param string $pk
	 *        	Clave primaria en la tabla
	 * @return object
	 */
	public static function find($ID = false) {
		if ($ID == null || ! is_numeric($ID)) {
			return null;
		}
		global $wpdb;
		$modelo = get_called_class();
		$query = 'SELECT *
				FROM wp_' . static::$table . '
				WHERE ' . static::$PK . '= %d';
		$object = $wpdb->get_row($wpdb->prepare($query, $ID));
		if (! $object) {
			return null;
		}
		$a = new $modelo();
		if ($object) {
			foreach ($object as $c => $val) {
				$a->$c = $val;
			}
		}
		return $a;
	}

	/**
	 * Buscar todos los valores a partir de una columna
	 *
	 * @param string $columna
	 * @param string $valor
	 * @param boolean $single
	 *        	Por defecto false. True si es sólo 1.
	 * @return ModelBase|ModelBase[]
	 */
	public static function findAllBy($columna, $valor, $single = false) {
		global $wpdb;
		$objects = [];
		$modelo = get_called_class();
		$query = 'SELECT * FROM wp_' . static::$table . ' WHERE ' . $columna . '= %s';
		$resultsQuery = $wpdb->get_results($wpdb->prepare($query, $valor));

		/*
		 * Declaro una función que será la que montará el obj en cuestión.
		 */
		$montarObj = function ($_object) use($modelo) {
			$object = new $modelo();
			foreach ($_object as $column => $val) {
				$object->$column = $val;
			}
			return $object;
		};

		if ($single) {
			foreach ($resultsQuery as $_object) {
				return $montarObj($_object);
			}
		}

		foreach ($resultsQuery as $_object) {
			$objects[] = $montarObj($_object);
		}

		return $objects;
	}

	/**
	 * Hacer un DELETE
	 *
	 * @return Exception|boolean
	 */
	public function delete() {
		if ($this->ID !== false) {
			global $wpdb;
			try {
				return $wpdb->query($wpdb->prepare('
						DELETE FROM wp_' . static::$table . ' WHERE ID = %d', $this->ID));
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

	/**
	 *
	 * @param unknown $columna
	 * @param unknown $que
	 * @param unknown $valor
	 * @return boolean
	 */
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