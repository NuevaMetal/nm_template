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
 * Cadena para debug
 *
 * @param string $str
 */
function debug($str) {
	error_log(" DEBUG - " . $str);
}
