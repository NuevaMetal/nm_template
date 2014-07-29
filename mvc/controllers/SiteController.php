<?php
class SiteController extends ChesterBaseController {

	public function __construct() {
		Utils::debug("SiteController BEGIN");
	}

	/**
	 */
	public function showGalleries() {
		Utils::debug("showGalleries BEGIN");
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

		Utils::debug("showGalleries END");
	}

	/**
	 * page-pattern-primer.php
	 */
	public function showPatternPrimer() {
		$patternPrimerController = new ChesterPatternPrimerController();

		$post = $patternPrimerController->renderPattern('post', array(
			'post' => array(
				'permalink' => 'http://brightonculture.co.uk',
				'title' => 'Post title',
				'time' => '12th Nov 2012',
				'content' => '<p>Sample content</p>'
			)
		));

		$postPreview = $patternPrimerController->renderPattern('post_previews', array(
			'posts' => array(
				'permalink' => 'http://brightonculture.co.uk',
				'title' => 'Post preview title',
				'time' => '12th Nov 2012',
				'content' => '<p>Sample content</p>'
			)
		));

		$patternGroup = $patternPrimerController->renderCustomPatternGroup($post . $postPreview, 'modules/');

		$patternPrimerController->showPatternPrimer(array(
			'typography',
			'grids'
		), $patternGroup);
	}

}
?>
