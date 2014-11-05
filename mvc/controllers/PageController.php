<?php
require_once 'BaseController.php';
require_once 'HomeController.php';

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

		$seccion = HomeController::getSeccion($current_category, 4);

		return $this->renderPage('categoria', [
			'seccion' => $seccion
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
			'post' => $post
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
			return $this->renderPage('error', [
				'num' => 404
			]);
		}

		$listaBloqueados = UserBloqueado::getByStatus(UserBloqueado::ESTADO_BLOQUEADO);

		return $this->renderPage('pages/users_bloqueados', [
			'bloqueados' => $listaBloqueados,
			'hay_bloqueados' => count($listaBloqueados) > 0,
			'estado_borrado' => UserBloqueado::ESTADO_BORRADO
		]);
	}

	/**
	 * page-pending-users.php
	 */
	public function getUsuariosPendientes() {
		if (! $this->current_user || ! $this->current_user->canEditor()) {
			return $this->renderPage('error', [
				'num' => 404
			]);
		}
		$listaPendientes = UserPendiente::getByStatus(UserPendiente::PENDIENTE);
		$listaAceptados = UserPendiente::getByStatus(UserPendiente::ACEPTADO);
		$listaRechazados = UserPendiente::getByStatus(UserPendiente::RECHAZADO);
		return $this->renderPage('pages/users_pendientes', [
			'pendientes' => $listaPendientes,
			'hay_pendientes' => count($listaPendientes) > 0,
			'aceptados' => $listaAceptados,
			'hay_aceptados' => count($listaAceptados) > 0,
			'rechazados' => $listaRechazados,
			'hay_rechazados' => count($listaRechazados) > 0,
			'estado' => Revision::USER_DESBANEADO
		]);
	}

	/**
	 * page-revisions.php
	 */
	public function getRevisiones() {
		/*
		 * Parsear revisiones
		 */
		$_parsearRevisiones = function ($listaRevisiones, $pendiente) {
			/*
			 * Parsear los usuarios por revisión
			 */
			$_parsearUsersByRevision = function ($revision, $estado = Revision::ESTADO_PENDIENTE) {
				global $wpdb;
				$user_ids = $wpdb->get_results($wpdb->prepare('
						SELECT user_id, updated_at, count
						FROM wp_revisiones
						WHERE post_id = %d
						AND status = %d', $revision->post_id, $estado));
				$users = [];
				foreach ($user_ids as $u) {
					$user = User::find($u->user_id);
					$users[] = [
						'user' => $user,
						'updated_at' => $u->updated_at,
						'count' => $u->count
					];
				}
				return $users;
			};

			$revisiones = [];
			$num = 0;
			foreach ($listaRevisiones as $revision) {
				$post = Post::find($revision->post_id);
				if (isset($revisiones[$post->ID])) {
					continue;
				}
				$_revision = [
					'num' => ++ $num,
					'count' => $revision->count,
					'permalink' => $post->getUrl(),
					'post_id' => $post->ID,
					'title' => $post->post_title,
					'pendiente' => $pendiente,
					'estado' => (! $pendiente) ? Revision::ESTADO_PENDIENTE : Revision::ESTADO_CORREGIDO,
					'estado_borrar' => Revision::ESTADO_BORRADO,
					'usuarios' => $_parsearUsersByRevision($revision, ($pendiente) ? Revision::ESTADO_PENDIENTE : Revision::ESTADO_CORREGIDO)
				];
				$revisiones[$post->ID] = $_revision;
			}
			$revisiones = array_values($revisiones);
			return $revisiones;
		};

		$_parsearRevisionesBan = function ($listaBaneos) {
			$revisiones = [];
			foreach ($listaBaneos as $num => $l) {
				$user = User::find($l->user_id);
				$editor = User::find($l->editor_id);
				$revision = [];
				$revision['num'] = $num + 1;
				$revision['user'] = $user;
				$revision['editor'] = $editor;
				$revision['updated_at'] = $l->updated_at;
				$revisiones[] = $revision;
			}
			return $revisiones;
		};

		$listaBaneos = Revision::allBan();
		$baneos = $_parsearRevisionesBan($listaBaneos);

		$listaPendientes = Revision::where('status', '=', Revision::ESTADO_PENDIENTE);
		$listaRevisadas = Revision::where('status', '=', Revision::ESTADO_CORREGIDO);

		$pendientes = $_parsearRevisiones($listaPendientes, $pendiente = true);
		$revisadas = $_parsearRevisiones($listaRevisadas, $pendiente = false);

		return $this->renderPage('pages/revisiones', [
			'total_pendientes' => Revision::getTotalPorRevisar(),
			'pendientes' => [
				'estado' => 'Pendientes',
				'reportes' => $pendientes
			],
			'revisadas' => [
				'estado' => 'Revisadas',
				'reportes' => $revisadas
			],
			'baneados' => [
				'baneos' => $baneos,
				'estado' => Revision::USER_DESBANEADO
			]
		]);
	}
}
