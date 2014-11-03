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
			'content' => $this->_render('busqueda', $args)
		]);
	}

	/**
	 * tag.php
	 */
	public function getTag() {
		$current_tag = single_tag_title('', false);
		$term = get_term_by('name', $current_tag, 'post_tag');

		$cant = 4;

		$args['imagen'] = 'noimage';
		$args['seccion'] = 'busqueda';
		$args['a_buscar'] = strtolower($current_tag);
		$args['header'] = I18n::trans('resultado_tag', [
			'que' => $current_tag
		]);
		$args['url'] = get_tag_link($cat);
		$args['cant'] = $cant;
		$args['tipo'] = Utils::TIPO_TAG;
		$args['posts'] = HomeController::getPostsByTag($current_tag, $cant);

		/*
		 * Obtenemos el fichero de idioma 'generos' para obtener el valor de la definición de la tag
		 * apartir de la clave de la tag. Si no se encontrase retornaría un false, y por tanto no
		 * se llegaría a pintar.
		 */
		$fileGeneros = I18n::getFicheroIdioma('generos');

		return $this->_renderPageBase([
			'content' => $this->_render('busqueda/_seccion', [
				'definicion' => $fileGeneros['definicion_' . $term->slug],
				'seccion' => $args,
				'tag_trans' => I18n::transu('generos.' . $term->slug)
			])
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
