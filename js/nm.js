/*
 Autor: José María Valera Reales <@Chemaclass>
*/

function subheaderLoginFormToogle() {
	$('#subheader #loginform .ayuda').slideToggle();
}

$( document ).ready(function() {
	subheaderLoginFormToogle();
	$('#subheader #loginform ').click(function(){
		subheaderLoginFormToogle();
		console.log("clicaste");
	});
});


