<?php
require_once 'BaseController.php';
/**
 * Controlador principal de la web
 *
 * @author chema
 *
 */
class BandasController extends BaseController {

	/**
	 * index.php
	 */
	public function showGalleries() {
		//Set third variable to true
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop(false, array(
			'location',
			'map',
			'website'
		), true);

		$content_block_1 = $this->render('galleries', array(
			'posts' => $posts
		));

		$content_block_2 = $this->render('sidebar');

		echo $this->renderPage('grids/grid_two_column', array(
			'content_block_1' => $content_block_1,
			'content_block_2' => $content_block_2
		));
	}

}
