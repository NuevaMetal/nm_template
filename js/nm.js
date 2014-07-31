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
	$("#content").addClass("aumentar-padding-top-content");
}

/**
 * Cuando se está viendo el header; aún no se ha hecho scroll
 */
function scrollOff() {
	$(".navbar-principal").removeClass("navbar-fixed-top");
	$(".navbar-login").removeClass("hidden");
	$("#content").removeClass("aumentar-padding-top-content");
}

/**
 * Mover texto a la izquierda
 * 
 * @param elem
 *            Elemento contenedor a mover
 */
function moverTextoIzquierda(elem) {
	var text = $(elem).children('a').text();
	function getTiempo(wid, len) {
		// console.log("wid: " + wid + ", len: " + len);
		if (len < 52 && w > COL.MD && w < COL.LG) {
			len -= 20;
		}
		if (len < 40)
			return len * 0.3;
		else if (len < 50)
			return len * 2.2;
		else if (len < 60)
			return len * 3;
		else if (len < 100)
			return len * 4;
		return len;
	}
	var w = $(window).width();
	var c = getTiempo(w, text.length);
	// Sólo animar si no está siendo animado ya. Para evitar sobrecarga
	if (!$(elem).is(':animated') && (w > COL.SM || w < 500)) {
		$(elem).animate({
			marginLeft : "-=" + c
		}, 1000, function() {
			setTimeout(function() {
				$(elem).removeAttr('style');
			}, 600);
		});
	}
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
$(document).ready( function() {
	$('.post-title').hover(function() {
		moverTextoIzquierda(this);
	}, function() {
		// $(this).removeAttr('style');
	});

	$('.back-to-top').click(function(event) {
		event.preventDefault();
		$('html, body').animate({
			scrollTop : 0
		}, 500);
		return false;
	});

	$(".thumbnail").mouseenter( function() {
		$(this).find('.caption').removeClass("fadeOutUp")
				.addClass("fadeInDown").show();
	})
	.mouseleave( function() {
		$(this).find('.caption').removeClass("fadeInDown")
				.addClass("fadeOutUp");
	});

});
