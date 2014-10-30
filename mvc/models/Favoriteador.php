<?php
require_once 'ModelBase.php';
/**
 * Modelo que representa un favoriteador.
 * Que puede tener favoritos
 *
 * @author José María Valera Reales <@Chemaclass>
 */
abstract class Favoriteador extends ModelBase {

	/**
	 * Devuelve una lista de arrays con las etiquetas de las entradas a las que le dio favoritos
	 *
	 * @return array
	 */
	public function getArrayEtiquetasFavoritas($cant = User::NUM_ETI_FAV_PERFIL_DEFAULT) {
		$favoritos = $this->getFavoritos(false, false, false);
		$tags = [];
		foreach ($favoritos as $postFavorito) {
			if ($postFavorito->tieneEtiquetas()) {
				foreach ($postFavorito->getEtiquetas() as $t) {
					if (isset($tags[$t->name])) {
						$tags[$t->name]->total ++;
					} else {
						$tags[$t->name] = $t;
						$tags[$t->name]->total = 1;
					}
				}
			}
		}
		// Ordenamos el array de etiquetas por su cantidad total
		usort($tags, function ($a, $b) {
			return $a->total < $b->total;
		});

		if ($cant) {
			return array_slice($tags, 0, $cant);
		}

		return $tags;
	}

	/**
	 * Devuelve las IDs de los posts favoritos
	 *
	 * @param number $offset
	 * @param number $limit
	 * @param string $categoria
	 * @return array<integer>
	 */
	private function _getFavoritosIds($offset = 0, $limit = User::NUM_FAV_PERFIL_DEFAULT, $categoria = false) {
		global $wpdb;
		$tabla = $wpdb->prefix . Favorito::$table;
		$user_id = $this->ID;
		if ($categoria) {
			$sql = 'SELECT distinct post_id
					FROM wp_favoritos, (
							SELECT object_id as id
							FROM wp_term_relationships rel
							JOIN wp_term_taxonomy tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
							JOIN wp_terms ter ON tax.term_id = ter.term_id
						WHERE name = %s
						AND taxonomy = "category") p
					WHERE user_id = %d
					AND status = %d
					AND post_id IN (p.id)
					ORDER BY updated_at desc ';
		} else {
			$sql = 'SELECT distinct post_id FROM wp_favoritos
				WHERE user_id = %d
				AND status = %d
				ORDER BY updated_at desc ';
		}
		if ($limit) {
			$sql .= ' LIMIT ' . $limit;
		}
		if ($offset) {
			$sql .= ' OFFSET ' . $offset;
		}
		if ($categoria) {
			return $wpdb->get_col($wpdb->prepare($sql, $categoria, $this->ID, Favorito::ACTIVO));
		}
		return $wpdb->get_col($wpdb->prepare($sql, $this->ID, Favorito::ACTIVO));
	}

	/**
	 * Devuelve la lista de todos los favoritos de un User
	 *
	 * @param number $limit
	 * @param number $offset
	 * @param boolean $conCategorias
	 * @return array
	 */
	public function getFavoritos($offset = 0, $limit = User::NUM_FAV_PERFIL_DEFAULT, $categoria = false) {
		foreach ($this->_getFavoritosIds($offset, $limit, $categoria) as $post_id) {
			$posts[] = Post::find($post_id);
		}
		return $posts;
	}

	/**
	 * Devuelve la lista de favoritos de bandas
	 */
	public function getFavoritosBandas($offset = 0, $limit = User::NUM_POSTS_FAV) {
		return $this->getFavoritos($offset, $limit, Post::CATEGORY_BANDAS);
	}

	/**
	 * Devuelve la lista de favoritos de vídeos
	 */
	public function getFavoritosVideos($offset = 0, $limit = User::NUM_POSTS_FAV) {
		return $this->getFavoritos($offset, $limit, Post::CATEGORY_VIDEOS);
	}

	/**
	 * Devuelve la lista de favoritos de Críticas
	 */
	public function getFavoritosCriticas($offset = 0, $limit = User::NUM_POSTS_FAV) {
		return $this->getFavoritos($offset, $limit, Post::CATEGORY_CRITICAS);
	}

	/**
	 * Devuelve la lista de favoritos de Crónicas
	 */
	public function getFavoritosCronicas($offset = 0, $limit = User::NUM_POSTS_FAV) {
		return $this->getFavoritos($offset, $limit, Post::CATEGORY_CRONICAS);
	}

	/**
	 * Devuelve la lista de favoritos de Noticias
	 */
	public function getFavoritosNoticias($offset = 0, $limit = User::NUM_POSTS_FAV) {
		return $this->getFavoritos($offset, $limit, Post::CATEGORY_NOTICIAS);
	}

	/**
	 * Devuelve la lista de favoritos de Conciertos
	 */
	public function getFavoritosConciertos($offset = 0, $limit = User::NUM_POSTS_FAV) {
		return $this->getFavoritos($offset, $limit, Post::CATEGORY_CONCIERTOS);
	}

	/**
	 * Devuelve la lista de favoritos de Entrevistas
	 */
	public function getFavoritosEntrevistas($offset = 0, $limit = User::NUM_POSTS_FAV) {
		return $this->getFavoritos($offset, $limit, Post::CATEGORY_ENTREVISTAS);
	}

	/**
	 * Devuelve el número total de favoritos que tiene el user
	 *
	 * @return number Total de favoritos que tiene el User
	 */
	public function getTotalFavoritos($categoria = false) {
		return count($this->_getFavoritosIds(false, false, $categoria));
	}

	/**
	 * Devuelve el número total de favoritos bandas
	 *
	 * @return number
	 */
	public function getTotalFavoritosBandas() {
		return $this->getTotalFavoritos(Post::CATEGORY_BANDAS);
	}
	public function getTotalFavoritosVideos() {
		return $this->getTotalFavoritos(Post::CATEGORY_VIDEOS);
	}
	public function getTotalFavoritosEntrevistas() {
		return $this->getTotalFavoritos(Post::CATEGORY_ENTREVISTAS);
	}
	public function getTotalFavoritosCriticas() {
		return $this->getTotalFavoritos(Post::CATEGORY_CRITICAS);
	}
	public function getTotalFavoritosCronicas() {
		return $this->getTotalFavoritos(Post::CATEGORY_CRONICAS);
	}
	public function getTotalFavoritosNoticias() {
		return $this->getTotalFavoritos(Post::CATEGORY_NOTICIAS);
	}
	public function getTotalFavoritosConciertos() {
		return $this->getTotalFavoritos(Post::CATEGORY_CONCIERTOS);
	}

	/**
	 * Devuelve el número total de favoritos que han recibido sus entradas
	 *
	 * @return string
	 */
	public function getTotalFavoritosRecibidos() {
		global $wpdb;
		$activo = Favorito::ACTIVO;
		return (int) $wpdb->get_var("SELECT SUM( p.totales )
			FROM (
				SELECT COUNT(ids.ID) as totales FROM wp_favoritos f,
					(SELECT ID FROM wp_posts
					where post_author = $this->ID) ids
				where f.post_id = ids.ID
				AND f.status = $activo
				GROUP BY f.user_id
			) p");
	}
}