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

		$tagFav = $user->getArrayEtiquetasFavoritas();
		$args = self::_getArrayPostsAutor($author_id, 4);
		$args ['header'] = [
			'user_avatar' => get_avatar($author_id),
			'user_url' => get_the_author_meta('user_url'),
			'display_name' => get_the_author_meta('display_name'),
			'description' => get_the_author_meta('description'),
			'edit_user_link' => ($author_id == wp_get_current_user()->ID) ? get_edit_user_link() : false,
			'header' => "$header ($autorCountPosts " . I18n::trans('entradas') . ')'
		];
		$args ['body'] = [
			'total_fav_dados' => $user->getCountFavoritos(),
			'total_fav_recibidos' => '?',
			'the_tags' => $tagFav,
			'has_tags' => count($tagFav)
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
		$args ['template_url'] = get_template_directory_uri();
		$args ['posts'] = HomeController::getPostsByAuthor($aBuscar, $cant, [], $otherParams);
		return $args;
	}

}
