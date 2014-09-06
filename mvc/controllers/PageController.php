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
			'header' => "Categoría '$current_category'",
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
	 * tag.php
	 */
	public function getAuthor() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();
		$author_id = get_the_author_meta('ID');
		$author_name = get_the_author($author_id);
		$user_post_count = count_user_posts($author_id);

		$meta = $this->render('post/_meta', [
			'user_avatar' => get_avatar($author_id, 36),
			'user_url' => get_the_author_meta('user_url'),
			'display_name' => get_the_author_meta('display_name'),
			'description' => get_the_author_meta('description'),
			'edit_user_link' => ($author_id == wp_get_current_user()->ID) ? get_edit_user_link() : false
		]);

		$header = I18n::transu('entradas_de', [
			'nombre' => $author_name
		]);

		$entradas = I18n::trans('entradas');

		$content = $this->_renderBusqueda([
			'header' => "$header ($user_post_count $entradas)",
			'subheader' => $meta,
			'posts' => $posts
		]);
		return $this->_renderPageBase([
			'content' => $content
		]);
	}

	/**
	 * search.php
	 */
	public function getSearch() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();
		$search_query = get_search_query();

		$content = $this->_renderBusqueda([
			'header' => "Resultado de la búsqueda '$search_query'",
			'posts' => $posts
		]);
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
	 * 404.php
	 */
	public function getError($num) {
		$content = $this->render('error', array(
			'num' => $num
		));
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

}
