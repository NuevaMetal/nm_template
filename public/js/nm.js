/*
 Autor: Jose Maria Valera Reales <@Chemaclass>
 */
$(document).ready(function(){
	
});

/**
 * Controlar el scroll
 */
$(window).scroll(function() {
	seHaceScroll();
});

$(window).on("resize", function() {
	seHaceScroll();	
});

var ALTURA_MINIMA_PARA_MOSTRAR_MAS = 2000;

/**
 * Constantes de la anchura
 */
var COL = { SM : 768, MD : 992, LG : 1200, XL : 1600, };

function seHaceScroll() {
	var scroll = $(window).scrollTop();
	var windowHeight = $( window ).height();
	var documentHeight = $(document).height();
	var winWidth = $(window).width();

	// Ajuste del menú Para pantallas no xs
	if (!getWindowWidth('xs')) {
		if (scroll >= 260 || winWidth < COL.SM-15) {		
			scrollOn();
		} else {
			scrollOff();
		}
	} else {
		// Ajuste del menú Para pantallas xs
		$(".navbar-principal").addClass("navbar-fixed-top");
		$(".perfil-login").addClass("hidden");
		$(".navbar-principal-login-xs").parent('button').removeClass("hidden");
	}
	
	if (scroll > 200) {
		$('.back-to-top').fadeIn(500);
	} else {
		$('.back-to-top').fadeOut(500);
	}
	// Si estamos en la home no cargaremos el mostrar más de forma automática
	if($('#home').length>0)return;
	// Si solo hay un mostrar más, entonces lo presionará solo al bajar 
	var alturaMenosScroll = (documentHeight - windowHeight)-scroll;
	var noHayspin = $('.mostrar-mas').find('.fa-spin').hasClass('hidden');
	var sePuede = noHayspin && alturaMenosScroll <= ALTURA_MINIMA_PARA_MOSTRAR_MAS;
	if( $('.mostrar-mas').size() == 1 && sePuede) {
		$('.mostrar-mas').trigger('click');
	} else if($('#autor .mostrar-mas').size() == 1 && sePuede){
		$('#autor .mostrar-mas').trigger('click');
	}
	// scroll en la pantalla de actividad
	if($('#actividad').length > 0 && sePuede){
		seHaceScrollEnActividad();
	}
}

/**
 * Cuando se hace scroll y se deja de ver el header
 */
function scrollOn() {

	$(".navbar-principal").addClass("navbar-fixed-top");
	$(".perfil-login").addClass("hidden");
	
	$("#content").addClass("aumentar-padding-top-content");
	
	$(".navbar-principal-login").removeClass("hidden");
	$(".navbar-principal-login-xs").parent('button').removeClass("hidden");
}

/**
 * Cuando se está viendo el header; aún no se ha hecho scroll
 */
function scrollOff() {
	$(".navbar-principal").removeClass("navbar-fixed-top");
	$(".perfil-login").removeClass("hidden");
	
	$(".navbar-principal-login-xs").parent('button').addClass("hidden");
	$("#content").removeClass("aumentar-padding-top-content");
	
	$(".navbar-principal-login").addClass("hidden");
}

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
 * Botón me gusta
 */
$(document).on('click', '.btn-me-gusta', function(e) {
	e.preventDefault();
	var $this = $(this);
	var post = $this.parents('.post');
	var sidebar = $this.parents('.post-content').find('#sidebar');
	var formulario = $this.parents('.formulario');
	var form = formulario.find('form');
	var post_val = form.find('[name=post]').val();
	var user_val = form.find('[name=user]').val();
	var te_gusta = $this.attr('te-gusta'); 
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
			post.find('.total-me-gustas .cant').html(json.total_me_gustas);
			formulario.find('.fa-spin').addClass('hidden');
			$this.replaceWith(json.btn);
			// Al sidebar
			if (json.user_que_gusta['quitar']) {
				sidebar.find('.users-que-gustan').find('.user-'+json.user_que_gusta['user']).remove();
			} else {
				sidebar.find('.users-que-gustan').prepend(json.user_que_gusta);
			}
			mostrarAlerta(json.alert, 2);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
});

/**
 * Mostrar más
 */
$(document).on('click', '.mostrar-mas', function(e) {
	e.preventDefault();
	var $this = $(this);
	var posts = $(this).parents('.posts');
	var seccion = $(posts).find('.seccion');
	var cant = $(posts).children('.cant').text();
	var tipo = $(posts).children('.tipo').text();
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
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$(posts).find('.fa-spin').removeClass('hidden');
			$(posts).find('.icono-mas').addClass('hidden');
			$this.attr("disabled", true);
		},
		success : function(json) {
			if(json.code == 200 ) {
				$(seccion).append(json.content);
			}
			$(posts).find('.fa-spin').addClass('hidden');
			$(posts).find('.icono-mas').removeClass('hidden');
			$this.attr("disabled", false);
		},
		error: function (xhr, ajaxOptions, thrownError) {
//	         alert("Ocurrió un error inesperado.\n" 
//	        		+"Por favor, ponte en contacto con los administradores y coméntale qué sucedió.");
			 console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			 + ",\n thrownError "+thrownError);
			$this.addClass("hidden");
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
 * Botón para solicitar ser colaborador
 */
$(document).on('click', '.ser-colaborador', function(e) {
	e.preventDefault();
	var $this = $(this);
	var url = $this.attr('url');
	var user = $this.attr('user');
	var data = {
		submit : 'ser-colaborador',
		user : user
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$this.attr("disabled", true);
		},
		success : function(json) {
			mostrarAlerta(json.alerta, 5);
			$this.attr("disabled", false);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log("ser-colaborador ERROR (?)");
		}
	});
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
			mostrarAlerta(json.alerta, 5);
			formulario.find('.fa-spin').addClass('hidden');
		},
		error: function (xhr, ajaxOptions, thrownError) {
	        alert("Ocurrió un error inesperado.\n" 
	        		+"Por favor, ponte en contacto con los administradores y cómentale qué sucedió");
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
});

/**
 * Mostrar una alerta
 */
function mostrarAlerta(texto, segundos){
	$('#alertas').hide();
	$('#alertas').html(texto);
	$('#alertas').fadeIn();		
	setTimeout(function(){
		$('#alertas').fadeOut();
	}, segundos*1000);
}

/**
 * Agrandar los iconos de las redes sociales
 */
$(document).on('mouseover', '.redes-sociales a', function(e) {
	e.preventDefault();
	$(this).addClass('btn-lg');
});

$(document).on('mouseleave', '.redes-sociales a', function(e) {
	e.preventDefault();
	$(this).removeClass('btn-lg');
});

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
 * Documento listo para JQuery
 */
$(document).ready(function() {
	if (getWindowWidth('xs')) {
		scrollOn()
		scrollOff()
	}

	// Pongo esto porque si no no hace caso
	setTimeout(function() {
		seHaceScroll();
	}, 10);
	
	//Si no está la home
	if ($("#home").length == 0){
		cargarMenus();
	}
	// Y si estámos en la home entonces se cargará en el window.load, cuando todos los elementos 
	// estén cargados
});

/**
 * Cuando todos los elementos básicos estén cargados (ej:imágenes)
 */
$(window).load(function(){
	if ($("#home").length > 0){
		cargarMenus();
		cargarSecciones();
	}
});

/**
 * Cargar los Menus
 */
function cargarMenus(){
	// Cargamos los menus por ajax
	cargarMenu('menu-principal');
	cargarMenu('menu-perfil');
	cargarMenu('menu-footer');	
}

/**
 * Cargar las secciones
 */
function cargarSecciones() {
	
	cargarSeccion('bandas', 4);
	
	setTimeout(function() {
		cargarSeccion('videos', 4);		
	}, 1000);
	setTimeout(function() {
		cargarSeccion('criticas', 2);
		cargarSeccion('cronicas', 2);
	}, 2500);
	setTimeout(function() {
		cargarSeccion('noticias', 2);
		cargarSeccion('conciertos', 2);
	}, 4000);
	setTimeout(function() {
		cargarSeccion('entrevistas', 4);
	}, 5500);
}

/**
 * Cargar una sección apartir de su nombre
 * 
 * @param nombreSeccion Nombre de la sección a cargar
 * @param cant cantidad de elementos a cargar
 */
function cargarSeccion(nombreSeccion, cant){
	var seccion = $('#'+nombreSeccion);
	if(seccion.length==0) return; // Si no existe el elemento no hacemos nada
	var url = seccion.parent().attr('url');
	var data = {
		submit : 'home',
		seccion : nombreSeccion,
		cant : cant
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			seccion.find('.fa-spin').removeClass('hidden');
		},
		success : function(json) {
			seccion.find('.seccion_contenido').html(json.seccion);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
}

/**
 * Cargar el menú por Ajax
 * 
 * @param tipoMenu El menu a cargar
 */
function cargarMenu(tipoMenu){
	var menu = $('#'+tipoMenu);
	if(menu.length==0) return; // Si no existe el elemento no hacemos nada
	
	var url = menu.parents('#page').attr('url');
	var data = {
		submit : 'menu',
		tipo : tipoMenu
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			menu.find('.fa-spin').removeClass('hidden');
		},
		success : function(json) {
			menu.addClass('hidden'); //Oculto el menú
			menu.html(json.menu);	// Añado el html
			menu.fadeTo( "fast", 0, function(){ // Le pongo 0 a opacidad
				// Muestro de nuevo el elemento 
				menu.removeClass('hidden')
				// Aumento hasta 100% su opacidad para conseguir el efecto
				menu.fadeTo( "slow", 1);
			});
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
}

/**
 * Botón borrar comentario
 */
$(document).on('click', '.borrar-comentario', function(e) {
	e.preventDefault();
	var $this =  $(this);
	var url = $this.parents('.post-content').attr('url');
	var comment = $this.parents('.row');
	var id = comment.attr('id').split('-')[1];
	var data = {
		submit : 'post',
		tipo: 'borrar-comentario',
		id : id
	};
	var txt = comment.find('p').text();
	txt = (txt.length > 30) ? txt.substr(0,30) + "..." : txt;
	if(!confirm('Estás seguro de querer eliminar el comentario?\n' + txt)) {
		return;
	}
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$this.addClass('hidden');
			comment.find('.fa-spin').removeClass('hidden');
		},
		success : function(json) {
			comment.remove();
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
});


/**
 * Botón seguir
 */
$(document).on('click', '.seguir', function(e) {
	e.preventDefault();
	var $this =  $(this);
	var url = $this.attr('url');
	var id = $this.attr('id');
	var seguir = $this.attr('seguir');
	var data = {
		submit : 'user',
		tipo: 'seguir',
		id : id,
		seguir: seguir
	};	
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$this.find('.fa-spin').removeClass('hidden');
			$this.find('.fa-more').addClass('hidden');
			$this.attr('editable', false);
		},
		success : function(json) {
			if(json.code == 200){
				$this.attr('editable', true);
				$this.replaceWith(json.btn);
				$this.find('.fa-spin').addClass('hidden');
				$this.find('.fa-more').removeClass('hidden');
				mostrarAlerta(json.alert, 2);
			} else {
				mostrarAlerta(json.err, 2);
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			+ ",\n thrownError "+thrownError);
	     }
	});
});

/**
 * Se hace scroll en la pantalla de actividad
 */
function seHaceScrollEnActividad() {	
	var actividad = $('#actividad');
	var actividades = $('.actividades');	
	var url = $('#page').attr('url');
	var size = $(actividades).children().size();
	var data = {
		submit : 'user',
		tipo: 'actividad',
		size: size,
	};
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		beforeSend: function() {
			$(actividad).find('.fa-spin').removeClass('hidden');		
		},
		success : function(json) {
			if(json.code == 200 ) {
				$(actividades).append(json.content);
			}
			$(actividad).find('.fa-spin').addClass('hidden');
		},
		error: function (xhr, ajaxOptions, thrownError) {
//	         alert("Ocurrió un error inesperado.\n" 
//	        		+"Por favor, ponte en contacto con los administradores y coméntale qué sucedió.");
			 console.log("status: "+xhr.status + ",\n responseText: "+xhr.responseText 
			 + ",\n thrownError "+thrownError);
	     }
	});
}
