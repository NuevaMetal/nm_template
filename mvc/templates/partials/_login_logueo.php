<?php
$display_name = wp_get_current_user()->display_name;
?>
<div class="login">
	<span><?php echo __("Bienvenido") . " <strong>$display_name</strong>!" ?></span>
	<div class="acciones">
		<a href="/wp-admin/profile.php" target="_blank" title="View your profile">Perfil</a>
		<a href="/wp-admin/post-new.php" target="_blank" title="New Post">Publicar</a>
		<a href="/wp-login.php?action=logout" title="Logout">Salir</a>
	</div>
</div>