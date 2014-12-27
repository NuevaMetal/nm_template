/*
 Autor: Jose Maria Valera Reales <@Chemaclass>
 =========================================
 Animaciones
 =========================================
 */
$(document).ready(function() {
	$('[data-toggle="tooltip"]').tooltip();
});

var timer;

/**
 * Animar el avatar del menú gris<->rojo
 */
$(document).on('mouseover', '.navbar-brand img, .navbar-collapse', function(e) {
	e.preventDefault();
	clearInterval(navbarInterval);
	_navbarRojo();
});

$(document).on('mouseleave', '.navbar-brand img, .navbar-collapse', function(e) {
	e.preventDefault();
	navbarInterval = crearIntervalLatido(1000)
	_navbarGris();
});

var navbarInterval = crearIntervalLatido(1500);

function crearIntervalLatido(time) {
	var _navbarLatido = 0;
	return setInterval(function () {
		if ( _navbarLatido%2 == 0 ) {
			_navbarRojo();
		} else {
			_navbarGris();
		}
		_navbarLatido++;
	}, time);
}

function _navbarRojo() {
	var imgUrl = $('.navbar-brand img').attr('src').replace('_gris.jpg', '.jpg')
	$('.navbar-brand img').attr('src', imgUrl);
}

function _navbarGris() {
	var imgUrl = $('.navbar-brand img').attr('src').replace('.jpg', '_gris.jpg')
	$('.navbar-brand img').attr('src', imgUrl);
}

/**
 * Animar el la descripción del autor de un post
 */
$(document).on('mouseover', '#post-meta', function(e) {
	e.preventDefault();
	clearTimeout(timer);
	var meta = $('#post-meta');
	//meta.find('.avatar').addClass('bounce');
	meta.find('.descripcion').css('display','block');
	meta.find('.descripcion').removeClass('zoomOut');
	meta.find('.descripcion').addClass('zoomIn');
});

$(document).on('mouseleave', '#post-meta', function(e) {
	e.preventDefault();
	var meta = $('#post-meta');
	//meta.find('.avatar').removeClass('bounce');	
	meta.find('.descripcion').removeClass('zoomIn');
	meta.find('.descripcion').addClass('zoomOut');
	timer = setTimeout(function(){
		meta.find('.descripcion').css('display','none');
	}, 1000);
});

/** Pasar el ratón por el nombre del autor */
$(document).on('mouseover', '#post-meta h3', function(e) {
	e.preventDefault();
	 $('#post-meta .avatar').addClass('bounce');
});
/** Pasar el ratón por la img del avatar del autor */
$(document).on('mouseleave', '#post-meta .avatar', function(e) {
	e.preventDefault();
	 $('#post-meta .avatar').removeClass('bounce');
});

// Agrandar los iconos de las redes sociales.
//pasarRaton('.redes-sociales a', 'btn-lg');

//pasarRaton('.must-log-in a', 'mostrar-menu-lateral');
/**
 * Dar al selector una clase cuando se pase el ratón por encima y quitarla cuando se quite el ratón.
 * 
 * @param selector Selector objetivo.
 * @param clase Clase a añadir y quitar.
 */
function pasarRaton(selector, clase){
	function mouseOver(selector, clase){
		$(document).on('mouseover',selector, function(e) {
			e.preventDefault();
			$(this).addClass(clase);
		});
	}
	function mouseLeave(selector, clase){
		$(document).on('mouseleave',selector, function(e) {
			e.preventDefault();
			$(this).removeClass(clase);
		});
	}
	mouseOver(selector, clase);
	mouseLeave(selector, clase);
}

/** Pasar el ratón por "más usuarios que le dieron me gusta a un post" */
$(document).on('click', '#post-sidebar .users-gustan .otros-mas .texto', function(e) {
	e.preventDefault();
	if($('#post-sidebar .users-gustan .otros').hasClass('zoomIn')) {
		ocultarOtrosUsersQueGustan();
		console.log("a");
	} else {
		mostrarOtrosUsersQueGustan();
		console.log("b");
	}
});
/** Pasar el ratón por "más usuarios que le dieron me gusta" */
$(document).on('mouseleave', '#post-sidebar .users-gustan .otros', function(e) {
	e.preventDefault();
	ocultarOtrosUsersQueGustan();
});

function mostrarOtrosUsersQueGustan() {
	clearTimeout(timer);
	var otros = $('#post-sidebar .users-gustan .otros');
	otros.css('display','block');
	otros.removeClass('zoomOut');
	otros.addClass('zoomIn');
}
function ocultarOtrosUsersQueGustan() {
	var otros = $('#post-sidebar .users-gustan .otros');
	timer = setTimeout(function(){
		otros.css('display','none')
	}, 100);                                                           
	otros.removeClass('zoomIn');
	otros.addClass('zoomOut');
}

/*
 * Mostrar/Ocultar el menú lateral
 */
var timerMenuDisplay;
var timerMenuAnimacion;
var timerLoginForm;
var contentIn = 'col-sm-10';
var contentOut = 'col-sm-10';
var menuIn = 'animated fadeInLeft';
var menuOut = 'animated fadeOutLeft';

$(document).on('click', '.navbar-header .navbar-brand', function(e) {
	e.preventDefault();
	window.location.href = '/';
});

$(document).on('click', '.mostrar-menu-lateral', function(e) {
	e.preventDefault();
	if(!comprobarSiMostrarMenuLateral()){
		return false;
	}
	menuLateralToggle();
});

// Comprueba si debemos mostrar el menú lateral
function comprobarSiMostrarMenuLateral() {
	if (getWindowWidth('xs') || $('#page').attr('user')=='') {
		$('.back-to-top').trigger('click');		
		//$('.login').css("border-bottom",'5px solid red');
		$('.login').addClass("borde-abajo-amarillo");
		timerLoginForm = setTimeout(function(){
			//$('.login').css("border-bottom",'none');
			$('.login').removeClass("borde-abajo-amarillo");
		}, 2500);
		return false;
	}
	return true;
}
// Menu lateral mostrar como un toggle
function menuLateralToggle() {	
	if ($('#menu-lateral').hasClass(menuIn)) {
		ocultarMenuLateral(0);
	} else {
		mostrarMenuLateral();
		$('.back-to-top').trigger('click');
	}
	return false;
}

function ocultarMenuLateral(segParaQuitar){
	var content = $('#content');
	var menu = $('#menu-lateral');
	timerMenuDisplay = setTimeout(function(){		
		menu.css('display','none');
	}, segParaQuitar);
	timerMenuAnimacion = setTimeout(function(){
		content.addClass(contentOut);
		content.removeClass(contentIn);
		menu.removeClass(menuIn);
		menu.addClass(menuOut);
	}, segParaQuitar);
}

function mostrarMenuLateral() {
	console.log("mostrarMenuLateral");
	clearTimeout(timerMenuAnimacion);
	clearTimeout(timerMenuDisplay);
	var content = $('#content');
	content.addClass(contentIn);
	content.addClass(contentOut);
	var menu = $('#menu-lateral');
	menu.css('display','block');
	menu.removeClass(menuOut);
	menu.addClass(menuIn);	
}

$(document).on('click', '#menu-lateral .cerrar-menu-lateral', function(e) {
	e.preventDefault();
	ocultarMenuLateral(500);
});

$(document).on('change', '#menu-footer .etiquetas', function(e) {
	e.preventDefault();
	var valueSelected = this.value;
	window.location.href = '/tag/'+valueSelected;
});
