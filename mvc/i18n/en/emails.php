<?php
return [
	'aviso_admin_nuevo_user' => '
		New user registered in :blogname <br>
		<b>Username</b>: :user_login <br>
		<b>Pass temp: </b>: :user_pass <br>
		<b>E-mail</b>: :user_email <br>',

	'nuevo_comentario' => '
		<h1>New comment</h1>
		ID => :ID <br>
		post_ID => :post_ID <br>
		author => :author <br>
		author_email => :author_email <br>
		author_url => :author_url <br>
		author_IP => :author_IP <br>
		date => :date <br>
		user_id => :user_id <br>
		content => :content <br>
		',

	'nuevo_user' => 'Hi bro,
		<br>
		Welcome to NuevaMetal! Heres how to
			<a href="http://nuevametal.com/wp-login.php"> log in</a>:<br>
		<b>Username</b>: :user_login <br>
		<b>Password</b>: :user_pass <br>
		<br>
		If you have any problems, please contact me at :admin_email <br>
		See you!',

	'password_reset' => '
		<h2>Requested a password change in <a href=" :blogurl " target="_blank"> :blogname </a></h2>
		<b>Username:</b> :user_login <br>
		<b>E-mail:</b> :user_email <br>
		<b>Request new password</b>:<br>
		<a href=" :user_key_url " target="_blank"> :user_key_url </a> <br><br>
		<p>You have requested a password change. Enter this link and it will send an email with a new randomly
		generated password. Then remember to change it from your user control panel.</p>
		<small>If you didn\'t request any changes you can skip this mail.</small>',

	'password_reset_2' => '
		<h2>Retrieving your password in <a href=" :blogurl " target="_blank"> :blogname </a></h2>
		<b>Username:</b> :user_login <br>
		<b>E-mail:</b> :user_email <br>
		<b>Password:</b> :user_pass <br><br>
		<p>Here you have the new password. Remember to change it from your user control panel. <br>
		If you have any problems, please contact me :admin_email <br>
		See you!</p>'
];