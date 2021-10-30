var myLine;
var myLine2;
var myLine3;
var i = 0;
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

			//$.redirect("../acreditacion/becas-financieras",{ inicio: $('#fechainicio').val(), termino: $('#fechatermino').val()}, 'GET');
	});

	var anios = [];
	for (j=inicio; j<=termino;j++)
		anios.push(j);
		$.ajax({
				method: "GET",
				dataType: "json",
				url: "../api/get_origen_doctorados",
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
				datasetFill : false,
				yAxisMinimumInterval : 20,
				legend : true,
				savePng : true,
				savePngOutput: "Save",
				xAxisLabel: "Años",
				yAxisLabel: "Origen Alumnos (N°)",
				annotateDisplay : true,
				annotateLabel: "<%= v1 %> - <%= v3 %>",
			});
		});

		$("#tab2").on('click', function(){


		$.ajax({
				method: "GET",
				dataType: "json",
				url: "../api/get_origen_magister",
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
			var ctx = document.getElementById("canvas2").getContext("2d");
				myLine2 = new Chart(ctx).Bar(lineChartData, {
				responsive: true,
				bezierCurve : false,
				yAxisMinimumInterval : 70,
				legend : true,
				extrapolateMissingData : false,
				datasetFill : false,
				savePng : true,
				savePngOutput: "Save",
				xAxisLabel: "Años",
				yAxisLabel: "Origen Alumnos (N°)",
				annotateDisplay : true,
				annotateLabel: "<%= v1 %> - <%= v3 %>",
			});
		});
	});

	$("#tab3").on('click', function(){
		$.ajax({
				method: "GET",
				dataType: "json",
				url: "../api/get_origen_magister_profesional",
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
			var ctx3 = document.getElementById("canvas3").getContext("2d");
				myLine3 = new Chart(ctx3).Bar(lineChartData, {
				responsive: true,
				bezierCurve : false,
				yAxisMinimumInterval : 150,
				legend : true,
				extrapolateMissingData : false,
				datasetFill : false,
				savePng : true,
				savePngOutput: "Save",
				xAxisLabel: "Años",
				yAxisLabel: "Origen Alumnos (N°)",
				annotateDisplay : true,
				annotateLabel: "<%= v1 %> - <%= v3 %>",
			});
		});
	});

		});
