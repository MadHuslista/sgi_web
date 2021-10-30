var myLine;
$(document).ready(function(){
	var inicio = $('#content').data('inicio');
	var termino = $('#content').data('termino');
	var d = new Date();
	var n = d.getFullYear();
	$("#fechainicio").val(inicio);
	$("#fechatermino").val(termino);

	$("#fechainicio").change(function(){
			var limite = $("#fechainicio").val();
			$("#fechatermino").empty(); //remove all child nodes
			for(i=n-1; i > limite; i--){
				var newOption = $('<option value="'+i+'">'+i+'</option>');
				$('#fechatermino').append(newOption);
			}
	});

	$("#actualizarrango").on('click',function(){

			$.redirect("../programa/programas-extranjeros",{ inicio: $('#fechainicio').val(), termino: $('#fechatermino').val()}, 'GET');
	});

	var color = ["#008452", "#F7AE00", "#8493AB"];
	var i = 0;
	var anios = [];
	for (j=inicio; j<=termino;j++)
		anios.push(j);
	$.ajax({
				method: "GET",
				dataType: "json",
				url: "../api/get_extranjeros",
	})
	.done(function( msg ) {
		console.log(msg);
		var data = [];
		$.each(msg, function(){
				item = {};
				item["data"] = [this.value];
				item["label"] = this.label;
				item["pointColor"] = color[i];
				item["strokeColor"] = color[i];
				data.push(item);
				i++;
		});
		console.log(data);
		var lineChartData = {labels : anios, datasets:data};
		var ctx2 = document.getElementById("canvas").getContext("2d");
			myLine = new Chart(ctx2).Bar(lineChartData, {
			responsive: true,
			bezierCurve : false,
			datasetFill : false,
			multiTooltipTemplate: "<%= datasetLabel %> - <%= value %>",
			onAnimationComplete: done
		});
  });
	function done() {
		console.log('done');
		var height = $( window ).height()/3;
		var width = $( window ).width();
		base_image = new Image();
		base_image.src = myLine.toBase64Image();
		console.log(myLine.toBase64Image());

		var canvas2 = document.getElementById('tmpcanvas');
		context2 = canvas2.getContext('2d');
		$('#tmpcanvas').attr('height', height);
		$('#tmpcanvas').attr('width', width);
		// Draw image within
		context2.drawImage(base_image, 0,0);
}

});
