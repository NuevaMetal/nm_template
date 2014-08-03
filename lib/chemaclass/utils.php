<?php
/**
 * Clase con utilidades
 *
 * @author chemaclass
 *
 */
class Utils {

	/**
	 * devuelve el contenido de un texto etiquetadoencontrado entre las etiquetas especificadas
	 *
	 * @param string $string
	 *        contenido a buscar
	 * @param string $tagname
	 *        etiqueta a buscar
	 * @return string contenido en dicha etiqueta
	 */
	public static function getTextBetweenTags($string, $tagname) {
		$pattern = "/<$tagname ?.*>(.*)<\/$tagname>/";
		preg_match($pattern, $string, $matches);
		return $matches [1];
	}

	/**
	 * Función para info
	 *
	 * @param string $str
	 *        Cadena a pintar
	 */
	public static function info($str) {
		error_log(" INFO - " . $str);
	}

	/**
	 * Función para DEBUG
	 *
	 * @param string $str
	 *        Cadena a pintar
	 */
	public static function debug($str) {
		error_log(" DEBUG - " . $str);
	}

	/**
	 * Devuelve un array con posts similares basásndose en sus tags
	 *
	 * @param number $max
	 *        Número máximo de posts similares que queremos
	 * @return array<post>
	 */
	public static function getPostsSimilares($max = 4) {
		$cont = 0;
		$postsSimilares = array();
		global $post;
		$nextTagThumb = '-1';
		$tags = wp_get_post_tags($post->ID);
		foreach ($tags as $tag) {
			if ($tags) {
				$what_tag = $tags [($nextTagThumb + '1')]->term_id;
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

				$posts = get_posts($args);

				foreach ($posts as $k => $_p) {
					$post = array(
						'permalink' => get_permalink($_p->ID),
						'title' => $_p->post_title,
						'time' => $_p->post_modified,
						'author' => get_user_by('id', $_p->post_author)->display_name,
						'author_link' => get_author_posts_url($_p->post_author)
					);
					$post = self::addThumbnailsToPost($post, $_p);

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

	private static function addThumbnailsToPost($post, $_p) {
		$sizes = array(
			'thumbnail',
			'medium',
			'large',
			'full'
		);
		foreach ($sizes as $size) {
			$imageObject = wp_get_attachment_image_src(get_post_thumbnail_id($_p->ID), $size);
			if (!empty($imageObject)) {
				$post ['featured_image_url_' . $size] = $imageObject [0];
			}
		}
		return $post;
	}

	/**
	 * Función Disqus
	 */
	public static function getDisqusEmbed($disqus_shortname) {
		global $post;
		return '<div id="disqus_thread"></div>
    <script type="text/javascript">
        var disqus_shortname = "' . $disqus_shortname . '";
        var disqus_title = "' . $post->post_title . '";
        var disqus_url = "' . get_permalink($post->ID) . '";
        var disqus_identifier = "' . $disqus_shortname . '-' . $post->ID . '";
    </script>';
	}

}

/**
 * -------------------------------------
 * Funciones de acceso rápido
 * -------------------------------------
 */

/**
 *
 * @param mixed $expression
 * @param string $tag
 */
function dd($expression, $tag = "Tag") {
	echo '' . $tag . '<br>';
	var_dump($expression);
	exit();
}