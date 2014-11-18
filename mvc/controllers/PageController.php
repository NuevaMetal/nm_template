<?php

namespace Controllers;

use I18n\I18n;
use Libs\Utils;
use Models\Post;
use Models\Revision;
use Models\User;
use Models\UserBloqueado;
use Models\UserPendiente;
use Models\UserBaneado;
use Models\Analitica;

/**
 * Controlador principal de la web
 *
 * @author chemaclass
 */
class PageController extends BaseController {

	/**
	 * Paǵina de sitios de interés
	 */
	public function getAmigas() {
		return $this->renderPage('pages/amigas');
	}

	/**
	 * Analitica.
	 * page-analytics.php
	 */
	public function getAnalitica() {
		$logueados_hoy = Analitica::getUsersLogueados(50);
		$logueados_ayer = Analitica::getUsersLogueados(50, 'date(now())-1');

		return $this->renderPage('pages/analitica', [
			'logueados_hoy' => $logueados_hoy,
			'logueados_ayer' => $logueados_ayer
		]);
	}

	/**
	 * Paǵina de contacto
	 */
	public function getContacto() {
		return $this->renderPage('pages/contacto');
	}
	/**
	 * category.php
	 */
	public function getCategory() {
		// TODO:
		$current_category = single_cat_title("", false);
		$current_category = strtolower($current_category);

		$args = HomeController::getSeccion($current_category, HomeController::NUM_POST_POR_SECCION);

		return $this->renderPage('categoria', [
			'seccion' => $args,
			'total_posts' => count($args['posts'])
		]);
	}

	/**
	 * Paǵina de aviso legal
	 */
	public function getLegal() {
		return $this->renderPage('pages/legal');
	}

	/**
	 * Paǵina de redes
	 */
	public function getRedes() {
		return $this->renderPage('pages/redes');
	}

	/**
	 * Paǵina de nuevametal
	 */
	public function getMega() {
		return $this->renderPage('pages/mega');
	}

	/**
	 * Paǵina de nuevametal
	 */
	public function getNuevaMetal() {
		return $this->renderPage('pages/nuevametal');
	}

	/**
	 * single.php
	 */
	public function getPost() {
		if (have_posts()) {
			the_post();
			$post = Post::find(get_the_ID());
		}

		if (! isset($post)) {
			return $this->renderPage('404');
		}

		return $this->renderPage('post', [
			'post' => $post,
			'ENTRADAS_SIMILARES' => Post::ENTRADAS_SIMILARES,
			'ENTRADAS_RELACIONADAS' => Post::ENTRADAS_RELACIONADAS
		]);
	}

	/**
	 * search.php
	 */
	public function getSearch() {
		$search_query = get_search_query();
		// Obtenemos los argumentos necesarios para pintarla
		$args = HomeController::getBusqueda($search_query, 4);
		$users = User::getUsersBySearch($search_query, $offset = 0, $limit = 4);
		$args['users'] = [
			'header' => I18n::trans('resultado_busqueda_usuarios', [
				'que' => $search_query
			]),
			'seccion' => 'busqueda-users',
			'a_buscar' => $search_query,
			'tipo' => Utils::TIPO_BUSCAR_USUARIOS,
			'cant' => 4,
			'total_usuarios' => count($users),
			'lista_usuarios' => $users
		];

		return $this->renderPage('busqueda', $args);
	}

	/**
	 * tag.php
	 */
	public function getTag() {
		$current_tag = single_tag_title('', false);
		$term = get_term_by('name', $current_tag, 'post_tag');

		$cant = 4;

		$args['imagen'] = 'NM_avatar_2';
		$args['seccion'] = 'busqueda';
		$args['a_buscar'] = strtolower($current_tag);
		$args['header'] = I18n::trans('resultado_tag', [
			'que' => $current_tag
		]);
		$args['url'] = get_tag_link($cat);
		$args['cant'] = $cant;
		$args['tipo'] = Utils::TIPO_TAG;
		$args['posts'] = HomeController::getPostsByTag($current_tag, $cant);

		/*
		 * Obtenemos el fichero de idioma 'generos' para obtener el valor de la definición de la tag
		 * apartir de la clave de la tag. Si no se encontrase retornaría un false, y por tanto no
		 * se llegaría a pintar.
		 */
		$fileGeneros = I18n::getFicheroIdioma('generos', I18n::getLangByCurrentUser());

		return $this->renderPage('categoria', [
			'definicion' => $fileGeneros['definicion_' . $term->slug],
			'seccion' => $args,
			'total_posts' => count($args['posts']),
			'tag_trans' => I18n::transu('generos.' . $term->slug)
		]);
	}

	/**
	 * Paǵina de tutorial
	 */
	public function getTutorial() {
		return $this->renderPage('pages/tutorial');
	}

	/**
	 * page-blocked-users.php
	 */
	public function getUsuariosBloqueados() {
		if (! $this->current_user || ! $this->current_user->canEditor()) {
			return $this->getError(404);
		}

		$listaBloqueados = UserBloqueado::getByStatus(UserBloqueado::ESTADO_BLOQUEADO);
		$totalBloqueados = UserBloqueado::getTotalBloqueados();

		return $this->renderPage('pages/users_bloqueados', [
			'bloqueados' => $listaBloqueados,
			'total_bloqueados' => $totalBloqueados,
			'estado_borrado' => UserBloqueado::ESTADO_BORRADO
		]);
	}

	/**
	 * page-pending-users.php
	 */
	public function getUsuariosPendientes() {
		if (! $this->current_user || ! $this->current_user->canEditor()) {
			return $this->getError(404);
		}
		$listaPendientes = UserPendiente::getByStatus(UserPendiente::PENDIENTE);
		$listaAceptados = UserPendiente::getByStatus(UserPendiente::ACEPTADO);
		$listaRechazados = UserPendiente::getByStatus(UserPendiente::RECHAZADO);
		return $this->renderPage('pages/users_pendientes', [
			'pendientes' => $listaPendientes,
			'total_pendientes' => count($listaPendientes),
			'aceptados' => $listaAceptados,
			'total_aceptados' => count($listaAceptados),
			'rechazados' => $listaRechazados,
			'total_rechazados' => count($listaRechazados)
		]);
	}

	/**
	 * page-revisions.php
	 */
	public function getRevisiones() {
		$listaPendientes = Revision::getPendientes();
		$listaRevisadas = Revision::getCorregidas();
		$listaBaneos = UserBaneado::getBaneados();

		return $this->renderPage('pages/revisiones', [
			'total_pendientes' => Revision::getTotalPendientes(),
			'total_corregidas' => Revision::getTotalCorregidas(),
			'total_baneados' => UserBaneado::getTotalBaneados(),
			'ESTADO_BORRADO' => Revision::ESTADO_BORRADO,
			'ESTADO_CORREGIDO' => Revision::ESTADO_CORREGIDO,
			'ESTADO_PENDIENTE' => Revision::ESTADO_PENDIENTE,
			'USER_BANEADO' => UserBaneado::BANEADO,
			'USER_DESBANEADO' => UserBaneado::DESBANEADO,
			'pendientes' => [
				'sEstado' => 'Pendientes',
				'lista' => $listaPendientes
			],
			'corregidas' => [
				'sEstado' => 'Corregidas',
				'lista' => $listaRevisadas
			],
			'baneados' => [
				'lista' => $listaBaneos
			]
		]);
	}
}
