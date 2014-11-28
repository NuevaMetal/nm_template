<?php

namespace Controllers;

use Libs\Utils;
use Libs\Env;
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
	private $minimizarCodigo;
	protected $current_user;
	protected $template;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->minimizarCodigo = false; //Env::isProduccion();
		$this->current_user = Utils::getCurrentUser();

		$templatesFolder = self::getTemplatesFolderLocation();

		$this->template = new Mustache_Engine(array(
			'cache_file_mode' => 0660,
			'cache_lambda_templates' => true,
			'loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
			'partials_loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
			'helpers' => array(
				'trans' => function ($value) {
					return I18n::trans($value);
				},
				'transu' => function ($value) {
					return I18n::transu($value);
				},
				'case' => [
					'lower' => function ($value) {
						return strtolower((string) $value);
					},
					'upper' => function ($value) {
						return strtoupper((string) $value);
					}
				],
				'count' => function ($value) {
					return count($value);
				},
				'date' => [
					'xmlschema' => function ($value) {
						return date('c', strtotime($value));
					},
					'string' => function ($value) {
						return date('l, d F Y', strtotime($value));
					},
					'format' => function ($value) {
						return date(get_option('date_format'), strtotime($value));
					}
				],
				'formatear_numero' => function ($value) {
					return Utils::formatearNumero($value);
				},
				'quitar_guiones' => function ($value) {
					return str_replace('-', ' ', $value);
				},
				'toArray' => function ($value) {
					return explode(',', $value);
				},
				'trans_palabras' => function ($value) {
					$out = '';
					foreach (explode(' ', $value) as $traducir) {
						$out .= ' ' . I18n::trans($traducir);
					}
					return ucfirst($out);
				},
				'ucfirst' => function ($value) {
					return ucfirst($value);
				},
				'si0poner1' => function ($value) {
					return ($value == 0) ? 1 : $value;
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
	 * 404.php
	 */
	public function getError($num = 404) {
		return $this->renderPage('error', [
			'num' => $num,
			'mensaje' => I18n::trans('error.' . $num)
		]);
	}

	/**
	 * Añadimos las variables comunes que todos los controladores.
	 * Aquí añadiremos las variables comunes como el usuario actual, entorno, etc, que tendrán
	 * disponibles todas las vistas.
	 *
	 * @param array $templateVars
	 *        	Referencia del array con las variables que pasaran todos los controladores a sus vistas
	 */
	private function _addVariablesGlobales($templateVars = []) {
		// debug($_SERVER['REQUEST_URI']);

		$citas = array_values(I18n::getFicheroIdioma('citas'));

		return array_merge($templateVars, [
			'blog_title' => self::_getBlogTitle(),
			'descripcion' => I18n::trans('nm.descripcion'),
			'current_user' => $this->current_user,
			'home_url' => get_home_url(),
			'is_produccion' => Env::isProduccion(),
			'is_desarrollo' => Env::isDesarrollo(),
			'is_local' => Env::isLocal(),
			'template_url' => get_template_directory_uri(),
			'login_url' => wp_login_url($_SERVER['REQUEST_URI']),
			'current_lang' => I18n::getLangByCurrentUser(),
			'cita_random' => $citas[rand(0, count($citas)-1)]
		]);
	}

	/**
	 * Pintar header + plantilla + footer
	 *
	 * @param string $templateName
	 *        	Nombre de la vista a pintar
	 * @param array $templateVars
	 *        	Parámetros para la vista
	 */
	public function renderPage($templateName, $templateVars = []) {
		$templateVars = $this->_addVariablesGlobales($templateVars);
		// Pintamos el header, la plantilla que nos dieron y seguidamente el footer
		foreach ([
			'header',
			$templateName,
			'footer'
		] as $_template) {
			echo $this->render($_template, $templateVars);
		}
	}

	/**
	 * Pintar un partial
	 *
	 * @param string $templateName
	 *        	Nombre del partial a pintar
	 * @param array $templateVars
	 *        	Parámetros para la vista
	 */
	public function render($templateName, $templateVars = []) {
		$html = $this->template->render($templateName, $this->_addVariablesGlobales($templateVars));
		return $this->minimizarHtml($html);
	}

	/**
	 * /**
	 * Devuelve el código html minimizado.
	 * Todo en una línea y sin espacios ni intros.
	 *
	 * @param string $html
	 * @param string $completo
	 * @return string
	 */
	protected function minimizarHtml($html) {
		// Eliminar comentarios de html
		$explode = explode("\n", $html);
		if ($this->minimizarCodigo) {
			// Quito tabulaciones
			$explode = array_map('trim', $explode);
		}
		// Quito líneas en blanco y saltos de línea
		$explodeFiltered = array_filter($explode, 'trim');
		if ($this->minimizarCodigo) {
			return implode(" ", $explodeFiltered);
		}
		return implode("\n", $explodeFiltered);
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
		return $this->render('partials/alertas/_alerta', $args);
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
