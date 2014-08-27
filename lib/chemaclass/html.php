<?php
/**
 * Clase con recursos para el HTML
 *
 * @author José María Valera Reales <@Chemaclass>
 *
 */
class Html {

	/**
	 * Quitar atributos a las etiquetas html que se encuentre, o menos las que se encuentre.
	 * Por defecto las etiquetas indicadas (2º param) serán de las que no se borrarán sus attr.
	 *
	 * @param string $html
	 *        HTML a buscar
	 * @param string $attr
	 *        Lista con los nombres de las etiquetas que queremos encontrar
	 * @param boolean $aBorrar
	 *        true indica que se deben borrar los attr de las etiquetas indicadas
	 *        y false se indica que se tienen que borrar todos los attr de las
	 *        etiquetas menos las indicadas
	 * @return html sin los attr especificados en sus etiquetas
	 */
	public static function quitarAtributos($html, $attr = array(), $aBorrar = false) {
		if (empty($attr)) {
			return preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i", '<$1$2>', $html);
		}
		array_walk($attr, function (&$item) {
			$item = "\b$item\b";
		});
		$attrStr = implode('|', $attr);
		$accion = ($aBorrar) ? '=' : '!';
		$pattern = '~<(?' . $accion . '[' . $attrStr . '])([a-z][a-z0-9]*)[^>]*(/?)>~is';
		// 	dd($pattern); // '~<(?![\bimg\b|\biframe\b])([a-z][a-z0-9]*)[^>]*(/?)>~is'
		$html = preg_replace($pattern, '<$1$2>', $html);
		return $html;
	}

}