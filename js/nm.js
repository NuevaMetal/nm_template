/*
 Autor: Jose Maria Valera Reales <@Chemaclass>
 */
/**
 * Controlar el scroll
 */
$(window).scroll(function() {
	var scroll = $(window).scrollTop();
	var windowHeight = $( window ).height();
	var documentHeight = $(document).height();

	if (scroll >= 200) {
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
	var noHaySpinner = $('.mostrar-mas').find('.fa-spinner').hasClass('hidden');
	if( $('.mostrar-mas').size() == 1 && noHaySpinner && flag <= 280) {
		$('.mostrar-mas').trigger('click');
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
				$('#alertas').html(json.content);
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


	/* 
	 * **************************************
	 *              Cookies
	 * **************************************
	 */
	function getCookie(c_name) {
		var c_value = document.cookie;
		var c_start = c_value.indexOf(" " + c_name + "=");
		if (c_start == -1) {
			c_start = c_value.indexOf(c_name + "=");
		}
		if (c_start == -1) {
			c_value = null;
		} else {
			c_start = c_value.indexOf("=", c_start) + 1;
			var c_end = c_value.indexOf(";", c_start);
			if (c_end == -1) {
				c_end = c_value.length;
			}
			c_value = unescape(c_value.substring(c_start, c_end));
		}
		return c_value;
	}
	function setCookie(c_name, value, exdays) {
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value = escape(value)
				+ ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
		document.cookie = c_name + "=" + c_value;
	}
	if (getCookie('nm_aviso') != "1") {
		document.getElementById("cookies").style.display = "block";
	}
	
	$('.poner-cookie').click(function() {
		setCookie('nm_aviso', '1', 365);
		document.getElementById("cookies").style.display = "none";
	});
	
});