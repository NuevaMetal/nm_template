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
});

/**
 * Cuando se hace scroll y se deja de ver el header
 */
function scrollOn() {
	$(".navbar-principal").addClass("navbar-fixed-top");
	$(".navbar-login").addClass("hidden");
}

/**
 * Cuando se está viendo el header; aún no se ha hecho scroll
 */
function scrollOff() {
	$(".navbar-principal").removeClass("navbar-fixed-top");
	$(".navbar-login").removeClass("hidden");
}