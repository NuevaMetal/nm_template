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
		global $post;

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
					array_push($posts, self::getPost($dateFormat, $customFields));
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
		if (!$dateFormat) {
			$dateFormat = get_option('date_format');
		}
		$conCategorias = false;
		if (!$post_id) {
			$conCategorias = true;
			$post_id = get_the_ID();
		}

		$post = self::_getArrayPostById($post_id);
		$post ['time'] = get_the_time($dateFormat, $post_id);

		if ($conCategorias) {
			$tags = get_the_tags($post_id);
			$categories = get_the_category($post_id);
			$post ['the_tags'] = self::getTagsAsArray($tags);
			$post ['the_categories'] = self::getCategoriesAsArray($categories);
			if (!$tags) {
				$post ['has_tags'] = false;
			} else {
				$post ['has_tags'] = true;
			}
			if (!$categories) {
				$post ['has_categories'] = false;
			} else {
				$post ['has_categories'] = true;
			}
		}

		//Añadir la analítica sólo si es un editor o admin
		if (current_user_can('edit_others_pages')) {
			$post ['analitica'] = [
				'visitas_totales' => Analitica::getTotalVisitasByPostId($post_id),
				'visitas_user' => Analitica::getVisitasUnicasByPostId($post_id)
			];
		}

		$post = self::addCustomFieldsToPost($customFields, $post);
		$post = self::addThumbnailsToPost($post);

		return $post;
	}

	/**
	 * Devuelve la información mínima de un post conociendo su ID
	 *
	 * @param integer $post_id
	 *        Identificador del Post
	 * @return array
	 */
	private function _getArrayPostById($post_id) {
		$post = get_post($post_id);
		$title = get_the_title($post_id);

		$title_corto = Utils::getPalabrasByStr($title, Utils::CANT_TITLE_CORTO_DEFAULT);
		$title_corto = Utils::quitarPalabrasInnecesariasDeSeccion($title_corto);
		return array(
			'ID' => $post_id,
			'post_id' => $post_id,
			'permalink' => get_permalink($post_id),
			'title' => $title,
			'title_corto' => $title_corto,
			'date_published' => get_the_time('c'),
			'content' => Utils::traducirPost(self::getTheFilteredContentFromLoop()),
			'excerpt' => Utils::traducirPost(Utils::getExcerptById($post_id, Utils::CANT_EXCERPT_DEFAULT)),
			'author' => get_the_author_meta('display_name', $post->post_author),
			'genero' => Utils::getGeneroById($post_id),
			'pais' => Utils::getPaisById($post_id),
			'author_link' => get_the_author_link($post_id),
			'the_tags' => self::getTagsAsArray($tags),
			'the_categories' => self::getCategoriesAsArray($categories),
			'total_me_gustas' => Utils::getTotalMeGustas(false, $post_id)
		);
	}

	private static function getBlogTitle() {
		if (is_home()) {
			return get_bloginfo('name');
		} else {
			return wp_title("-", false, "right") . " " . get_bloginfo('name');
		}
	}

	private static function getTheFilteredContentFromLoop() {
		$content = apply_filters('the_content', get_the_content());
		$content = str_replace(']]>', ']]&gt;', $content);
		return $content;
	}

	private static function getTagsAsArray($theTags) {
		if (!$theTags) {
			return array();
		}
		$array = array();

		foreach ($theTags as $tag) {
			$tagAsArray = get_object_vars($tag);
			$tagAsArray ['tag_link'] = get_tag_link($tag->term_id);
			array_push($array, $tagAsArray);
		}

		return $array;
	}

	private static function getCategoriesAsArray($theCategories) {
		if (!$theCategories) {
			return array();
		}
		$array = array();

		foreach ($theCategories as $category) {
			$categoryAsArray = get_object_vars($category);
			$categoryAsArray ['category_link'] = get_category_link($category->term_id);
			array_push($array, $categoryAsArray);
		}

		return $array;
	}

	private static function addCustomFieldsToPost($customFields = array(), $post = FALSE) {
		if (empty($customFields) || empty($post)) {
			return $post;
		}

		foreach ($customFields as $customField) {
			if (empty($customField)) {
				continue;
			}
			$post = self::setCustomFieldOnPost($post ['ID'], $customField, $post);
		}

		return $post;
	}

	private static function setCustomFieldOnPost($postId, $customField, $post) {
		if (is_string($customField)) {
			$post [$customField] = get_post_meta($postId, ChesterWPAlchemyHelpers::$metaKeyPrefix . $customField, true);
		} else {
			$name = $customField ['name'];
			$post [$name] = get_post_meta($postId, ChesterWPAlchemyHelpers::$metaKeyPrefix . $name, true);
			if ($customField ['fieldType'] == 'textarea') {
				$post [$name] = wpautop($post [$name]);
			}
		}
		return $post;
	}

	private static function addThumbnailsToPost($post) {
		$sizes = array(
			'thumbnail',
			'medium',
			'large',
			'full'
		);

		foreach ($sizes as $size) {
			$imageObject = wp_get_attachment_image_src(get_post_thumbnail_id($post ['post_id']), $size);
			if (!empty($imageObject)) {
				$post ['featured_image_url_' . $size] = $imageObject [0];
			}
		}
		return $post;
	}

}

?>
