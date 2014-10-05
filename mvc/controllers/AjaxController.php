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
			//Si ya existe, aumentamos su contador
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}revisiones
		 		SET count = count + 1
		 		WHERE post_id = %d
		 		AND user_id = %d
		 		AND status = 0;", $post_id, $user_id));
			// y notificamos que ya envió una notificación para este post
			return $this->renderAlertaInfo('Ya notificaste esta entrada', $post_title);
		}

		if (!empty($result)) {
			return $this->renderAlertaSuccess("Notificación enviada con éxito", $post_title);
		}

		return $this->renderAlertaDanger('Ocurrió un error inesperado');
	}

	/**
	 * Añadir un User que solicita ser colaborador
	 *
	 * @param integer $user_id
	 *        Identificador del User
	 */
	public function solicitarColaborador($user_id) {
		$user = User::find($user_id);
		if ($user->isSuscriptor()) {
			$userPendiente = new UserPendiente($user_id);
			$userPendiente->save();
			$json ['alerta'] = $this->renderAlertaInfo('Tu petición ha sido enviada.');
		} else {
			$json ['alerta'] = $this->renderAlertaInfo('¿No eres un suscriptor?');
		}
		$json ['code'] = 200;
		return $json;
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
			$posts = $homeController->getPostsByTag($que, $cant, $moreQuerySettings);
		} else if ($tipo == Utils::TIPO_CATEGORY) {
			$otherParams = [];
			if ($que == Utils::CATEGORIA_ENTREVISTAS) {
				$otherParams = [
					'cant_excerpt' => Post::CANT_EXCERPT_ENTREVISTA
				];
			}
			$posts = $homeController->getPostsByCategory($que, $cant, $moreQuerySettings, $otherParams);
		} else if ($tipo == Utils::TIPO_SEARCH) {
			$posts = $homeController->getPostsBySearch($que, $cant, $moreQuerySettings);
		} else if ($tipo == Utils::TIPO_AUTHOR) {
			$posts = $homeController->getPostsByAuthor($que, $cant, $moreQuerySettings);
		} else if ($tipo == Utils::TIPO_AUTHOR_FAV) {
			$user = User::find($que);
			$posts = $user->getFavoritos($cant, $offset);
		}

		foreach ($posts as &$p) {
			$p->title_corto = sanearString($p->title_corto);
			$p->content = sanearString($p->content);
			$p->excerpt = sanearString($p->excerpt);
		}

		if ($tipo == Utils::TIPO_AUTHOR_FAV) {
			$content = $this->render('autor/_favoritos', [
				'posts' => $posts,
				'reducido' => ($cant == 2) ? true : false
			]);
		} else {
			$content = $this->render('home/_posts', [
				'posts' => $posts,
				'reducido' => ($cant == 2) ? true : false
			]);
		}
		if (in_array($que, [
			Utils::CATEGORIA_ENTREVISTAS,
			Utils::CATEGORIA_NOTICIAS,
			Utils::CATEGORIA_CONCIERTOS,
			Utils::CATEGORIA_CRITICAS,
			Utils::CATEGORIA_CRONICAS
		])) {
			$content = utf8_encode($content);
		}

		$json ['content'] = $content;
		$json ['code'] = 200;

		return $json;
	}

	/**
	 * Crear me gusta
	 */
	public function crearMeGusta($post_id, $user_id) {
		global $wpdb;
		$nonce = $_POST ['nonce'];
		$post = Post::find($post_id);

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
			//Si ya existe, aumetamos su contador y modificamos su estado para decir que te gusta
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}favoritos
			SET status =  0, count = count + 1
			WHERE post_id = %d
			AND user_id = %d
			AND status = 1;", $post_id, $user_id));
		}

		if (!empty($result)) {
			$json ['code'] = 200;
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'isMeGusta' => true,
				'getNonceMeGusta' => $nonce
			]);

			$json ['alert'] = $this->renderAlertaInfo('Te gusta', $post_title);
		} else {
			Utils::debug("crearMeGusta()>Ocurrió un error inesperado");
			$json ['code'] = 504;
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'isMeGusta' => false,
				'getNonceMeGusta' => $nonce
			]);
			$json ['alert'] = $this->renderAlertaDanger('Ocurrió un error inesperado');
		}
		$json ['total_me_gustas'] = $post->getCountFavoritos();
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
		$post = Post::find($post_id);
		$post_title = $post->post_title;

		// Segundo comprobamos si dicho usuario ya le dió alguna vez a me gusta a ese post
		$num = ( int ) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "favoritos
				WHERE status = 0
				AND post_id = $post_id
				AND user_id = $user_id;");
		if ($num) {
			//Si ya existe, aumetamos su contador y modificamos su estado para decir que ya no te gusta
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}favoritos
			SET status =  1, count = count + 1
			WHERE post_id = %d
			AND user_id = %d
			AND status = 0;", $post_id, $user_id));
		}
		if (!empty($result)) {
			$json ['code'] = 200;
			$json ['alert'] = $this->renderAlertaInfo('Te dejó de gustar', $post_title);
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'isMeGusta' => false,
				'getNonceMeGusta' => $nonce
			]);
		} else {
			$json ['code'] = 504;
			$json ['alert'] = $this->renderAlertaDanger('Ocurrió un error inesperado');
			$json ['btn'] = $this->render('post/_btn_me_gusta', [
				'isMeGusta' => true,
				'getNonceMeGusta' => $nonce
			]);
		}
		$json ['total_me_gustas'] = $post->getCountFavoritos();
		return $json;
	}

	/**
	 *
	 * @param string $submit
	 */
	public static function getJsonBySubmit($submit, $_datos) {
		$ajax = new AjaxController();
		$current_user = Utils::getCurrentUser();
		$current_userCanEditor = $current_user && $current_user->canEditor();

		switch ($submit) {
			case Ajax::NOTIFICAR :
				$post_id = $_datos ['post'];
				$user_id = $_datos ['user'];
				$json ['alerta'] = $ajax->crearNotificacion($post_id, $user_id);
				break;
			case Ajax::SER_COLABORADOR :
				$user_id = $_datos ['user'];
				$json = $ajax->solicitarColaborador($user_id);
				break;
			case Ajax::ME_GUSTA :
				$post_id = $_datos ['post'];
				$user_id = $_datos ['user'];
				$te_gusta = $_datos ['te_gusta'];
				if ($te_gusta == Utils::SI) {
					$json = $ajax->crearMeGusta($post_id, $user_id);
				} else {
					$json = $ajax->quitarMeGusta($post_id, $user_id);
				}
				break;
			case Ajax::MOSTRAR_MAS :
				$tipo = $_datos ['tipo'];
				$que = $_datos ['que'];
				$cant = $_datos ['cant'];
				$offset = $_datos ['size'];
				$json = $ajax->mostrarMas($tipo, $que, $cant, $offset);
				break;
			case Ajax::REVISION :
				if (!$current_userCanEditor) {
					return 'No tienes permisos';
				}
				$estado = $_datos ['estado'];
				$post_id = $_datos ['que_id'];
				$json = $ajax->editarRevision($estado, $post_id);
				break;
			case Ajax::REVISION_BAN :
				if (!$current_userCanEditor) {
					return 'No tienes permisos';
				}
				$estado = $_datos ['estado'];
				$user_id = $_datos ['que_id'];
				$editor_id = wp_get_current_user()->ID;
				$json = $ajax->editarRevisionBan($estado, $editor_id, $user_id);
				break;
			case Ajax::ANALITICA_PERFIL_POST_PUBLICADOS_MES :
				$user_id = $_datos ['user'];
				$cant = $_datos ['cant'];
				$user = User::find($user_id);
				$result = $user->getTotalEntradasPublicadasPorMes($cant);
				$xKey = 'mes';
				$yKeys = [
					'total'
				];
				$labels = [
					'Publicadas'
				];
				$json = Ajax::jsonParaMorris($result, $xKey, $yKeys, $labels);
				break;
			case Ajax::ADMIN_PANEL_USER :
				$que = $_datos ['que'];
				$user_id = $_datos ['user'];
				$user = User::find($user_id);
				// Comprobamos que el user actual sea un editor o admin y el user no sea un Admin
				if ($current_userCanEditor && !$user->isAdmin()) {
					return 'No tienes permisos';
				}
				switch ($que) {
					case Ajax::QUITAR_HEADER :
						$user->setImgHeader(null);
						$json = Ajax::QUITAR_HEADER;
						break;
					case Ajax::QUITAR_AVATAR :
						$user->setAvatar(null);
						$json = Ajax::QUITAR_AVATAR;
						break;
					case Ajax::BLOQUEAR :
						$userBloqueado = new UserBloqueado($user_id);
						$userBloqueado->editor_id = $current_user->ID;
						$userBloqueado->save();
						$json = Ajax::BLOQUEAR;
						break;
					case Ajax::DESBLOQUEAR :
						$userBloqueado = new UserBloqueado($user_id);
						$userBloqueado->borrar();
						$json = Ajax::BLOQUEAR;
						break;
				}
				break;
			default :
				$json ['alerta'] = $ajax->renderAlertaDanger('Ocurrió un error inesperado');
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

$submit = $_REQUEST ['submit'];
$nonce = $_POST ['nonce'];
$post_id = $_POST ['post'];

if (in_array($submit, [
	Ajax::NOTIFICAR,
	Ajax::ME_GUSTA
]) && !Ajax::esNonce($nonce, $submit, $post_id)) {
	die("An unexpected error has ocurred.");
}

$json = AjaxController::getJsonBySubmit($submit, $_REQUEST);

echo json_encode($json);
