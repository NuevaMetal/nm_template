<?php
//namespace Controllers\AnaliticaController;
//use Controllers\BaseController;
require_once 'BaseController.php';
require_once 'HomeController.php';
/**
 * Controlador principal de la web
 *
 * @author chemaclass
 *
 */
class AnaliticaController extends BaseController {

	/**
	 * Create table and register an option when activate
	 *
	 * @return void
	 */
	public static function install() {
		Utils::debug("> AnaliticaController->install() ");
		global $wpdb;
		// Create table
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}analiticas (
			`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) UNSIGNED NOT NULL,
			`created_at` TIMESTAMP NOT NULL DEFAULT 0,
			`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`ID`),
			FOREIGN KEY (`user_id`) REFERENCES `wp_users`(`ID`) ON DELETE SET NULL,
			UNIQUE KEY (user_id, created_at)
			)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($query);

		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}seguimientos (
			`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`analitica_id` bigint(20) UNSIGNED NOT NULL,
			`post_id` bigint(20) UNSIGNED NOT NULL,
			`total` int(10) NOT NULL DEFAULT '1',
			`ip` varchar(45) NOT NULL,
			`created_at` TIMESTAMP NOT NULL DEFAULT 0,
			`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`ID`),
			FOREIGN KEY (`analitica_id`) REFERENCES `wp_users`(`ID`) ON DELETE CASCADE,
			FOREIGN KEY (`post_id`) REFERENCES `wp_posts`(`ID`) ON DELETE SET NULL,
			UNIQUE KEY (analitica_id, post_id)
			)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($query);
	}

	/**
	 * Drop tables
	 *
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}seguimientos ");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}analiticas ");
	}

	/**
	 * Pintar el index
	 *
	 * @param unknown $content
	 * @return string
	 */
	public function getIndex() {
		$content = $this->render('plugin/analitica', [
			'analiticas' => Analitica::all()
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
	}

}
