/**
 * 
 */
$(document).on('click','.quitar-header, .quitar-avatar, .bloquear', function(e) {
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
	$.ajax({
		url : url,
		type : "POST",
		data : data,
		dataType : "json",
		success : function(json) {
			switch (json) {
			case 'quitar-header':
				var header = $('.img-header img');
				header.attr('src','');
				header.addClass('sin-thumbnail');
				break;
			case 'quitar-avatar':
//				var header = $('.avatar');
//				header.attr('src','');
//				header.addClass('sin-thumbnail');
				alert("TODO");
				break;
			case 'bloquear':
				alert("TODO");
				break;
			}
		},
		error : function(xhr, ajaxOptions, thrownError) {
			console.log("status: " + xhr.status + ",\n responseText: "
					+ xhr.responseText + ",\n thrownError "
					+ thrownError);
		}
	});
});
