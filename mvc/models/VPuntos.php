<?php

namespace Models;

/**
 * Puntos
 *
 * @author chema
 */
class VPuntos extends VActividad {

	/*
	 * Miembros
	 */
	private $totalPuntos;
	private $total;
	private $tipoQue;

	public function __construct($tipo, $total){
		$this->tipoQue = $tipo;
		$this->total = $total;
		$this->totalPuntos = $this->total * $this->getPuntosByTipo();
	}

	/**
	 *
	 * @param unknown $a
	 * @return number
	 */
	public function getPuntosByTipo() {
		switch ($this->tipoQue) {
			case VActividad::TIPO_SEGUIMIENTO_USER :
				return VActividad::PUNTOS_TIPO_SEGUIMIENTO_USER;
			case VActividad::TIPO_SEGUIMIENTO_USER_A_TI :
				return VActividad::PUNTOS_TIPO_SEGUIMIENTO_USER_A_TI;
			case VActividad::TIPO_FAVORITOS_RECIBIDOS :
				return VActividad::PUNTOS_TIPO_FAVORITOS_RECIBIDOS;
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
		return $this->totalPuntos;
	}
}