<?php
require_once 'AlertaController.php';

/**
 * Controlador del AJAX
 *
 * @author Chemaclass
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
		$isBan = (int) $wpdb->get_var('SELECT COUNT(*)
				FROM ' . $wpdb->prefix . "revisiones_ban
				WHERE user_id = $user_id AND status = 1;");
		if ($isBan) {
			return $this->renderAlertaWarning('Usuario baneado.
					Ponte en contacto con los administradores si
					quieres volver a enviar revisiones');
		}
		// Segundo comprobamos si dicho usuario ya notificó sobre dicho post
		$num = (int) $wpdb->get_var('SELECT COUNT(*)
		 	FROM ' . $wpdb->prefix . "revisiones WHERE `status` = 0
			AND post_id = $post_id AND user_id = $user_id;");

		// Si no existe, lo creamos
		if (! $num) {
			$result = $wpdb->query($wpdb->prepare("
INSERT INTO {$wpdb->prefix}revisiones (post_id,user_id,created_at,updated_at)
 VALUES (%d, %d, null, null );", $post_id, $user_id));
		} else {
			// Si ya existe, aumentamos su contador
			$result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}revisiones
		 		SET count = count + 1
		 		WHERE post_id = %d
		 		AND user_id = %d
		 		AND status = 0;", $post_id, $user_id));
			// y notificamos que ya envió una notificación para este post
			return $this->renderAlertaInfo('Ya notificaste esta entrada', $post_title);
		}

		if (! empty($result)) {
			return $this->renderAlertaSuccess("Notificación enviada con éxito", $post_title);
		}

		return $this->renderAlertaDanger('Ocurrió un error inesperado');
	}

	/**
	 * Añadir un User que solicita ser colaborador
	 *
	 * @param integer $user_id
	 *        	Identificador del User
	 */
	public function solicitarColaborador($user_id) {
		$user = User::find($user_id);
		if ($user->isSuscriptor()) {
			$userPendiente = new UserPendiente($user_id);
			$userPendiente->save();
			$json['alerta'] = $this->renderAlertaInfo('Tu petición ha sido enviada.');
		} else {
			$json['alerta'] = $this->renderAlertaInfo('¿No eres un suscriptor?');
		}
		$json['code'] = 200;
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
		$moreQuerySettings['offset'] = $offset;
		if ($tipo == Utils::TIPO_TAG) {
			$posts = $homeController->getPostsByTag($que, $cant, $moreQuerySettings);
		} else if ($tipo == Utils::TIPO_CATEGORY) {
			$posts = $homeController->getPostsByCategory($que, $cant, $moreQuerySettings);
		} else if ($tipo == Utils::TIPO_SEARCH) {
			$posts = $homeController->getPostsBySearch($que, $cant, $moreQuerySettings);
		} else if ($tipo == Utils::TIPO_AUTHOR) {
			$posts = $homeController->getPostsByAuthor($que, $cant, $moreQuerySettings);
		} else if ($tipo == Utils::TIPO_AUTHOR_FAV) {
			$user = User::find($que);
			$posts = $user->getFavoritos($cant, $offset);
		}

		if ($tipo == Utils::TIPO_AUTHOR_FAV) {
			$content = $this->render('autor/_favoritos', [
				'posts' => $posts,
				'reducido' => ($cant == 2)
			]);
		} else {
			$content = $this->render('home/_posts', [
				'posts' => $posts,
				'reducido' => ($cant == 2)
			]);
		}
		// Convierte la codificación a UTF-8
		$content = mb_convert_encoding($content, "UTF-8");

		$json['content'] = $content;
		$json['code'] = 200;

		return $json;
	}

	/**
	 *
	 * @param unknown $estado
	 * @param unknown $editor_id
	 * @param unknown $user_id
	 * @return number
	 */
	public function editarRevisionBan($estado, $editor_id, $user_id) {
		global $wpdb;
		$nonce = $_POST['nonce'];
		$mensaje = '?';
		switch ($estado) {
			case Revision::USER_BANEADO :
				$mensaje = Revision::banear($editor_id, $user_id);
				break;
			case Revision::USER_DESBANEADO :
				$mensaje = Revision::desbanear($editor_id, $user_id);
				break;
		}
		$json['code'] = 200;
		$json['alert'] = $this->renderAlertaSuccess($mensaje);
		return $json;
	}
	public function editarRevision($estado, $post_id) {
		global $wpdb;
		$nonce = $_POST['nonce'];
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
		$json['code'] = 200;
		$json['alert'] = $this->renderAlertaSuccess($mensaje);
		return $json;
	}

	/**
	 * Crear me gusta de un Post a un User
	 *
	 * @param Post $post
	 *        	Post que es gustado
	 * @param User $user
	 *        	User al que le gusta el Post
	 * @return Json para el ajax
	 */
	private function _crearMeGusta($post, $user) {
		$result = $post->crearMeGusta($user);

		$nonce = $_POST['nonce'];

		if (! empty($result)) {
			$json['code'] = 200;
			$json['alert'] = $this->renderAlertaInfo('Te gusta', $post->post_title);
			$json['btn'] = $this->render('post/_btn_me_gusta', [
				'isMeGusta' => true,
				'getNonceMeGusta' => $nonce
			]);
			$json['user_que_gusta'] = $this->render('post/sidebar/_user_que_gusta', [
				'user' => $user
			]);
		} else {
			$json['code'] = 504;
			$json['alert'] = $this->renderAlertaDanger('Ocurrió un error inesperado');
			$json['btn'] = $this->render('post/_btn_me_gusta', [
				'isMeGusta' => false,
				'getNonceMeGusta' => $nonce
			]);
		}
		$json['total_me_gustas'] = $post->getTotalMeGustas();
		return $json;
	}

	/**
	 * Quitar me gusta
	 *
	 * @param Post $post
	 * @param User $user
	 * @return Json
	 */
	private function _quitarMeGusta($post, $user) {
		$result = $post->quitarMeGusta($user);
		$nonce = $_POST['nonce'];

		if (! empty($result)) {
			$json['code'] = 200;
			$json['alert'] = $this->renderAlertaInfo('Te dejó de gustar', $post->post_title);
			$json['btn'] = $this->render('post/_btn_me_gusta', [
				'isMeGusta' => false,
				'getNonceMeGusta' => $nonce
			]);
			$json['user_que_gusta'] = [
				'quitar' => true,
				'user' => $user->user_nicename
			];
		} else {
			$json['code'] = 504;
			$json['alert'] = $this->renderAlertaDanger('Ocurrió un error inesperado');
			$json['btn'] = $this->render('post/_btn_me_gusta', [
				'isMeGusta' => true,
				'getNonceMeGusta' => $nonce
			]);
		}
		$json['total_me_gustas'] = $post->getTotalMeGustas();
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
				$post_id = $_datos['post'];
				$user_id = $_datos['user'];
				$json['alerta'] = $ajax->crearNotificacion($post_id, $user_id);
				break;
			case Ajax::SER_COLABORADOR :
				$user_id = $_datos['user'];
				$json = $ajax->solicitarColaborador($user_id);
				break;
			case Ajax::ME_GUSTA :
				$post = Post::find($_datos['post']);
				$user = User::find($_datos['user']);
				$te_gusta = $_datos['te_gusta'];
				if ($te_gusta == Utils::SI) {
					$json = $ajax->_crearMeGusta($post, $user);
				} else {
					$json = $ajax->_quitarMeGusta($post, $user);
				}
				break;
			case Ajax::MOSTRAR_MAS :
				$tipo = $_datos['tipo'];
				$que = $_datos['que'];
				$cant = $_datos['cant'];
				$offset = $_datos['size'];
				$json = $ajax->mostrarMas($tipo, $que, $cant, $offset);
				break;
			case Ajax::REVISION :
				if (! $current_userCanEditor) {
					return 'No tienes permisos';
				}
				$estado = $_datos['estado'];
				$post_id = $_datos['que_id'];
				$json = $ajax->editarRevision($estado, $post_id);
				break;
			case Ajax::REVISION_BAN :
				if (! $current_userCanEditor) {
					return 'No tienes permisos';
				}
				$estado = $_datos['estado'];
				$user_id = $_datos['que_id'];
				$editor_id = wp_get_current_user()->ID;
				$json = $ajax->editarRevisionBan($estado, $editor_id, $user_id);
				break;
			case Ajax::ANALITICA_PERFIL_POST_PUBLICADOS_MES :
				$user_id = $_datos['user'];
				$cant = $_datos['cant'];
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
				$que = $_datos['que'];
				$user_id = $_datos['user'];
				$user = User::find($user_id);
				// Comprobamos que el user actual sea un editor o admin
				if (! $current_userCanEditor || ($user->isAdmin() && ! $current_user->isAdmin())) {
					return 'No tienes permisos';
				}
				switch ($que) {
					case Ajax::QUITAR_HEADER :
						$user->setImgHeader(null);
						break;
					case Ajax::QUITAR_AVATAR :
						$user->setAvatar(null);
						break;
					case Ajax::BLOQUEAR :
						$userBloqueado = new UserBloqueado($user_id);
						$userBloqueado->editor_id = $current_user->ID;
						$userBloqueado->save();
						break;
					case Ajax::DESBLOQUEAR :
						$userBloqueado = new UserBloqueado($user_id);
						$userBloqueado->borrar();
						break;
				}
				break;
			case Ajax::HOME :
				$nombreSeccion = $_datos['seccion'];
				$cantidad = $_datos['cant'];
				$argsSeccion = HomeController::getSeccion($nombreSeccion, $cantidad);
				$argsSeccion['reducido'] = ($cantidad == 2);
				$json['seccion'] = $ajax->_render('home/_seccion_contenido', $argsSeccion);
				break;
			case Ajax::MENU :
				$tipoMenu = $_datos['tipo'];
				$menuArgs = [
					'login_url' => wp_login_url('/'),
					'redirect_to' => '/'
				];
				switch ($tipoMenu) {
					case Ajax::MENU_PRINCIPAL :
						$json['menu'] = $ajax->_render('menu/principal', $menuArgs);
						break;
					case Ajax::MENU_PERFIL :
						$json['menu'] = $ajax->_render('menu/perfil', $menuArgs);
						break;
					case Ajax::MENU_FOOTER :
						$json['menu'] = $ajax->_render('menu/footer');
						break;
				}
				break;
			case Ajax::POST :
				if (! $current_userCanEditor) {
					return 'No tienes permisos';
				}
				$tipo = $_datos['tipo'];
				switch ($tipo) {
					case Comment::BORRAR_COMENTARIO :
						$comment = new Comment($_datos['id']);
						$comment->borrar();
						break;
				}
				break;
			case Ajax::USER :
				if (! $current_user) {
					return 'No tienes permisos';
				}
				switch ($_datos['tipo']) {
					case User::SEGUIR :
						$aQuienId = $_datos['id'];
						try {
							$flag = $current_user->seguir($aQuienId, $_datos['seguir']);
							$aQuien = User::find($aQuienId);

							$json['code'] = 200;
							if ($flag) {
								$alert = $ajax->renderAlertaInfo(I18n::transu('user.ahora_sigues_a', [
									'nombre' => $aQuien->display_name
								]));
							} else {
								$alert = $ajax->renderAlertaInfo(I18n::transu('user.dejaste_de_seguir_a', [
									'nombre' => $aQuien->display_name
								]));
							}
							$json['alert'] = $alert;
							$json['btn'] = $ajax->_render('autor/_btn_seguir', [
								'user' => $aQuien
							]);
						} catch ( Exception $e ) {
							$json['code'] = $e->getCode();
							$json['err'] = $ajax->renderAlertaDanger($e->getMessage());
						}
						break;
				}
				break;
			default :
				$json['alerta'] = $ajax->renderAlertaDanger('Ocurrió un error inesperado');
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
	'code' => 504
]; // Error default

$submit = $_REQUEST['submit'];
$nonce = $_POST['nonce'];
$post_id = $_POST['post'];

if (in_array($submit, [
	Ajax::NOTIFICAR,
	Ajax::ME_GUSTA
]) && ! Ajax::esNonce($nonce, $submit, $post_id)) {
	die("An unexpected error has ocurred.");
}

$json = AjaxController::getJsonBySubmit($submit, $_REQUEST);

echo json_encode($json);
