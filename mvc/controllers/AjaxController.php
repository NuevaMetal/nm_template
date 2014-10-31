<?php
// Cargamos WP.
// Si no se hace, en Ajax no se conocerá y no funcionará ninguna función de WP
require_once dirname(__FILE__) . '/../../../../../wp-load.php';
require_once 'BaseController.php';

/**
 * Controlador del AJAX
 *
 * @author Chemaclass
 */
class AjaxController extends BaseController {

	/*
	 * Miembros
	 */
	public $current_user;
	public $err;
	public $err_sin_permisos;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->current_user = Utils::getCurrentUser();
		$this->err = I18n::transu('error');
		$this->err = I18n::transu('sin_permisos');
	}

	/**
	 * Crear una nueva notificacion de informe de un post en la BBDD
	 *
	 * @return View
	 */
	private function _crearNotificacion($post_id, $user_id) {
		global $wpdb;
		// TODO: refactorizar
		$post = Post::find($post_id);
		$user = User::find($user_id);

		if ($user->isRevisionBan()) {
			return $this->renderAlertaWarning('Usuario baneado.
					Ponte en contacto con los administradores si
					quieres volver a enviar revisiones');
		}
		// Segundo comprobamos si dicho usuario ya notificó sobre dicho post
		// Si no existe, lo creamos
		if (! $user->yaNotificoPost($post_id)) {
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
			return $this->renderAlertaInfo('Ya notificaste esta entrada', $post->post_title);
		}

		if (! empty($result)) {
			return $this->renderAlertaSuccess("Notificación enviada con éxito", $post->post_title);
		}

		return $this->renderAlertaDanger($this->err);
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
	private function _mostrarMas($tipo, $que, $cant, $offset) {
		$homeController = new HomeController();
		$moreQuerySettings['offset'] = $offset;

		/*
		 * Obtenemos los datos
		 */
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
			$posts = $user->getFavoritos($offset, $cant);
		} else if ($tipo == Utils::TIPO_BUSCAR_USUARIOS) {
			$users = User::getUsersBySearch($que, $offset, $cant);
		}
		/*
		 * Pintamos
		 */
		if ($tipo == Utils::TIPO_AUTHOR_FAV) {
			$count = count($posts);
			$content = $this->render('user/perfil/favoritos/_posts', [
				'posts' => $posts,
				'reducido' => ($cant == 2)
			]);
		} elseif ($tipo == Utils::TIPO_BUSCAR_USUARIOS) {
			$count = count($users);
			$content = $this->render('busqueda/_users', [
				'lista_usuarios' => $users
			]);
		} else {
			$count = count($posts);
			$content = $this->render('home/_posts', [
				'posts' => $posts,
				'reducido' => ($cant == 2)
			]);
		}

		// Convierte la codificación a UTF-8
		$content = mb_convert_encoding($content, "UTF-8");

		$json['content'] = $content;
		$json['cant'] = $count;
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
	private function _editarRevisionBan($estado, $editor_id, $user_id) {
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

	/**
	 *
	 * @param unknown $estado
	 * @param unknown $post_id
	 * @return number
	 */
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
			$json['alert'] = $this->renderAlertaDanger($this->err);
			$json['btn'] = $this->render('post/_btn_me_gusta', [
				'isMeGusta' => true,
				'getNonceMeGusta' => $nonce
			]);
		}
		$json['total_me_gustas'] = $post->getTotalMeGustas();
		return $json;
	}

	/**
	 * Atiende a la petición de notificar
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonNotificar($_datos) {
		$post_id = $_datos['post'];
		$user_id = $_datos['user'];
		$json['alerta'] = $this->_crearNotificacion($post_id, $user_id);
		return $json;
	}

	/**
	 * Atiende a la petición de solicitud para ser colaborador
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonSerColaborador($_datos) {
		$user_id = $_datos['user'];
		return $this->solicitarColaborador($user_id);
	}

	/**
	 * Atiende a la petición de un Me Gusta
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonMeGusta($_datos) {
		$post = Post::find($_datos['post']);
		$user = User::find($_datos['user']);
		$te_gusta = $_datos['te_gusta'];
		if ($te_gusta == Utils::SI) {
			return $this->_crearMeGusta($post, $user);
		}
		return $this->_quitarMeGusta($post, $user);
	}

	/**
	 * Atiende a la petición de un Mostrar más
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonMostrarMas($_datos) {
		$tipo = $_datos['tipo'];
		$que = $_datos['que'];
		$cant = $_datos['cant'];
		$offset = $_datos['size'];
		return $this->_mostrarMas($tipo, $que, $cant, $offset);
	}

	/**
	 * Atiende a la petición de revisión de un post
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonRevision($_datos) {
		if (! $this->current_user->canEditor()) {
			return $this->err_sin_permisos;
		}
		$estado = $_datos['estado'];
		$post_id = $_datos['que_id'];
		return $this->_editarRevisionBan($estado, $post_id);
	}

	/**
	 * Atiende a la petición del baneo de un usuario para enviar revisiones de un post
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonRevisionBan($_datos) {
		if (! $this->current_user->canEditor()) {
			return 'No tienes permisos';
		}
		$estado = $_datos['estado'];
		$user_id = $_datos['que_id'];
		$editor_id = $this->current_user->ID;
		return $this->_editarRevisionBan($estado, $editor_id, $user_id);
	}

	/**
	 * Atiende a la petición de la analitica del perfil y sus posts publicados por mes
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonAnaliticaPerfilPostPublicadosMes($_datos) {
		$user_id = $_datos['user'];
		$cant = $_datos['cant'];
		$user = User::find($user_id);
		if (! $user || ! is_numeric($cant)) {
			return [];
		}
		$result = $user->getTotalEntradasPublicadasPorMes($cant);
		$xKey = 'mes';
		$yKeys = [
			'total'
		];
		$labels = [
			'Publicadas'
		];
		return Ajax::jsonParaMorris($result, $xKey, $yKeys, $labels);
	}

	/**
	 * Atiende a la petición del panel de administración rápida para un User
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonAdminPanelUser($_datos) {
		$que = $_datos['que'];
		$user_id = $_datos['user'];
		$user = User::find($user_id);
		// Comprobamos que el user actual sea un editor o admin
		if (! $this->current_user->canEditor() || ($user->isAdmin() && ! $this->current_user->isAdmin())) {
			return $this->err_sin_permisos;
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
				$userBloqueado->editor_id = $this->current_user->ID;
				$userBloqueado->save();
				break;
			case Ajax::DESBLOQUEAR :
				$userBloqueado = new UserBloqueado($user_id);
				$userBloqueado->borrar();
				break;
		}
		return null;
	}

	/**
	 * Atiende a la petición de la home
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonHome($_datos) {
		$nombreSeccion = $_datos['seccion'];
		$cantidad = $_datos['cant'];
		$argsSeccion = HomeController::getSeccion($nombreSeccion, $cantidad);
		$argsSeccion['reducido'] = ($cantidad == 2);
		$json['seccion'] = $this->_render('home/_seccion_contenido', $argsSeccion);
		return $json;
	}

	/**
	 * Atiende a los menus
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonMenu($_datos) {
		$tipoMenu = $_datos['tipo'];
		$menuArgs = [
			'login_url' => wp_login_url('/'),
			'redirect_to' => '/'
		];
		switch ($tipoMenu) {
			case Ajax::MENU_PRINCIPAL :
				$json['menu'] = $this->_render('menu/principal', $menuArgs);
				break;
			case Ajax::MENU_PERFIL :
				$json['menu'] = $this->_render('menu/perfil', $menuArgs);
				break;
			case Ajax::MENU_FOOTER :
				$json['menu'] = $this->_render('menu/footer');
				break;
		}
		return $json;
	}

	/**
	 * Atiende a la petición para acciones de un Post
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonPost($_datos) {
		if (! $this->current_user->canEditor()) {
			return $this->err_sin_permisos;
		}
		$tipo = $_datos['tipo'];
		switch ($tipo) {
			case Comment::BORRAR_COMENTARIO :
				$comment = new Comment($_datos['id']);
				$comment->borrar();
				break;
		}
		break;
	}

	/**
	 * Atiende a la petición para acciones de un User de tipo Seguir
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonUserSeguir($_datos) {
		$aQuienId = $_datos['id'];
		try {
			$ahoraLoSigue = $this->current_user->seguir($aQuienId, $_datos['seguir']);
			$aQuien = User::find($aQuienId);

			$json['code'] = 200;
			if ($ahoraLoSigue) {
				$alert = $this->renderAlertaInfo(I18n::transu('user.ahora_sigues_a', [
					'nombre' => $aQuien->display_name
				]));
			} else {
				$alert = $this->renderAlertaInfo(I18n::transu('user.dejaste_de_seguir_a', [
					'nombre' => $aQuien->display_name
				]));
			}
			$json['cant'] = $aQuien->getTotalSeguidores();
			$json['alert'] = $alert;
			$json['btn'] = $this->_render('user/_btn_seguir_user', [
				'user' => $aQuien
			]);
		} catch ( Exception $e ) {
			$json['code'] = $e->getCode();
			$json['err'] = $this->renderAlertaDanger($e->getMessage());
		}
		return $json;
	}

	/**
	 * Atiende a la petición para acciones de un User de tipo Enviar Mensaje
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonUserEnviarMensaje($_datos) {
		$aQuienId = $_datos['user_id'];
		$respuestaId = $_datos['respuesta_id'];
		try {
			$this->current_user->enviarMensajePrivado($_datos['mensaje'], $aQuienId, $_datos['respuesta_id']);
			$aQuien = User::find($aQuienId);
			if ($respuestaId) {
				$alert = $this->renderAlertaInfo(I18n::transu('actividad.mensaje_respondido_exito', [
					'nombre' => $aQuien->display_name
				]));
			} else {
				$alert = $this->renderAlertaInfo(I18n::transu('actividad.mensaje_enviado_exito', [
					'nombre' => $aQuien->display_name
				]));
			}
			$json['code'] = 200;
		} catch ( Exception $e ) {
			$json['code'] = $e->getCode();
			$alert = $this->renderAlertaDanger($e->getMessage());
		}
		$json['alert'] = $alert;
		return $json;
	}

	/**
	 * Atiende a la petición para acciones de un User de tipo Borrar Mensaje
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonUserBorrarMensaje($_datos) {
		$mensajeId = $_datos['mensaje_id'];
		try {
			$mensaje = new Mensaje();
			$mensaje->ID = $mensajeId;
			$mensaje->borrar();
			$alert = $this->renderAlertaInfo(I18n::transu('user.mensaje_borrado'));
			$json['code'] = 200;
		} catch ( Exception $e ) {
			$json['code'] = $e->getCode();
			$alert = $this->renderAlertaDanger($e->getMessage());
		}
		$json['alert'] = $alert;
		return $json;
	}

	/**
	 * Atiende a la petición para acciones de un User de tipo Actividad
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonUserActividad($_datos) {
		$offset = $_datos['size'];
		$json['code'] = 200;
		switch ($_datos['tipo_id']) {
			case '#actividades' :
				$actividades = $this->current_user->getActividades($offset);
				$content = $this->_render('user/actividad/_actividades', [
					'actividades' => $actividades
				]);
				break;
			case '#actividades-propias' :
				$actividades = $this->current_user->getActividadesPropias($offset);
				$content = $this->_render('user/actividad/_actividades', [
					'actividades' => $actividades
				]);
				break;
			case '#seguidores' :
				$content = $this->_render('user/actividad/_usuarios', [
					'usuarios' => $this->current_user->getSeguidores($offset)
				]);
				break;
			case '#siguiendo' :
				$content = $this->_render('user/actividad/_usuarios', [
					'usuarios' => $this->current_user->getSiguiendo($offset)
				]);
				break;
			default :
				$json['code'] = 500;
		}
		$json['content'] = $content;
		return $json;
	}

	/**
	 * Atiende a la petición para acciones de un User de tipo Actividad
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonUserFavoritos($_datos) {
		$offset = $_datos['size'];
		$json['code'] = 200;
		switch ($_datos['tipo_id']) {
			case '#' . Post::CATEGORY_BANDAS :
				$favoritos = $this->current_user->getFavoritosBandas($offset);
				$content = $this->_render('user/favoritos/_lista', [
					'lista' => $favoritos
				]);
				break;
			case '#' . Post::CATEGORY_VIDEOS :
				$favoritos = $this->current_user->getFavoritosVideos($offset);
				$content = $this->_render('user/favoritos/_lista', [
					'lista' => $favoritos
				]);
				break;
			case '#' . Post::CATEGORY_VIDEOS :
				$favoritos = $this->current_user->getFavoritosVideos($offset);
				$content = $this->_render('user/favoritos/_lista', [
					'lista' => $favoritos
				]);
				break;
			default :
				$json['code'] = 500;
		}
		$json['content'] = $content;
		return $json;
	}

	/**
	 * Atiende a la petición para acciones de un User de tipo Mensajes
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonUserMensajes($_datos) {
		$offset = $_datos['size'];
		$json['code'] = 200;
		switch ($_datos['tipo_id']) {
			case '#recibidos' :
				$mensajes = $this->current_user->getMensajesRecibidos($offset);
				$json['content'] = $this->_render('user/mensajes/_mensajes_recibidos', [
					'getMensajesRecibidos' => $mensajes
				]);
				break;
			case '#enviados' :
				$mensajes = $this->current_user->getMensajesEnviados($offset);
				$json['content'] = $this->_render('user/mensajes/_mensajes_enviados', [
					'getMensajesEnviados' => $mensajes
				]);
				break;
			default :
				$json['code'] = 500;
		}
		return $json;
	}

	/**
	 * Atiende a la petición para acciones de un User
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonUser($_datos) {
		if (! isset($_datos['tipo'])) {
			return $this->err;
		}
		if (! $this->current_user->canEditor()) {
			return $this->err_sin_permisos;
		}
		switch ($_datos['tipo']) {
			case User::SEGUIR :
				return $this->_jsonUserSeguir($_datos);

			case User::ENVIAR_MENSAJE :
				return $this->_jsonUserEnviarMensaje($_datos);

			case User::BORRAR_MENSAJE :
				return $this->_jsonUserBorrarMensaje($_datos);

			case User::ACTIVIDAD :
				return $this->_jsonUserActividad($_datos);

			case User::MENSAJES :
				return $this->_jsonUserMensajes($_datos);

			case User::FAVORITOS :
				return $this->_jsonUserFavoritos($_datos);
		}
		return $json;
	}

	/**
	 *
	 * @param string $submit
	 */
	public function getJsonBySubmit($submit, $_datos) {
		switch ($submit) {
			case Ajax::NOTIFICAR :
				return $this->_jsonNotificar($_datos);

			case Ajax::SER_COLABORADOR :
				return $this->_jsonSerColaborador($_datos);

			case Ajax::ME_GUSTA :
				return $this->_jsonMeGusta($_datos);

			case Ajax::MOSTRAR_MAS :
				return $this->_jsonMostrarMas($_datos);

			case Ajax::REVISION :
				return $this->_jsonRevision($_datos);

			case Ajax::REVISION_BAN :
				return $this->_jsonRevisionBan($_datos);

			case Ajax::ANALITICA_PERFIL_POST_PUBLICADOS_MES :
				return $this->_jsonAnaliticaPerfilPostPublicadosMes($_datos);

			case Ajax::ADMIN_PANEL_USER :
				return $this->_jsonAdminPanelUser($_datos);

			case Ajax::HOME :
				return $this->_jsonHome($_datos);

			case Ajax::MENU :
				return $this->_jsonMenu($_datos);

			case Ajax::POST :
				return $this->_jsonPost($_datos);

			case Ajax::USER :
				return $this->_jsonUser($_datos);

			default :
				$json['alerta'] = $this->renderAlertaDanger($this->err);
				return $json;
		}
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

$ajax = new AjaxController();

/**
 * Comprobar el nonce para peticiones que tengan relacción con un Post
 */
if (in_array($submit, [
	Ajax::NOTIFICAR,
	Ajax::ME_GUSTA
]) && ! Ajax::esNonce($nonce, $submit, $post_id)) {
	die($ajax->err);
}

/**
 * Comprobar el nonce para peticiones que tengan relacción con un User
 */
if ($submit == Ajax::USER && isset($_POST['tipo'])) {
	$tipo = $_POST['tipo'];
	if (in_array($tipo, [
		User::ENVIAR_MENSAJE,
		User::SEGUIR,
		User::BORRAR_MENSAJE
	]) && ! Ajax::esNonce($nonce, $tipo, $ajax->current_user->ID)) {
		die($ajax->err);
	}
}

$json = $ajax->getJsonBySubmit($submit, $_REQUEST);

echo json_encode($json);