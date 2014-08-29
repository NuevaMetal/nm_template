/*
 Autor: Jose Maria Valera Reales <@Chemaclass>
 */
/**
 * Controlar el scroll
 */
$(window).scroll(function() {
	seHaceScroll();
});

$(window).on("resize", function(){	
	seHaceScroll();
});

function seHaceScroll(){
	var scroll = $(window).scrollTop();
	var windowHeight = $( window ).height();
	var documentHeight = $(document).height();
	var winWidth = $(window).width();

	if (scroll >= 260 || winWidth < COL.SM-15) {
		scrollOn();
	} else {
		scrollOff();
	}

	if (scroll > 200) {
		$('.back-to-top').fadeIn(500);
	} else {
		$('.back-to-top').fadeOut(500);
	}

	// Si solo hay un mostrar más, entonces lo presionará solo al bajar 
	var flag = (documentHeight - windowHeight)-scroll;
	var noHayspin = $('.mostrar-mas').find('.fa-spin').hasClass('hidden');
	if( $('.mostrar-mas').size() == 1 && noHayspin && flag <= 280) {
		$('.mostrar-mas').trigger('click');
	}	
}
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
});

/**
 * Mostrar más
 */
$(document).on('click', '.mostrar-mas', function(e) {
	e.preventDefault();
	var posts = $(this).parents('.posts');
	var seccion = $(posts).find('.seccion');
	var cant = $(posts).find('.cant').text();
	var tipo = $(posts).find('.tipo').text();
	var que = $(this).attr('mostrar-mas');
	var url = $(this).attr('url');
	var size = $(seccion).children().size();
	
	var data = {
		submit : 'mostrar-mas',
		que : que,
		cant: cant,
		size: size,
		tipo: tipo
	};
	//console.log("mostrar-mas: " + que);	
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$(posts).find('.fa-spin').removeClass('hidden');
		},
		success : function(json) {
			console.log(json);
			if(json.code == 200 ) {
				$(seccion).append(json.content);
			}
			$(posts).find('.fa-spin').addClass('hidden');
		},
		error: function (xhr, ajaxOptions, thrownError) {
	        alert("Ocurrió un error inesperado.\n" 
	        		+"Por favor, ponte en contacto con los administradores y coméntale qué sucedió.");
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
});


/**
 * Botón notificar
 */
$(document).on('click', '.btn-me-gusta', function(e) {
	e.preventDefault();
	var $this = $(this);
	var formulario = $(this).parents('.formulario');
	var form = formulario.find('form');
	var post_val = form.find('[name=post]').val();
	var user_val = form.find('[name=user]').val();
	var te_gusta = $(this).attr('te-gusta'); 
	var nonce = $(this).attr('nonce');  
	var url = form.attr('action');
	var data = {
		submit : 'me-gusta',
		post : post_val,
		user : user_val,
		te_gusta: te_gusta,
		nonce: nonce
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			formulario.find('.fa-spin').removeClass('hidden');
		},
		success : function(json) {
			$('#alertas').html(json.content);
			$this.replaceWith(json.btn);
			$('#alertas').fadeIn();
			formulario.find('.fa-spin').addClass('hidden');				
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

$(document).on('click', '.back-to-top', function(e) {
	e.preventDefault();
	$('html, body').animate({
		scrollTop : 0
	}, 500);
	return false;
});

$(document).on('click', '.navbar-header .navbar-brand', function(e) {
	$('.back-to-top').trigger('click');
});

/**
 * Botón notificar
 */
$(document).on('click', '.btn-notificar', function(e) {
	e.preventDefault();
	var formulario = $(this).parents('.formulario');
	var form = formulario.find('form');
	var post_val = form.find('[name=post]').val();
	var user_val = form.find('[name=user]').val();
	var nonce = form.find('[name=nonce]').val();
	var url = form.attr('action');
	var data = {
		submit : 'notificar',
		post : post_val,
		user : user_val,
		nonce: nonce
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			formulario.find('.fa-spin').removeClass('hidden');
		},
		success : function(json) {
			$('#alertas').html(json.content);
			$('#alertas').fadeIn();
			formulario.find('.fa-spin').addClass('hidden');				
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

$(document).on('mouseover', '.redes-sociales a', function(e) {
	e.preventDefault();
	$(this).addClass('btn-lg');
});
$(document).on('mouseleave', '.redes-sociales a', function(e) {
	e.preventDefault();
	$(this).removeClass('btn-lg');
});