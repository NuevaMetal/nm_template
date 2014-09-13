<?php
// namespace Controllers\AutorController;
// use Controllers\BaseController;
require_once 'BaseController.php';
/**
 * Controlador del autor y su perfil
 *
 * @author chemaclass
 *
 */
class AutorController extends BaseController {

	/**
	 * author.php
	 */
	public function getAuthor() {
		$author_id = get_the_author_meta('ID');
		$user = User::find($author_id);
		$autorCountPosts = $user->getCountPosts();

		$header = I18n::transu('entradas_de', [
			'nombre' => $user->display_name
		]);

		$args = [
			'user' => $user
		];
		$args ['posts'] = self::_getArrayPostsAutor($author_id, 4);
		$args ['header'] = "$header ($autorCountPosts " . I18n::trans('entradas') . ')';
		$args ['favoritos'] = [
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

	private static function _getArrayPostsAutor($aBuscar, $cant = 4, $args = [], $otherParams = []) {
		$args ['imagen'] = 'noimage';
		$args ['seccion'] = 'autor';
		$args ['a_buscar'] = $aBuscar;
		$args ['cant'] = $cant;
		$args ['tipo'] = Utils::TIPO_AUTHOR;
		$args ['posts'] = HomeController::getPostsByAuthor($aBuscar, $cant, [], $otherParams);
		return $args;
	}

	/**
	 * Devuelve el Html que pinta lo inputs para las redes sociales
	 *
	 * @param string $user_ID
	 *        Identificador del User. Por defecto el User actual
	 */
	public function getPerfilRedesSociales($user_ID = false) {
		if (!$user_ID) {
			global $user_ID;
		}
		$user = User::find($user_ID);
		return $this->render('autor/perfil/_redes_sociales', [
			'user' => $user,
			'KEY_USER_FACEBOOK' => User::KEY_USER_FACEBOOK,
			'KEY_USER_TWITTER' => User::KEY_USER_TWITTER,
			'KEY_USER_GOOGLE_PLUS' => User::KEY_USER_GOOGLE_PLUS
		]);
	}

}
