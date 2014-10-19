<?php
// namespace Controllers\AutorController;
// use Controllers\BaseController;
require_once 'BaseController.php';
/**
 * Controlador del autor y su perfil
 *
 * @author chemaclass
 */
class AutorController extends BaseController {

	/**
	 * author.php
	 */
	public function getAuthor() {
		$current_user = Utils::getCurrentUser();

		$author_id = get_the_author_meta('ID');
		$user = User::find($author_id);

		if (! $user) { // si el user no ha publicado nada aún
			return $this->_getAuthorSinPublicaciones();
		} else if ($user->isBloqueado() && (! $current_user || ($current_user && ! $current_user->canEditor()))) {
			// si el user ha sido bloqueado y el user actual no es editor.
			// De ser editor podría ver su perfil para modificar ciertos datos
			// y/o quitarle el bloqueo a dicho usuario
			return $this->_getAuthorBloqueado($user);
		}

		$autorCountPosts = $user->getCountPosts();

		$header = I18n::transu('entradas_de', [
			'nombre' => $user->display_name
		]);
		$args = [
			'user' => $user,
			'current_user' => Utils::getCurrentUser(),
			'ANALITICA_PERFIL_POST_PUBLICADOS_MES' => Ajax::ANALITICA_PERFIL_POST_PUBLICADOS_MES
		];
		$args['posts'] = self::_getArrayPostsAutor($author_id, 4);
		$args['header'] = "$header ($autorCountPosts " . I18n::trans('entradas') . ')';
		$args['favoritos'] = [
			'a_buscar' => $user->ID,
			'cant' => User::NUM_FAV_PERFIL_DEFAULT,
			'tipo' => Utils::TIPO_AUTHOR_FAV,
			'posts' => $user->getFavoritos(User::NUM_FAV_PERFIL_DEFAULT)
		];
		$content = $this->_renderAutor($args);

		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * El autor aún no hizo ninguna publicación
	 */
	private function _getAuthorSinPublicaciones() {
		return $this->_renderPageBase([
			'content' => $this->_render('autor/_sin_publicaciones')
		]);
	}

	/**
	 * El autor ha sido bloqueado
	 *
	 * @param User $user
	 *        	Usuario bloqueado
	 */
	private function _getAuthorBloqueado($user) {
		return $this->_renderPageBase([
			'content' => $this->_render('autor/_bloqueado', [
				'user' => $user
			])
		]);
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
		return $this->render('autor/perfil/_redes_sociales', [
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
				$template = 'autor/perfil/_img_header';
				break;
			case User::KEY_USER_IMG_AVATAR :
				$template = 'autor/perfil/_img_avatar';
				break;
		}
		return $this->_render($template, [
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
		return $this->render('autor/perfil/_tipo_usuario', [
			'user' => $user,
			'KEY_USER_TIPO' => User::KEY_USER_TIPO,
			'tipos' => $tipos,
			'template_url' => $template_url
		]);
	}
}
