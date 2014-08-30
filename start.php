<?php

// --------------------------------------------------------------
// The path to the application directory.
// --------------------------------------------------------------
$paths ['app'] = 'mvc';

// --------------------------------------------------------------
// The path to the application directory.
// --------------------------------------------------------------
$paths ['lib'] = 'mvc/lib';

// --------------------------------------------------------------
// The path to the application directory.
// --------------------------------------------------------------
$paths ['controllers'] = 'mvc/controllers';

// --------------------------------------------------------------
// The path to the i18n directory.
// --------------------------------------------------------------
$paths ['i18n'] = 'mvc/i18n';

// --------------------------------------------------------------
// The path to the i18n directory.
// --------------------------------------------------------------
$paths ['models'] = 'mvc/models';

// --------------------------------------------------------------
// The path to the config directory.
// --------------------------------------------------------------
$paths ['config'] = 'config';

// --------------------------------------------------------------
// The path to the public directory.
// --------------------------------------------------------------
$paths ['public'] = 'public';
// --------------------------------------------------------------
// Change to the current working directory.
// --------------------------------------------------------------
chdir(__DIR__);

// --------------------------------------------------------------
// Define the directory separator for the environment.
// --------------------------------------------------------------
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

// --------------------------------------------------------------
// Define the path to the base directory.
// --------------------------------------------------------------
$GLOBALS ['nm_paths'] ['base'] = __DIR__ . DS;

// --------------------------------------------------------------
// Define each constant if it hasn't been defined.
// --------------------------------------------------------------
foreach ($paths as $name => $path) {
	if (!isset($GLOBALS ['nm_paths'] [$name])) {
		$GLOBALS ['nm_paths'] [$name] = realpath($path) . DS;
	}
}

/**
 * A global path helper function.
 *
 * <code>
 * $storage = path('storage');
 * </code>
 *
 * @param string $path
 * @return string
 */
function path($path) {
	return $GLOBALS ['nm_paths'] [$path];
}

/**
 * A global path setter function.
 *
 * @param string $path
 * @param string $value
 * @return void
 */
function set_path($path, $value) {
	$GLOBALS ['nm_paths'] [$path] = $value;
}
?>