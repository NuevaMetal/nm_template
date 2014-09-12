<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 *
 */
class Post extends ModelBase {
	public static $table = "posts";

	/**
	 * Devuelve el array con la información del Post
	 *
	 * @param integer $post_id
	 *        Identificador del Post
	 * @param string $dateFormat
	 * @return array
	 */
	public static function get($post_id = false, $dateFormat = false, $conCategorias = false) {
		if (!$dateFormat) {
			$dateFormat = get_option('date_format');
		}
		if (!$post_id) {
			$conCategorias = true;
			$post_id = get_the_ID();
		}

		$post = self::getArrayById($post_id, $conCategorias);
		$post ['time'] = get_the_time($dateFormat, $post_id);

		return $post;
	}

	/**
	 * Devuelve la información mínima de un post conociendo su ID
	 *
	 * @param integer $post_id
	 *        Identificador del Post
	 * @return array
	 */
	public static function getArrayById($post_id, $conCategorias = false) {
		$post = Post::find($post_id);
		$title = get_the_title($post_id);
		$tags = wp_get_post_tags($post_id);
		$categories = wp_get_post_categories($post_id);
		$title_corto = Utils::getPalabrasByStr($title, Utils::CANT_TITLE_CORTO_DEFAULT);
		$title_corto = Utils::quitarPalabrasInnecesariasDeSeccion($title_corto);
		$post = array(
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
			'total_me_gustas' => $post->getCountFavoritos()
		);

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

		//$post = self::addCustomFieldsToPost($customFields, $post);
		$post = self::addThumbnailsToPost($post);

		return $post;
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

	/**
	 * Devuelve un array con posts similares basásndose en sus tags
	 *
	 * @param number $max
	 *        Número máximo de posts similares que queremos
	 * @return array<post>
	 */
	public static function getPostsSimilares($max = 4, $post_id = false) {
		Utils::debug("> getPostsSimilares($max)");
		$cont = 0;
		$postsSimilares = array();
		if (!post_id) {
			global $post;
			$post_id = $post->ID;
		}
		$nextTagThumb = '-1';
		$tags = wp_get_post_tags($post_id);
		Utils::debug("> a > post_id: $post_id");
		foreach ($tags as $tag) {
			Utils::debug("> b ");
			if ($tags) {
				$what_tag = $tags [($nextTagThumb + '1')]->term_id;
				Utils::debug("> c ");
				$args = array(
					'tag__in' => array(
						$what_tag
					),
					'post__not_in' => array(
						$post->ID
					),
					'showposts' => 3,
					'ignore_stickies' => 1
				);
				Utils::debug("> d ");

				$posts = get_posts($args);
				Utils::debug("> coutn: $cont | count(posts):" . count($posts));

				foreach ($posts as $k => $_p) {
					Utils::debug(">> post_id : $_p->ID");
					$title = explode('-', $_p->post_title);
					$category = get_the_category($_p->ID);
					$post = [
						'post_id' => $_p->ID,
						'permalink' => get_permalink($_p->ID),
						'title' => $_p->post_title,
						'title_corto' => $title [0],
						'time' => $_p->post_modified,
						'author' => get_user_by('id', $_p->post_author)->display_name,
						'author_link' => get_author_posts_url($_p->post_author)
					];
					//'category' => $category [0]->name
					$post = self::addThumbnailsToPost($post);
					//$post = Post::get($_p->ID);
					$postsSimilares [] = $post;
					if (++$cont == $max) {
						break 2;
					}
				}
			}
			wp_reset_query();
			$nextTagThumb = ($nextTagThumb + 1);
		}
		return $postsSimilares;
	}

	/**
	 * Devuelve la primera categoría que encuentra del post
	 *
	 * @param integer $post_id
	 *        Identificador del Post
	 * @return Categoria
	 */
	public static function getCategoryName($post_id) {
		$cat = get_the_category($post_id);
		return $cat [0]->name;
	}

	/**
	 * Devuelve el número total de favoritos que tiene el Post
	 *
	 * @return number Total de favoritos que tiene el Post
	 */
	public function getCountFavoritos() {
		global $wpdb;
		$activo = Favorito::ACTIVO;
		return ( int ) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "favoritos
				WHERE post_id = $this->ID AND status = $activo;");
	}

}