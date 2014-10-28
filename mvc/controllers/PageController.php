<?php

// namespace Controllers\PageController;
// use Controllers\BaseController;
require_once 'BaseController.php';
/**
 * Controlador principal de la web
 *
 * @author chemaclass
 */
class PageController extends BaseController {

	/**
	 * index.php
	 */
	public function getIndex() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		return $this->_renderPageBase([
			'content' => $this->render('busqueda/_seccion', [
				'posts' => $posts
			])
		]);
	}

	/**
	 * Ver la actividad de un User
	 */
	public function getActividad() {
		$user = Utils::getCurrentUser();
		return $this->_renderPageBase([
			'content' => $this->_render('user/_actividad', [
				'conSidebar' => false,
				'user' => $user
			])
		]);
	}

	/**
	 * Ver los favoritos de un User
	 */
	public function getFavoritos() {
		$user = Utils::getCurrentUser();
		return $this->_renderPageBase([
			'content' => $this->_render('user/_favoritos', [
				'user' => $user
			])
		]);
	}

	/**
	 * Ver los favoritos de un User
	 */
	public function getMensajes() {
		$user = Utils::getCurrentUser();
		return $this->_renderPageBase([
			'content' => $this->_render('mensajes', [
				'user' => $user
			])
		]);
	}

	/**
	 * Paǵina de sitios de interés
	 */
	public function getAmigas() {
		$content = $this->_render('pages/amigas');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de contacto
	 */
	public function getContacto() {
		$content = $this->_render('pages/contacto');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}
	/**
	 * category.php
	 */
	public function getCategory() {
		$current_category = single_cat_title("", false);
		$current_category = strtolower($current_category);

		$seccion = HomeController::getSeccion($current_category, 4);

		$content = $this->_render('busqueda/_seccion', [
			'seccion' => $seccion
		]);

		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de aviso legal
	 */
	public function getLegal() {
		$content = $this->_render('pages/legal');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * page.php
	 */
	public function getPage() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$content = $this->_renderPage([
			'post' => $posts[0]
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de redes
	 */
	public function getRedes() {
		$content = $this->_render('pages/redes');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de nuevametal
	 */
	public function getMega() {
		$content = $this->_render('pages/mega');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de nuevametal
	 */
	public function getNuevaMetal() {
		$content = $this->_render('pages/nuevametal');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * single.php
	 */
	public function getPost() {
		if (have_posts()) {
			the_post();
			$post = Post::find(get_the_ID());
		}

		if (! isset($post)) {
			return $this->renderPage('404');
		}

		return $this->_renderPageBase([
			'content' => $this->_render('post', [
				'post' => $post
			])
		]);
	}

	/**
	 * search.php
	 */
	public function getSearch() {
		$search_query = get_search_query();
		// $_SESSION['search_query'] = $search_query;
		// Obtenemos los argumentos necesarios para pintarla
		$args = HomeController::getBusqueda($search_query, 4);
		$users = User::getUsersBySearch($search_query, $offset = 0, $limit = 4);
		$args['users'] = [
			'header' => I18n::trans('resultado_busqueda_usuarios', [
				'que' => $search_query
			]),
			'seccion' => 'busqueda-users',
			'a_buscar' => $search_query,
			'tipo' => Utils::TIPO_BUSCAR_USUARIOS,
			'cant' => 4,
			'total_usuarios' => count($users),
			'lista_usuarios' => $users
		];
		return $this->_renderPageBase([
			'content' => $this->_renderBusqueda($args)
		]);
	}

	/**
	 * tag.php
	 */
	public function getTag() {
		$current_tag = single_tag_title("", false);

		$seccion = HomeController::getTags($current_tag, 4);

		$content = $this->_render('busqueda/_seccion', [
			'header' => "Búsqueda por la etiqueta '$current_tag'",
			'seccion' => $seccion
		]);

		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de tutorial
	 */
	public function getTutorial() {
		$content = $this->_render('pages/tutorial');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}
}
