/*
 Autor: Jose Maria Valera Reales <@Chemaclass>
 */
/**
 * Controlar el scroll
 */
$(window).scroll(function() {
	var scroll = $(window).scrollTop();
	if (scroll >= 240) {
		scrollOn();
	} else {
		scrollOff();
	}

	if ($(this).scrollTop() > 220) {
		$('.back-to-top').fadeIn(500);
	} else {
		$('.back-to-top').fadeOut(500);
	}
});

/**
 * Cuando se hace scroll y se deja de ver el header
 */
function scrollOn() {
	$(".navbar-principal").addClass("navbar-fixed-top");
	$(".navbar-login").addClass("hidden");
	$(".navbar-principal-login").removeClass("hidden");
	$("#content").addClass("aumentar-padding-top-content");
	
}

/**
 * Cuando se está viendo el header; aún no se ha hecho scroll
 */
function scrollOff() {
	$(".navbar-principal").removeClass("navbar-fixed-top");
	$(".navbar-login").removeClass("hidden");
	$(".navbar-principal-login").addClass("hidden");
	$("#content").removeClass("aumentar-padding-top-content");
}

/**
 * Constantes de la anchura
 */
var COL = {
	SM : 768,
	MD : 992,
	LG : 1200
};

/**
 * Documento listo para JQuery
 */
$(document).ready(function() {

	$('.back-to-top').click(function(event) {
		event.preventDefault();
		$('html, body').animate({
			scrollTop : 0
		}, 500);
		return false;
	});

//	$( ".thumbnail" )
//    .mouseenter(function() {
//        $(this).find('.caption').removeClass("fadeOutUp").addClass("fadeInDown").show();
//    })
//    .mouseleave(function() {
//        $(this).find('.caption').removeClass("fadeInDown").addClass("fadeOutUp");
//    }); 


	/**
	 * Cargar Disqus
	 */
	var disqus_shortname = 'nuevametalweb';
	(function() {
	    var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
	    dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
	    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
	})();
	
});

