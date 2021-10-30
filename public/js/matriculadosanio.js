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

			$.redirect("../programa/cantidad-matriculados-anio",{ inicio: $('#fechainicio').val(), termino: $('#fechatermino').val()}, 'GET');
	});

	var color = ["#8DC153", "#F7AE00", "#0082D6"];
	var i = 0;
	var anios = [];
	for (j=inicio; j<=termino;j++)
		anios.push(j);
	$.ajax({
				method: "GET",
				dataType: "json",
				data:{inicio:$('#fechainicio').val(), termino:$('#fechatermino').val()},
				url: "../api/get_matriculados_anio",
	})
	.done(function( msg ) {
		console.log(msg);
		var data = [];
		$.each(msg, function(){
				item = {};
				item["data"] = this.value;
				item["label"] = this.label;
				item["fillColor"] = color[i];
				item["strokeColor"] = color[i];
				data.push(item);
				i++;
		});
		console.log(data);
		var lineChartData = {labels : anios, datasets:data};
		console.log(lineChartData);
		var ctx2 = document.getElementById("canvas").getContext("2d");
			myLine = new Chart(ctx2).Bar(lineChartData, {
			responsive: true,
			datasetFill : false,
			yAxisMinimumInterval : 200,
			legend : true,
			savePng : true,
			savePngOutput: "Save",
			xAxisLabel: "Años",
			yAxisLabel: "Alumnos matriculados (N°)",
			annotateDisplay : true,
			annotateLabel: "<%= v1 %> - <%= v3 %>",
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
