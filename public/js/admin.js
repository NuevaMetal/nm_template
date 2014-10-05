/**
 * 
 */
$(document).on('click','.quitar-header, .quitar-avatar, .bloquear, .desbloquear', function(e) {
	e.preventDefault();
	var $this = $(this);
	if(!confirm('¿Estás seguro de querer '+$this.text().toUpperCase()+' a este usuario?')){
		return;
	}
	var url = $this.attr('url');
	var que = $this.attr('que');
	var user = $this.attr('user');
	var data = {
		submit : 'admin-panel-user',
		que : que,
		user : user
	};
	if(que == 'bloquear' && !confirm('Bloquear a un usuario implica que este ya no podrá acceder a su cuenta'
			+' además de que ya no se podrá visualizar su perfil de usuario público a menos que se desactive el bloqueo.'
			+' ¿Estás seguro de continuar?')){
		return;
	}
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		success : function(json) {
			location.reload();
		},
		error : function(xhr, ajaxOptions, thrownError) {
			console.log("status: " + xhr.status + ",\n responseText: "
					+ xhr.responseText + ",\n thrownError "
					+ thrownError);
		}
	});
});
