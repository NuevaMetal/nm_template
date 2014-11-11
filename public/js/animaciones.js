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
	meta.find('.avatar').addClass('tada');
	meta.find('.descripcion').css('display','block');
	meta.find('.descripcion').removeClass('flipOutX');
	meta.find('.descripcion').addClass('flipInX');
});

$(document).on('mouseleave', '#post-meta', function(e) {
	e.preventDefault();
	var meta = $('#post-meta');
	meta.find('.avatar').removeClass('tada');	
	meta.find('.descripcion').removeClass('flipInX');
	meta.find('.descripcion').addClass('flipOutX');
	timer = setTimeout(function(){
		meta.find('.descripcion').css('display','none');
	}, 1500);
});

/**
 * Agrandar los iconos de las redes sociales.
 */
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
