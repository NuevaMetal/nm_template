<?php
// Cargamos WP.
// Si no se hace, en Ajax no se conocerá y no funcionará ninguna función de WP
require_once dirname(__FILE__) . '/../../../../../wp-load.php';
require_once 'BaseController.php';
require_once 'HomeController.php';

/**
 * Controlador de las alertas
 *
 * @author Chemaclass
 *
 */
abstract class AlertaController extends BaseController {

	/**
	 * Crear una alerta
	 *
	 * @param string $tipo
	 *        Tipo de alerta. Será el nombre de la clase que definirá el estilo de la alerta
	 * @param string $mensaje
	 * @param string $strong
	 * @param unknown $args
	 * @return View
	 */
	protected function renderAlerta($tipo, $mensaje, $strong = false, $args = []) {
		$args ['tipo'] = $tipo;
		$args ['mensaje'] = $mensaje;
		$args ['strong'] = $strong;
		return [
			'code' => 200,
			'content' => $this->render('ajax/alerta', $args)
		];
	}

	/**
	 * Crear una alerta de tipo Success
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaSuccess($mensaje, $strong = false) {
		return $this->renderAlerta('success', $mensaje, $strong);
	}

	/**
	 * Crear una alerta de tipo Danger
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	public function renderAlertaDanger($mensaje, $strong = false) {
		return $this->renderAlerta('danger', $mensaje, $strong);
	}

	/**
	 * Crear una alerta de tipo Info
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaInfo($mensaje, $strong = false) {
		return $this->renderAlerta('info', $mensaje, $strong);
	}

	/**
	 * Crear una alerta de tipo Warning
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaWarning($mensaje, $strong = false) {
		return $this->renderAlerta('warning', $mensaje, $strong);
	}

}

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
	public function crearNotificacion($post_id, $user_id) {
		global $wpdb;
		$post = get_post($post_id);
		$post_title = $post->post_title;

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
			return $this->renderAlertaInfo('Ya notificaste esta entrada', $post_title);
		}

		if (!empty($result)) {
			return $this->renderAlertaSuccess("Notificación enviada con éxito", $post_title);
		}

		return $this->renderAlertaDanger('Ocurrió un error inesperado');
	}

	/**
	 * Devuelve una lista de post para mostrar más
	 *
	 * @param string $que
	 * @param integer $max
	 * @param integer $offset
	 * @return array
	 */
	public function mostrarMas($tipo, $que, $cant, $offset) {
		$homeController = new HomeController();
		$offset--; // Quitamos uno por el header
		$moreQuerySettings ['offset'] = $offset;
		if ($tipo == Utils::TIPO_TAG) {
			$bandas = $homeController->getPostsByTag($que, $cant, $moreQuerySettings);
		} else if ($tipo == Utils::TIPO_CATEGORY) {
			$bandas = $homeController->getPostsByCategory($que, $cant, $moreQuerySettings);
		}
		$json ['code'] = 200;

		$json ['content'] = $this->render('home/_posts', [
			'posts' => $bandas,
			'reducido' => ($cant == 2) ? true : false
		]);
		return $json;
	}

	/**
	 * Crear me gusta
	 */
	public function crearMeGusta($post_id, $user_id) {
		global $wpdb;
		$post = get_post($post_id);
		$post_title = $post->post_title;

		// Segundo comprobamos si dicho usuario ya le dió alguna vez a me gusta a ese post
		$num = ( int ) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "me_gustas
				WHERE post_id = $post_id
				AND user_id = $user_id;");

		// Si no existe, lo creamos
		if (!$num) {
			$result = $wpdb->query($wpdb->prepare("
					INSERT INTO {$wpdb->prefix}me_gustas (post_id,user_id,created_at,updated_at)
					VALUES (%d, %d, null, null );", $post_id, $user_id));
		} else {
			//Si ya existe, aumetamos su contador
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}me_gustas
			SET count = count + 1
			WHERE post_id = %d
			AND user_id = %d;", $post_id, $user_id));
			// Y modificamos su estado para decir que te gusta
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}me_gustas
			SET status =  0
			WHERE post_id = %d
			AND user_id = %d
			AND status = 1;", $post_id, $user_id));
		}

		if (!empty($result)) {
			$json ['code'] = 200;
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'me_gusta' => true
			]);
			$json ['alert'] = $this->renderAlertaInfo('Te gusta', $post_title);
		} else {
			Utils::debug("crearMeGusta()>Ocurrió un error inesperado");
			$json ['code'] = 504;
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'me_gusta' => false
			]);
			$json ['alert'] = $this->renderAlertaDanger('Ocurrió un error inesperado');
		}
		return $json;
	}

	/**
	 * Crear me gusta
	 */
	public function quitarMeGusta($post_id, $user_id) {
		global $wpdb;
		$post = get_post($post_id);
		$post_title = $post->post_title;

		// Segundo comprobamos si dicho usuario ya le dió alguna vez a me gusta a ese post
		$num = ( int ) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "me_gustas
				WHERE status = 0
				AND post_id = $post_id
				AND user_id = $user_id;");
		if ($num) {
			//Si ya existe, aumetamos su contador
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}me_gustas
			SET count = count + 1
			WHERE post_id = %d
			AND user_id = %d
			AND status = 0;", $post_id, $user_id));
			// Y modificamos su estado para decir que ya no te gusta
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}me_gustas
			SET status =  1
			WHERE post_id = %d
			AND user_id = %d
			AND status = 0;", $post_id, $user_id));
		}
		if (!empty($result)) {
			$json ['code'] = 200;
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'me_gusta' => false
			]);
			$json ['alert'] = $this->renderAlertaInfo('Te dejó de gustar', $post_title);
		} else {
			Utils::debug("quitarMeGusta()>Ocurrió un error inesperado");
			$json ['code'] = 504;
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'me_gusta' => true
			]);
			$json ['alert'] = $this->renderAlertaDanger('Ocurrió un error inesperado');
		}
		return $json;
	}

}

$json = array();

$ajax = new AjaxController();

switch ($_REQUEST ['submit']) {
	case "notificar" :
		$post_id = $_POST ['post'];
		$user_id = $_POST ['user'];
		$json = $ajax->crearNotificacion($post_id, $user_id);
		break;
	case "me-gusta" :
		$post_id = $_POST ['post'];
		$user_id = $_POST ['user'];
		$te_gusta = $_POST ['te_gusta'];

		if ($te_gusta == "si") {
			$json = $ajax->crearMeGusta($post_id, $user_id);
		} else {
			$json = $ajax->quitarMeGusta($post_id, $user_id);
		}
		break;
	case "mostrar-mas" :
		$tipo = $_REQUEST ['tipo'];
		$que = $_REQUEST ['que'];
		$cant = $_REQUEST ['cant'];
		$offset = $_REQUEST ['size'];

		$json = $ajax->mostrarMas($tipo, $que, $cant, $offset);
		break;
	default :
		$json = $ajax->renderAlertaDanger('Ocurrió un error inesperado');
}

echo json_encode($json);
