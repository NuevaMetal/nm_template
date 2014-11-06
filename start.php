<?php
use Libs\Acciones;
use Libs\Filtros;

define(URL_PRODUCCION, 'nuevametal.com');
define(URL_DESARROLLO, 'dev.nuevametal.com');

// --------------------------------------------------------------
// Acciones
// --------------------------------------------------------------
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

Acciones::establecerDefectoOpcionesParaAdjuntos();

Acciones::publicarPostsProgramados();

// --------------------------------------------------------------
// Filtros
// --------------------------------------------------------------

Filtros::comentariosConBootstrap3();

Filtros::contentSavePre();
Filtros::theContent();

Filtros::preCommentContent();
