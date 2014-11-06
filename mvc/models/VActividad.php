<?php

namespace Models;

use I18n\I18n;
use Libs\Utils;
use Models\Post;
use Models\User;
use Models\Comment;

/**
 * Analítica
 *
 * @author chema
 */
class VActividad extends ModelBase {
	public static $table = "v_actividades";

	const DATE_FORMAT = 'l, d F Y';

	/*
	 * Tipos de Actividad
	 */
	const TIPO_SEGUIMIENTO_USER = 'tipo_seguimiento_user';

	const TIPO_SEGUIMIENTO_USER_A_TI = 'tipo_seguimiento_user_a_ti';

	const TIPO_FAVORITOS_RECIBIDOS = 'tipo_favoritos_recibidos';

	const TIPO_ME_GUSTA = 'tipo_me_gusta';

	const TIPO_NUEVA_ENTRADA = 'tipo_nueva_entrada';

	const TIPO_ENTRADA_EDITADA = 'tipo_entrada_editada';

	const TIPO_NUEVO_COMENTARIO = 'tipo_nuevo_comentario';

	/*
	 * Puntos por tipos de actividad
	 */
	const PUNTOS_TIPO_SEGUIMIENTO_USER = 1;

	const PUNTOS_TIPO_SEGUIMIENTO_USER_A_TI = 4;

	const PUNTOS_TIPO_FAVORITOS_RECIBIDOS = 2;

	const PUNTOS_TIPO_ME_GUSTA = 1;

	const PUNTOS_TIPO_NUEVA_ENTRADA = 7;

	const PUNTOS_TIPO_ENTRADA_EDITADA = 0;

	const PUNTOS_TIPO_NUEVO_COMENTARIO = 3;

	/*
	 * Miembros
	 */
	public $user_id;
	public $que_id;
	public $tipo_que;
	public $updated_at;

	/**
	 * Constructor
	 *
	 * @param string $tipo_que
	 *        	Tipo de aquello a seguir
	 * @param string $user_id
	 *        	Identificador del User
	 * @param string $que_id
	 *        	Identificador de aquello a seguir
	 */
	public function __construct($tipo_que = false, $user_id = false, $que_id = false, $updated_at = false) {
		parent::__construct();
		if ($tipo_que) {
			$this->tipo_que = $tipo_que;
		}
		if ($user_id) {
			$this->user_id = $user_id;
		}
		if ($que_id) {
			$this->que_id = $que_id;
		}
		if ($updated_at) {
			$this->updated_at = $updated_at;
		}
	}

	/**
	 * Devuelve la fecha de la actividad
	 */
	public function getDate() {
		// date($dateFormat, strtotime($this->updated_at));
		$strToTime = strtotime($this->updated_at);
		// 1 (para lunes) hasta 7 (para domingo)
		$numDiaSemana = date('N', $strToTime);
		$nombreDia = Utils::getDiaTransByNum($numDiaSemana);
		// Representación numérica de un mes, sin ceros iniciales. 1 hasta 12
		$numMes = date('n', $strToTime);
		$nombremes = Utils::getMesTransByNum($numMes);
		$numDia = date('d', $strToTime); // Número del día
		$ano = date('Y', $strToTime); // Año
		return "$nombreDia, $numDia $nombremes $ano";
	}

	/**
	 * Devuelve la hora en la que se produjo la actividad
	 */
	public function getTime() {
		// date($dateFormat, strtotime($this->updated_at));
		$strToTime = strtotime($this->updated_at);
		$hora = date('H', $strToTime);
		$minutos = date('m', $strToTime);
		return "$hora:$minutos";
	}

	/**
	 * Devuelve el User
	 *
	 * @return User Usuario que sigue
	 */
	public function getUser() {
		return User::find($this->user_id);
	}

	/**
	 * Devuelve el objeto al que pertenece el seguimiento
	 *
	 * @return object
	 */
	public function getQue() {
		// La ID de la tabla comentarios es distinta a ID, es comment_ID (por convención de WP)
		// Por este motivo trato primero este caso, y el resto después
		if ($this->tipo_que == self::TIPO_NUEVO_COMENTARIO) {
			return Comment::first('comment_ID', '=', $this->que_id);
		}

		$model = $this->getModelQue();
		switch ($model) {
			case 'Post' :
				return Post::find($this->que_id);
			case 'User' :
				return User::find($this->que_id);
		}
		return null;
	}

	/**
	 * Devuelve el nombre del modelo del 'que'
	 */
	public function getModelQue() {
		return self::_getModelByTipo($this->tipo_que);
	}

	/**
	 * Devuelve el nombre del modelo del que conociendo su tipo de seguimiento
	 *
	 * @param integer $tipo
	 *        	Tipo de seguimiento
	 */
	private static function _getModelByTipo($tipoQue = false) {
		switch ($tipoQue) {
			case self::TIPO_ME_GUSTA :
			case self::TIPO_NUEVA_ENTRADA :
			case self::TIPO_ENTRADA_EDITADA :
				return 'Post';
			case self::TIPO_NUEVO_COMENTARIO :
				return 'Comment';
			case self::TIPO_SEGUIMIENTO_USER :
				return 'User';
		}
		return null;
	}

	/**
	 * Comprobar si el tipo es un tipo permitido
	 */
	private function _esTipoPermitido() {
		return in_array($this->tipo_que, $this->_getTiposPermitidos());
	}

	/**
	 * Devuelve la lista de tipos permitidos de seguimientos
	 *
	 * @return array<integer>
	 */
	private static function _getTiposPermitidos() {
		return [
			self::TIPO_ENTRADA_EDITADA,
			self::TIPO_ME_GUSTA,
			self::TIPO_NUEVA_ENTRADA,
			self::TIPO_NUEVO_COMENTARIO,
			self::TIPO_SEGUIMIENTO_USER
		];
	}

	/**
	 * Devuelve true si el tipo es de un seguimiento a un User
	 *
	 * @return boolean
	 */
	public function isTipoSeguimientoUser() {
		return $this->tipo_que == self::TIPO_SEGUIMIENTO_USER;
	}

	/**
	 * Devuelve true si el tipo es de un me gusta
	 *
	 * @return boolean
	 */
	public function isTipoMeGusta() {
		return $this->tipo_que == self::TIPO_ME_GUSTA;
	}

	/**
	 * Devuelve true si el tipo es de una entrada publicada
	 *
	 * @return boolean
	 */
	public function isTipoNuevaEntrada() {
		return $this->tipo_que == self::TIPO_NUEVA_ENTRADA;
	}

	/**
	 * Devuelve true si el tipo es de una entrada publicada
	 *
	 * @return boolean
	 */
	public function isTipoEntradaEditada() {
		return $this->tipo_que == self::TIPO_ENTRADA_EDITADA;
	}

	/**
	 * Devuelve true si el tipo es de un comentario
	 *
	 * @return boolean
	 */
	public function isTipoNuevoComentario() {
		return $this->tipo_que == self::TIPO_NUEVO_COMENTARIO;
	}

	/**
	 * Devuelve el nombre del icono de FA según su tipo
	 *
	 * @return string
	 */
	public function getIcoByTipo() {
		switch ($this->tipo_que) {
			case VActividad::TIPO_SEGUIMIENTO_USER :
				return 'fa-users';
			case VActividad::TIPO_SEGUIMIENTO_USER_A_TI :
				return 'fa-child';
			case VActividad::TIPO_FAVORITOS_RECIBIDOS :
				return 'fa-star';
			case VActividad::TIPO_ME_GUSTA :
				return 'fa-star-o';
			case VActividad::TIPO_NUEVA_ENTRADA :
				return 'fa-file-text-o';
			case VActividad::TIPO_NUEVO_COMENTARIO :
				return 'fa-comment-o';
			case VActividad::TIPO_ENTRADA_EDITADA :
				return 'fa-pencil-square-o';
			default :
				return null;
		}
	}

	/**
	 * Devuelve el tipo traducido
	 *
	 * @return string
	 */
	public function getTipoTrans() {
		switch ($this->tipo_que) {
			case VActividad::TIPO_SEGUIMIENTO_USER :
				return I18n::transu('actividad.tipo_seguimiento_user');
			case VActividad::TIPO_SEGUIMIENTO_USER_A_TI :
				return I18n::transu('actividad.tipo_seguimiento_user_a_ti');
			case VActividad::TIPO_FAVORITOS_RECIBIDOS :
				return I18n::transu('actividad.tipo_favoritos_recibidos');
			case VActividad::TIPO_ME_GUSTA :
				return I18n::transu('actividad.tipo_me_gusta');
			case VActividad::TIPO_NUEVA_ENTRADA :
				return I18n::transu('actividad.tipo_nueva_entrada');
			case VActividad::TIPO_NUEVO_COMENTARIO :
				return I18n::transu('actividad.tipo_nuevo_comentario');
			case VActividad::TIPO_ENTRADA_EDITADA :
				return I18n::transu('actividad.tipo_entrada_editada');
			default :
				return null;
		}
	}

	/**
	 * Crear vista en la bbdd
	 */
	private static function _crearVista() {
		$sql = "CREATE OR REPLACE VIEW wp_v_actividades AS
(
	select user_id AS user_id, a_quien_id AS que_id, 'tipo_seguimiento_user' AS 'tipo_que', updated_at AS 'updated_at'
	from wp_users_seguimientos
	order by updated_at desc
) union (
	select user_id AS user_id, post_id AS que_id, 'tipo_me_gusta' AS 'tipo_que', updated_at AS 'updated_at'
	from wp_favoritos
	where status = 0
	order by updated_at desc
) union (
	select post_author AS user_id, ID AS que_id, 'tipo_entrada_editada' AS 'tipo_que', post_modified AS 'updated_at'
	from wp_posts
	where post_type = 'post'
		and post_status = 'publish'
		and post_date != post_modified
	order by updated_at desc
) union (
	select post_author AS user_id, ID AS que_id, 'tipo_nueva_entrada' AS 'tipo_que', post_date AS 'updated_at'
	from wp_posts
	where post_type = 'post'
		and post_status = 'publish'
	order by updated_at desc
) union (
	select user_id AS user_id, comment_ID AS que_id, 'tipo_nuevo_comentario' AS 'tipo_que', comment_date AS 'updated_at'
	from wp_comments
	where comment_approved = 1
	order by updated_at desc
)";
	}
}