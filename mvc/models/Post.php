<?php
require_once 'ModelBase.php';
/**
 *
 * @author chema
 *
 */
class Post extends ModelBase {
	public static $table = "posts";

	const IMG_THUMBNAIL = 'thumbnail';

	const IMG_MEDIUM = 'medium';

	const IMG_LARGE = 'large';

	const IMG_FULL = 'full';

	const NUM_SIMILARES_DEFAULT = 4;

	// Cantidad del extracto de una entrevista
	const CANT_EXCERPT_DEFAULT = 12;
	// Cantidad del título corto por defecto
	const CANT_TITLE_CORTO_DEFAULT = 5;

	// Cantidad del extracto de una entrevista
	const CANT_EXCERPT_ENTREVISTA = 16;

	public function getFormComentarios() {
		ob_start();
		$params = [
			'comment_notes_after' => '',
			'author' => '<p class="comment-form-author">' . '<label for="author">' . __('Your Name') . '</label>
					<input id="author" name="author" type="text"  value="Your First and Last Name" size="30"' . $aria_req . ' /></p>',
			'comment_field' => '<div class="form-group comment-form-comment">
			            <label for="comment">' . _x('Comment', 'noun') . '</label>
			            <textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
			        </div>'
		];
		comment_form($params, $this->ID);
		$comment_form = ob_get_clean();
		$comment_form = str_replace('class="comment-form"', 'class="comment-form"', $comment_form);
		$comment_form = str_replace('id="submit"', 'class="btn btn-danger"', $comment_form);
		return $comment_form;
	}

	/**
	 * Devuelve una lista de comentarios
	 */
	public function getComentarios() {
		$args_comments = array(
			'post_id' => $this->ID,
			'orderby' => 'comment_date_gmt',
			'status' => 'approve'
		);
		return get_comments($args_comments, $this->ID);
	}

	/**
	 * Devuelve el array con la información del Post
	 *
	 * @param integer $post_id
	 *        Identificador del Post
	 * @param string $dateFormat
	 * @return array
	 * @deprecated por usar find
	 * @see Post::find
	 */
	public static function get($post_id = false, $dateFormat = false, $conCategorias = false) {
		return Post::find($post_id);
	}

	public function getUrl() {
		return get_permalink($this->ID);
	}

	public function getUrlEditar() {
		return get_edit_post_link($this->ID);
	}

	public function getTitulo($corto = false, $cantCorto = self::CANT_TITLE_CORTO_DEFAULT) {
		$title = get_the_title($this->ID);
		//($corto) ? explode('-', $title)[0] : $title;
		return ($corto) ? Utils::getPalabrasByStr($title, $cantCorto) : $title;
	}

	public function getTituloCorto() {
		return $this->getTitulo(true);
	}

	/**
	 * Fecha ISO 8601.
	 * 2004-02-12T15:19:21+00:00
	 */
	public function getTimeISO() {
		return get_the_time('c', $this->ID);
	}

	public function getTime($dateFormat = false) {
		if (!$dateFormat) {
			$dateFormat = (is_single()) ? 'l, d F Y' : get_option('date_format');
		}
		return get_the_time($dateFormat, $this->ID);
	}

	public function getContent() {
		return self::getTheFilteredContentFromLoop($this->post_content);
	}

	public function getExcerpt() {
		// Quito las etiquetas e img
		$the_excerpt = strip_tags(strip_shortcodes($this->post_content));
		// Dejo el str en una única línea
		$the_excerpt = trim(preg_replace('/\s\s+/', ' ', $the_excerpt));
		// Sustituyo todos los espacios raros por espacios normales
		$the_excerpt = preg_replace("/[\xc2|\xa0]/", ' ', $the_excerpt);
		$the_excerpt = Utils::getPalabrasByStr($the_excerpt, self::CANT_EXCERPT_DEFAULT);
		// Aplicamos negrita a ciertas palabras
		$the_excerpt = preg_replace([
			'/(Género)/i',
			'/(País)/i',
			'/(Álbumes)/i',
			'/(Estado)/i',
			'/(Miembros)/i'
		], '<b>$1</b>', $the_excerpt);
		return $the_excerpt;
	}

	/**
	 * Traducir todo el contenido que tengamos dentro de nuestro i18n
	 * en el fichero post.php
	 * Y aplicamos el filtro de idioma de forma genérica, aplicado a todos los idiomas,
	 * que se encuentra en el fichero post_format
	 *
	 * @param string $content
	 *        Contenido del post
	 * @deprecated Por preferencia a no traducir sólo unas palaras
	 */
	private static function _traducirPost($content) {
		$lista = I18n::getFicheroIdioma('post');
		//Sustituimos todos los str del contenido que estén en la lista
		$content = str_ireplace(array_keys($lista), $lista, $content);
		$lista = I18n::getFicheroIdioma('../post_format');
		return str_ireplace(array_keys($lista), $lista, $content);
	}

	public function getGenero() {
		$post_content = $this->post_content;
		$post_content = strip_tags(strip_shortcodes($post_content));
		$post_content = substr($post_content, 0, 60);
		//preg_match('/(?m:\bg[é|e]?neros?\b\W*\b(\w+)\b\W*(.*)[\s]*$)/ui', $post_content, $out);
		preg_match('/(?m:\bg[é|e]?neros?\b\W*(\w*)(.*)$)/ui', $post_content, $out);
		array_shift($out); // Quito el primer del array
		$out = implode('', $out);
		$out = explode(',', $out);
		$out = $out [0];
		return $out;
	}

	public function getPais() {
		$post_content = $this->post_content;
		$post_content = strip_tags(strip_shortcodes($post_content));
		$post_content = substr($post_content, 0, 100);
		//preg_match('/(?m:\bpa[í|i]s[es]*\b\W*\b(\w*)\b\W*(\w*).*$)/ui', $post_content, $out);
		preg_match('/(?m:\bpa[í|i]s[es]*\b\W*(\w*)(.*)$)/ui', $post_content, $out);
		array_shift($out); // Quito el primer del array
		$out = implode('', $out);
		return $out;
	}

	public function getTotalMeGustas() {
		return $this->getCountFavoritos();
	}

	public function getAutor() {
		return User::find($this->post_author);
	}

	public function getEtiquetas() {
		$tags = get_the_tags($this->ID);
		return self::_getTags($tags);
	}

	public function tieneEtiquetas() {
		return count($this->getEtiquetas());
	}

	public function getCategorias() {
		$categories = get_the_category($this->ID);
		return self::_getCategories($categories);
	}

	public function tieneCategorias() {
		return count($this->getCategorias());
	}

	public function getAnalitica() {
		return [
			'visitas_totales' => Analitica::getTotalVisitasByPostId($this->ID),
			'visitas_unicas' => Analitica::getVisitasUnicasByPostId($this->ID)
		];
	}

	public function getThumbnails() {
		$thumbnails = [];
		$sizes = [
			self::IMG_THUMBNAIL,
			self::IMG_MEDIUM,
			self::IMG_LARGE,
			self::IMG_FULL
		];
		foreach ($sizes as $size) {
			$imageObject = wp_get_attachment_image_src(get_post_thumbnail_id($this->ID), $size);
			if (!empty($imageObject)) {
				$thumbnails [$size] = $imageObject [0];
			}
		}
		return $thumbnails;
	}

	private static function getTheFilteredContentFromLoop($_content) {
		$content = apply_filters('the_content', $_content);
		$content = str_replace(']]>', ']]&gt;', $content);
		return $content;
	}

	private static function _getTags($theTags) {
		if (!$theTags) {
			return array();
		}
		$array = array();
		foreach ($theTags as $tag) {
			$tag->tag_link = get_tag_link($tag->term_id);
			$array [] = $tag;
		}
		return $array;
	}

	private static function _getCategories($theCategories) {
		if (!$theCategories) {
			return array();
		}
		$array = array();
		foreach ($theCategories as $category) {
			$category->category_link = get_category_link($category->term_id);
			$array [] = $category;
		}
		return $array;
	}

	/**
	 * Devuelve un array con posts similares basásndose en sus etiquetas
	 *
	 * @param number $max
	 *        Número máximo de posts similares que queremos
	 * @return array<Post>
	 */
	public function getSimilares($max = self::NUM_SIMILARES_DEFAULT) {
		$cont = 0;
		$postsSimilares = array();
		$nextTagThumb = -1;
		$tags = $this->getEtiquetas();
		foreach ($tags as $tag) {
			if ($tag) {
				$what_tag = $tags [($nextTagThumb + 1)]->term_id;
				$args = array(
					'tag__in' => array(
						$what_tag
					),
					'post__not_in' => array(
						$this->ID
					),
					'showposts' => 3,
					'ignore_stickies' => 1
				);

				$posts = get_posts($args);

				foreach ($posts as $k => $_p) {
					$postsSimilares [] = Post::get($_p->ID);
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

	public function haySimilares() {
		return count($this->getSimilares());
	}

	/**
	 * Devuelve la primera categoría que encuentra del post
	 *
	 * @return string
	 */
	public function getCategoriaNombre() {
		$categorias = $this->getCategorias();
		return ($categorias) ? $categorias [0]->name : '';
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

	public function getNonceMeGusta() {
		return Ajax::crearNonce(Ajax::ME_GUSTA, $this->ID);
	}

	public function getNonceNotificar() {
		return Ajax::crearNonce(Ajax::NOTIFICAR, $this->ID);
	}

	public function isMeGusta($user_id = false) {
		if (!$user_id) {
			$user_id = wp_get_current_user()->ID;
		}
		global $wpdb;
		$leGusta = ( int ) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*)
				FROM ' . $wpdb->prefix . 'favoritos
				WHERE user_id = %d
				AND post_id = %d
				AND status = 0;', $user_id, $this->ID));
		return $leGusta > 0;
	}

}