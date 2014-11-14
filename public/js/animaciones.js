/*
 Autor: Jose Maria Valera Reales <@Chemaclass>
 =========================================
 Animaciones
 =========================================
 */

var timer;

/**
 * Animar el avatar del menú gris<->rojo
 */
$(document).on('mouseover', '.navbar-brand img, .navbar-collapse', function(e) {
	e.preventDefault();
	var imgUrl = $('.navbar-brand img').attr('src').replace('_gris.jpg', '.jpg')
	$('.navbar-brand img').attr('src', imgUrl);
});

$(document).on('mouseleave', '.navbar-brand img, .navbar-collapse', function(e) {
	e.preventDefault();
	var imgUrl = $('.navbar-brand img').attr('src').replace('.jpg', '_gris.jpg')
	$('.navbar-brand img').attr('src', imgUrl);
});

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
pasarRaton('.redes-sociales a', 'btn-lg');

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
$(document).on('click', '#sidebar .users-gustan .otros-mas .texto', function(e) {
	e.preventDefault();
	if($('#sidebar .users-gustan .otros').hasClass('zoomIn')) {
		ocultarOtrosUsersQueGustan();
	} else {
		mostrarOtrosUsersQueGustan();
	}
});
/** Pasar el ratón por "más usuarios que le dieron me gusta" */
$(document).on('mouseleave', '#sidebar .users-gustan .otros', function(e) {
	e.preventDefault();
	ocultarOtrosUsersQueGustan();
});

function mostrarOtrosUsersQueGustan() {
	clearTimeout(timer);
	var otros = $('#sidebar .users-gustan .otros');
	otros.css('display','block');
	otros.removeClass('zoomOut');
	otros.addClass('zoomIn');
}
function ocultarOtrosUsersQueGustan() {
	var otros = $('#sidebar .users-gustan .otros');
	timer = setTimeout(function(){
		otros.css('display','none')
	}, 100);                                                           
	otros.removeClass('zoomIn');
	otros.addClass('zoomOut');
}
