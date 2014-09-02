<?php
require_once 'ModelBase.php';
/**
 * Seguimiento por Hora
 *
 * @author chema
 *
 */
class SeguimientoHora extends ModelBase {
	public static $table = "seguimientos_horas";
	public $seguimiento_id;

	public function __construct($seguimiento_id = null) {
		$this->seguimiento_id = $seguimiento_id;
		parent::__construct();
	}

	/**
	 * Devuelve el seguimiento asociado
	 *
	 * @return Seguimiento Seguimiento asociado,
	 *         o NULL en caso de no tener analitica_id asociada
	 */
	public function getSeguimiento() {
		if ($this->seguimiento_id == null) {
			return null;
		}
		return Seguimiento::find($this->seguimiento_id);
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
		if ($this->seguimiento_id != null) {
			$result = $wpdb->query($wpdb->prepare("
				INSERT INTO $wpdb->prefix" . static::$table . " (seguimiento_id)
				VALUES (%d);", $this->seguimiento_id));
			$this->ID = $wpdb->insert_id;
		}
	}

}
