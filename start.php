<?php
require_once 'mvc/libs/Helpers.php';

use Libs\Acciones;
use Libs\Filtros;

define(URL_PRODUCCION, 'nuevametal.com');
define(URL_DESARROLLO, 'dev.nuevametal.com');
define(URL_LOCAL, 'nuevametal.local');

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

Acciones::commentPost();
// --------------------------------------------------------------
// Filtros
// --------------------------------------------------------------

Filtros::comentariosConBootstrap3();

Filtros::contentSavePre();
Filtros::theContent();
Filtros::theTitle();
