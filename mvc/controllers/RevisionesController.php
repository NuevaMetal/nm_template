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
		$listaPendientes = Revision::where('status', '=', Revision::ESTADO_PENDIENTE);
		$listaRevisadas = Revision::where('status', '=', Revision::ESTADO_CORREGIDO);

		$pendientes = self::_parsearRevisiones($listaPendientes, $pendiente = true);
		$revisadas = self::_parsearRevisiones($listaRevisadas, $pendiente = false);

		$content = $this->_render('plugin/revisiones', [
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
		$num = 0;
		foreach ($listaRevisiones as $revision) {
			$post = Post::find($revision->post_id);
			if (isset($revisiones [$post->ID])) {
				continue;
			}
			$_revision = [
				'num' => ++$num,
				'count' => $revision->count,
				'permalink' => $post->getUrl(),
				'post_id' => $post->ID,
				'title' => $post->post_title,
				'pendiente' => $pendiente,
				'estado' => (!$pendiente) ? Revision::ESTADO_PENDIENTE : Revision::ESTADO_CORREGIDO,
				'estado_borrar' => Revision::ESTADO_BORRADO,
				'usuarios' => self::_parsearUsersByRevision($revision, ($pendiente) ? Revision::ESTADO_PENDIENTE : Revision::ESTADO_CORREGIDO)
			];
			$revisiones [$post->ID] = $_revision;
		}
		$revisiones = array_values($revisiones);
		return $revisiones;
	}

	private function _parsearUsersByRevision($revision, $estado = Revision::ESTADO_PENDIENTE) {
		global $wpdb;
		$user_ids = $wpdb->get_results("SELECT user_id, updated_at, count
				FROM {$wpdb->prefix}revisiones
				WHERE post_id = {$revision->post_id}
				AND status = $estado");
		$users = [];
		foreach ($user_ids as $u) {
			$user = User::find($u->user_id);
			$users [] = [
				'user' => $user,
				'updated_at' => $u->updated_at,
				'count' => $u->count
			];
		}
		return $users;
	}

	/**
	 * Pintar el index
	 *
	 * @param unknown $content
	 * @return string
	 */
	public function getBanIndex() {
		$listaBaneos = Revision::allBan();

		$baneos = self::_parsearRevisionesBan($listaBaneos);
		$template_url = get_template_directory_uri();

		$content = $this->_render('plugin/revisiones_ban', [
			'baneos' => $baneos,
			'estado' => Revision::USER_DESBANEADO
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
	}

	private function _parsearRevisionesBan($listaBaneos) {
		$revisiones = [];
		foreach ($listaBaneos as $num => $l) {
			$user = User::find($l->user_id);
			$editor = User::find($l->editor_id);
			$revision = [];
			$revision ['num'] = $num + 1;
			$revision ['user'] = $user;
			$revision ['editor'] = $editor;
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