<?php
require_once 'KeysRequest.php';

/**
 *
 * @author chema
 */
class Ajax {

	const ANALITICA = "analitica";

	const ADMIN_PANEL_USER = 'admin-panel-user';

	const ANALITICA_PERFIL_POST_PUBLICADOS_MES = 'analitica-perfil-post-publicados-mes';

	const BLOQUEAR = 'bloquear';

	const BORRAR_COLABORADOR_PENDIENTE = 'borrar';

	const DESBLOQUEAR = 'desbloquear';

	const HACER_COLABORADOR = 'hacer-colaborador';

	const HACER_PENDIENTE_COLABORADOR = 'hacer-pendiente';

	const HOME = 'home';

	const ME_GUSTA = "me-gusta";

	const MENU = 'menu';

	const MENU_FOOTER = "menu-footer";

	const MENU_PRINCIPAL = "menu-principal";

	const MENU_PERFIL = "menu-perfil";

	const MOSTRAR_MAS = "mostrar-mas";

	const NOTIFICAR = "notificar";

	const POST = "post";

	const RECHAZAR_COLABORADOR = 'rechazar-colaborador';

	const REVISION = "revision";

	const REVISION_BAN = "revision-ban";

	const QUITAR_AVATAR = 'quitar-avatar';

	const QUITAR_HEADER = 'quitar-header';

	const USER = 'user';

	const SER_COLABORADOR = 'ser-colaborador';

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
	 * Comprueba la clave Nonce para las peticiones AJAX
	 *
	 * @param string $nonce
	 *        	Clave a comparar
	 * @param string $tipoNonceString
	 *        	Tipo de Nonce creado
	 * @param string $id
	 *        	Identificador
	 */
	public static function esNonce($nonce, $tipoNonceString, $id) {
		return wp_verify_nonce($nonce, $tipoNonceString . $id);
	}

	/**
	 * Envuelve en un array la respuesta a enviar por ajax
	 *
	 * @param integer $code
	 *        	Código del tipo de mensaje, 1 para ok y 0 para error.
	 * @param string $message
	 *        	Mensaje con el motivo del error.
	 * @param string $content
	 *        	Contenido del resultado de la operación.
	 * @return array
	 */
	public static function envelope($code = 0, $message = 'OK', $content = "") {
		return array(
			'code' => $code,
			'message' => (string) $message,
			'content' => $content
		);
	}

	/**
	 * Respuesta del servidor OK
	 *
	 * @param array|string $content
	 *        	Contenido a enviar
	 */
	public static function responseOK($content = "") {
		return self::envelope(KeysRequest::OK, 'OK', $content);
	}

	/**
	 * Respuesta del servidor Error genérico
	 *
	 * @param string $mensaje
	 *        	Mensaje informando del error
	 */
	public static function responseError($mensaje = "") {
		return self::envelope(KeysRequest::INCORRECTO, $mensaje, "");
	}

	/**
	 * Respuesta del servidor genérico.
	 * Responde con un json
	 *
	 * @param int $codigo
	 *        	Código de respuesta
	 * @param str $mensaje
	 *        	Mensaje de la respuesta
	 * @param array $content
	 *        	Contenido a enviar
	 */
	public static function response($codigo, $mensaje, $content = "") {
		return json_encode(self::envelope($codigo, $mensaje, $content));
	}
}