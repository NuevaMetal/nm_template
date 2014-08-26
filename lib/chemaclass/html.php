<?php
/**
 * Clase con recursos para el HTML
 *
 * @author chemaclass
 *
 */
class Html {

	/**
	 * Quitar atributos a las etiquetas html que se encuentre.
	 * Todos los atributos por default, pero se le pueden especificar
	 * cuales, pasándoles su nombre en un array como segundo parámetro
	 *
	 * @param string $html
	 *        HTML a buscar
	 * @param string $attr
	 *        Lista con los nombres de los attr a eliminar
	 * @return html sin los attr especificados en sus etiquetas
	 */
	public static function quitarAtributos($html, $attr = null) {
		if ($attr == null) {
			Utils::debug("attr == null");
			return preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $html);
		}
		Utils::debug("attr != null");
		array_walk($attr, function (&$item) {
			$item .= '="([a-z][a-z0-9]*)"';
		});
		$attrStr = '[' . implode('|', $attr) . ']';
		// $pattern = "/<([a-z][a-z0-9]*)(?=$attrStr)[^>]*?(\/?)>/i"; // ?
		// dd($pattern);
		// TODO: Falta implementar, poder elegir qué attr eliminar
		return $html;
	}

}