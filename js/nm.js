/*
 Autor: José María Valera Reales <@Chemaclass>
 */

function subheaderLoginFormToogle() {
	$('#subheader #loginform .ayuda').slideToggle();
}

$(document).ready(function() {
	subheaderLoginFormToogle();
	$('#subheader #loginform ').click(function() {
		subheaderLoginFormToogle();
		console.log("clicaste");
	});
});

$(window).scroll(function() {
	var scroll = $(window).scrollTop();
	if (scroll >= 200) {
		$(".navbar-principal").addClass("navbar-fixed-top");
	} else {
		$(".navbar-principal").removeClass("navbar-fixed-top");
	}
});
