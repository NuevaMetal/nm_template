<?php
require_once 'BaseController.php';
/**
 * Controlador de los seguimientos
 *
 * @author chemaclass
 */
class SeguimientoController extends BaseController {

	/**
	 * Crear las tablas para la analítica
	 *
	 * @return void
	 */
	public static function install() {
		Utils::debug("> SeguimientoController->install() ");
		global $wpdb;
		// Create table
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}seguimientos (
			ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED NOT NULL,
			que_id bigint(20) UNSIGNED NOT NULL,
			tipo_que TINYINT UNSIGNED NOT NULL,
			created_at TIMESTAMP NOT NULL DEFAULT 0,
			updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (ID),
			FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE SET NULL,
			UNIQUE KEY (user_id, created_at)
			)ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$wpdb->query($query);
	}
}