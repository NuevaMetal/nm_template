<?php

namespace Models;

use I18n\I18n;
use Libs\Utils;

/**
 * Modelo que representa una etiqueta.
 * terms.term_id					->	term_taxonomy.term_id
 * term_taxonomy.term_taxonomy_id	->	term_relationships.term_taxonomy_id.
 * Este modelo sirve para hacer las consultas y operaciones sobre las etiquetas de las entradas.
 *
 * @author José María Valera Reales <@Chemaclass>
 */
class Etiqueta {

	const LIMIT_ETIQUETAS_CONTADAS = 10;

	const LIMIT_TODAS_ETIQUETAS = 10;

	const TRANSIENT_ETIQUETAS_CONTADAS = 'etiquetas-contadas';

	const TRANSIENT_TODAS_ETIQUETAS_CONTADAS = 'todas-etiquetas-contadas';

	/**
	 * Devuelve una lista con todas las etiquetas
	 *
	 * @return array<Etiqueta> Listado de todas las etiquetas.
	 */
	public static function getTodasEtiquetas($limit = self::LIMIT_TODAS_ETIQUETAS) {
		global $wpdb;
		return $wpdb->results($wpdb->prepare('
				SELECT  term_taxonomy_id as taxonomy_id, name, slug
				FROM wp_term_taxonomy ta
				JOIN wp_terms te ON (te.term_id = ta.term_id)
				WHERE taxonomy = "post_tag"
				ORDER BY name desc
				LIMIT %d', $limit));
	}

	/**
	 * Devuelve una lista con todas las etiquetas
	 *
	 * @see http://codex.wordpress.org/Transients_API
	 * @return array<Etiqueta> Listado de todas las etiquetas.
	 */
	public static function getEtiquetasContadas($limit = self::LIMIT_ETIQUETAS_CONTADAS) {
		global $wpdb;
		if (false === ($results = get_transient(self::TRANSIENT_ETIQUETAS_CONTADAS))) {
			$results = $wpdb->get_results($wpdb->prepare('
				SELECT ta.term_taxonomy_id as taxonomy_id, name, slug, count(*) total
				FROM wp_term_taxonomy ta
				JOIN wp_terms te ON (te.term_id = ta.term_id)
				JOIN wp_term_relationships re ON (re.term_taxonomy_id = ta.term_taxonomy_id)
				WHERE taxonomy = "post_tag"
				GROUP BY name, slug, taxonomy_id
				ORDER BY total DESC, name, slug
				LIMIT %d', $limit));
			set_transient(self::TRANSIENT_ETIQUETAS_CONTADAS, $results, 12 * HOUR_IN_SECONDS);
		}
		return $results;
	}

	/**
	 * Devuelve la lista de todas las etiquetas contadas.
	 * Guardaremos el resultado de la query en un transient por unas horas.
	 *
	 * @see http://codex.wordpress.org/Transients_API
	 * @return array<Etiqueta> Listado de todas las etiquetas.
	 */
	public static function getTodasEtiquetasContadas() {
		global $wpdb;
		//delete_transient(self::TRANSIENT_TODAS_ETIQUETAS_CONTADAS);
		if (false === ($results = get_transient(self::TRANSIENT_TODAS_ETIQUETAS_CONTADAS))) {
			$results = $wpdb->get_results('
				SELECT ta.term_taxonomy_id as taxonomy_id, name, slug, count(*) total
				FROM wp_term_taxonomy ta
				JOIN wp_terms te ON (te.term_id = ta.term_id)
				JOIN wp_term_relationships re ON (re.term_taxonomy_id = ta.term_taxonomy_id)
				WHERE taxonomy = "post_tag"
				GROUP BY name, slug, taxonomy_id
				ORDER BY name ASC, total, slug');
			set_transient(self::TRANSIENT_TODAS_ETIQUETAS_CONTADAS, $results, 12 * HOUR_IN_SECONDS);
		}
		return $results;
	}
}