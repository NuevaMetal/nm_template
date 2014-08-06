<?php
// Cargamos WP.
// Si no se hace, en Ajax no se conocerá y no funcionará ninguna función de WP
require_once dirname(__FILE__) . '/../../../../../wp-load.php';
require_once 'AlertaController.php';

/**
 * Controlador del AJAX
 *
 * @author Chemaclass
 *
 */
class AjaxController extends AlertaController {

	/**
	 * Crear una nueva notificacion de informe de un post en la BBDD
	 *
	 * @return View
	 */
	public function crearNotificacion() {
		global $wpdb;
		$post_id = $_POST ['post'];
		$user_id = $_POST ['user'];
		$post = get_post($post_id);
		$strong = $post->post_title;

		// Primero comprobamos que el user no esté baneado
		$isBan = ( int ) $wpdb->get_var('SELECT COUNT(*)
				FROM ' . $wpdb->prefix . "revisiones_ban
				WHERE user_id = $user_id AND status = 1;");
		if ($isBan) {
			return $this->renderAlertaWarning('Usuario baneado.
					Ponte en contacto con los administradores si
					quieres volver a enviar revisiones');
		}
		// Segundo comprobamos si dicho usuario ya notificó sobre dicho post
		$num = ( int ) $wpdb->get_var('SELECT COUNT(*)
		 	FROM ' . $wpdb->prefix . "revisiones WHERE `status` = 0
			AND post_id = $post_id AND user_id = $user_id;");

		// Si no existe, lo creamos
		if (!$num) {
			$result = $wpdb->query($wpdb->prepare("
INSERT INTO {$wpdb->prefix}revisiones (post_id,user_id,created_at,updated_at)
 VALUES (%d, %d, null, null );", $post_id, $user_id));
		} else {
			//Si ya existe, aumetamos su contador
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}revisiones
		 		SET count = count + 1
		 		WHERE post_id = %d
		 		AND status = 0;", $post_id));
			// y notificamos que ya envió una notificación para este post
			return $this->renderAlertaInfo('Ya notificaste esta entrada', $strong);
		}

		if (!empty($result)) {
			return $this->renderAlertaSuccess('Notificación enviada con éxito', $strong);
		}

		return $this->renderAlertaDanger('Ocurrió un error inesperado');
	}

	/**
	 * Corregir notificacion
	 */
	public function corregirNotificacion() {
		// TODO: implementar
	}

}

$json = array();

$ajax = new AjaxController();
//dd($_REQUEST);
switch ($_REQUEST ['submit']) {
	case "notificar" :
		$json ['alerta'] = $ajax->crearNotificacion();
		break;
	case "notificar-corregido" :
		$json ['alerta'] = $ajax->corregirNotificacion();
		break;
	default :
		$json ['alerta'] = $ajax->renderAlertaDanger('Ocurrió un error inesperado');
}

echo json_encode($json);
