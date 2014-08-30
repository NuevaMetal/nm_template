<?php
require_once 'ModelBase.php';
/**
 * Seguimiento
 *
 * @author chema
 *
 */
class Seguimiento extends ModelBase {
	public static $table = "seguimiento";
	public $analitica_id;
	public $post_id;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Devuelve la analítica asociada al seguimiento
	 *
	 * @return Analitica Analitica del del seguimiento,
	 *         o NULL en caso de no tener analitica_id asociada
	 */
	public function getAnalitica() {
		if ($this->analitica_id == null) {
			return null;
		}
		return Analitica::find($this->analitica_id);
	}

	/**
	 * Buscar y/o crear y devolver el seguimiento
	 *
	 * @param integer $analitica_ID
	 * @return Seguimiento
	 */
	public static function findOrAdd($analitica_ID, $post_ID) {
		$seguimiento = self::findByAnaliticaYPost($analitica_ID, $post_ID);
		if (!$seguimiento) {
			// Si no hay seguimiento crearlo
			$seguimiento = new Seguimiento();
			$seguimiento->analitica_id = $analitica_ID;
			$seguimiento->post_id = $post_ID;
			$seguimiento->save();
		}
		return $seguimiento;
	}

	/**
	 * Buscar un seguimiento a partir de su analítica y post
	 *
	 * @param unknown $analitica_ID
	 * @param unknown $post_ID
	 */
	public static function findByAnaliticaYPost($analitica_ID, $post_ID) {
		if ($analitica_ID == null || !is_numeric($analitica_ID)) {
			$analitica_ID = -1;
		}
		global $wpdb;
		$query = "SELECT *
				FROM $wpdb->prefix" . static::$table . "
				WHERE analitica_id = $analitica_ID
				AND post_id = $post_ID;";
		return $wpdb->get_row($query);
	}

	/**
	 *
	 * @param string $ID
	 * @return NULL
	 */
	public function save() {
		global $wpdb;
		//Comprobamos si existe
		$seguimiento = $wpdb->get_row("SELECT *
				FROM $wpdb->prefix" . static::$table . "
				WHERE ID = {$this->ID}");
		if (is_null($seguimiento)) {
			$seguimiento = self::findByAnaliticaYPost($this->analitica_id, $this->post_id);
		}
		if ($seguimiento) {
			//Si existe actualizamos
			$wpdb->query($wpdb->prepare("
				UPDATE $wpdb->prefix" . static::$table . "
					SET updated_at = now(),total = total + 1
					WHERE ID = %d", $seguimiento->ID));
			$this->ID = $seguimiento->ID;
		} else {
			//Si no existe lo creamos
			$current_ip = $_SERVER ['REMOTE_ADDR'];
			$result = $wpdb->query($wpdb->prepare("
				INSERT INTO $wpdb->prefix" . static::$table . " (analitica_id, post_id, ip, created_at, updated_at)
				VALUES (%d, %d, %s,null, null);", $this->analitica_id, $this->post_id, $current_ip));
			$this->ID = $wpdb->insert_id;
		}
	}

}
