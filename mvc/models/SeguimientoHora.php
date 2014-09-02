<?php
require_once 'ModelBase.php';
/**
 * Seguimiento
 *
 * @author chema
 *
 */
class SeguimientoHora extends ModelBase {
	public static $table = "seguimientos_horas";
	public $seguimiento_id;

	public function __construct() {
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
		//TODO: Falta implementar
	}

}
