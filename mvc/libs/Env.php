<?php

namespace Libs;

/**
 * Clase que encargada de gestionar el entorno
 *
 * @author chema
 */
class Env {

	/**
	 * Devuelve true si estamos en el entorno de producción
	 *
	 * @return boolean
	 */
	public static function isProduccion() {
		$SERVER_NAME = $_SERVER['SERVER_NAME'];
		return ($SERVER_NAME == URL_PRODUCCION);
	}

	/**
	 * Devuelve true si estamos en el entorno de producción
	 *
	 * @return boolean
	 */
	public static function isDesarrollo() {
		$SERVER_NAME = $_SERVER['SERVER_NAME'];
		return ($SERVER_NAME == URL_DESARROLLO);
	}

	/**
	 * Devuelve true si estamos en el entorno de producción
	 *
	 * @return boolean
	 */
	public static function isLocal() {
		$SERVER_NAME = $_SERVER['SERVER_NAME'];
		return ($SERVER_NAME == URL_LOCAL);
	}
}