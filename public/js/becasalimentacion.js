var myLine;
var myLine2;
var color = ["#8DC153", "#F7AE00", "#0082D6"];
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

			$.redirect("../acreditacion/becas-alimentacion",{ inicio: $('#fechainicio').val(), termino: $('#fechatermino').val()}, 'GET');
	});


	var i = 0;
	var anios = [];
	for (j=inicio; j<=termino;j++){
		if(termino != j)
			anios.push(j);
		else
			anios.push(j+'-1');
	}

		$.ajax({
				method: "GET",
				dataType: "json",
				url: "../api/get_becas_alimentacion",
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
			var ctx2 = document.getElementById("canvas").getContext("2d");
				myLine = new Chart(ctx2).Bar(lineChartData, {
				responsive: true,
				bezierCurve : false,
				yAxisMinimumInterval : 25,
				legend : true,
				datasetFill : false,
				savePng : true,
				savePngOutput: "Save",
				xAxisLabel: "Años",
				yAxisLabel: "Becas Otorgadas (N°)",
				annotateDisplay : true,
				annotateLabel: "<%= v1 %> - <%= v3 %>",
			});
		});

	});
