/*
 Autor: Jose Maria Valera Reales <@Chemaclass>
 */
/**
 * Controlar el scroll
 */
$(window).scroll(function() {
	var scroll = $(window).scrollTop();
	if (scroll >= 200) {
		scrollOn();
	} else {
		scrollOff();
	}
	
	if ($(this).scrollTop() > 220) {
		//if (!getWindowWidth('xs')) {
			$('.back-to-top').fadeIn(500);
		//}
	} else {
		$('.back-to-top').fadeOut(500);
	}
	
});

/**
 * Cuando se hace scroll y se deja de ver el header
 */
function scrollOn() {

	$(".navbar-principal").addClass("navbar-fixed-top");
	$(".perfil-login").addClass("hidden");
	
	if (!getWindowWidth('xs')) {
		$("#content").addClass("aumentar-padding-top-content");
		$("#sidebar").addClass("aumentar-padding-top-content");
	}
	
	$(".navbar-principal-login").removeClass("hidden");
	$(".navbar-principal-login-xs").parent('button').removeClass("hidden");
}

/**
 * Cuando se está viendo el header; aún no se ha hecho scroll
 */
function scrollOff() {
	if (!getWindowWidth('xs')) {
		$(".navbar-principal").removeClass("navbar-fixed-top");
		$(".perfil-login").removeClass("hidden");
		$(".navbar-principal-login-xs").parent('button').addClass("hidden");
	}
		$("#content").removeClass("aumentar-padding-top-content");
		$("#sidebar").removeClass("aumentar-padding-top-content");
	
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
	if (getWindowWidth('xs')) {
		scrollOn()
		scrollOff()
	}
	$('.back-to-top').click(function(event) {
		event.preventDefault();
		$('html, body').animate({
			scrollTop : 0
		}, 500);
		return false;
	});

	$('.navbar-header .navbar-brand').click(function(){
		$('.back-to-top').trigger('click');
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
	
	$('.mostrar-mas').click(function(e){
		e.preventDefault();
		var posts = $(this).parents('.posts');
		var seccion = $(posts).find('.seccion');
		var que = $(this).attr('mostrar-mas');
		var url = $(this).attr('url');
		var size = $(seccion).children().size();
		
		var data = {
			submit : 'mostrar-mas',
			que : que,
			max: 4,
			size: size 
		};
		console.log("mostrar-mas: " + que);
		$.ajax({
			url : url,
			type : "POST",
			data : data,
			dataType : "json",
			beforeSend: function() {
				$(posts).find('.fa-spinner').removeClass('hidden');
			},
			success : function(json) {
				$(seccion).append(json.content);
				$(posts).find('.fa-spinner').addClass('hidden');
			},
			error: function (xhr, ajaxOptions, thrownError) {
		        alert("Ocurrió un error inesperado.\n" 
		        		+"Por favor, ponte en contacto con los administradores y cómentale qué sucedió");
				console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
				+ ",\n thrownError "+thrownError);
		     }
		});
	});
});