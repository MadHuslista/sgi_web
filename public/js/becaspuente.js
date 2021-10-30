
$(document).ready(function(){
	var myLine;
	var canvasData;
	var myLine2;
	var color = ["#8DC153", "#F7AE00", "#0082D6"];
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

			$.redirect("../acreditacion/becas-puente",{ inicio: $('#fechainicio').val(), termino: $('#fechatermino').val()}, 'GET');
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
				url: "../api/get_becas_puente",
		})
		.done(function( msg ) {
			console.log(msg);
			var data = [];
			var datatmp = [];
			var lenght = msg.length;
			console.log(lenght);
			for (var i = 0; i < lenght; i++) {
				item = {};
				datatmp.push(msg[i]["value"]);
				console.log(msg[i]);
				//item["data"] = [msg[i]["value"]];
				item["label"] = msg[i]["label"];
				item["fillColor"] = "#8DC153";
				item["strokeColor"] = "#8DC153";


			}
			item["data"] = datatmp;
			data.push(item);

			console.log(data);
			var lineChartData = {labels : anios, datasets:data};
			var ctx2 = document.getElementById("canvas").getContext("2d");
				myLine = new Chart(ctx2).Bar(lineChartData, {
				responsive: true,
				bezierCurve : false,
				yAxisMinimumInterval : 5,
				legend : true,
				datasetFill : false,
				savePng : true,
				savePngOutput: "Save",
				xAxisLabel: "Años",
				yAxisLabel: "Becas Otorgadas (N°)",
				annotateDisplay : true,
				annotateLabel: "<%= v1 %> - <%= v3 %>",
			});
			canvasData = document.getElementById("canvas").toDataURL("image/png");
		});

		});
