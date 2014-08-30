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
	public static function find($ID = null) {
		if ($ID == null || !is_numeric($ID)) {
			$ID = -1;
		}
		global $wpdb;
		$modelo = get_called_class();
		$query = "SELECT *
				FROM {$wpdb->prefix}" . static::$table . "
				WHERE ID = $ID";
		$result = [];
		$object = $wpdb->get_row($query);
		$a = new $modelo();
		foreach ($object as $c => $val) {
			$a->$c = $val;
		}
		$result [] = $a;
		return $result;
	}

}