<?php
/**
 *
 * @param unknown $expression
 * @param string $tag
 */
function dd($expression, $tag = "Tag") {
	echo '' . $tag . '<br>';
	var_dump($expression);
	exit();
}

/**
 * Reemplaza todos los acentos por sus equivalentes sin ellos
 *
 * @param string $string
 *        	la cadena a sanear
 * @param boolean $completo
 *        	se encarga de eliminar cualquier caracter extraño
 * @return string $string Texto saneado
 */
function sanearString($string, $completo = false) {
	$string = trim($string);
	$string = str_replace(array(
		'á',
		'à',
		'ä',
		'â',
		'ª',
		'Á',
		'À',
		'Â',
		'Ä'
	), array(
		'a',
		'a',
		'a',
		'a',
		'a',
		'A',
		'A',
		'A',
		'A'
	), $string);
	$string = str_replace(array(
		'é',
		'è',
		'ë',
		'ê',
		'É',
		'È',
		'Ê',
		'Ë'
	), array(
		'e',
		'e',
		'e',
		'e',
		'E',
		'E',
		'E',
		'E'
	), $string);
	$string = str_replace(array(
		'í',
		'ì',
		'ï',
		'î',
		'Í',
		'Ì',
		'Ï',
		'Î'
	), array(
		'i',
		'i',
		'i',
		'i',
		'I',
		'I',
		'I',
		'I'
	), $string);
	$string = str_replace(array(
		'ó',
		'ò',
		'ö',
		'ô',
		'Ó',
		'Ò',
		'Ö',
		'Ô'
	), array(
		'o',
		'o',
		'o',
		'o',
		'O',
		'O',
		'O',
		'O'
	), $string);
	$string = str_replace(array(
		'ú',
		'ù',
		'ü',
		'û',
		'Ú',
		'Ù',
		'Û',
		'Ü'
	), array(
		'u',
		'u',
		'u',
		'u',
		'U',
		'U',
		'U',
		'U'
	), $string);
	$string = str_replace(array(
		'ñ',
		'Ñ',
		'ç',
		'Ç'
	), array(
		'n',
		'N',
		'c',
		'C'
	), $string);
	// Esta parte se encarga de eliminar cualquier caracter extraño
	if ($completo) {
		$string = str_replace(array(
			"\\",
			"¨",
			"º",
			"-",
			"~",
			"#",
			"@",
			"|",
			"!",
			"\"",
			"·",
			"$",
			"%",
			"&",
			"/",
			"(",
			")",
			"?",
			"'",
			"¡",
			"¿",
			"[",
			"^",
			"`",
			"]",
			"+",
			"}",
			"{",
			"¨",
			"´",
			">",
			"< ",
			";",
			",",
			":",
			".",
			" "
		), '', $string);
	}
	return $string;
}