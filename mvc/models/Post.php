<?php

namespace Models;

use Libs\Utils;
use Libs\Ajax;
use I18n\I18n;

/**
 *
 * @author chema
 */
class Post extends Image {
	public static $table = "posts";

	const IMG_THUMBNAIL = 'thumbnail';

	const IMG_MEDIUM = 'medium';

	const IMG_LARGE = 'large';

	const IMG_FULL = 'full';

	const NUM_SIMILARES = 6;

	const NUM_RELACIONADAS = 4;

	const NUM_USER_QUE_GUSTAN_DEFAULT = 5;

	// Cantidad del extracto de una entrevista
	const CANT_EXCERPT = 12;
	// Cantidad de chars para el título corto
	const CANT_TITLE_CORTO = 50;

	// Cantidad del extracto de una entrevista
	const CANT_EXCERPT_ENTREVISTA = 16;

	const CATEGORY_BANDAS = 'bandas';

	const CATEGORY_VIDEOS = 'videos';

	const CATEGORY_ENTREVISTAS = 'entrevistas';

	const CATEGORY_NOTICIAS = 'noticias';

	const CATEGORY_CONCIERTOS = 'conciertos';

	const CATEGORY_CRITICAS = 'criticas';

	const CATEGORY_CRONICAS = 'cronicas';

	const ENTRADAS_SIMILARES = 'entradas-similares';

	const ENTRADAS_RELACIONADAS = 'entradas-relacionadas';

	/**
	 *
	 * @return mixed
	 */
	public function getFormComentarios() {
		ob_start();
		$placeholderTextarea = I18n::transu('compartir_comentario', []);
		$params = [
			'comment_notes_after' => '',
			'author' => '<p class="comment-form-author">' . '<label for="author">' . __('Your Name') . '</label>
					<input id="author" name="author" type="text"  value="Your First and Last Name" size="30" /></p>',
			'comment_field' => '
				<div class="form-group comment-form-comment">
		            <label for="comment">' . _x('Comment', 'noun') . '</label>
		            <textarea class="form-control" id="comment" name="comment" cols="45" rows="2"
							maxlength="1000" aria-required="true" placeholder="' . $placeholderTextarea . '"></textarea>
		        </div>'
		];
		comment_form($params, $this->ID);
		$comment_form = ob_get_clean();
		$comment_form = str_replace('class="comment-reply-title"', 'class="comment-reply-title titular"', $comment_form);
		$comment_form = str_replace('Deja un comentario', I18n::transu('deja_un_comentario'), $comment_form);
		$comment_form = str_replace('Conectado como', I18n::transu('conectado_como'), $comment_form);
		$comment_form = str_replace('¿Quieres salir?', I18n::transu('quieres_salir'), $comment_form);
		$comment_form = str_replace('Comentario', I18n::transu('comentario'), $comment_form);
		$comment_form = str_replace('Publicar comentario', I18n::transu('publicar_comentario'), $comment_form);
		$comment_form = str_replace('id="submit"', 'class="btn btn-danger"', $comment_form);
		return $comment_form;
	}

	/**
	 * Devuelve una lista de comentarios
	 *
	 * @see http://codex.wordpress.org/Function_Reference/get_comments
	 */
	public function getComentarios() {
		$args_comments = [
			'post_id' => $this->ID,
			'orderby' => 'comment_date_gmt',
			'status' => 'approve'
		];
		$comentarios = [];
		foreach (get_comments($args_comments, $this->ID) as $c) {
			$comentarios[] = Comment::find($c->comment_ID);
		}
		return $comentarios;
	}

	/**
	 * Devuelve el númeto total de comentarios que tiene un Post
	 *
	 * @return number
	 */
	public function getTotalComentarios() {
		return count($this->getComentarios());
	}

	/**
	 * Devuelve la URL del post
	 *
	 * @return string
	 */
	public function getUrl() {
		return get_permalink($this->ID);
	}

	/**
	 * Devuelve la URL para editar el post
	 *
	 * @return string
	 */
	public function getUrlEditar() {
		return get_edit_post_link($this->ID);
	}

	/**
	 * Devuelve el título del Post
	 *
	 * @param string $corto
	 * @param unknown $cantCorto
	 * @return Ambigous <unknown, string>
	 */
	public function getTitulo($corto = false, $cantCorto = self::CANT_TITLE_CORTO) {
		$title = get_the_title($this->ID);
		/*
		 * Corto el string, busco el último espacio ' ' y vuelvo a cortar para quitar
		 * la última palabra y concatenar con ..., o de lo contrario devuelvo el título
		 * cortado ya.
		 */
		$getCharsByStr = function ($str) use($cantCorto) {
			if (strlen($str) > $cantCorto) {
				$substr = substr($str, 0, $cantCorto);
				// strrchr => devuelve el str de la última ocurrencia
				$posicionUltimoEspacio = strpos($substr, strrchr($substr, ' '));
				if ($posicionUltimoEspacio) {
					$substr = substr($substr, 0, $posicionUltimoEspacio) . '...';
				}
				return $substr;
			}
			return $str;
		};
		return ($corto) ? $getCharsByStr($title) : $title;
	}

	/**
	 * Devuelve el título acortado del Post
	 *
	 * @return string
	 */
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
		if (! $dateFormat) {
			$dateFormat = (is_single()) ? 'l, d F Y' : get_option('date_format');
		}
		return get_the_time($dateFormat, $this->ID);
	}

	/**
	 * Devuelve el contenido del post
	 *
	 * @return string
	 */
	public function getContent() {
		$content = apply_filters('the_content', $this->post_content);
		$content = str_replace(']]>', ']]&gt;', $content);
		return $content;
	}

	/**
	 * Devuelve el excerpt del content.
	 * Es decir, una porción del principio.
	 *
	 * @return string
	 */
	public function getExcerpt() {
		// Quito las etiquetas e img
		$the_excerpt = strip_tags(strip_shortcodes($this->post_content));
		// Dejo el str en una única línea
		$the_excerpt = trim(preg_replace('/\s\s+/', ' ', $the_excerpt));
		// Sustituyo todos los espacios raros por espacios normales
		$the_excerpt = preg_replace("/[\xc2|\xa0]/", ' ', $the_excerpt);
		$the_excerpt = self::getPalabrasByStr($the_excerpt, self::CANT_EXCERPT);
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
	 * Devuelve un número de palabras de un string
	 *
	 * @param string $str
	 *        	Cadena en la que buscar las palabras
	 * @param number $cant
	 *        	Cantidad de palabras que queremos obtener
	 * @return string Cadena 'limitada' al número de palabras especificadas
	 */
	private static function getPalabrasByStr($str, $cant = 8, $separador = ' ') {
		// Genero un array a partir del content separando por espacios
		$palabras = explode($separador, $str, $cant + 1);
		$nPalabras = count($palabras);
		// Aplicamos un filtro para quitar determinadas palabras
		$palabras = array_filter($palabras, function ($item) {
			return ! Utils::strContieneAlgunValorArray($item, [
				'youtube'
			]) ? $item : '';
		});
		// Quitamos los valores vacíos
		$palabrasFiltradas = array_filter($palabras, 'strlen');
		$nPalabrasFiltradas = count($palabrasFiltradas);
		// Si hay un distinto número de palabras, significará que se filtraron algunas
		if ($nPalabrasFiltradas != $nPalabras) {
			$cant -= ($nPalabras - $nPalabrasFiltradas);
			$palabras = $palabrasFiltradas;
		}
		// Si el content fuese más largo que el extracto, concatenar '...'
		if (count($palabras) > $cant) {
			array_pop($palabras);
			$permalink = get_permalink($post_id);
			$palabras[] = '...';
		}
		// Obtengo el extracto del contenido juntando todas las palabras unidas por un espacio
		return implode($separador, $palabras);
	}

	/**
	 * Traducir todo el contenido que tengamos dentro de nuestro i18n
	 * en el fichero post.php
	 * Y aplicamos el filtro de idioma de forma genérica, aplicado a todos los idiomas,
	 * que se encuentra en el fichero post_format
	 *
	 * @param string $content
	 *        	Contenido del post
	 * @deprecated Por preferencia a no traducir sólo unas palaras
	 */
	private static function _traducirPost($content) {
		$lista = I18n::getFicheroIdioma('post');
		// Sustituimos todos los str del contenido que estén en la lista
		$content = str_ireplace(array_keys($lista), $lista, $content);
		$lista = I18n::getFicheroIdioma('../post_format');
		return str_ireplace(array_keys($lista), $lista, $content);
	}

	/**
	 * Devuelve el Género si estuviera definido al principio del contenido
	 *
	 * @return string
	 */
	public function getGenero() {
		$post_content = $this->post_content;
		$post_content = strip_tags(strip_shortcodes($post_content));
		$post_content = substr($post_content, 0, 60);
		// preg_match('/(?m:\bg[é|e]?neros?\b\W*\b(\w+)\b\W*(.*)[\s]*$)/ui', $post_content, $out);
		preg_match('/(?m:\bg[é|e]?neros?\b\W*(\w*)(.*)$)/ui', $post_content, $out);
		array_shift($out); // Quito el primer del array
		$out = implode('', $out);
		$out = explode(',', $out);
		$out = $out[0];
		return $out;
	}

	/**
	 * Devuelve el País si estuviera definido al principio del contenido
	 *
	 * @return string
	 */
	public function getPais() {
		$post_content = $this->post_content;
		$post_content = strip_tags(strip_shortcodes($post_content));
		$post_content = substr($post_content, 0, 100);
		// preg_match('/(?m:\bpa[í|i]s[es]*\b\W*\b(\w*)\b\W*(\w*).*$)/ui', $post_content, $out);
		preg_match('/(?m:\bpa[í|i]s[es]*\b\W*(\w*)(.*)$)/ui', $post_content, $out);
		array_shift($out); // Quito el primer del array
		$out = implode('', $out);
		return $out;
	}

	/**
	 * Devuelve el autor del Post
	 *
	 * @return User
	 */
	public function getAutor() {
		return User::find($this->post_author);
	}

	/**
	 * Devuelve todas las etiquetas
	 *
	 * @return array<string>
	 */
	public function getEtiquetas() {
		$tags = get_the_tags($this->ID);
		if (! $tags) {
			return array();
		}
		foreach ($tags as $tag) {
			$tag->tag_link = get_tag_link($tag->term_id);
			$array[] = $tag;
		}
		return $array;
	}

	/**
	 * Devuelve el número total de etiquetas
	 *
	 * @return number
	 */
	public function getTotalEtiquetas() {
		return count($this->getEtiquetas());
	}

	/**
	 * Devuelve las categorías del Post
	 *
	 * @return array
	 */
	public function getCategorias() {
		$categories = get_the_category($this->ID);
		if (! $categories) {
			return array();
		}
		foreach ($categories as $category) {
			$category->category_link = get_category_link($category->term_id);
			$array[] = $category;
		}
		return $array;
	}

	/**
	 * Devuelve el total de categorías
	 *
	 * @return integer
	 */
	public function getTotalCategorias() {
		return count($this->getCategorias());
	}

	/**
	 * Devuelve el thumbnail con clave thumbnail
	 *
	 * @return string src
	 */
	public function getThumbnail() {
		return $this->_getThumbnail(self::IMG_THUMBNAIL);
	}

	/**
	 * Devuelve el thumbnail con clave medium
	 *
	 * @return string src
	 */
	public function getThumbnailMedium() {
		return $this->_getThumbnail(self::IMG_MEDIUM);
	}

	/**
	 * Devuelve el src del thumbnail del post
	 *
	 * @param string $size
	 *        	tamaño
	 */
	private function _getThumbnail($size) {
		/*
		 * Defino una función para obtener el src del attachment a partir del id del post
		 */
		$getSrc = function ($_id) use($size) {
			$imageObject = wp_get_attachment_image_src(get_post_thumbnail_id($_id), $size);
			if (empty($imageObject)) {
				return false;
			}
			return $imageObject[0];
		};

		if (($imageObject = $getSrc($this->ID))) {
			return $imageObject;
		} else {
			// Si no hay cogemos la primera imágen del post y la añadimos como thumbnail
			preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $this->post_content, $matches);
			$src = $matches[1];
			$attachmentId = Utils::getAttachmentIdFromUrl($src);
			// Intento establecer como thumbnail la primera img encontrada al post
			set_post_thumbnail($this->ID, $attachmentId);
			// En caso de encontrarla la devolvemos, en caso contrario devolvemos el src
			if (($imageObject = $getSrc($this->ID))) {
				return $imageObject;
			}
			return $src;
		}
	}

	/**
	 * Devuelve todos los tamaños del thumbnail posible
	 *
	 * @return multitype:unknown
	 */
	public function getThumbnails() {
		$sizes = [
			self::IMG_THUMBNAIL,
			self::IMG_MEDIUM,
			self::IMG_LARGE,
			self::IMG_FULL
		];
		foreach ($sizes as $size) {
			$thumbnails[] = $this->_getThumbnail($size);
		}
		return $thumbnails;
	}

	/**
	 * Devuelve la primera categoría que encuentra del post.
	 * http://codex.wordpress.org/Function_Reference/get_the_category
	 *
	 * @return object
	 */
	public function getCategoria() {
		$categories = get_the_category($this->ID);
		return $categories[0];
	}

	/**
	 * Devuelve un array con posts similares basásndose en sus etiquetas
	 *
	 * @param number $max
	 *        	Número máximo de posts similares que queremos
	 * @return array<Post>
	 */
	public function getSimilares($max = self::NUM_SIMILARES) {
		$tags = $this->getEtiquetas();
		$cat_id = ($cat = $this->getCategoria()) ? $cat->term_id : false;
		return VGenerosPost::getPostsRandomByEtiquetas($tags, $max, $cat_id);
	}

	/**
	 * Devuelve el número total de entradas similares que se encuentran
	 *
	 * @return number
	 */
	public function getTotalSimilares() {
		return count($this->getSimilares());
	}

	/**
	 * Devuelve el total de entradas relacionadas
	 *
	 * @return number
	 */
	public function getTotalRelacionadas() {
		return count($this->_getRelacionadasIds());
	}

	/**
	 * Devuelve el ID de los post relacionados
	 *
	 * @return array<integer>
	 */
	private function _getRelacionadasIds() {
		global $wpdb;
		$title = '%' . $this->post_title . '%';
		return $wpdb->get_col($wpdb->prepare('SELECT ID FROM wp_posts
				WHERE post_type = "post"
					AND post_status = "publish"
					AND post_title LIKE %s
					AND ID != %d
				ORDER BY RAND()', $title, $this->ID));
	}

	/**
	 * Devuelve un array con posts relacionados basásndose en su título
	 *
	 * @param number $max
	 *        	Número máximo de posts 'refencia' que queremos
	 * @return array<Post>
	 */
	public function getRelacionadas($max = self::NUM_RELACIONADAS) {
		$cont = 0;
		foreach ($this->_getRelacionadasIds() as $post_id) {
			$posts[] = Post::find($post_id);
			if (++ $cont == $max) {
				break;
			}
		}
		return $posts;
	}

	/**
	 * Devuelve el número total de favoritos que tiene el Post
	 *
	 * @return number Total de favoritos que tiene el Post
	 */
	public function getTotalMeGustas() {
		global $wpdb;
		return (int) $wpdb->get_var($wpdb->prepare('
				SELECT COUNT(*) FROM wp_favoritos
				WHERE post_id = %d AND status = %d', $this->ID, Favorito::ACTIVO));
	}

	/**
	 *
	 * @return string
	 */
	public function getNonceMeGusta() {
		return $this->crearNonce(Ajax::ME_GUSTA, $this->ID);
	}

	/**
	 *
	 * @return string
	 */
	public function getNonceNotificar() {
		return $this->crearNonce(Ajax::NOTIFICAR, $this->ID);
	}

	/**
	 *
	 * @param string $user_id
	 * @return boolean
	 */
	public function isMeGusta($user_id = false) {
		if (! $user_id) {
			$user_id = wp_get_current_user()->ID;
		}
		global $wpdb;
		return (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*)
				FROM ' . $wpdb->prefix . 'favoritos
				WHERE user_id = %d
				AND post_id = %d
				AND status = %d', $user_id, $this->ID, Favorito::ACTIVO));
	}

	/**
	 * Devuelve un array con los usuarios que le dieron a me gusta a el Post
	 *
	 * @return array<User>
	 */
	public function getUsersQueGustan() {
		global $wpdb;
		$result = $wpdb->get_results($wpdb->prepare('SELECT user_id
				FROM ' . $wpdb->prefix . 'favoritos
				WHERE post_id = %d
				AND status = %d
				order by updated_at desc', $this->ID, Favorito::ACTIVO));
		$users = [];
		foreach ($result as $r) {
			$users['user'][] = User::find($r->user_id);
		}
		return $users;
	}

	/**
	 * Devuelve true si hay usuarios que le dieron a me gusta al Post
	 *
	 * @return boolean
	 */
	public function hayUsersQueGustan() {
		return count($this->getUsersQueGustan()) > 0;
	}

	/**
	 * Devuelve una lista limitada de usuarios y el número adicional de usuarios a los que le dieron un me gusta
	 *
	 * @return array<array<User>, integer>
	 */
	public function getUsersQueGustanLimitado($numUserQueGustan = self::NUM_USER_QUE_GUSTAN_DEFAULT) {
		$users = $this->getUsersQueGustan();
		$cantidad = 0;
		$count = count($users);
		if ($count > $numUserQueGustan) {
			$users = array_slice($users, 0, $numUserQueGustan);
			$cantidad = $count - $numUserQueGustan;
		}
		return [
			'lista' => $users,
			'mas' => $cantidad
		];
	}

	/**
	 * Crear me gusta
	 *
	 * @param integer $user_id
	 *        	Identificador del User
	 * @return json
	 */
	public function crearMeGusta($user) {
		global $wpdb;
		// Comprobamos si dicho usuario ya le dió alguna vez a me gusta a ese post
		$num = (int) $wpdb->get_var($wpdb->prepare('
				SELECT COUNT(*) FROM wp_favoritos
				WHERE post_id = %d AND user_id =%d', $this->ID, $user->ID));

		// Si no existe, lo creamos con estado borrado por defecto.
		if (! $num) {
			$result = $wpdb->query($wpdb->prepare('
					INSERT INTO wp_favoritos (post_id, user_id, status, created_at, updated_at)
					VALUES (%d, %d, %d, null, null );', $this->ID, $user->ID, Favorito::BORRADO));
		}

		// Obtenemos su estado actual
		$estado = (int) $wpdb->get_var($wpdb->prepare('SELECT status FROM wp_favoritos
				WHERE post_id = %d AND user_id = %d', $this->ID, $user->ID));
		// Si está borrado lo ponemos activo
		if ($estado == Favorito::BORRADO) {
			$result = $wpdb->query($wpdb->prepare('
					UPDATE wp_favoritos
					SET status =  %d, count = count + 1
					WHERE post_id = %d AND user_id = %d
					AND status = %d;', Favorito::ACTIVO, $this->ID, $user->ID, Favorito::BORRADO));
		} else {
			// Si está activo lo ponemos borrado
			$result = $wpdb->query($wpdb->prepare('
					UPDATE wp_favoritos
					SET status =  %d, count = count + 1
					WHERE post_id = %d AND user_id = %d
					AND status = %d;', Favorito::BORRADO, $this->ID, $user->ID, Favorito::ACTIVO));
		}

		return $result;
	}

	/**
	 * Quitar me gusta
	 *
	 * @param User $user
	 * @return integer
	 */
	public function quitarMeGusta($user) {
		global $wpdb;
		$statusActivo = Favorito::ACTIVO;
		$statusBorrado = Favorito::BORRADO;
		// Segundo comprobamos si dicho usuario ya le dió alguna vez a me gusta a ese post
		$num = (int) $wpdb->get_var('SELECT COUNT(*)
		 		FROM ' . $wpdb->prefix . "favoritos
				WHERE post_id = {$this->ID}
				AND user_id = {$user->ID};");

		// Si no existe, lo creamos
		if (! $num) {
			$result = $wpdb->query($wpdb->prepare("
						INSERT INTO {$wpdb->prefix}favoritos (post_id, user_id, created_at, updated_at)
						VALUES (%d, %d, null, null );", $this->ID, $user->ID));
		} else {
			// Si ya existe, aumetamos su contador y modificamos su estado para decir que te gusta
			$result = $wpdb->query($wpdb->prepare("
						UPDATE {$wpdb->prefix}favoritos
						SET status =  $statusBorrado, count = count + 1
						WHERE post_id = %d
							AND user_id = %d
							AND status = $statusActivo;", $this->ID, $user->ID));
		}
		return $result;
	}

	/**
	 * Devuelve el nonce para un ver las entradas similares
	 *
	 * @return string
	 */
	public function getNonceEntradasSimilares() {
		return $this->crearNonce(Post::ENTRADAS_SIMILARES);
	}

	/**
	 * Devuelve el nonce para un ver las entradas relacionadas
	 *
	 * @return string
	 */
	public function getNonceEntradasRelacionadas() {
		return $this->crearNonce(Post::ENTRADAS_RELACIONADAS);
	}
}