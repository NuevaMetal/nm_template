<?php
//namespace Controllers\AnaliticaController;
//use Controllers\BaseController;
require_once 'BaseController.php';
/**
 *
 * @author chema
 */
class UserBloqueadoController extends BaseController {

	public function getIndex() {
		$listaPendientes = UserBloqueado::getByStatus(UserBloqueado::PENDIENTE);

		$content = $this->_render('plugin/users_bloqueados', [
			'pendientes' => $listaPendientes,
			'hay_pendientes' => count($listaPendientes) > 0,
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
		Utils::debug("> UserBloqueadoController->install() ");
		global $wpdb;
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}users_bloqueados (
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
		Utils::debug("> UserBloqueadoController->uninstall() ");
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}users_bloqueados ");
	}

}