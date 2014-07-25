<?php
class TestController extends ChesterBaseController {

	/**
	 * index.php
	 */
	public function homeTest() {
		$posts = ChesterWPCoreDataHelpers::getWordpressPostsFromLoop();

		$content_block_1 = $this->render('post_previews', array(
			'posts' => $posts,
			'next_posts_link' => get_next_posts_link(),
			'previous_posts_link' => get_previous_posts_link()
		));

		$content_block_2 = $this->render('sidebar');

		echo $this->renderPage('grids/grid_two_column', array(
			'content_block_1' => $content_block_1,
			'content_block_2' => $content_block_2
		));
	}

}