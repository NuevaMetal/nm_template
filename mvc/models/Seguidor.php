<?php
/**
 * Interface Seguidor
 *
 * @author chema
 */
interface Seguidor {

	/**
	 *
	 * @param integer $que_id
	 *        	Identificador del modelo a seguir
	 * @param integer $tipo_id
	 *        	Tipo
	 */
	public function sigue($que_id, $tipo_id);
}