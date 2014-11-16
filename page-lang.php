<?php
use I18n\I18n;

$lang = $_GET['lang'];
$redirect = $_GET['redirect'];

/*
 * Comprobamos que el idioma está disponible
 */
if (in_array($lang, I18n::getTodosIdiomasDisponibles())) {
	session_start();
	$_SESSION[I18n::IDIOMA_SESION] = $lang;
}

/*
 * También nos aseguramos de que la url sea relativa a la web, por tanto que no incluya ninguna url absoluta.
 * En tal caso, será redirigido a la home.
 */
if (! $redirect || strpos($redirect, 'http') !== false || strpos($redirect, 'https') !== false) {
	header("Location: /");
} else {
	header("Location: $redirect");
}
