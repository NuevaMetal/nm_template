<?php
/**
 * Acciones de Wordpress
 *
 * @author chema
 *
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
				'user_email' => $user_email,
				'blogname' => get_option('blogname')
			]);

			$enviado = Correo::enviarCorreoGenerico([
				get_option('admin_email')
			], sprintf(__('[%s] New User Registration'), get_option('blogname')), $emailAvisoAdminNuevoUser);

			if (!$enviado) {
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
			if (!$enviado) {
				Utils::info("Fallo al enviar el correo al User con ID: $user_id");
			}
			header('Location: /wp-login.php');
		});
	}

	/**
	 * Enviar una nueva contraseña al email.
	 */
	public static function loginInit() {
		add_action('login_init', function () {
			if ($_REQUEST ['action'] == 'lostpassword' && $_REQUEST ['wp-submit'] == 'Obtener una contraseña nueva') {
				$user_email = $_POST ['user_login'];
				$user = get_user_by('email', $user_email);

				$user_login = stripslashes($user->user_login);
				$user_pass = wp_generate_password(12, false);
				wp_set_password($user_pass, $user->ID);

				$emailAvisoAdminPasswordReset = I18n::trans('emails.aviso_admin_password_reset', [
					'user_login' => $user_login,
					'user_email' => $user_email,
					'blogname' => get_option('blogname')
				]);

				$enviado = Correo::enviarCorreoGenerico([
					get_option('admin_email')
				], sprintf(__('[%s] New User Recovering Pass'), get_option('blogname')), $emailAvisoAdminPasswordReset);

				if (!$enviado) {
					Utils::info("Fallo al enviar el correo al User con ID: $user_id");
				}

				if (empty($user_pass) || !$user->ID)
					return;

				$emailNuevoUser = I18n::trans('emails.password_reset', [
					'user_login' => $user_login,
					'user_pass' => $user_pass,
					'admin_email' => get_option('admin_email')
				]);

				$enviado = Correo::enviarCorreoGenerico([
					$user_email
				], sprintf(__('[%s] Your username and password'), get_option('blogname')), $emailNuevoUser);
				if (!$enviado) {
					Utils::info("Fallo al enviar el correo al User con ID: $user_id");
				}
				header('Location: /wp-login.php');
			}
		});
	}

	public static function perfilAddImgHeader() {

		function nm_perfil_add_img_header($user) {
			require_once 'mvc/controllers/AutorController.php';
			$c = new AutorController();
			echo $c->getPerfilImgHeader($user->ID);
		}
		add_action('show_user_profile', 'nm_perfil_add_img_header');
		add_action('edit_user_profile', 'nm_perfil_add_img_header');
	}

	/**
	 * Añado las redes sociales al perfil del User
	 */
	public static function perfilUpdateImgHeader() {

		function nm_perfil_update_img_header($user_ID) {
			//Primero comprobamos que el user tenga permisos y exista la clave en los FILES
			if (current_user_can('edit_user', $user_ID) && isset($_FILES [User::KEY_USER_IMG_HEADER])) {
				//Después comprobamos que tenga un nombre definido
				$imgHeader = $_FILES [User::KEY_USER_IMG_HEADER];
				if ($imgHeader ['name']) {
					$user = User::find($user_ID);
					try {
						$user->setImgHeader($imgHeader);
					} catch (Exception $e) {
						// Añadimos el mensaje de error en las notificaciones
						add_action('user_profile_update_errors', function ($errors) use($e) {
							$errors->add(User::KEY_USER_IMG_HEADER, $e->getMessage());
						});
					}
				}
			}
		}
		add_action('personal_options_update', 'nm_perfil_update_img_header');
		add_action('edit_user_profile_update', 'nm_perfil_update_img_header');
	}

	/**
	 * Añado las redes sociales al perfil del User
	 */
	public static function perfilAddRedesSociales() {

		function nm_perfil_add_redes_sociales($user) {
			require_once 'mvc/controllers/AutorController.php';
			$c = new AutorController();
			echo $c->getPerfilRedesSociales($user->ID);
		}
		add_action('show_user_profile', 'nm_perfil_add_redes_sociales');
		add_action('edit_user_profile', 'nm_perfil_add_redes_sociales');
	}

	/**
	 * Actualizo las redes sociales del perfil del User
	 * Facebook, Twiter, Google+, Youtube, Soundcloud
	 */
	public static function perfilUpdateRedesSociales() {

		function nm_perfil_update_redes_sociales($user_ID) {
			if (current_user_can('edit_user', $user_ID)) {
				$user = User::find($user_ID);
				$user->setFacebook($_POST [User::KEY_USER_FACEBOOK]);
				$user->setTwitter($_POST [User::KEY_USER_TWITTER]);
				$user->setGooglePlus($_POST [User::KEY_USER_GOOGLE_PLUS]);
				$user->setYoutube($_POST [User::KEY_USER_YOUTUBE]);
				$user->setSoundcloud($_POST [User::KEY_USER_SOUNDCLOUD]);
			}
		}
		add_action('personal_options_update', 'nm_perfil_update_redes_sociales');
		add_action('edit_user_profile_update', 'nm_perfil_update_redes_sociales');
	}

	/**
	 * Añado el tipo de user al perfil
	 */
	public static function perfilAddTipoUsuario() {

		function nm_perfil_add_tipo_user($user) {
			require_once 'mvc/controllers/AutorController.php';
			$c = new AutorController();
			echo $c->getPerfilTipoUser($user->ID);
		}
		add_action('show_user_profile', 'nm_perfil_add_tipo_user');
		add_action('edit_user_profile', 'nm_perfil_add_tipo_user');
	}

	/**
	 * Actualizo el tipo de user
	 */
	public static function perfilUpdateTipoUsuario() {

		function nm_perfil_update_tipo_user($user_ID) {
			if (current_user_can('edit_user', $user_ID)) {
				$user = User::find($user_ID);
				$user->setTipo($_POST [User::KEY_USER_TIPO]);
			}
		}
		add_action('personal_options_update', 'nm_perfil_update_tipo_user');
		add_action('edit_user_profile_update', 'nm_perfil_update_tipo_user');
	}

	/**
	 * Cargar estilos en la página de login
	 */
	public static function cargarEstilosPaginaLogin() {
		add_action('login_enqueue_scripts', function () {
			wp_enqueue_style('main', get_template_directory_uri() . '/public/css/main.css');
			//wp_enqueue_script('custom-login', get_template_directory_uri() . '/style-login.js');
		});

		add_filter('login_headerurl', function () {
			return home_url();
		});
		add_filter('login_headertitle', function () {
			return 'NuevaMetal.com';
		});
	}

	/**<br
	 * Quitar items del menu para los usuarios
	 */
	public static function quitarItemsParaLosUsuarios() {
		add_action('admin_menu', function () {

			$user = User::find(wp_get_current_user()->ID); //Obtenemos los datos del usuario actual
			if (!$user || !$user->isEditor()) { // Si es que el usuario no tiene rol de editor o admin
				remove_menu_page('edit-comments.php'); // Removemos el ítem comentarios
				remove_menu_page('upload.php'); // Removemos el ítem medios
			}
		});
	}

	/**
	 * Quitar información sobrante de los perfiles de los usuarios
	 */
	public static function perfilQuitarInfoSobrante() {
		// Elimino el esquema de colores de las opciones del perfil que vienen por defecto
		// en profile.php
		remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');

		/**
		 * Elimino las opciones personales: editor visual,Atajos de teclado y Barra de herramientas
		 */
		function nm_remove_personal_options($subject) {
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

			//Añado un id a la sección de "Acerca de ti"
			$subject = str_replace('<h3>Acerca de ti</h3>', '<h3 id="acerca-de-ti">Acerca de ti</h3>', $subject);
			return $subject;
		}

		function nm_remove_personal_options_start() {
			ob_start('nm_remove_personal_options');
		}

		function nm_remove_personal_options_end() {
			ob_end_flush();
		}
		// Para el perfil propio
		add_action('admin_head-profile.php', 'nm_remove_personal_options_start');
		add_action('admin_footer-profile.php', 'nm_remove_personal_options_end');
		// Para el perfil de otro user
		add_action('admin_head-user-edit.php', 'nm_remove_personal_options_start');
		add_action('admin_footer-user-edit.php', 'nm_remove_personal_options_end');
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
	 * Remover la pestaña de "Ayuda" y "Opciones" en Wordpress
	 */
	public static function adminBarQuitarAyudaYOpciones() {

		function hide_help() {
			echo '<style type="text/css">
            #contextual-help-link-wrap, #screen-options-link-wrap { display: none !important; }
          </style>';
		}
		add_action('admin_head', 'hide_help');
	}

	/**
	 * Añadir la información extra al perfil del Usuario
	 */
	public static function perfilAddInfo() {
		require_once 'mvc/models/User.php';
		$current_user = User::find(wp_get_current_user()->ID);
		if ($current_user && $current_user->canColaborador()) {
			Acciones::perfilAddImgHeader();
			Acciones::perfilUpdateImgHeader();

			Acciones::perfilAddRedesSociales();
			Acciones::perfilUpdateRedesSociales();

			Acciones::perfilAddTipoUsuario();
			Acciones::perfilUpdateTipoUsuario();
		}
	}

}

Acciones::userRegister();
Acciones::loginInit();

Acciones::perfilQuitarInfoSobrante();
Acciones::perfilAddInfo();

Acciones::cargarEstilosPaginaLogin();

Acciones::quitarItemsParaLosUsuarios();

Acciones::adminBarQuitarLogoWP();
Acciones::adminBarQuitarAyudaYOpciones();
