<?php
//namespace Controllers\AnaliticaController;
//use Controllers\BaseController;
require_once 'BaseController.php';
require_once 'HomeController.php';
/**
 *
 * @author chemaclass
 *
 */
class FavoritosController extends BaseController {

	/**
	 * Creamos las tablas
	 *
	 * @return void
	 */
	public static function install() {
		Utils::debug("> FavoritosController->install() ");
		global $wpdb;
		// Create table
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}favoritos (
			`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`post_id` bigint(20) UNSIGNED NOT NULL,
			`user_id` bigint(20) UNSIGNED NOT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '0',
			`count` int(10) NOT NULL DEFAULT '1',
			`created_at` TIMESTAMP NOT NULL DEFAULT 0,
			`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`ID`),
			FOREIGN KEY (`post_id`) REFERENCES `wp_posts`(`ID`),
			FOREIGN KEY (`user_id`) REFERENCES `wp_users`(`ID`)
		)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

		// status: 0-activo, 1-inactivo
		$wpdb->query($query);
	}

	/**
	 * Drop tables
	 *
	 * @return void
	 */
	public static function uninstall() {
		Utils::debug("> FavoritosController->uninstall() ");
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}favoritos ");
	}

	/**
	 * Pintar el index
	 *
	 * @param unknown $content
	 * @return string
	 */
	public function getIndex() {
		$current_user = wp_get_current_user();
		$favoritos = Favorito::getFavoritosByUserId($current_user->ID);

		$content = $this->render('plugin/favoritos', [
			'current_user' => $current_user,
			'total' => Utils::getTotalMeGustas($current_user->ID),
			'favoritos' => $favoritos
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
	}

}