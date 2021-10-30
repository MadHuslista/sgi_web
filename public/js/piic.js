
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

			$.redirect("../programa/piic",{ inicio: $('#fechainicio').val(), termino: $('#fechatermino').val()}, 'GET');
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
				url: "../api/get_piic",
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
				yAxisMinimumInterval : 20,
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


		$.ajax({
				method: "GET",
				dataType: "json",
				url: "../api/get_montos_becas_piic",
		})
		.done(function( msg ) {
			console.log(msg);
			console.log(i);
			i = 0;
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
			chartJSLineStyle["my_defined_style"]=[1,1000];
			var ctx = document.getElementById("canvas2").getContext("2d");
				myLine2 = new Chart(ctx).Bar(lineChartData, {
				responsive: true,
				bezierCurve : false,
				yAxisMinimumInterval : 40000000,
				legend : true,
				thousandSeparator : ".",
				fmtXLabel: 'notformatted',
				datasetFill : false,
				savePng : true,
				savePngOutput: "Save",
				xAxisLabel: "Años",
				yAxisLabel: "Monto (CLP)",
				annotateDisplay : true,
				annotateLabel: "<%= v1 %> - <%= v3 %>",
			});
		});


		console.log(document.getElementById("canvas").toDataURL("image/png"));

		});
