<?php
/**
 *
 * @author chemaclass
 *
 */
class I18n {

	// Spanish const
	const LANG_ES = 'es';
	// English const
	const LANG_EN = 'en';

	/**
	 * Devuelve la palabra traducida según el idioma del navegador
	 *
	 * @param string $key
	 *        clave del fichero de idiomas
	 * @return string valor del idioma al que le corresponde dicha clave
	 */
	public static function trans($key, $params = [], $idiomaForzado = false) {
		if ($idiomaForzado && in_array($idiomaForzado, static::_getTodosIdiomasDisponibles())) {
			$lang = $idiomaForzado;
		} else {
			$lang = Utils::getLang();
		}
		Utils::debug("> lang: $lang");
		// Lista con las claves /valor según el idioma
		$langArray = require (dirname(__FILE__) . '/' . $lang . '.php');
		return isset($langArray [$key]) ? $langArray [$key] : $key;
	}

	/**
	 * Devuelve una lista con todos los idiomas disponibles
	 *
	 * @return array<string> Nombre de los ficheros de los idiomas disponibles
	 */
	private static function _getTodosIdiomasDisponibles() {
		return [
			self::LANG_ES,
			self::LANG_EN
		];
	}

}