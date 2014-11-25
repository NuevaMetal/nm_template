<?php

namespace I18n;

use Libs\Utils;

/**
 *
 * @author chemaclass
 */
class I18n {

	// Spanish const
	const LANG_ES = 'es';
	// English const
	const LANG_EN = 'en';
	//
	const IDIOMA_SESION = 'idioma-sesion';

	/**
	 * Devuelve una lista con todos los idiomas disponibles
	 *
	 * @return array<string> Nombre de los ficheros de los idiomas disponibles
	 */
	public static function getTodosIdiomasDisponibles() {
		return [
			self::LANG_ES,
			self::LANG_EN
		];
	}

	/**
	 * Devuelve el idioma del usuario actual si lo tuviera.
	 * False en caso contrario
	 *
	 * @return string|boolean Idioma actual del usuario
	 */
	public static function getLangByCurrentUser() {
		session_start();
		// Si se estableció un idioma para la sesión
		if ($_SESSION[self::IDIOMA_SESION]) {
			return $_SESSION[self::IDIOMA_SESION];
		}
		$currentUser = Utils::getCurrentUser();
		// Si el usuario está logueado y tiene establecido el idioma, pondremos su idioma
		if ($currentUser && ($idioma = $currentUser->getIdioma())) {
			return $idioma;
		}
		return false;
	}

	/**
	 * Devuelve la palabra traducida según el idioma del navegador
	 *
	 * @param string $key
	 *        	clave del fichero de idiomas
	 * @return string valor del idioma al que le corresponde dicha clave
	 */
	public static function trans($traducir, $params = [], $idiomaForzado = false) {
		if (! $idiomaForzado) {
			$idiomaForzado = self::getLangByCurrentUser();
		}
		$traducir = strtolower($traducir);
		// Utils::debug("trad: $traducir");
		static::_getParams($traducir, $params);
		if ($idiomaForzado && in_array($idiomaForzado, self::getTodosIdiomasDisponibles())) {
			$dir = $idiomaForzado;
		} else {
			$dir = Utils::getLang();
		}
		list($file, $key) = explode('.', $traducir);
		// Si no le pasamos fichero a traducir, cogerá del fichero global
		if (is_null($key)) {
			$key = $file;
			$file = "global";
		}
		// Lista con las claves/valor según el idioma
		$langArray = self::getFicheroIdioma($file, $dir);
		$key = trim($key);
		$valor = isset($langArray[$key]) ? $langArray[$key] : $key;
		// Utils::debug("trad: $traducir | key: $key | valor: $valor ||");
		if (is_numeric(strpos($valor, ':')) && ! empty($params) && is_array($params)) {
			// Utils::debug("/$dir/$file.php > valor: $valor");
			$valor = static::_setParams($valor, $params);
		}
		// Utils::debug("> valor: $valor");
		return $valor;
	}

	/**
	 * Devuelve el array asociado al fichero de idioma que se le indica por parametros
	 *
	 * @param string $idioma
	 *        	Iniciales del idioma
	 * @param string $fichero
	 *        	Nombre del fichero del idioma
	 */
	public static function getFicheroIdioma($fichero, $idioma = false) {
		if (! $idioma) {
			$idioma = static::getLangByCurrentUser();
			if (! $idioma) {
				$idioma = Utils::getLang();
			}
		}
		return require (dirname(__FILE__) . "/$idioma/$fichero.php");
	}

	/**
	 * Establecer los parámetros al string
	 *
	 * @param string $valor
	 *        	String final
	 * @param arrsy $params
	 *        	Lista de parámetros
	 */
	private static function _setParams($valor, $params) {
		// Utils::debug("> _setParams($valor, $params) ");
		$strFinal = $valor;
		$key = '';
		for($i = 0; $i < strlen($strFinal); $i ++) {
			if ($strFinal[$i] == ':') { // 1º
				$_a = $i + 1;
				for($j = $_a; $j < strlen($strFinal); $j ++) {
					$esUltimo = ($j == strlen($strFinal) - 1);
					if (in_array($strFinal[$j], [
						' ',
						',',
						'\\',
						'\'',
						'"'
					]) || $esUltimo) { // 2º
						$_b = $j;
						$_b = ($esUltimo) ? $_b + 1 : $_b;
						$key = substr($strFinal, $_a, $_b - $_a);
						// Utils::debug("> $esUltimo | key: $key, valor: {$strFinal [$j]}");
						$i = $_b;
						break;
					}
				}

				// Encontramos la key
				if (isset($params[$key])) {
					$langKey = $params[$key];
					$strFinalA = substr($strFinal, 0, $_a - 1);
					$strFinalB = substr($strFinal, $_b);
					$strFinal = $strFinalA . $langKey . $strFinalB;
					// Utils::debug($strFinal);
				}
			}
		}
		return $strFinal;
	}

	/**
	 * Formatear, si fuera necesario, el texto a traducir con sus parámetros
	 *
	 * @param string $traducir
	 *        	Texto a traducir con sus parámetros en forma de "JSON"
	 *        	Se identifica dicho array por estar entre corchetes []
	 *        	y cada clave/valor se separan por ':' y cada elemento por una ','
	 * @param array $params
	 */
	private static function _getParams(&$traducir, &$params) {
		/*
		 * ( Si es un array y está vacío o si es un objeto)
		 * Y ( Si la cadena a traducir tiene '[' donde irían los parámetros )
		 */
		if (((is_array($params) && empty($params)) || is_object($params)) && ($pos = strpos($traducir, '['))) {
			$params = [];
			$_params = $params;
			// +1 y -1 es para quitarle los corchetes '[]'
			$strParams = substr($traducir, $pos + 1, strlen($strParams) - 1);
			$traducir = substr($traducir, 0, $pos);
			// Separamos por una coma los distintos parámetros
			$_params = explode(',', $strParams);
			foreach ($_params as $value) {
				list($k, $v) = explode(':', $value);
				$params[$k] = $v;
			}
		}
	}

	/**
	 * Devuelve la palabra traducida según el idioma del navegador con la primera letra mayúscula
	 *
	 * @param string $key
	 *        	clave del fichero de idiomas
	 * @return string valor del idioma al que le corresponde dicha clave
	 */
	public static function transu($key, $params = [], $idiomaForzado = false) {
		return ucfirst(self::trans($key, $params, $idiomaForzado));
	}

	/**
	 *
	 * @param unknown $key
	 * @param unknown $params
	 * @param unknown $idiomaForzado
	 */
	public static function substr($key, $params = [], $idiomaForzado = false) {
		list($string, $len) = explode(' ', $key);
		Utils::debug("$string, $len");
		if ($len)
			return substr($string, 0, $len);
		else
			return $string;
	}
}