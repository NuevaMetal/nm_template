<?php

namespace Libs;

use I18n\I18n;
use Controllers\UserController;
use Models\User;
use Libs\Utils;
use Models\Comment;
use Models\Analitica;

/**
 * Acciones de Wordpress
 *
 * @author chema
 */
class Acciones {

	/**
	 * Justo después de registrar un nuevo User vamos a generarle una clave aleatoria
	 * y vamos a enviarsela por correo para que pueda acceder
	 */
	public static function userRegister() {
		add_action('user_register', function ($user_id) {
			$user = get_user_by('id', $user_id);

			$user_login = stripslashes($user->user_login);
			$user_email = stripslashes($user->user_email);
			$user_pass = wp_generate_password(12, false);
			wp_set_password($user_pass, $user_id);

			$emailAvisoAdminNuevoUser = I18n::trans('emails.aviso_admin_nuevo_user', [
				'user_login' => $user_login,
				'user_pass' => $user_pass,
				'user_email' => $user_email,
				'blogname' => get_option('blogname')
			]);

			$enviado = Correo::enviarCorreoGenerico([
				get_option('admin_email')
			], sprintf(__('[%s] New User Registration'), get_option('blogname')), $emailAvisoAdminNuevoUser);

			if (! $enviado) {
				Utils::info("Fallo al enviar el correo al User con ID: $user_id");
			}

			if (empty($user_pass))
				return;

			$emailNuevoUser = I18n::trans('emails.nuevo_user', [
				'user_login' => $user_login,
				'user_pass' => $user_pass,
				'admin_email' => get_option('admin_email')
			]);

			$enviado = Correo::enviarCorreoGenerico([
				$user_email
			], sprintf(__('[%s] Your username and password'), get_option('blogname')), $emailNuevoUser);
			if (! $enviado) {
				Utils::info("Fallo al enviar el correo al User con ID: $user_id");
			}
			header('Location: /wp-login.php');
		});
	}

	/**
	 * Enviar una nueva contraseña al email.
	 */
	public static function generarNuevaPassword() {
		add_action('login_init', function () {
			if ($_REQUEST['action'] == 'lostpassword' && $_REQUEST['wp-submit'] == 'Obtener una contraseña nueva') {
				$user_email_or_login = $_POST['user_login'];
				$user = get_user_by('email', $user_email_or_login);
				// Si no tenemos user probamos por su login
				if (! $user) {
					$user = get_user_by('login', $user_email_or_login);
				}
				$user = User::find($user->ID);
				// Si seguimos sin user es que no existe
				if (! $user || ! $user->ID) {
					return;
				}

				$urlKey = $user->getActivationKeyUrl();

				$plantillaEmailPasswordReset = I18n::trans('emails.password_reset', [
					'blogname' => get_option('blogname'),
					'blogurl' => home_url(),
					'user_login' => $user->getLogin(),
					'user_key_url' => $urlKey,
					'user_email' => $user->getEmail(),
					'admin_email' => get_option('admin_email')
				]);

				$enviado = Correo::enviarCorreoGenerico([
					get_option('admin_email'),
					$user->getEmail()
				], sprintf(__('[%s] Your username and password'), get_option('blogname')), $plantillaEmailPasswordReset);
				if (! $enviado) {
					Utils::info("FALLO al enviar correo generico 'plantillaEmailPasswordReset'");
				}
				header('Location: /wp-login.php');
			}
		});
	}

	/**
	 * Añadir la vista para más información
	 */
	public static function perfilAddAdicionalInfo() {
		$nm_perfil_add_adicional_info = function ($user) {
			$c = new UserController();
			echo $c->getPerfilAdicionalInfo($user->ID);
		};
		add_action('show_user_profile', $nm_perfil_add_adicional_info);
		add_action('edit_user_profile', $nm_perfil_add_adicional_info);

		/*
		 * Actualizo la información adicional del perfil del user
		 */
		$nm_perfil_update_adicional_info = function ($user_ID) {
			if (current_user_can('edit_user', $user_ID)) {
				$user = User::find($user_ID);
				$user->setUbicacion($_POST[User::KEY_USER_UBICACION]);
				$user->setBandasDestacadas($_POST[User::KEY_USER_BANDAS_DESTACADAS]);
				$user->setGenerosDestacados($_POST[User::KEY_USER_GENEROS_DESTACADOS]);
			}
		};
		add_action('personal_options_update', $nm_perfil_update_adicional_info);
		add_action('edit_user_profile_update', $nm_perfil_update_adicional_info);
	}

	/**
	 */
	public static function perfilAddImgAvatarYHeader() {
		$nm_perfil_add_img_header = function ($user) {
			$c = new UserController();
			echo $c->getPerfilImg(User::KEY_USER_IMG_AVATAR, $user->ID);
			echo $c->getPerfilImg(User::KEY_USER_IMG_HEADER, $user->ID);
		};
		add_action('show_user_profile', $nm_perfil_add_img_header);
		add_action('edit_user_profile', $nm_perfil_add_img_header);

		/*
		 * Añado las imágenes de avatar y header al perfil del User
		 */
		$nm_perfil_update_img = function ($user_ID, $keyUserImg) {
			try {
				// Primero comprobamos que el user tenga permisos y exista la clave en los FILES
				if (current_user_can('edit_user', $user_ID) && isset($_FILES[$keyUserImg])) {
					// Después comprobamos que tenga un nombre definido
					$img = $_FILES[$keyUserImg];
					if ($img['name']) {
						$user = User::find($user_ID);
						switch ($keyUserImg) {
							case User::KEY_USER_IMG_HEADER :
								$user->setHeader($img);
								break;
							case User::KEY_USER_IMG_AVATAR :
								$user->setAvatar($img);
								break;
						}
					}
				}
			} catch ( \Exception $e ) {
				// Añadimos el mensaje de error en las notificaciones
				add_action('user_profile_update_errors', function ($errors) use($e) {
					$errors->add($keyUserImg, $e->getMessage());
				});
			}
		};
		/*
		 * use($nm_perfil_update_img) => poder usar dicha función dentro del ámbito
		 * de la función que lo engloba.
		 */
		$nm_perfil_update_img_avatar = function ($user_ID) use($nm_perfil_update_img) {
			$nm_perfil_update_img($user_ID, User::KEY_USER_IMG_AVATAR);
		};
		$nm_perfil_update_img_header = function ($user_ID) use($nm_perfil_update_img) {
			$nm_perfil_update_img($user_ID, User::KEY_USER_IMG_HEADER);
		};

		add_action('personal_options_update', $nm_perfil_update_img_avatar);
		add_action('edit_user_profile_update', $nm_perfil_update_img_avatar);
		add_action('personal_options_update', $nm_perfil_update_img_header);
		add_action('edit_user_profile_update', $nm_perfil_update_img_header);
	}

	/**
	 * Añado las redes sociales al perfil del User
	 */
	public static function perfilAddRedesSociales() {
		$nm_perfil_add_redes_sociales = function ($user) {
			$c = new UserController();
			echo $c->getPerfilRedesSociales($user->ID);
		};
		add_action('show_user_profile', $nm_perfil_add_redes_sociales);
		add_action('edit_user_profile', $nm_perfil_add_redes_sociales);

		/*
		 * Actualizo las redes sociales del perfil del User
		 * Facebook, Twiter, Google+, Youtube, Soundcloud
		 */
		$nm_perfil_update_redes_sociales = function ($user_ID) {
			if (current_user_can('edit_user', $user_ID)) {
				$user = User::find($user_ID);
				$user->setFacebook($_POST[User::KEY_USER_FACEBOOK]);
				$user->setTwitter($_POST[User::KEY_USER_TWITTER]);
				$user->setGooglePlus($_POST[User::KEY_USER_GOOGLE_PLUS]);
				$user->setYoutube($_POST[User::KEY_USER_YOUTUBE]);
				$user->setSoundcloud($_POST[User::KEY_USER_SOUNDCLOUD]);
			}
		};
		add_action('personal_options_update', $nm_perfil_update_redes_sociales);
		add_action('edit_user_profile_update', $nm_perfil_update_redes_sociales);
	}

	/**
	 * Añado el tipo de user al perfil
	 */
	public static function perfilAddTipoUsuario() {
		$nm_perfil_add_tipo_user = function ($user) {
			$c = new UserController();
			echo $c->getPerfilTipoUser($user->ID);
		};
		add_action('show_user_profile', $nm_perfil_add_tipo_user);
		add_action('edit_user_profile', $nm_perfil_add_tipo_user);

		/*
		 * Actualizo el tipo de user
		 */
		$nm_perfil_update_tipo_user = function ($user_ID) {
			if (current_user_can('edit_user', $user_ID)) {
				$user = User::find($user_ID);
				$user->setTipo($_POST[User::KEY_USER_TIPO]);
			}
		};
		add_action('personal_options_update', $nm_perfil_update_tipo_user);
		add_action('edit_user_profile_update', $nm_perfil_update_tipo_user);
	}

	/**
	 * Añado el tipo de user al perfil
	 */
	public static function perfilAddIdioma() {
		$nm_perfil_add_idioma = function ($user) {
			$c = new UserController();
			echo $c->getPerfilIdioma($user->ID);
		};
		add_action('show_user_profile', $nm_perfil_add_idioma);
		add_action('edit_user_profile', $nm_perfil_add_idioma);

		/*
		 * Actualizo el tipo de user
		 */
		$nm_perfil_update_idioma = function ($user_ID) {
			if (current_user_can('edit_user', $user_ID)) {
				$user = User::find($user_ID);
				$user->setIdioma($_POST[User::KEY_USER_IDIOMA]);
			}
		};
		add_action('personal_options_update', $nm_perfil_update_idioma);
		add_action('edit_user_profile_update', $nm_perfil_update_idioma);
	}

	/**
	 * Cargar estilos en la página de login
	 */
	public static function cargarEstilosPaginaLogin() {
		add_action('login_enqueue_scripts', function () {
			wp_enqueue_style('main', get_template_directory_uri() . '/public/css/main.css');
			// wp_enqueue_script('custom-login', get_template_directory_uri() . '/style-login.js');
		});

		add_filter('login_headerurl', function () {
			return home_url();
		});
		add_filter('login_headertitle', function () {
			return 'NuevaMetal.com';
		});
	}

	/**
	 * Quitar items del menu para los usuarios
	 */
	public static function quitarItemsParaLosUsuarios() {
		add_action('admin_menu', function () {
			// Obtenemos los datos del usuario actual
			$user = User::find(wp_get_current_user()->ID);
			// Si es que el usuario no tiene rol de editor o admin
			if (! $user || ! $user->canEditor()) {
				remove_menu_page('edit-comments.php'); // Removemos el ítem comentarios
				remove_menu_page('upload.php'); // Removemos el ítem medios
			}
			if (! $user || ! $user->isAdmin()) {
				remove_menu_page('edit.php?post_type=page');
			}
			// Nadie quiere tools.php
			remove_menu_page('tools.php'); // Removemos el ítem medios
		});
	}

	/**
	 * Quitar información sobrante de los perfiles de los usuarios
	 */
	public static function perfilQuitarInfoSobrante() {
		// Elimino el esquema de colores de las opciones del perfil que vienen por defecto
		// en profile.php
		remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');

		/*
		 * Elimino las opciones personales: Editor visual, Atajos de teclado y Barra de herramientas
		 */
		$nm_remove_personal_options_start = function () {
			ob_start(function ($subject) {
				// Opciones personales
				$subject = preg_replace('#<h3>Opciones personales</h3>.+?/table>#s', '', $subject, 1);

				// Yahoo IM
				$subject = preg_replace('#<th><label for="yim">.+?/th>#s', '', $subject, 1);
				$subject = preg_replace('#<td><input type="text" name="yim".+?/td>#s', '', $subject, 1);
				// AIM
				$subject = preg_replace('#<th><label for="aim">.+?/th>#s', '', $subject, 1);
				$subject = preg_replace('#<td><input type="text" name="aim".+?/td>#s', '', $subject, 1);
				// Jabber / Google Talk
				$subject = preg_replace('#<th><label for="jabber">.+?/th>#s', '', $subject, 1);
				$subject = preg_replace('#<td><input type="text" name="jabber".+?/td>#s', '', $subject, 1);

				// Añado un id a la sección de "Acerca de ti"
				$subject = str_replace('<h3>Acerca de ti</h3>', '<h3 id="acerca-de-ti">Acerca de ti</h3>', $subject);
				// Añado un id a la sección de "Nombre"
				$subject = str_replace('<h3>Nombre</h3>', '<h3 id="nombre">Nombre</h3>', $subject);
				return $subject;
			});
		};
		$nm_remove_personal_options_end = function () {
			ob_end_flush();
		};
		// Para el perfil propio
		add_action('admin_head-profile.php', $nm_remove_personal_options_start);
		add_action('admin_footer-profile.php', $nm_remove_personal_options_end);
		// Para el perfil de otro user
		add_action('admin_head-user-edit.php', $nm_remove_personal_options_start);
		add_action('admin_footer-user-edit.php', $nm_remove_personal_options_end);
	}

	/**
	 * Eliminar el logo de WP de la barra de administración de Wordpress
	 */
	public static function adminBarQuitarLogoWP() {
		add_action('wp_before_admin_bar_render', function () {
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu('wp-logo');
		});
	}

	/**
	 * Añadir la información extra al perfil del Usuario
	 */
	public static function perfilAddInfo() {
		$current_user = User::find(wp_get_current_user()->ID);
		// if ($current_user && $current_user->canColaborador()) {
		// Añado el enctype para poder pasar las imágenes por el formulario
		add_action('user_edit_form_tag', function () {
			echo 'enctype="multipart/form-data"';
		});
		Acciones::perfilAddAdicionalInfo();
		Acciones::perfilAddImgAvatarYHeader();
		Acciones::perfilAddRedesSociales();
		Acciones::perfilAddTipoUsuario();
		Acciones::perfilAddIdioma();
		// }
	}

	/**
	 * Cambiar el 'author' estático del slug base del autor por el tipo de usuario que sea
	 */
	public static function cambiarSlugBaseDelAutorPorSuTipo() {
		add_action('init', function () {
			global $wp_rewrite;
			$author_levels = User::getTiposDeUsuarioValidos();
			// Define the tag and use it in the rewrite rule
			add_rewrite_tag('%author_tipo%', '(' . implode('|', $author_levels) . ')');
			$wp_rewrite->author_base = '%author_tipo%';
		});

		add_filter('author_rewrite_rules', function ($author_rewrite_rules) {
			foreach ($author_rewrite_rules as $pattern => $substitution) {
				if (false === strpos($substitution, 'author_name')) {
					unset($author_rewrite_rules[$pattern]);
				}
			}
			return $author_rewrite_rules;
		});

		add_filter('author_link', function ($link, $author_id) {
			$user = User::find($author_id);
			return str_replace('%author_tipo%', $user->getTipo(), $link);
		}, 100, 2);
	}

	/**
	 * Formulario de login/registro
	 *
	 * @return void
	 */
	public static function registerForm() {
		session_start();
		// 1. Añado nuevo input donde introducir el captcha
		add_action('register_form', function () {
			$_SESSION['captcha_action'] = 'captcha_nm_' . time();
			// Creamos un "captcha" a partir de un nonce de WP que posteriormente comprobaremos en el filtro
			$captcha = wp_create_nonce($_SESSION['captcha_action']);
			?>
<p>
	<label for="cap">Introduce el captcha: <strong><?php echo $captcha?></strong><br>
		<input type="text" name="cap" id="cap" class="input" size="25"></label>
</p>
<?php
		});

		// 2. Añado validación. Comprobamos el input del captcha no esté vacío y que además coincida con su valor
		// utilizando para ello el wp_verify_nonce que nos proporciona WP
		add_filter('registration_errors', function ($errors) {
			if (! wp_verify_nonce($_POST['cap'], $_SESSION['captcha_action'])) {
				$errors->add('captcha_incorecto', '<strong>ERROR:</strong> Captcha incorrecto.');
			}
			return $errors;
		});
	}

	/**
	 * Action -> wp_login
	 * Comprobamos que el user no esté bloqueado
	 */
	public static function impedirLoginSiUserBloqueado() {
		add_action('wp_login', function ($user_login, $user) {
			$user = User::find($user->ID);
			if ($user->isBloqueado()) {
				wp_logout();
			}
		}, 10, 2);
	}

	/**
	 * Sobreescribo el get_avatar de WP
	 *
	 * @return string
	 */
	public static function sobrescribirGetAvatar() {
		// Añadiendo este filtro de WP sobrescribo el comportamiento del get_avatar que tiene WP interno.
		// De esta forma se obtendrán los avatares directamente del modelo
		add_filter('get_avatar', function ($avatar = '', $id_or_email, $size = User::AVATAR_SIZE_DEFAULT, $default = '', $alt = '') {
			if (is_numeric($id_or_email)) {
				$user_id = (int) $id_or_email;
			} elseif (is_string($id_or_email) && ($user = get_user_by('email', $id_or_email))) {
				$user_id = $user->ID;
			} elseif (is_object($id_or_email) && ! empty($id_or_email->user_id)) {
				$user_id = (int) $id_or_email->user_id;
			}
			$user = User::find($user_id);
			if (! $user) {
				return Utils::getUrlGravatarDefault($size);
			}
			if (! Utils::cadenaValida($alt)) {
				$alt = $user->display_name . ' avatar';
			}
			$img = '<img alt="' . esc_attr($alt) . '" src="' . $user->getAvatar($size) . '" ';
			$img .= 'class="avatar photo" height="' . $size . '" width="' . $size . '">';
			return $img;
		}, 10, 5);
	}

	/**
	 * Set the Attachment Display Settings "Link To" default to "none"
	 * This function is attached to the 'after_setup_theme' action hook.
	 */
	public static function establecerDefectoOpcionesParaAdjuntos() {
		add_action('after_setup_theme', function () {
			update_option('image_default_align', 'none');
			update_option('image_default_link_type', 'none');
			update_option('image_default_size', 'full');
		});
	}

	/**
	 * Publicar posts programados y que tienen una programación perdida
	 */
	public static function publicarPostsProgramados() {
		add_action('wp', function () {
			global $wpdb;
			$sql = 'SELECT ID FROM wp_posts WHERE (post_date > 0 && post_date <= %s) AND post_status = "future"';
			$postsIds = $wpdb->get_col($wpdb->prepare($sql, current_time('mysql', 0)));
			foreach ($postsIds as $postId) {
				if ($postId) {
					wp_publish_post($postId);
				}
			}
		});
	}

	/**
	 * Eliminar etiquetas de un comentario y limitar su tamaño.
	 */
	public static function commentPost() {
		add_action('comment_post', function ($comment_ID) {
			$comment = Comment::find($comment_ID);
			$user = Utils::getCurrentUser();
			if (! $user || ! $comment->user_id) {
				debug("> Comentario ID: {$comment->comment_ID} borrado forzosamente.");
				/*
				 * Si no hay usuario, o el autor del comentario no está registrado, borramos el comentario.
				 * Y forzamos su borrado.
				 */
				$comment->borrar(true);
			}
			/*
			 * Quitamos las etiquetas para impesir ataques XSS entre otros.
			 * Y nos aseguramos de que no tenga más del tamaño permitido.
			 */
			$content = $comment->comment_content;
			$content = Html::quitarEtiquetas($content);
			if (strlen($content) > Comment::MAX_LENGTH) {
				$content = substr($content, 0, Comment::MAX_LENGTH);
			}
			$comment->comment_content = $content;
			$comment->save();

			/*
			 * Alguien envía un comentario o
			 * Se ha recibido un comentario para moderar.
			 */
			$pendienteDeModerar = (get_option('moderation_notify')) && ($comment->comment_approved != Comment::APROVADO);
			if (get_option('comments_notify') || $pendienteDeModerar) {
				$enviado = Correo::enviarCorreoGenerico([
					get_option('admin_email')
				], 'Nuevo comentario en NM', I18n::trans('emails.nuevo_comentario', [
					'ID' => $comment->comment_ID,
					'post_ID' => $comment->comment_post_ID,
					'author' => $comment->comment_author,
					'author_email' => $comment->comment_author_email,
					'author_url' => $comment->comment_author_url,
					'author_IP' => $comment->comment_author_IP,
					'date' => $comment->comment_date,
					'content' => $comment->comment_content,
					'user_id' => $comment->user_id
				]));
				if (! $enviado) {
					info("FALLO al enviar correo generico 'emails.nuevo_comentario'");
				}
			}
		});
	}

	/**
	 * Guardar la analítica
	 */
	public static function guardarAnalitica() {
		add_action('wp', function () {
			try {
				$analitica = new Analitica();
				$analitica->save();
			} catch ( Exception $e ) {
				info('No se pudo guardar la Analitica ?');
			}
		});
	}
}
