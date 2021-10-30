var d = new Date();
	var n = d.getFullYear();
	for (i=2000;i<=n;i++){
		$(".btn-group").append('<button type="button" class="btn btn-default year" id="'+i+'">'+i+'</button>')
	}
	//$(".btn-group").append('<button type="button" class="btn btn-default year" id="acum">ACUMULADO</button>')
