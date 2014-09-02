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
		$template_url = get_template_directory_uri();

		$content = $this->render('plugin/revisiones', [
			'current_user' => $current_user,
			'pendientes' => [
				'estado' => 'Pendientes',
				'reportes' => $pendientes,
				'url_accion' => "$template_url/ajax.php"
			],
			'revisadas' => [
				'estado' => 'Revisadas',
				'reportes' => $revisadas,
				'url_accion' => "$template_url/ajax.php"
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
			$revision ['count'] = $l->count;
			$revision ['permalink'] = get_permalink($post->ID);
			$revision ['post_id'] = $post->ID;
			$revision ['title'] = $post->post_title;
			$revision ['pendiente'] = $pendiente;
			$revision ['estado'] = ($pendiente) ? Revision::ESTADO_CORREGIDO : Revision::ESTADO_PENDIENTE;
			$revision ['estado_borrar'] = Revision::ESTADO_BORRADO;
			$revision ['usuarios'] = self::_parsearUsersByRevision($l);
			//dd($revision);
			$revisiones [] = $revision;
		}
		return $revisiones;
	}

	private function _parsearUsersByRevision($revision) {
		$user = get_user_by('id', $revision->user_id);
		//dd($user);
		return [
			'user_id' => $user->ID,
			'user_login' => $user->user_login,
			'user_posts_url' => get_author_posts_url($user->ID),
			'updated_at' => $revision->updated_at,
			'baneado' => Revision::isUserBan($user->ID)
		];
	}

	/**
	 * Pintar el index
	 *
	 * @param unknown $content
	 * @return string
	 */
	public function getBanIndex() {
		$current_user = wp_get_current_user();
		$listaBaneos = Revision::allBan();

		$baneos = self::_parsearRevisionesBan($listaBaneos);
		$template_url = get_template_directory_uri();

		$content = $this->render('plugin/revisiones_ban', [
			'current_user' => $current_user,
			'baneos' => $baneos,
			'url_accion' => "$template_url/ajax.php",
			'estado' => Revision::USER_DESBANEADO
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
	}

	private function _parsearRevisionesBan($listaBaneos) {
		$revisiones = [];
		foreach ($listaBaneos as $num => $l) {
			$user = get_user_by('id', $l->user_id);
			$editor = get_user_by('id', $l->editor_id);
			$revision = [];
			$revision ['num'] = $num + 1;
			$revision ['user_id'] = $user->ID;
			$revision ['user_login'] = $user->user_login;
			$revision ['user_posts_url'] = get_author_posts_url($user->ID);
			$revision ['editor_id'] = $editor->ID;
			$revision ['editor_login'] = $editor->user_login;
			$revision ['editor_posts_url'] = get_author_posts_url($editor->ID);
			$revision ['updated_at'] = $l->updated_at;
			$revisiones [] = $revision;
		}
		return $revisiones;
	}

	/**
	 * Creamos las tablas
	 *
	 * @return void
	 */
	public function install() {
		Utils::debug("> RevisionesController->install() ");
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
		Utils::debug("> RevisionesController->uninstall() ");
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}revisiones ");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}revisiones_ban ");
	}

}