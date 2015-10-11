<?php
namespace Controllers;

use I18n\I18n;
use Libs\Correo;
use Libs\Utils;
use Models\Analitica;
use Models\Post;
use Models\Revision;
use Models\User;
use Models\UserBaneado;
use Models\UserBloqueado;
use Models\UserPendiente;

/**
 * Controlador principal de la web
 *
 * @author chemaclass
 */
class PageController extends BaseController
{

    /*
     * Miembros
     */
    protected $permisoEditor = false;

    protected $permisoAdmin = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->permisoEditor = $this->current_user && $this->current_user->canEditor();
        $this->permisoAdmin = $this->permisoEditor && $this->current_user->canAdmin();
    }

    /**
     * Paǵina de sitios de interés
     */
    public function getAmigas()
    {
        return $this->renderPage('pages/amigas');
    }

    /**
     * Analitica.
     * page-analytics.php
     */
    public function getAnalitica()
    {
        if (! $this->permisoEditor) {
            return $this->getError(404);
        }
        $logueados_hoy = Analitica::getUsersLogueados(50);
        $logueados_ayer = Analitica::getUsersLogueados(50, 'date(now())-1');
        $logueados_ayer_2 = Analitica::getUsersLogueados(50, 'date(now())-2');
        $logueados_ayer_3 = Analitica::getUsersLogueados(50, 'date(now())-3');
        $logueados_ayer_4 = Analitica::getUsersLogueados(50, 'date(now())-4');
        $logueados_ayer_5 = Analitica::getUsersLogueados(50, 'date(now())-5');

        return $this->renderPage('pages/analitica', [
            'logueados_hoy' => $logueados_hoy,
            'logueados_ayer' => $logueados_ayer,
            'logueados_ayer_2' => $logueados_ayer_2,
            'logueados_ayer_3' => $logueados_ayer_3,
            'logueados_ayer_4' => $logueados_ayer_4,
            'logueados_ayer_5' => $logueados_ayer_5
        ]);
    }

    /**
     * Paǵina de contacto
     */
    public function getContacto()
    {
        session_start();

        $CLAVE_NONCE = 'clave_nonce_contacto';
        $CLAVE_CAPTCHA = 'clave_captcha_contacto';
        $mostrarFormulario = true;
        $errores = [];

        $nonce = $_POST['nonce'];
        $captcha = $_POST['captcha'];
        $toDepart = $_GET['to'];

        if ($nonce) {
            // Verificamos si hay nonce
            $verify_captcha = $captcha && wp_verify_nonce($captcha, $_SESSION[$CLAVE_CAPTCHA]);
            if (wp_verify_nonce($nonce, $_SESSION[$CLAVE_NONCE]) && $verify_captcha) {

                $mostrarFormulario = false;

                $departamento = $_POST['departamento'];
                $email = $_POST['email'];
                $nombre = $_POST['nombre'];
                $web = $_POST['web'];
                $mensaje = $_POST['mensaje'];

                $emailsDepartamentos = [
                    get_option('admin_email')
                ];
                if ($departamento) {
                    switch ($departamento) {
                        case 'publicidad':
                            $user = User::findAllBy('user_login', 'Juan Valera', true);
                            $emailsDepartamentos[] = $user->getEmail();
                            break;
                        case 'desarrollo':
                            $user = User::findAllBy('user_login', 'Chemaclass', true);
                            $emailsDepartamentos[] = $user->getEmail();
                            break;
                        case 'general':
                            $user = User::findAllBy('user_login', 'JesusVa', true);
                            $emailsDepartamentos[] = $user->getEmail();
                            $user = User::findAllBy('user_login', 'Juan Valera', true);
                            $emailsDepartamentos[] = $user->getEmail();
                            break;
                    }
                }

                $blogname = get_option('blogname');
                $plantillaContacto = I18n::trans('emails.contacto', [
                    'blogname' => $blogname,
                    'blogurl' => home_url(),
                    'departamento' => ucfirst($departamento),
                    'email' => $email,
                    'mensaje' => $mensaje,
                    'nombre' => $nombre,
                    'web' => $web
                ]);
                $asunto = 'Mensaje de contacto [' . $blogname . ']';
                $enviado = Correo::enviarCorreoGenerico(array_unique($emailsDepartamentos), $asunto, $plantillaContacto);
            } else
                if (! $verify_captcha) {
                    $errores[] = 'Captcha incorrecto';
                }
        }
        /*
         * Creamos un nuevo nonce y un nuevo captcha en cada petición.
         */
        $_SESSION[$CLAVE_NONCE] = 'nonce-contacto' . time();
        $nonce = wp_create_nonce($_SESSION[$CLAVE_NONCE]);
        $_SESSION[$CLAVE_CAPTCHA] = 'captcha-contacto' . time();
        $captcha = wp_create_nonce($_SESSION[$CLAVE_CAPTCHA]);

        return $this->renderPage('pages/contacto', [
            'mostrarFormulario' => $mostrarFormulario,
            'nonce' => $nonce,
            'captcha' => $captcha,
            'errores' => $errores,
            'nombreDepartamento' => $departamento,
            'toDepart' => [
                'general' => $toDepart == 'general',
                'publicidad' => $toDepart == 'publicidad',
                'desarrollo' => $toDepart == 'desarrollo'
            ]
        ]);
    }

    /**
     * category.php
     */
    public function getCategory()
    {
        // TODO:
        $current_category = single_cat_title("", false);
        $current_category = strtolower($current_category);

        $args = HomeController::getSeccion($current_category, HomeController::NUM_POST_POR_SECCION);

        return $this->renderPage('busqueda', [
            'seccion' => $args,
            'total_posts' => count($args['posts'])
        ]);
    }

    /**
     * Paǵina de aviso legal.
     */
    public function getLegal()
    {
        return $this->renderPage('pages/legal');
    }

    /**
     * Página de Conócenos
     * About Us.
     */
    public function getEquipo()
    {
        // $chema = User::findAllBy('user_nicename', 'chemaclass', true);
        // $jesus = User::findAllBy('user_nicename', 'jesusva', true);
        // $juan = User::findAllBy('user_nicename', 'juan-valera', true);
        // $lola = User::findAllBy('user_nicename', 'metalola', true);
        $admins = User::findAllByRol(User::ROL_ADMIN);
        $editors = User::findAllByRol(User::ROL_EDITOR);
        return $this->renderPage('pages/equipo', [
            'admins' => $admins,
            'editors' => $editors
        ]);
    }

    /**
     * Página por defecto
     */
    public function getPage()
    {
        if (have_posts()) {
            the_post();
            $page = Post::find(get_the_ID());
        }

        if (! isset($page)) {
            return $this->renderPage('404');
        }

        return $this->renderPage('pages/page', [
            'page' => $page
        ]);
    }

    /**
     * Paǵina de redes
     */
    public function getRedes()
    {
        return $this->renderPage('pages/redes');
    }

    /**
     * Paǵina de nuevametal
     */
    public function getMega()
    {
        return $this->renderPage('pages/mega');
    }

    /**
     * Paǵina de nuevametal
     */
    public function getNuevaMetal()
    {
        return $this->renderPage('pages/nuevametal');
    }

    /**
     * single.php
     */
    public function getPost()
    {
        if (have_posts()) {
            the_post();
            $post = Post::find(get_the_ID());
        }

        if (! isset($post)) {
            return $this->renderPage('404');
        }

        return $this->renderPage('post', [
            'post' => $post,
            'ENTRADAS_SIMILARES' => Post::ENTRADAS_SIMILARES,
            'ENTRADAS_RELACIONADAS' => Post::ENTRADAS_RELACIONADAS
        ]);
    }

    /**
     * pending-posts.php
     */
    public function getPostsPendientes()
    {
        if (! $this->permisoEditor) {
            return $this->getError(404);
        }
        $postsPendientes = Post::getPendientes();
        return $this->renderPage('pages/pendientes_aceptar', [
            'total_entradas_pendientes' => count($postsPendientes),
            'entradas_pendientes' => $postsPendientes
        ]);
    }

    /**
     * page-revisions.php
     */
    public function getRevisiones()
    {
        if (! $this->permisoEditor) {
            return $this->getError(404);
        }
        $listaPendientes = Revision::getPendientes();
        $listaRevisadas = Revision::getCorregidas();
        $listaBaneos = UserBaneado::getBaneados();

        return $this->renderPage('pages/revisiones', [
            'total_pendientes' => count($listaPendientes),
            'total_corregidas' => count($listaRevisadas),
            'total_baneados' => count($listaBaneos),

            'ESTADO_BORRADO' => Revision::ESTADO_BORRADO,
            'ESTADO_CORREGIDO' => Revision::ESTADO_CORREGIDO,
            'ESTADO_PENDIENTE' => Revision::ESTADO_PENDIENTE,
            'USER_BANEADO' => UserBaneado::BANEADO,
            'USER_DESBANEADO' => UserBaneado::DESBANEADO,

            'pendientes' => [
                'lista' => $listaPendientes
            ],
            'corregidas' => [
                'lista' => $listaRevisadas
            ],
            'baneados' => [
                'lista' => $listaBaneos
            ]
        ]);
    }

    /**
     * search.php
     */
    public function getSearch()
    {
        $search_query = get_search_query();
        // Obtenemos los argumentos necesarios para pintarla
        $args = HomeController::getBusqueda($search_query, 4);
        $users = User::getUsersBySearch($search_query, $offset = 0, $limit = 4);
        $args['users'] = [
            'header' => I18n::trans('resultado_busqueda_usuarios', [
                'que' => $search_query
            ]),
            'seccion' => 'busqueda-users',
            'a_buscar' => $search_query,
            'tipo' => Utils::TIPO_BUSCAR_USUARIOS,
            'cant' => 4,
            'total_usuarios' => count($users),
            'lista_usuarios' => $users
        ];

        return $this->renderPage('busqueda', $args);
    }

    /**
     * tag.php
     */
    public function getTag()
    {
        $current_tag = single_tag_title('', false);
        $term = get_term_by('name', $current_tag, 'post_tag');

        $cant = 4;

        $args['seccion'] = 'busqueda';
        $args['a_buscar'] = strtolower($current_tag);
        $args['que'] = $current_tag;
        $args['url'] = get_tag_link($cat);
        $args['cant'] = $cant;
        $args['tipo'] = Utils::TIPO_TAG;
        $args['posts'] = HomeController::getPostsByTag($current_tag, $cant);

        /*
         * Obtenemos el fichero de idioma 'generos' para obtener el valor de la definición de la tag
         * apartir de la clave de la tag. Si no se encontrase retornaría un false, y por tanto no
         * se llegaría a pintar.
         */
        $fileGeneros = I18n::getFicheroIdioma('generos', I18n::getLangByCurrentUser());

        return $this->renderPage('busqueda', [
            'definicion' => $fileGeneros['definicion_' . $term->slug],
            'seccion' => $args,
            'total_posts' => count($args['posts']),
            'tag_trans' => I18n::transu('generos.' . $term->slug)
        ]);
    }

    /**
     * page-blocked-users.php
     */
    public function getUsuariosBloqueados()
    {
        if (! $this->permisoEditor) {
            return $this->getError(404);
        }
        $listaBloqueados = UserBloqueado::getByStatus(UserBloqueado::ESTADO_BLOQUEADO);
        $totalBloqueados = UserBloqueado::getTotalBloqueados();

        return $this->renderPage('pages/users_bloqueados', [
            'bloqueados' => $listaBloqueados,
            'total_bloqueados' => $totalBloqueados,
            'estado_borrado' => UserBloqueado::ESTADO_BORRADO
        ]);
    }

    /**
     * page-pending-users.php
     */
    public function getUsuariosPendientes()
    {
        if (! $this->permisoEditor) {
            return $this->getError(404);
        }
        $listaPendientes = UserPendiente::getByStatus(UserPendiente::PENDIENTE);
        $listaAceptados = UserPendiente::getByStatus(UserPendiente::ACEPTADO);
        $listaRechazados = UserPendiente::getByStatus(UserPendiente::RECHAZADO);
        return $this->renderPage('pages/users_pendientes', [
            'pendientes' => $listaPendientes,
            'total_pendientes' => count($listaPendientes),
            'aceptados' => $listaAceptados,
            'total_aceptados' => count($listaAceptados),
            'rechazados' => $listaRechazados,
            'total_rechazados' => count($listaRechazados)
        ]);
    }

    /**
     * Paǵina de tutorial
     */
    public function getTutorial()
    {
        return $this->renderPage('pages/tutorial');
    }
}
