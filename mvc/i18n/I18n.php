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
	// Constante de idioma
	public static $lang = I18n::LANG_ES;
	// Lista con las claves /valor según el idioma
	public static $langArray;

	/**
	 *
	 * @param unknown $key
	 * @return Ambigous <unknown, multitype:string >
	 */
	public static function trans($key) {
		self::$langArray = require (dirname(__FILE__) . '/' . self::$lang . '.php');
		return isset(self::$langArray [$key]) ? self::$langArray [$key] : $key;
	}

}