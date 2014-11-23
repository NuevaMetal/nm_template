<?php

namespace Controllers;

use I18n\I18n;
use Libs\Utils;
use Libs\Ajax;
use Models\Comment;
use Models\Mensaje;
use Models\Post;
use Models\User;
use Models\UserBloqueado;
use Models\UserPendiente;
use Models\UserBaneado;
use Models\Revision;
use Libs\KeysRequest;
use Models\Analitica;

// Cargamos WP.
// Si no se hace, en Ajax no se conocerá y no funcionará ninguna función de WP
require_once dirname(__FILE__) . '/../../../../../wp-load.php';

/**
 * Controlador del AJAX
 *
 * @author Chemaclass
 */
class AjaxController extends BaseController {

	/*
	 * Miembros
	 */
	public $err;
	public $err_sin_permisos;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->err = I18n::transu('error');
		$this->err_sin_permisos = I18n::transu('sin_permisos');
	}

	/**
	 * Crear una nueva notificacion de informe de un post en la BBDD
	 *
	 * @return View
	 */
	private function _crearNotificacionRevision($post_id, $user_id) {
		global $wpdb;
		// TODO: refactorizar
		$post = Post::find($post_id);
		$user = User::find($user_id);

		if ($user->estaBaneadoDeRevisiones()) {
			return $this->renderAlertaWarning('Usuario baneado para enviar nuevos reportes.
					Ponte en contacto con los administradores si quieres volver a enviar revisiones');
		}
		// Segundo comprobamos si dicho usuario ya notificó sobre dicho post
		// Si no existe, lo creamos
		if (! $user->yaNotificoPost($post_id)) {
			$result = $wpdb->query($wpdb->prepare('
			INSERT INTO wp_revisiones (post_id,user_id,created_at,updated_at)
			 VALUES (%d, %d, null, null )', $post_id, $user_id));
		} else {
			// Si ya existe, aumentamos su contador
			$result = $user->aumentarContadorRevision($post_id);
			// y notificamos que ya envió una notificación para este post
			return $this->renderAlertaInfo('Ya notificaste esta entrada', $post->post_title);
		}

		if (! empty($result)) {
			return $this->renderAlertaSuccess('Notificación enviada con éxito', $post->post_title);
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
		$json['content'] = $content;
		$json['cant'] = $count;
		$json['code'] = 200;
		return $json;
	}

	/**
	 * Atiende a la petición de notificar
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonNotificarRevision($_datos) {
		$post_id = $_datos['post'];
		$user_id = $_datos['user'];
		$json['alerta'] = $this->_crearNotificacionRevision($post_id, $user_id);
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
		$post_id = $_datos['que_id'];
		$mensaje = '?';
		switch ($_datos['estado']) {
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
	 * Atiende a la petición del baneo de un usuario para enviar revisiones de un post
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonRevisionBan($_datos) {
		if (! $this->current_user->canEditor()) {
			return $this->err_sin_permisos;
		}

		$estado = $_datos['estado'];
		$user_id = $_datos['que_id'];
		$user = new UserBaneado();
		$user->user_id = $user_id;
		$user->editor_id = $this->current_user->ID;

		$alert = '?';
		$json['code'] = 200;
		switch ($estado) {
			case UserBaneado::BANEADO :
				if ($user->banearDeLasRevisiones()) {
					$alert = $this->renderAlertaSuccess('Usuario baneado correctamente.');
				} else {
					$alert = $this->renderAlertaWarning('Ocurrió un error inesperado.');
				}
				break;
			case UserBaneado::DESBANEADO :
				if ($user->desbanearDeLasRevisiones()) {
					$alert = $this->renderAlertaSuccess('Usuario desbaneado correctamente.');
				} else {
					$alert = $this->renderAlertaWarning('Ocurrió un error inesperado.');
				}
				break;
		}
		$json['alert'] = $alert;
		return $json;
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
		// Comprobamos que sea el mismo usuario el que quiera cambiarse el header o el avatar.
		// En caso contrario comprobamos si es un editor o admin
		if (! (($que == Ajax::QUITAR_HEADER || $que == Ajax::QUITAR_AVATAR) && $user->ID == $this->current_user->ID)) {
			// Comprobamos que el user actual sea un editor o admin
			if (! $this->current_user->canEditor() || ($user->isAdmin() && ! $this->current_user->isAdmin())) {
				return $this->err_sin_permisos;
			}
		}
		switch ($que) {
			case Ajax::QUITAR_HEADER :
				$user->setHeader(null);
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
		$json['content'] = 'OK';
		return $json;
	}

	/**
	 * Hacer colaborador a un usuario
	 *
	 * @param array $_datos
	 * @return array
	 */
	private function _jsonAdminHacerColaborador($_datos) {
		if (! $this->current_user || ! $this->current_user->canEditor()) {
			return $this->err_sin_permisos;
		}
		$user_id = $_datos['user'];
		$editor_id = $_datos['editor'];
		$que = $_datos['que'];
		$userPendiente = UserPendiente::first('user_id', '=', $user_id);
		if ($que == Ajax::HACER_COLABORADOR) {
			$userPendiente->aceptarPor($editor_id);
		} else if ($que == Ajax::RECHAZAR_COLABORADOR) {
			$userPendiente->rechazarPor($editor_id);
		} else if ($que == Ajax::HACER_PENDIENTE_COLABORADOR) {
			$userPendiente->pendienterPor($editor_id);
		} else if ($que == Ajax::BORRAR_COLABORADOR_PENDIENTE) {
			$userPendiente->delete();
		}
		$json['content'] = 'OK';
		return $json;
	}

	/**
	 * Atiende a la petición de la home
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonHome($_datos) {
		switch ($_datos['tipo']) {
			case 'seccion' :
				$nombreSeccion = $_datos['seccion'];
				$cantidad = $_datos['cant'];
				$argsSeccion = HomeController::getSeccion($nombreSeccion, $cantidad);
				$argsSeccion['reducido'] = ($cantidad == 2);
				$json['seccion'] = $this->render('home/_seccion_contenido', $argsSeccion);
				break;
			case 'carousel' :
				$json['content'] = $this->render('home/_carousel', [
					'postsConMasFavoritos' => Post::getConMasFavoritos(Post::NUM_CAROUSEL_HOME)
				]);
				break;
		}
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
			'login_url' => wp_login_url('/')
		];
		/*
		 * Añadimos parámetros si se pidió el menú principal o el de perfil.
		 */
		if ($tipoMenu == Ajax::MENU_PRINCIPAL || $tipoMenu == Ajax::MENU_LATERAL) {
			// Comprobamos si el usuario está logueado.
			if ($this->current_user->ID > 0) {
				$menuArgs['total_mensajes'] = $this->current_user->getTotalMensajesRecibidosSinLeer();
				$menuArgs['total_favoritos'] = $this->current_user->getTotalFavoritos();
				$menuArgs['total_actividad'] = $this->current_user->getTotalActividades();
				$menuArgs['total_posts'] = $this->current_user->getTotalPosts();

				// Comprobamos que el user tenga permisos de editor
				if ($this->current_user->canEditor()) {
					$menuArgs['total_revisiones'] = Revision::getTotalPendientes();
					$menuArgs['total_bloqueados'] = UserBloqueado::getTotalBloqueados();
					$menuArgs['total_pendientes'] = UserPendiente::getTotalPendientes();
				}
			}
		}
		switch ($tipoMenu) {
			case Ajax::MENU_PRINCIPAL :
				$json['menu'] = $this->render('menu/principal', $menuArgs);
				break;
			case Ajax::MENU_PERFIL :
				$json['menu'] = $this->render('menu/perfil', $menuArgs);
				break;
			case Ajax::MENU_LATERAL :
				$json['menu'] = $this->render('menu/lateral', $menuArgs);
				break;
			case Ajax::MENU_FOOTER :
				$json['menu'] = $this->render('menu/footer');
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
		$json = [];
		$json['code'] = 200;
		switch ($_datos['tipo']) {
			case Comment::BORRAR_COMENTARIO :
				if (! $this->current_user || ! $this->current_user->canEditor()) {
					return $this->err_sin_permisos;
				}
				$comment = new Comment($_datos['id']);
				$comment->borrar();
				$alert = $this->renderAlertaInfo(I18n::trans('comentario_borrado_exito'));
				break;
			case Post::ENTRADAS_SIMILARES :
				$post = Post::find($_datos['post']);
				$content = $this->render('post/sidebar/_similares', [
					'post' => $post
				]);
				break;
			case Post::ENTRADAS_RELACIONADAS :
				$post = Post::find($_datos['post']);
				$content = $this->render('post/sidebar/_relacionadas', [
					'post' => $post
				]);
				break;
			case Ajax::ME_GUSTA :
				$post = Post::find($_datos['post']);
				// El usuario debe estar logueado
				if ($this->current_user && $this->current_user->canSuscriptor()) {

					$teGusta = $this->current_user->meGustaToogle($post);

					$content = $this->render('home/_btn_estrella_me_gusta', [
						'ID' => $post->ID,
						'isMeGusta' => $teGusta,
						'getNonceMeGusta' => $_datos['nonce'],
						'getTotalMeGustas' => $post->getTotalMeGustas()
					]);
					if ($teGusta) {
						$alert = $this->renderAlertaDanger($post->getTitulo(), I18n::transu('post.te_gusta'), $post->getUrl());
						$json['user_que_gusta'] = $this->render('post/sidebar/_user_que_gusta_ajax', [
							'user' => $this->current_user
						]);
					} else {
						$alert = $this->renderAlertaWarning($post->getTitulo(), I18n::transu('post.te_dejo_de_gustar'), $post->getUrl());
						$json['user_que_gusta'] = [
							'quitar' => true,
							'user' => $this->current_user->user_nicename
						];
					}
				} else {
					// Si no está logueado devuelve la vista igual.
					$content = $this->render('home/_btn_estrella_me_gusta', [
						'ID' => $post->ID,
						'isMeGusta' => $teGusta,
						'getNonceMeGusta' => $_datos['nonce'],
						'getTotalMeGustas' => $post->getTotalMeGustas()
					]);
					$alert = $this->renderAlertaDanger(false, I18n::transu('user.login_necesario_para_favorito'));
				}
				break;
		}

		$json['alert'] = $alert;
		$json['content'] = $content;
		return $json;
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
			$aQuien = User::find($aQuienId);
			if (! $aQuien) {
				throw new \Exception('Usuario no existe', 504);
			}
			$ahoraLoSigue = $this->current_user->seguir($aQuienId, $_datos['seguir']);

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
			$json['btn'] = $this->render('user/_btn_seguir_user', [
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
				$alert = $this->renderAlertaInfo(I18n::transu('mensajes.respondido_exito', [
					'nombre' => $aQuien->display_name
				]));
			} else {
				$alert = $this->renderAlertaInfo(I18n::transu('mensajes.enviado_exito', [
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
			switch ($_datos['borrar']) {
				case 'recibido' :
					$mensaje->moverARecibido();
					$alert = $this->renderAlertaInfo(I18n::transu('mensajes.movido_a_recibido'));
					break;
				case 'papelera' :
					$mensaje->borrar();
					$alert = $this->renderAlertaInfo(I18n::transu('mensajes.movido_a_papelera'));
					break;
				case 'definitivo' :
					$mensaje->borrarDefinitivo();
					$alert = $this->renderAlertaInfo(I18n::transu('mensajes.borrado'));
					break;
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
				$content = $this->render('user/actividad/_actividades', [
					'actividades' => $actividades
				]);
				break;
			case '#actividades-propias' :
				$actividades = $this->current_user->getActividadesPropias($offset);
				$content = $this->render('user/actividad/_actividades', [
					'actividades' => $actividades
				]);
				break;
			case '#seguidores' :
				$content = $this->render('user/actividad/_usuarios', [
					'usuarios' => $this->current_user->getSeguidores($offset)
				]);
				break;
			case '#siguiendo' :
				$content = $this->render('user/actividad/_usuarios', [
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
				$content = $this->render('user/favoritos/_lista', [
					'lista' => $favoritos
				]);
				break;
			case '#' . Post::CATEGORY_VIDEOS :
				$favoritos = $this->current_user->getFavoritosVideos($offset);
				$content = $this->render('user/favoritos/_lista', [
					'lista' => $favoritos
				]);
				break;
			case '#' . Post::CATEGORY_ENTREVISTAS :
				$favoritos = $this->current_user->getFavoritosEntrevistas($offset);
				$content = $this->render('user/favoritos/_lista', [
					'lista' => $favoritos
				]);
				break;
			case '#' . Post::CATEGORY_CRITICAS :
				$favoritos = $this->current_user->getFavoritosCriticas($offset);
				$content = $this->render('user/favoritos/_lista', [
					'lista' => $favoritos
				]);
				break;
			case '#' . Post::CATEGORY_CRONICAS :
				$favoritos = $this->current_user->getFavoritosCronicas($offset);
				$content = $this->render('user/favoritos/_lista', [
					'lista' => $favoritos
				]);
				break;
			case '#' . Post::CATEGORY_NOTICIAS :
				$favoritos = $this->current_user->getFavoritosNoticias($offset);
				$content = $this->render('user/favoritos/_lista', [
					'lista' => $favoritos
				]);
				break;
			case '#' . Post::CATEGORY_CONCIERTOS :
				$favoritos = $this->current_user->getFavoritosConciertos($offset);
				$content = $this->render('user/favoritos/_lista', [
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
		$nonceBorrarMensaje = $this->current_user->getNonceBorrarMensaje();
		switch ($_datos['tipo_id']) {
			case '#recibidos' :
				$mensajes = $this->current_user->getMensajesRecibidos($offset);
				$json['content'] = $this->render('user/mensajes/_mensajes_recibidos', [
					'getMensajesRecibidos' => $mensajes,
					'getNonceBorrarMensaje' => $nonceBorrarMensaje
				]);
				break;
			case '#enviados' :
				$mensajes = $this->current_user->getMensajesEnviados($offset);
				$json['content'] = $this->render('user/mensajes/_mensajes_enviados', [
					'getMensajesEnviados' => $mensajes,
					'getNonceBorrarMensaje' => $nonceBorrarMensaje
				]);
				break;
			case '#borrados' :
				$mensajes = $this->current_user->getMensajesBorrados($offset);
				$json['content'] = $this->render('user/mensajes/_mensajes_borrados', [
					'getMensajesBorrados' => $mensajes,
					'getNonceBorrarMensaje' => $nonceBorrarMensaje
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
		return null;
	}

	/**
	 * Atiende a la petición para la analítica de usuarios interna
	 *
	 * @param array $_datos
	 * @return array JSON de respuesta para para JS
	 */
	private function _jsonAnalitica($_datos) {
		if (! $this->current_user || ! $this->current_user->isAdmin()) {
			return $this->err_sin_permisos;
		}
		$tabla = $_datos['tabla'];
		$cant = $_datos['cant'];
		switch ($tabla) {
			case Analitica::TOTAL_USERS :
				$total = Analitica::getTotalRegistrosPorDia($cant);
				$json['data'] = Analitica::formatearDias($total);
				$json['xkey'] = 'dia';
				$json['ykeys'] = [
					'total'
				];
				$json['labels'] = [
					'Usuarios registrados'
				];
				return $json;

			case Analitica::TOTAL_VISITAS_USERS :
				$total = Analitica::getTotalVisitasUsersLogueados($cant);
				$json['data'] = Analitica::formatearDias($total);
				$json['xkey'] = 'dia';
				$json['ykeys'] = [
					'total'
				];
				$json['labels'] = [
					'Usuarios logueados'
				];
				return $json;
		}
	}

	/**
	 *
	 * @param string $submit
	 */
	public function getJsonBySubmit($submit, $_datos) {
		switch ($submit) {
			case Ajax::NOTIFICAR :
				return $this->_jsonNotificarRevision($_datos);

			case Ajax::SER_COLABORADOR :
				return $this->_jsonSerColaborador($_datos);

			case Ajax::MOSTRAR_MAS :
				return $this->_jsonMostrarMas($_datos);

			case Ajax::REVISION :
				return $this->_jsonRevision($_datos);

			case Ajax::REVISION_BAN :
				return $this->_jsonRevisionBan($_datos);

			case Ajax::ANALITICA :
				return $this->_jsonAnalitica($_datos);

			case Ajax::ANALITICA_PERFIL_POST_PUBLICADOS_MES :
				return $this->_jsonAnaliticaPerfilPostPublicadosMes($_datos);

			case Ajax::ADMIN_PANEL_USER :
				return $this->_jsonAdminPanelUser($_datos);

			case Ajax::HACER_COLABORADOR :
				return $this->_jsonAdminHacerColaborador($_datos);

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

	/**
	 * -------------------------------------
	 * Controlador para las peticiones AJAX
	 * -------------------------------------
	 */
	public function main() {
		$json = [
			'code' => 504
		]; // Error default

		$submit = $_REQUEST['submit'];
		$nonce = $_REQUEST['nonce'];
		$post_id = $_REQUEST['post'];

		// Comprobamos que haya algún submit
		if (! $submit) {
			die('');
		}

		/**
		 * Comprobar el nonce para peticiones que tengan relacción con un Post
		 */
		if (in_array($submit, [
			Ajax::NOTIFICAR,
			Ajax::ME_GUSTA
		]) && ! Ajax::esNonce($nonce, $submit, $post_id)) {
			die($this->err);
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
			]) && ! Ajax::esNonce($nonce, $tipo, $this->current_user->ID)) {
				die($this->err);
			}
		}

		/**
		 * Comprobar el nonce para peticiones que tengan relacción con un Post
		 */
		if ($submit == Ajax::POST && isset($_REQUEST['tipo'])) {
			$tipo = $_REQUEST['tipo'];
			if (in_array($tipo, [
				Post::ENTRADAS_SIMILARES,
				Post::ENTRADAS_RELACIONADAS
			]) && ! Ajax::esNonce($nonce, $tipo, $_REQUEST['post'])) {
				die($this->err);
			}
		}

		$json = $this->getJsonBySubmit($submit, $_REQUEST);

		// Convierte la codificación a UTF-8
		$json['content'] = mb_convert_encoding($json['content'], "UTF-8");

		echo json_encode($json);
	}
}

$ajax = new AjaxController();
$ajax->main();