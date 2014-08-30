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
class RevisionesController extends BaseController {

	/**
	 * Pintar el index
	 *
	 * @param unknown $content
	 * @return string
	 */
	public function getIndex() {
		$current_user = wp_get_current_user();
		$listaPendientes = Revision::where('status', '=', Revision::ESTADO_PENDIENTE);
		$listaRevisadas = Revision::where('status', '=', Revision::ESTADO_CORREGIDO);

		$pendientes = self::_parsearRevisiones($listaPendientes, $pendiente = true);
		$revisadas = self::_parsearRevisiones($listaRevisadas, $pendiente = false);
		$content = $this->render('plugin/revisiones', [
			'current_user' => $current_user,
			'pendientes' => [
				'estado' => 'Pendientes',
				'reportes' => $pendientes
			],
			'revisadas' => [
				'estado' => 'Revisadas',
				'reportes' => $revisadas
			]
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
	}

	private function _parsearRevisiones($listaRevisiones, $pendiente) {
		$revisiones = [];
		foreach ($listaRevisiones as $num => $l) {
			$post = get_post($l->post_id);
			$revision = [];
			$revision ['num'] = $num + 1;
			$revision ['permalink'] = get_permalink($post->ID);
			$revision ['post_id'] = $post->ID;
			$revision ['title'] = $post->post_title;
			$revision ['pendiente'] = $pendiente;
			$revision ['usuarios'] = [];
			$revisiones [] = $revision;
		}
		return $revisiones;
	}

	/**
	 * Pintar el index
	 *
	 * @param unknown $content
	 * @return string
	 */
	public function getBanIndex() {
		$current_user = wp_get_current_user();
		$favoritos = Favorito::getFavoritosByUserId($current_user->ID);

		$content = $this->render('plugin/revisiones_ban', [
			'current_user' => $current_user,
			'total' => Utils::getTotalMeGustas($current_user->ID),
			'favoritos' => $favoritos
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
	}

	/**
	 * Creamos las tablas
	 *
	 * @return void
	 */
	public function install() {
		global $wpdb;
		// Create table
		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}revisiones (
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

		// status: 1-pendiente, 2-revisada, 3-borrada
		$wpdb->query($query);

		$query = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}revisiones_ban (
		`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) UNSIGNED NOT NULL,
		`editor_id` bigint(20) UNSIGNED NOT NULL,
		`status` tinyint(1) NOT NULL DEFAULT '1',
		`created_at` TIMESTAMP NOT NULL DEFAULT 0,
		`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`ID`),
		FOREIGN KEY (`user_id`) REFERENCES `wp_users`(`ID`),
		FOREIGN KEY (`editor_id`) REFERENCES `wp_users`(`ID`)
		)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

		// status: 1-pendiente, 2-borrada
		// user_id -> User al que se le banean las revisiones
		// editor_id -> User que banean las revisiones al user_id
		$wpdb->query($query);
	}

	/**
	 * Drop tables
	 *
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}revisiones ");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}revisiones_ban ");
	}

}