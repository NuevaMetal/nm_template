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
	public static function generarNuevaPassword() {
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

		// Añado el enctype para poder pasar las imágenes por el formulario
		add_action('user_edit_form_tag', function () {
			echo 'enctype="multipart/form-data"';
		});

		function nm_perfil_add_img_header($user) {
			require_once 'mvc/controllers/AutorController.php';
			$c = new AutorController();
			echo $c->getPerfilImg(User::KEY_USER_IMG_AVATAR, $user->ID);
			echo $c->getPerfilImg(User::KEY_USER_IMG_HEADER, $user->ID);
		}
		add_action('show_user_profile', 'nm_perfil_add_img_header');
		add_action('edit_user_profile', 'nm_perfil_add_img_header');
	}

	/**
	 * Añado las imágenes de avatar y header al perfil del User
	 */
	public static function perfilUpdateImgHeader() {

		function nm_perfil_update_img($user_ID, $keyUserImg) {
			//Primero comprobamos que el user tenga permisos y exista la clave en los FILES
			if (current_user_can('edit_user', $user_ID) && isset($_FILES [$keyUserImg])) {
				//Después comprobamos que tenga un nombre definido
				$img = $_FILES [$keyUserImg];
				if ($img ['name']) {
					$user = User::find($user_ID);
					try {
						switch ($keyUserImg) {
							case User::KEY_USER_IMG_HEADER :
								$user->setImgHeader($img);
								break;
							case User::KEY_USER_IMG_AVATAR :
								$user->setAvatar($img);
								break;
						}
					} catch (Exception $e) {
						// Añadimos el mensaje de error en las notificaciones
						add_action('user_profile_update_errors', function ($errors) use($e) {
							$errors->add($keyUserImg, $e->getMessage());
						});
					}
				}
			}
		}

		function nm_perfil_update_img_avatar($user_ID) {
			nm_perfil_update_img($user_ID, User::KEY_USER_IMG_AVATAR);
		}

		function nm_perfil_update_img_header($user_ID) {
			nm_perfil_update_img($user_ID, User::KEY_USER_IMG_HEADER);
		}

		add_action('personal_options_update', 'nm_perfil_update_img_avatar');
		add_action('edit_user_profile_update', 'nm_perfil_update_img_avatar');
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

	/**
	 * Quitar items del menu para los usuarios
	 */
	public static function quitarItemsParaLosUsuarios() {
		add_action('admin_menu', function () {
			//Obtenemos los datos del usuario actual
			$user = User::find(wp_get_current_user()->ID);
			// Si es que el usuario no tiene rol de editor o admin
			if (!$user || !$user->isEditor()) {
				remove_menu_page('edit-comments.php'); // Removemos el ítem comentarios
				remove_menu_page('upload.php'); // Removemos el ítem medios
			}
			if (!$user || !$user->isAdmin()) {
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
			// Añado un id a la sección de "Nombre"
			$subject = str_replace('<h3>Nombre</h3>', '<h3 id="nombre">Nombre</h3>', $subject);
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
					unset($author_rewrite_rules [$pattern]);
				}
			}
			return $author_rewrite_rules;
		});

		add_filter('author_link', function ($link, $author_id) {
			$user = User::find($author_id);
			return str_replace('%author_tipo%', $user->getTipo(), $link);
		}, 100, 2);
	}

	public static function registerForm() {
		session_start();
		//1. Añado nuevo input donde introducir el captcha
		add_action('register_form', function () {
			$_SESSION ['captcha_action'] = 'captcha_nm_' . time();
			// Creamos un "captcha" a partir de un nonce de WP que posteriormente comprobaremos en el filtro
			$captcha = wp_create_nonce($_SESSION ['captcha_action']);
			?>
<p>
	<label for="cap">Introduce el captcha: <strong><?php echo $captcha?></strong><br>
		<input type="text" name="cap" id="cap" class="input" size="25"></label>
</p>
<?php
		});

		//2. Añado validación. Comprobamos el input del captcha no esté vacío y que además coincida con su valor
		// utilizando para ello el wp_verify_nonce que nos proporciona WP
		add_filter('registration_errors', function ($errors) {
			if (!wp_verify_nonce($_POST ['cap'], $_SESSION ['captcha_action'])) {
				$errors->add('captcha_incorecto', '<strong>ERROR:</strong> Captcha incorrecto.');
			}
			return $errors;
		});
	}

	/**
	 * Action -> wp_login
	 *
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
				$user_id = ( int ) $id_or_email;
			} elseif (is_string($id_or_email) && ($user = get_user_by('email', $id_or_email))) {
				$user_id = $user->ID;
			} elseif (is_object($id_or_email) && !empty($id_or_email->user_id)) {
				$user_id = ( int ) $id_or_email->user_id;
			}
			$user = User::find($user_id);
			if (!$user) {
				return Utils::getUrlGravatarDefault($size);
			}
			if (!Utils::cadenaValida($alt)) {
				$alt = $user->display_name . ' avatar';
			}
			$img = '<img alt="' . esc_attr($alt) . '" src="' . $user->getAvatar($size) . '" ';
			$img .= 'class="avatar photo" height="' . $size . '" width="' . $size . '">';
			return $img;
		}, 10, 5);
	}

}

Acciones::userRegister();
Acciones::generarNuevaPassword();

Acciones::perfilQuitarInfoSobrante();
Acciones::perfilAddInfo();

Acciones::cargarEstilosPaginaLogin();

Acciones::quitarItemsParaLosUsuarios();

Acciones::adminBarQuitarLogoWP();

Acciones::cambiarSlugBaseDelAutorPorSuTipo();

Acciones::registerForm();

Acciones::impedirLoginSiUserBloqueado();

Acciones::sobrescribirGetAvatar();
