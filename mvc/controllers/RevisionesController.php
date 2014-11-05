<?php
require_once 'BaseController.php';

/**
 *
 * @author chemaclass
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
			if (isset($revisiones[$post->ID])) {
				continue;
			}
			$_revision = [
				'num' => ++ $num,
				'count' => $revision->count,
				'permalink' => $post->getUrl(),
				'post_id' => $post->ID,
				'title' => $post->post_title,
				'pendiente' => $pendiente,
				'estado' => (! $pendiente) ? Revision::ESTADO_PENDIENTE : Revision::ESTADO_CORREGIDO,
				'estado_borrar' => Revision::ESTADO_BORRADO,
				'usuarios' => self::_parsearUsersByRevision($revision, ($pendiente) ? Revision::ESTADO_PENDIENTE : Revision::ESTADO_CORREGIDO)
			];
			$revisiones[$post->ID] = $_revision;
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
			$users[] = [
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
		$content = $this->_render('plugin/revisiones_ban', [
			'baneos' => $baneos,
			'estado' => Revision::USER_DESBANEADO
		]);

		return $this->_renderPageBasePlugin([
			'content' => $content
		]);
	}
}