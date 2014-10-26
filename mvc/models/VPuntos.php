<?php
require_once 'VActividad.php';
/**
 * Puntos
 *
 * @author chema
 */
class VPuntos extends VActividad {

	/*
	 * Miembros
	 */
	public $total;

	/**
	 *
	 * @param unknown $a
	 * @return number
	 */
	public function getPuntosByTipo() {
		switch ($this->tipo_que) {
			case VActividad::TIPO_SEGUIMIENTO_USER :
				return VActividad::PUNTOS_TIPO_SEGUIMIENTO_USER;
			case VActividad::TIPO_ME_GUSTA :
				return VActividad::PUNTOS_TIPO_ME_GUSTA;
			case VActividad::TIPO_NUEVA_ENTRADA :
				return VActividad::PUNTOS_TIPO_NUEVA_ENTRADA;
			case VActividad::TIPO_NUEVO_COMENTARIO :
				return VActividad::PUNTOS_TIPO_NUEVO_COMENTARIO;
			case VActividad::TIPO_ENTRADA_EDITADA :
				return VActividad::PUNTOS_TIPO_ENTRADA_EDITADA;
			default :
				return 0;
		}
	}

	/**
	 *
	 * @param unknown $a
	 * @return number
	 */
	public function getTotalPuntosByTipo() {
		return $this->total * $this->getPuntosByTipo();
	}
}