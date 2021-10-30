var myLine;
var color = ["#008452", "#F7AE00", "#8493AB"];
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
	for (j=2006; j<=2015;j++)
		anios.push(j);
		$.ajax({
				method: "GET",
				dataType: "json",
				url: "../api/productividad2",
		})
		.done(function( msg ) {
			console.log(msg);
			var data = [];
			$.each(msg, function(){
					item = {};
					item["data"] = this.value;
					item["label"] = this.label;
					item["pointColor"] = "#"+this.pointColor;
					item["strokeColor"] = "#"+this.strokeColor;
					data.push(item);
					i++;
			});
			console.log(data);
			var lineChartData = {labels : anios, datasets:data};
			var ctx2 = document.getElementById("canvas").getContext("2d");
				myLine = new Chart(ctx2).Line(lineChartData, {
				responsive: true,
				bezierCurve : true,
				yAxisMinimumInterval : 200,
				legend : true,
				datasetFill : false,
				savePng : true,
				savePngOutput: "Save",
				xAxisLabel: "Años",
				yAxisLabel: "Cantidad Publicaciones",
				annotateDisplay : true,
				annotateLabel: "<%= v1 %> - <%= v3 %>",
			});
			console.log(myLine);
		});

	});
