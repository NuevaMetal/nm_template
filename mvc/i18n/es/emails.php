<?php
return [
	'aceptado_como_colaborador' => 'Ey :user_login !
		<br>
		<h2>¡Acabas de ser aceptado como colaborador en <a href="http://nuevametal.com"> :blogname </a></h2>
		Recuerda que tienes un <a href="http://nuevametal.com/tutorial">Tutorial</a>
		bellísimo donde explicamos todo lo necesario para crear tus propias entradas.
		<br><br>
		Si tienes algún problema, por favor no dudes en contactar con nosotros a través de nuestro
		<a href="http://nuevametal.com/contacto">Formulario de Contacto</a>
		<br><br>
		¡Nos vemos!
		<br><br>
		Tu equipo de NuevaMetal.',

	'aviso_admin_nuevo_user' => '
		Nuevo usuario registrado en :blogname <br>
		<b>Nombre de usuario:</b> :user_login <br>
		<b>Pass temp:</b> :user_pass <br>
		<b>E-mail:</b>: :user_email <br>',

	'contacto' => '
	<div class="col-xs-12">
		<h1>Nuevo mensaje de contacto para el departamento <strong> :departamento </strong></h1>

		<fieldset>
    		<legend>De</legend>
			<div><strong>Nombre:</strong> :nombre  </div>
			<div><strong>Email:</strong> :email </div>
			<div><strong>Web:</strong> :web </div>
		</fieldset>

		<fieldset>
    		<legend>Para</legend>
			<div><strong>Departamento:</strong> :departamento </div>
		</fieldset>

		<fieldset>
    		<legend>Mensaje</legend>
			<div> :mensaje </div>
		</fieldset>

		<small>Mensaje enviado desde la página de contacto de la web <a href=" :blogurl "> :blogname </a></small>
	</div>',

	'nuevo_comentario' => '
		<h1>Nuevo comentario</h1>
		ID => :ID <br>
		post_ID => :post_ID <br>
		user_id => :user_id <br>
		author => :author <br>
		author_email => :author_email <br>
		author_url => :author_url <br>
		author_IP => :author_IP <br>
		date => :date <br>
		content => :content <br>
		',

	'nuevo_user' => '¡Ey bro!,
		<br>
		¡Bienvenido a NuevaMetal! Aquí tienes cómo hacer:
			<a href="http://nuevametal.com/wp-login.php">login</a>:<br>
		<b>Nombre de usuario</b>: :user_login <br>
		<b>Contraseña</b>: :user_pass <br>
		<br>
		Si tienes algún problema, por favor contáctame :admin_email <br>
		¡Nos vemos!',

	'password_reset' => '
		<h2>Solicitado un cambio de contraseña en <a href=" :blogurl " target="_blank"> :blogname </a></h2>
		<b>Nombre de usuario:</b> :user_login <br>
		<b>E-mail:</b> :user_email <br>
		<b>Solicitar una nueva contraseña</b>:<br>
		<a href=" :user_key_url " target="_blank"> :user_key_url </a> <br><br>
		<p>Se ha solicitado un cambio de contraseña. Entra en este enlace para que se te envíe un correo con una nueva contraseña
		generada aleatoriamente. Posteriormente recuerda cambiarla desde tu panel de control de usuario.</p>
		<small>Si no has solicitado ningún cambio puedes omitir este correo.</small>',

	'password_reset_2' => '
		<h2>Recuperando tu password en <a href=" :blogurl " target="_blank"> :blogname </a></h2>
		<b>Nombre de usuario:</b> :user_login <br>
		<b>E-mail:</b> :user_email <br>
		<b>Contraseña:</b> :user_pass <br><br>
		<p>Aquí tienes la nueva contraseña. Recuerda cambiarla desde tu panel de control de usuario.<br>
		Si tienes algún problema, por favor contáctame :admin_email <br>
		¡Nos vemos!</p>'
];