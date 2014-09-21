<?php
//namespace Controllers\AnaliticaController;
//use Controllers\BaseController;
require_once 'BaseController.php';
/**
 *
 * @author chema
 */
class UserPendienteController extends BaseController {

	public function getIndex() {
		$listaPendientes = UserPendiente::getByStatus(UserPendiente::PENDIENTE);
		$listaAceptados = UserPendiente::getByStatus(UserPendiente::ACEPTADO);
		$listaRechazados = UserPendiente::getByStatus(UserPendiente::RECHAZADO);
		$content = $this->_render('plugin/users_pendientes', [
			'pendientes' => $listaPendientes,
			'hay_pendientes' => count($listaPendientes) > 0,
			'aceptados' => $listaAceptados,
			'hay_aceptados' => count($listaAceptados) > 0,
			'rechazados' => $listaRechazados,
			'hay_rechazados' => count($listaRechazados) > 0,
			'estado' => Revision::USER_DESBANEADO
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
	}

	/**
	 * Crear las tablas
	 *
	 * @return void
	 */
	public static function install() {
		Utils::debug("> UsersPendientesController->install() ");
		global $wpdb;
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}users_pendientes (
		`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) UNSIGNED NOT NULL,
		`editor_id` bigint(20) UNSIGNED,
		`status` tinyint(1) NOT NULL DEFAULT '0',
		`created_at` TIMESTAMP NOT NULL DEFAULT 0,
		`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`ID`),
		FOREIGN KEY (`user_id`) REFERENCES `wp_users`(`ID`),
		FOREIGN KEY (`editor_id`) REFERENCES `wp_users`(`ID`)
		)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($query);
	}

	/**
	 * Eliminar las tablas
	 *
	 * @return void
	 */
	public static function uninstall() {
		Utils::debug("> UsersPendientesController->uninstall() ");
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}users_pendientes ");
	}

}