<?php

namespace Controllers;

use Libs\Utils;
use Models\User;
use Models\Post;
use I18n\I18n;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use Mustache_Logger_StreamLogger;

/**
 *
 * @author chema
 */
abstract class BaseController {

	/*
	 * Miembros
	 */
	protected $template = "";
	protected $current_user;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Mustache_Autoloader::register();
		$this->current_user = Utils::getCurrentUser();

		$templatesFolder = self::getTemplatesFolderLocation();

		$this->template = new Mustache_Engine(array(
			'cache_file_mode' => 0660,
			'cache_lambda_templates' => true,
			'loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
			'partials_loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
			'helpers' => array(
				'trans' => function ($text, $params = []) {
					return I18n::trans($text, $params);
				},
				'transu' => function ($text, $params = []) {
					return I18n::transu($text, $params);
				}
			),
			'escape' => function ($value) {
				return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
			},
			'charset' => 'UTF-8',
			'logger' => new Mustache_Logger_StreamLogger('php://stderr'),
			'strict_callables' => true,
			'pragmas' => [
				Mustache_Engine::PRAGMA_FILTERS,
				Mustache_Engine::PRAGMA_BLOCKS
			]
		));

		$this->template->addHelper('case', [
			'lower' => function ($value) {
				return strtolower((string) $value);
			},
			'upper' => function ($value) {
				return strtoupper((string) $value);
			}
		]);
	}

	/**
	 * Devuelve la ruta relativa donde se encuentran las vistas
	 *
	 * @return string
	 */
	protected static function getTemplatesFolderLocation() {
		return str_replace('//', '/', dirname(__FILE__) . '/') . '../templates';
	}

	/**
	 * Devuelve las alertas que tenga el usuario actual pendientes
	 *
	 * @return array>View> Alertas
	 */
	private function _getAlertas() {
		$alertas = [];
		$user = Utils::getCurrentUser();

		if ($user && ($total = $user->getTotalMensajesRecibidosSinLeer())) {
			if ($total == 1) {
				$msg = I18n::trans('actividad.tienes_un_mensaje_nuevo');
			} else {
				$msg = I18n::trans('actividad.tienes_mensajes_nuevos', [
					'total' => $total
				]);
			}
			$alertas[] = $this->renderAlertaDanger($msg, I18n::transu('mensajes'), home_url() . '/messages');
		}
		return $alertas;
	}

	/**
	 * 404.php
	 */
	public function getError($num = 404) {
		return $this->renderPage('error', [
			'num' => $num,
			'mensaje' => I18n::trans('error.' . $num)
		]);
	}

	/**
	 * Añadimos las variables comunes
	 *
	 * @param
	 *        	array &$templateVars
	 */
	private function _addVarsToTemplateVars(&$templateVars) {
		$templateVars = array_merge($templateVars, [
			'current_user' => $this->current_user,
			'template_url' => get_template_directory_uri(),
			'home_url' => get_home_url()
		]);
	}

	/**
	 *
	 * @param unknown $templateName
	 * @param string $templateVars
	 */
	public function render($templateName, $templateVars = []) {
		$this->_addVarsToTemplateVars($templateVars);
		return $this->template->render($templateName, $templateVars);
	}

	/**
	 *
	 * @param unknown $templateName
	 * @param string $templateVars
	 */
	public function renderPage($templateName, $templateVars = []) {
		$this->_addVarsToTemplateVars($templateVars);
		echo $this->render('header', self::getBlogInfoData());
		wp_head();
		echo $this->render('header_close');
		echo $this->render($templateName, $templateVars);
		wp_footer();
		echo $this->render('footer');
	}

	/**
	 * Devuelve una lista con la información básica del blog
	 *
	 * @return multitype:string NULL
	 */
	public static function getBlogInfoData() {
		return array(
			'blog_title' => self::_getBlogTitle(),
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

	/**
	 *
	 * @return string
	 */
	private static function _getBlogTitle() {
		if (is_home()) {
			return get_bloginfo('name');
		} else {
			return wp_title("-", false, "right") . " " . get_bloginfo('name');
		}
	}

	/*
	 * ALERTAS
	 */

	/**
	 * Crear una alerta
	 *
	 * @param string $tipo
	 *        	Tipo de alerta. Será el nombre de la clase que definirá el estilo de la alerta
	 * @param string $mensaje
	 * @param string $strong
	 * @param unknown $args
	 * @return View
	 */
	private function _renderAlerta($tipo, $mensaje, $strong = false, $href = false, $args = []) {
		$args['tipo'] = $tipo;
		$args['mensaje'] = $mensaje;
		$args['strong'] = $strong;
		$args['href'] = $href;
		return $this->render('ajax/alerta', $args);
	}

	/**
	 * Crear una alerta de tipo Success
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaSuccess($mensaje, $strong = false, $href = false) {
		return $this->_renderAlerta('success', $mensaje, $strong, $href);
	}

	/**
	 * Crear una alerta de tipo Danger
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	public function renderAlertaDanger($mensaje, $strong = false, $href = false) {
		return $this->_renderAlerta('danger', $mensaje, $strong, $href);
	}

	/**
	 * Crear una alerta de tipo Info
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaInfo($mensaje, $strong = false, $href = false) {
		return $this->_renderAlerta('info', $mensaje, $strong, $href);
	}

	/**
	 * Crear una alerta de tipo Warning
	 *
	 * @param string $mensaje
	 * @param string $strong
	 * @return View
	 */
	protected function renderAlertaWarning($mensaje, $strong = false, $href = false) {
		return $this->_renderAlerta('warning', $mensaje, $strong, $href);
	}

	/**
	 *
	 * @param string $dateFormat
	 * @param string $postType
	 * @param unknown $numberPostsToFetch
	 * @param unknown $customFields
	 * @param string $oddOrEven
	 * @param unknown $moreQuerySettings
	 * @return Ambigous <multitype:Ambigous , object, NULL, unknown>
	 */
	public static function getPosts($dateFormat = false, $postType = 'post', $numberPostsToFetch = -1, $customFields = [], $oddOrEven = false, $moreQuerySettings = []) {
		// Obtengo los post fijados
		$posts = self::_getStickyPosts($dateFormat, $postType, $numberPostsToFetch, $customFields, $oddOrEven, $moreQuerySettings);
		$isCat = isset($moreQuerySettings['cat']);
		$postsStickyIds = [];
		// Recorro los post fijados totales y compruebo que su categoría se corresponda con la categoría que se está buscando
		foreach (get_option('sticky_posts') as $post_id) {
			if ($isCat && ($post = Post::find($post_id)) && $post->getCategoria()->term_id == $moreQuerySettings['cat']) {
				$postsStickyIds[] = $post_id;
			}
		}
		// Comparamos la cantidad de post fijados totales con la cantidad de post fijados que hemos obtenido
		$countSticky = count($postsStickyIds);
		// De ser igual quiere decir que tenemos que restarle a la cantidad pedida el número de post fijados totales, de lo contrario
		// la cantidad pedida seguirá siendo la misma.
		$numberPostsToFetch = (count($posts) == $countSticky) ? $numberPostsToFetch - $countSticky : $numberPostsToFetch;

		$querySettings = [
			'orderby' => [
				'date' => 'DESC'
			],
			'post_type' => [
				$postType
			],
			'post__not_in' => $postsStickyIds,
			'posts_per_page' => $numberPostsToFetch,
			'post_status' => 'publish'
		];
		$querySettings = array_merge($querySettings, $moreQuerySettings);
		$loop = new \WP_Query($querySettings);

		return array_merge($posts, self::_loop($loop));
	}

	/**
	 * Devuelve los post fijados
	 *
	 * @return array<Post>
	 */
	private static function _getStickyPosts($dateFormat = false, $postType = 'post', $numberPostsToFetch = -1, $customFields = array(), $oddOrEven = false, $moreQuerySettings = array()) {
		$sticky_posts = get_option('sticky_posts');
		if (! $sticky_posts) {
			return [];
		}
		$querySettings = [
			'post_type' => [
				$postType
			],
			'post__in' => $sticky_posts,
			'posts_per_page' => $numberPostsToFetch
		];
		$querySettings = array_merge($querySettings, $moreQuerySettings);
		$loop = new \WP_Query($querySettings);

		return self::_loop($loop);
	}

	/**
	 * Recorrer la query y monta los objetos Post
	 *
	 * @param WP_Query $loop
	 * @param boolean $oddOrEven
	 * @return array<Post>
	 */
	private static function _loop($loop, $oddOrEven = false) {
		$posts = [];
		for($index = 0; $loop->have_posts(); $index ++) {
			$loop->the_post();
			if (! ($oddOrEven) || ($oddOrEven == 'EVEN' && $index % 2) || ($oddOrEven == 'ODD' && ! ($index % 2))) {
				$posts[] = Post::find(get_the_ID());
			}
		}
		return $posts;
	}
}
