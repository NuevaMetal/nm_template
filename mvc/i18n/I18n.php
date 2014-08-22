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

	/**
	 * Devuelve la palabra traducida según el idioma del navegador
	 *
	 * @param string $key
	 *        clave del fichero de idiomas
	 * @return string valor del idioma al que le corresponde dicha clave
	 */
	public static function trans($traducir, $params = [], $idiomaForzado = false) {
		list($file, $key) = explode('.', $traducir);
		if ($idiomaForzado && in_array($idiomaForzado, static::_getTodosIdiomasDisponibles())) {
			$dir = $idiomaForzado;
		} else {
			$dir = Utils::getLang();
		}
		// Si no le pasamos fichero a traducir, sogerá del fichero global
		if (is_null($key)) {
			$key = $file;
			$file = "global";
		}
		// Lista con las claves /valor según el idioma
		$langArray = require (dirname(__FILE__) . "/$dir/$file.php");
		return isset($langArray [$key]) ? $langArray [$key] : $key;
	}

	/**
	 * Devuelve la palabra traducida según el idioma del navegador con la primera letra mayúscula
	 *
	 * @param string $key
	 *        clave del fichero de idiomas
	 * @return string valor del idioma al que le corresponde dicha clave
	 */
	public static function transu($key, $params = [], $idiomaForzado = false) {
		return ucfirst(self::trans($key, $params, $idiomaForzado));
	}

	/**
	 * Devuelve la palabra traducida según el idioma del navegador con la primera letra mayúscula
	 *
	 * @param string $key
	 *        clave del fichero de idiomas
	 * @return string valor del idioma al que le corresponde dicha clave
	 */
	public static function transupper($key, $params = [], $idiomaForzado = false) {
		return strtoupper(self::trans($key, $params, $idiomaForzado));
	}

}