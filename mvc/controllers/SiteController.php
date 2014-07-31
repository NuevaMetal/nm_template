<?php
class SiteController extends ChesterBaseController {

	/**
	 * page-pattern-primer.php
	 */
	public function showPatternPrimer() {
		//TODO: todavía sin darle uso a esta funcionalidad. Falta estudiarla
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