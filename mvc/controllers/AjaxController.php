<?php
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
		$nonce = $_POST ['nonce'];
		$post = get_post($post_id);
		$post_title = $post->post_title;

		// Segundo comprobamos si dicho usuario ya le dió alguna vez a me gusta a ese post
		$num = ( int ) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "favoritos
				WHERE post_id = $post_id
				AND user_id = $user_id;");

		// Si no existe, lo creamos
		if (!$num) {
			$result = $wpdb->query($wpdb->prepare("
					INSERT INTO {$wpdb->prefix}favoritos (post_id,user_id,created_at,updated_at)
					VALUES (%d, %d, null, null );", $post_id, $user_id));
		} else {
			//Si ya existe, aumetamos su contador
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}favoritos
			SET count = count + 1
			WHERE post_id = %d
			AND user_id = %d;", $post_id, $user_id));
			// Y modificamos su estado para decir que te gusta
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}favoritos
			SET status =  0
			WHERE post_id = %d
			AND user_id = %d
			AND status = 1;", $post_id, $user_id));
		}

		if (!empty($result)) {
			$json ['code'] = 200;
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'me_gusta' => true,
				'nonce_me_gusta' => $nonce
			]);

			$json ['alert'] = $this->renderAlertaInfo('Te gusta', $post_title);
		} else {
			Utils::debug("crearMeGusta()>Ocurrió un error inesperado");
			$json ['code'] = 504;
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'me_gusta' => false,
				'nonce_me_gusta' => $nonce
			]);
			$json ['alert'] = $this->renderAlertaDanger('Ocurrió un error inesperado');
		}
		$json ['total_me_gustas'] = Utils::getTotalMeGustas(false, $post_id);
		return $json;
	}

	public function editarRevisionBan($estado, $editor_id, $user_id) {
		global $wpdb;
		$nonce = $_POST ['nonce'];
		$mensaje = '?';
		switch ($estado) {
			case Revision::USER_BANEADO :
				$mensaje = Revision::banear($editor_id, $user_id);
				break;
			case Revision::USER_DESBANEADO :
				$mensaje = Revision::desbanear($editor_id, $user_id);
				break;
		}
		$json ['code'] = 200;
		$json ['alert'] = $this->renderAlertaSuccess($mensaje);
		return $json;
	}

	public function editarRevision($estado, $post_id) {
		global $wpdb;
		$nonce = $_POST ['nonce'];
		$mensaje = '?';
		switch ($estado) {
			case Revision::ESTADO_PENDIENTE :
				$mensaje = Revision::pendiente($post_id);
				break;
			case Revision::ESTADO_CORREGIDO :
				$mensaje = Revision::corregir($post_id);
				break;
			case Revision::ESTADO_BORRADO :
				$mensaje = Revision::borrar($post_id);
				break;
		}
		$json ['code'] = 200;
		$json ['alert'] = $this->renderAlertaSuccess($mensaje);
		return $json;
	}

	/**
	 * Crear me gusta
	 */
	public function quitarMeGusta($post_id, $user_id) {
		global $wpdb;
		$nonce = $_POST ['nonce'];
		$post = get_post($post_id);
		$post_title = $post->post_title;

		// Segundo comprobamos si dicho usuario ya le dió alguna vez a me gusta a ese post
		$num = ( int ) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "favoritos
				WHERE status = 0
				AND post_id = $post_id
				AND user_id = $user_id;");
		if ($num) {
			//Si ya existe, aumetamos su contador
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}favoritos
			SET count = count + 1
			WHERE post_id = %d
			AND user_id = %d
			AND status = 0;", $post_id, $user_id));
			// Y modificamos su estado para decir que ya no te gusta
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}favoritos
			SET status =  1
			WHERE post_id = %d
			AND user_id = %d
			AND status = 0;", $post_id, $user_id));
		}
		if (!empty($result)) {
			$json ['code'] = 200;
			$json ['alert'] = $this->renderAlertaInfo('Te dejó de gustar', $post_title);
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'me_gusta' => false,
				'nonce_me_gusta' => $nonce
			]);
		} else {
			$json ['code'] = 504;
			$json ['alert'] = $this->renderAlertaDanger('Ocurrió un error inesperado');
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'me_gusta' => true,
				'nonce_me_gusta' => $nonce
			]);
		}
		$json ['total_me_gustas'] = Utils::getTotalMeGustas(false, $post_id);
		return $json;
	}

	/**
	 *
	 * @param string $submit
	 */
	public static function getJsonBySubmit($submit, $_datos) {
		$ajax = new AjaxController();

		switch ($submit) {
			case Utils::NOTIFICAR :
				$post_id = $_datos ['post'];
				$user_id = $_datos ['user'];
				$json = $ajax->crearNotificacion($post_id, $user_id);
				break;
			case Utils::ME_GUSTA :
				$post_id = $_datos ['post'];
				$user_id = $_datos ['user'];
				$te_gusta = $_datos ['te_gusta'];
				if ($te_gusta == Utils::SI) {
					$json = $ajax->crearMeGusta($post_id, $user_id);
				} else {
					$json = $ajax->quitarMeGusta($post_id, $user_id);
				}
				break;
			case Utils::MOSTRAR_MAS :
				$tipo = $_datos ['tipo'];
				$que = $_datos ['que'];
				$cant = $_datos ['cant'];
				$offset = $_datos ['size'];
				$json = $ajax->mostrarMas($tipo, $que, $cant, $offset);
				break;
			case Utils::REVISION :
				$estado = $_datos ['estado'];
				$post_id = $_datos ['que_id'];
				$json = $ajax->editarRevision($estado, $post_id);
				break;
			case Utils::REVISION_BAN :
				$estado = $_datos ['estado'];
				$user_id = $_datos ['que_id'];
				$editor_id = wp_get_current_user()->ID;
				$json = $ajax->editarRevisionBan($estado, $editor_id, $user_id);
				break;
			default :
				$json = $ajax->renderAlertaDanger('Ocurrió un error inesperado');
		}
		return $json;
	}

}

/**
 * -------------------------------------
 * Controlador para las peticiones AJAX
 * -------------------------------------
 */
$json = [
	'code' => 504 // Error default
];

$submit = $_POST ['submit'];

$nonce = $_POST ['nonce'];

if (in_array($submit, [
	Utils::NOTIFICAR,
	Utils::ME_GUSTA
]) && !Utils::esNonce($nonce, $submit, $_POST ['post'])) {
	die("An unexpected error has ocurred.");
}

$json = AjaxController::getJsonBySubmit($submit, $_POST);

echo json_encode($json);
