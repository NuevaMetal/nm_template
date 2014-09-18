<?php
require_once (dirname(__FILE__) . '/wp_alchemy_helpers.php');
require_once (path('app') . '/lib/Utils.php');

/**
 *
 * @author Mark Kirby
 * @copyright Copyright (c) 2012, Mark Kirby, http://mark-kirby.co.uk/
 * @license http://en.wikipedia.org/wiki/MIT_License The MIT License
 * @package Chester
 * @version 0.1
 * @link https://github.com/markirby/Chester-WordPress-MVC-Theme-Framework
 * @link http://thisishatch.co.uk/
 */
class ChesterWPCoreDataHelpers {

	public static function getBlogInfoData() {
		return array(
			'blog_title' => self::getBlogTitle(),
			'name' => get_bloginfo('name'),
			'description' => get_bloginfo('description'),
			'admin_email' => get_bloginfo('admin_email'),

			'url' => get_bloginfo('url'),
			'wpurl' => get_bloginfo('wpurl'),

			'stylesheet_directory' => get_bloginfo('stylesheet_directory'),
			'stylesheet_url' => get_bloginfo('stylesheet_url'),
			'template_directory' => get_bloginfo('template_directory'),
			'public_directory' => get_bloginfo('template_directory') . '/public',
			'template_url' => get_bloginfo('template_url'),

			'atom_url' => get_bloginfo('atom_url'),
			'rss2_url' => get_bloginfo('rss2_url'),
			'rss_url' => get_bloginfo('rss_url'),
			'pingback_url' => get_bloginfo('pingback_url'),
			'rdf_url' => get_bloginfo('rdf_url'),

			'comments_atom_url' => get_bloginfo('comments_atom_url'),
			'comments_rss2_url' => get_bloginfo('comments_rss2_url'),

			'charset' => get_bloginfo('charset'),
			'html_type' => get_bloginfo('html_type'),
			'language' => get_bloginfo('language'),
			'text_direction' => get_bloginfo('text_direction'),
			'version' => get_bloginfo('version'),

			'is_user_logged_in' => is_user_logged_in()
		);
	}

	public static function getWordpressPostsFromLoop($dateFormat = false, $customFields = array(), $fetchAllPosts = false) {
		$posts = array();

		if (!empty($fetchAllPosts)) {
			global $query_string;
			query_posts($query_string . '&posts_per_page=-1&orderby=menu_order');
		}

		if (have_posts()) {
			while (have_posts()) {
				the_post();
				array_push($posts, self::getPost($dateFormat, $customFields));
			}
		}

		return $posts;
	}

	public static function getPosts($dateFormat = false, $postType = 'post', $numberPostsToFetch = -1, $customFields = array(), $oddOrEven = false, $moreQuerySettings = array()) {
		$posts = array();

		$querySettings = array(
			'post_type' => array(
				$postType
			),
			'posts_per_page' => $numberPostsToFetch
		);
		$querySettings = array_merge($querySettings, $moreQuerySettings);
		$loop = new WP_Query($querySettings);

		$index = 0;
		if ($loop->have_posts()) {
			while ($loop->have_posts()) {
				$index++;
				$loop->the_post();

				if (!($oddOrEven) || ($oddOrEven == 'EVEN' && $index % 2) || ($oddOrEven == 'ODD' && !($index % 2))) {
					$posts [] = Post::find(get_the_ID());
				}
			}
		}
		return $posts;
	}

	public static function getPageCustomFields($id, $customFields) {
		$post = array();

		foreach ($customFields as $customField) {
			$post = self::setCustomFieldOnPost($id, $customField, $post);
		}

		return $post;
	}

	public static function getPost($dateFormat = false, $customFields = array(), $post_id = false) {
		return Post::get(($post_id) ? $post_id : get_the_ID(), $dateFormat);
	}

	private static function getBlogTitle() {
		if (is_home()) {
			return get_bloginfo('name');
		} else {
			return wp_title("-", false, "right") . " " . get_bloginfo('name');
		}
	}

}
