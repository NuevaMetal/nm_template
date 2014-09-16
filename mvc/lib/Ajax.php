<?php
class Ajax {

	const ANALITICA_PERFIL_POST_PUBLICADOS_MES = 'analitica-perfil-post-publicados-mes';

	const ME_GUSTA = "me-gusta";

	const NOTIFICAR = "notificar";

	const MOSTRAR_MAS = "mostrar-mas";

	const REVISION = "revision";

	const REVISION_BAN = "revision-ban";

	const QUITAR_AVATAR = 'quitar-avatar';

	const QUITAR_HEADER = 'quitar-header';

	const BLOQUEAR = 'bloquear';

	const ADMIN_PANEL_USER = 'admin-panel-user';

	/**
	 * JSON para Morris
	 */
	public static function jsonParaMorris($result, $xKey, $yKeys, $labels) {
		$json = [
			'data' => $result,
			'xkey' => $xKey,
			'ykeys' => $yKeys,
			'labels' => $labels
		];
		return $json;
	}

	/**
	 * Crear clave Nonce para las peticiones AJAX
	 *
	 * @param string $tipoNonceString
	 *        Tipo de Nonce a crear
	 * @param string $post_id
	 *        Identificador del post
	 * @return string Clave nonce apartir del tipoNonce + post_id
	 */
	public static function crearNonce($tipoNonceString, $post_id = false) {
		if (!$post_id) {
			global $post;
			$post_id = $post->ID;
		}
		return wp_create_nonce($tipoNonceString . $post_id);
	}

	/**
	 * Comprueba la clave Nonce para las peticiones AJAX
	 *
	 * @param string $nonce
	 *        Clave a comparar
	 * @param string $tipoNonceString
	 *        Tipo de Nonce creado
	 * @param string $post_id
	 *        Identificador del post
	 */
	public static function esNonce($nonce, $tipoNonceString, $post_id = false) {
		if (!$post_id) {
			global $post;
			$post_id = $post->ID;
		}
		return wp_verify_nonce($nonce, $tipoNonceString . $post_id);
	}

}