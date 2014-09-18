<?php

// namespace Controllers\PageController;
// use Controllers\BaseController;
require_once 'BaseController.php';
/**
 * Controlador principal de la web
 *
 * @author chemaclass
 *
 */
class PageController extends BaseController {

	/**
	 * index.php
	 */
	public function getIndex() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$content = $this->render('busqueda', [
			'posts' => $posts
		]);

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

		$content = $this->render('busqueda', [
			'seccion' => $seccion
		]);

		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * tag.php
	 */
	public function getTag() {
		$current_tag = single_tag_title("", false);

		$seccion = HomeController::getTags($current_tag, 4);

		$content = $this->render('busqueda', [
			'header' => "Búsqueda por la etiqueta '$current_tag'",
			'seccion' => $seccion
		]);

		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * search.php
	 */
	public function getSearch() {
		$search_query = get_search_query();
		// Obtenemos los argumentos necesarios para pintarla y pintamos el content
		$args = HomeController::getBusqueda($search_query, 4);
		$content = $this->_renderBusqueda($args);
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
			'post' => $posts [0]
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de aviso legal
	 */
	public function getLegal() {
		$content = $this->render('pages/legal');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de contacto
	 */
	public function getContacto() {
		$content = $this->render('pages/contacto');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de redes
	 */
	public function getRedes() {
		$content = $this->render('pages/redes');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de sitios de interés
	 */
	public function getAmigas() {
		$content = $this->render('pages/amigas');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de tutorial
	 */
	public function getTutorial() {
		$content = $this->render('pages/tutorial');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de nuevametal
	 */
	public function getNuevaMetal() {
		$content = $this->render('pages/nuevametal');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * Paǵina de nuevametal
	 */
	public function getMega() {
		$content = $this->render('pages/mega');
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

}
