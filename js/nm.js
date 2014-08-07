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
		if (!getWindowWidth('xs')) {
			$('.back-to-top').fadeIn(500);
		}
	} else {
		$('.back-to-top').fadeOut(500);
	}
	
});

/**
 * Cuando se hace scroll y se deja de ver el header
 */
function scrollOn() {
	if (!getWindowWidth('xs')) {
		$(".navbar-principal").addClass("navbar-fixed-top");
		$("#content").addClass("aumentar-padding-top-content");
		$(".perfil-login").addClass("hidden");
	}
	$(".navbar-principal-login").removeClass("hidden");
}

/**
 * Cuando se está viendo el header; aún no se ha hecho scroll
 */
function scrollOff() {
	$(".navbar-principal").removeClass("navbar-fixed-top");
	$("#content").removeClass("aumentar-padding-top-content");
	$(".perfil-login").removeClass("hidden");
	$(".navbar-principal-login").addClass("hidden");

}

/**
 * Constantes de la anchura
 */
var COL = { SM : 768, MD : 992, LG : 1200, XL : 1600, };

/**
 * Devuelve verdadero si el tamaño de la ventana se corresponde con el
 * solicitado mediante el parámetro que se le pasa.
 * 
 * @param string
 *            tam Tamaño
 * @returns {Boolean}
 */
function getWindowWidth(tam) {
	var w = $(window).width();
	if (tam == "xs") {
		return w < COL.SM;
	} else if (tam == "sm") {
		return w < COL.MD;
	} else if (tam == "md") {
		return w < COL.LG;
	} else if (tam == "lg") {
		return w < COL.XL;
	}
	return true;
}


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

	// $( ".thumbnail" )
	// .mouseenter(function() {
	// $(this).find('.caption').removeClass("fadeOutUp").addClass("fadeInDown").show();
	// })
	// .mouseleave(function() {
	// $(this).find('.caption').removeClass("fadeInDown").addClass("fadeOutUp");
	// });

	/**
	 * Cargar Disqus
	 */
	var disqus_shortname = 'nuevametalweb';
	(function() {
		var dsq = document.createElement('script');
		dsq.type = 'text/javascript';
		dsq.async = true;
		dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
		(document.getElementsByTagName('head')[0] || document
				.getElementsByTagName('body')[0]).appendChild(dsq);
	})();

	/**
	 * Botón notificar
	 */
	$('#btn-notificar').click(function(e) {
		e.preventDefault();
		var formulario = $(this).parents('.formulario');
		var form = formulario.find('form');
		var post_val = form.find('[name=post]').val();
		var user_val = form.find('[name=user]').val();
		var submit = form.find('[name="submit"]'); 
		var url = form.attr('action');
		var data = {
			submit : 'notificar',
			post : post_val,
			user : user_val
		};
		$.ajax({
			url : url,
			type : "POST",
			data : data,
			dataType : "json",
			beforeSend: function() {
				formulario.find('.fa-spinner').removeClass('hidden');
			},
			success : function(json) {
				$('#alertas').html(json.alerta);
				$('#alertas').fadeIn();
				formulario.find('.fa-spinner').addClass('hidden');				
				setTimeout(function(){
					$('#alertas').fadeOut();
				}, 5000);
			},
			error: function (xhr, ajaxOptions, thrownError) {
		        alert("Ocurrió un error inesperado.\n" 
		        		+"Por favor, ponte en contacto con los administradores y cómentale qué sucedió");
				console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
				+ ",\n thrownError "+thrownError);
		     }
		});
	});
	
	
	$('.login-necesario').click(function(e){
		e.preventDefault();
		$('.back-to-top').trigger('click');
	});
	
});