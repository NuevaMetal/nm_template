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
	public function getBandas() {
		$posts = ChesterWPCoreDataHelpers::getPosts($dateFormat = false,
				$postType = 'post',
				$numberPostsToFetch = -1,
				$customFields = array(
					'categoria' => 'achemacat'
				),
				$oddOrEven = false);

		$content = $this->render('bandas', array(
			'bandas' => $posts
		));

		return $this->_renderBase([
			'content' => $content
		]);
	}

}
