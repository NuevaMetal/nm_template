<?php

namespace Controllers;

use I18n\I18n;
use Libs\Ajax;
use Libs\Utils;
use Models\Post;
use Models\User;

/**
 * Controlador del autor y su perfil
 *
 * @author chemaclass
 */
class UserController extends BaseController {

	/**
	 * author.php
	 */
	public function getAuthor() {
		$author = get_queried_object();
		$author_id = $author->ID;
		$user = User::find($author_id);
		if (! $user) {
			return $this->getError('404');
		}
		// Comprobar si tiene publicaciones o ha sido bloqueado
		if (! $user || $this->_estaBloqueado($user)) {
			return $this->_getAuthorBloqueado($user);
		}

		$autorCountPosts = $user->getTotalPosts();

		$header = I18n::transu('entradas_de', [
			'nombre' => $user->display_name
		]);
		$args = [
			'user' => $user,
			'ANALITICA_PERFIL_POST_PUBLICADOS_MES' => Ajax::ANALITICA_PERFIL_POST_PUBLICADOS_MES
		];
		$args['posts'] = self::_getArrayPostsAutor($user->ID, 4);
		$args['header'] = "$header ($autorCountPosts " . I18n::trans('entradas') . ')';
		$args['favoritos'] = [
			'a_buscar' => $user->ID,
			'cant' => User::NUM_FAV_PERFIL_DEFAULT,
			'tipo' => Utils::TIPO_AUTHOR_FAV,
			'posts' => $user->getFavoritos(0, User::NUM_FAV_PERFIL_DEFAULT)
		];
		return $this->renderPage('user', $args);
	}

	/**
	 * El autor ha sido bloqueado
	 *
	 * @param User $user
	 *        	Usuario bloqueado
	 */
	private function _getAuthorBloqueado($user) {
		return $this->renderPage('user/bloqueado', [
			'user' => $user
		]);
	}

	/**
	 * Comprobar si el user no tiene perfil
	 *
	 * @param User $user
	 */
	private function _estaBloqueado($user) {
		$current_user = Utils::getCurrentUser();
		return $user->isBloqueado() && (! $current_user || ($current_user && ! $current_user->canEditor()));
	}

	/**
	 * Devuelve un aray con la info de los posts del autor
	 *
	 * @param integer $author_id
	 *        	Identificador del autor
	 * @param number $cant
	 *        	Cantidad de posts a ir mostrando
	 * @return array
	 */
	private static function _getArrayPostsAutor($author_id, $cant = 4) {
		$args = [];
		$args['imagen'] = 'noimage';
		$args['seccion'] = 'autor';
		$args['a_buscar'] = $author_id;
		$args['cant'] = $cant;
		$args['tipo'] = Utils::TIPO_AUTHOR;
		$args['posts'] = HomeController::getPostsByAuthor($author_id, $cant, []);
		return $args;
	}

	/**
	 * Devuelve el Html que pinta lo inputs para las redes sociales
	 *
	 * @param string $user_ID
	 *        	Identificador del User. Por defecto el User actual
	 */
	public function getPerfilRedesSociales($user_ID = false) {
		if (! $user_ID) {
			global $user_ID;
		}
		$user = User::find($user_ID);
		return $this->render('user/editar_perfil/_redes_sociales', [
			'user' => $user,
			'KEY_USER_FACEBOOK' => User::KEY_USER_FACEBOOK,
			'KEY_USER_TWITTER' => User::KEY_USER_TWITTER,
			'KEY_USER_GOOGLE_PLUS' => User::KEY_USER_GOOGLE_PLUS,
			'KEY_USER_YOUTUBE' => User::KEY_USER_YOUTUBE,
			'KEY_USER_SOUNDCLOUD' => User::KEY_USER_SOUNDCLOUD
		]);
	}

	/**
	 * Devuelve la vista del imgHeader para el perfil
	 *
	 * @param integer $user_ID
	 *        	Identficiador del user
	 */
	public function getPerfilImg($keyUserImg = User::KEY_USER_IMG_HEADER, $user_ID = false) {
		if (! $user_ID) {
			global $user_ID;
		}
		$user = User::find($user_ID);
		switch ($keyUserImg) {
			case User::KEY_USER_IMG_HEADER :
				$template = 'user/editar_perfil/_img_header';
				break;
			case User::KEY_USER_IMG_AVATAR :
				$template = 'user/editar_perfil/_img_avatar';
				break;
		}
		return $this->render($template, [
			'user' => $user,
			'KEY_USER_IMG_HEADER' => User::KEY_USER_IMG_HEADER,
			'KEY_USER_IMG_AVATAR' => User::KEY_USER_IMG_AVATAR
		]);
	}

	/**
	 * Devuelve la vista para establecer el tipo de User en el perfil
	 *
	 * @param string $user_ID
	 *        	Identficiador del user
	 */
	public function getPerfilTipoUser($user_ID = false) {
		if (! $user_ID) {
			global $user_ID;
		}
		$user = User::find($user_ID);
		$template_url = get_template_directory_uri();
		// Formateamos los tipos en un array sencillo para pintarlos fácilmente
		foreach (User::getTiposDeUsuarioValidos() as $t) {
			$tipos[] = [
				'value' => $t,
				'texto' => I18n::transu($t),
				'selected' => ($user->getTipo() == $t)
			];
		}
		return $this->render('user/editar_perfil/_tipo_usuario', [
			'user' => $user,
			'KEY_USER_TIPO' => User::KEY_USER_TIPO,
			'tipos' => $tipos,
			'template_url' => $template_url
		]);
	}

	/**
	 * Devuelve la vista para establecer el tipo de User en el perfil
	 *
	 * @param string $user_ID
	 *        	Identficiador del user
	 */
	public function getPerfilIdioma($user_ID = false) {
		if (! $user_ID) {
			global $user_ID;
		}
		$user = User::find($user_ID);
		// Formateamos los tipos en un array sencillo para pintarlos fácilmente
		foreach (I18n::getTodosIdiomasDisponibles() as $t) {
			$idiomas[] = [
				'value' => $t,
				'texto' => I18n::transu('user.' . $t),
				'selected' => (($idioma = $user->getIdioma()) && $idioma == $t)
			];
		}
		return $this->render('user/editar_perfil/_idioma', [
			'user' => $user,
			'KEY_USER_IDIOMA' => User::KEY_USER_IDIOMA,
			'idiomas' => $idiomas
		]);
	}

	/**
	 * Devuelve la vista para establecer el tipo de User en el perfil
	 *
	 * @param string $user_ID
	 *        	Identficiador del user
	 */
	public function getPerfilAdicionalInfo($user_ID = false) {
		if (! $user_ID) {
			global $user_ID;
		}
		$user = User::find($user_ID);

		return $this->render('user/editar_perfil/_adicional_info', [
			'user' => $user,
			'KEY_USER_UBICACION' => User::KEY_USER_UBICACION,
			'KEY_USER_BANDAS_DESTACADAS' => User::KEY_USER_BANDAS_DESTACADAS,
			'KEY_USER_GENEROS_DESTACADOS' => User::KEY_USER_GENEROS_DESTACADOS
		]);
	}

	/**
	 * Ver la actividad de un User
	 */
	public function getActividad() {
		$user = Utils::getCurrentUser();
		if (! $user) {
			return $this->getError('404');
		}

		$posiblesActivo = [
			'actividades' => false,
			'actividades-propias' => false,
			'seguidores' => false,
			'siguiendo' => false,
			'puntos' => false
		];

		$active = $_GET['active'];
		if ($active && in_array($active, array_keys($posiblesActivo))) {
			$posiblesActivo[$active] = true;
		} else {
			// Por defecto estará actividades, la primera pestaña.
			$posiblesActivo['actividades'] = true;
		}

		return $this->renderPage('user/actividad', [
			'conSidebar' => false,
			'user' => $user,
			'activo' => $posiblesActivo
		]);
	}

	/**
	 * Ver los favoritos de un User
	 */
	public function getMensajes() {
		$user = Utils::getCurrentUser();
		if (! $user) {
			return $this->getError('404');
		}
		return $this->renderPage('user/mensajes', [
			'user' => $user
		]);
	}

	/**
	 * Ver los favoritos de un User
	 */
	public function getFavoritos() {
		$user = Utils::getCurrentUser();
		if (! $user) {
			return $this->getError('404');
		}
		return $this->renderPage('user/favoritos', [
			'user' => $user,
			'CATEGORY_BANDAS' => Post::CATEGORY_BANDAS,
			'CATEGORY_CONCIERTOS' => Post::CATEGORY_CONCIERTOS,
			'CATEGORY_CRITICAS' => Post::CATEGORY_CRITICAS,
			'CATEGORY_CRONICAS' => Post::CATEGORY_CRONICAS,
			'CATEGORY_ENTREVISTAS' => Post::CATEGORY_ENTREVISTAS,
			'CATEGORY_NOTICIAS' => Post::CATEGORY_NOTICIAS,
			'CATEGORY_VIDEOS' => Post::CATEGORY_VIDEOS
		]);
	}
}
