var myLine;
var myLine2;
$(document).ready(function(){

	$.ajax({
			method: "GET",
			dataType: "json",
			url: "../api/get_programas_acreditados_donut",
	})
	.done(function( msg ) {
			console.log(msg);
			var ctx = document.getElementById("canvas").getContext("2d");
			  myLine = new Chart(ctx).Pie(msg, {
					responsive: true,
					bezierCurve : false,
					datasetFill : false,
					legend : true,
					datasetStrokeStyle: 'dotted',
					datasetFill : false,
					savePng : true,
					savePngOutput: "Save",
					xAxisLabel: "Años",
					yAxisLabel: "Monto (pesos)",
					annotateDisplay : true,
					annotateLabel: "<%= v1 %> - <%= v2 %>",
				});
  });
	$.ajax({
			method: "GET",
			dataType: "json",
			url: "../api/get_programas_acreditados_prof_donut",
	})
.done(function( msg ) {
		console.log(msg);
		var ctx2 = document.getElementById("canvas-2").getContext("2d");
			myLine2 = new Chart(ctx2).Pie(msg, {
				responsive: true,
				bezierCurve : false,
				datasetFill : false,
				legend : true,
				datasetStrokeStyle: 'dotted',
				datasetFill : false,
				savePng : true,
				savePngOutput: "Save",
				xAxisLabel: "Años",
				yAxisLabel: "Monto (pesos)",
				annotateDisplay : true,
				annotateLabel: "<%= v1 %> - <%= v2 %>",
		});
	});

$.ajax({
		method: "GET",
		dataType: "json",
		url: "../api/get_programas_acreditados_cienti_donut",
})
.done(function( msg ) {
	console.log(msg);
	var ctx3 = document.getElementById("canvas-3").getContext("2d");
		myLine2 = new Chart(ctx3).Pie(msg, {
			responsive: true,
			bezierCurve : false,
			datasetFill : false,
			legend : true,
			datasetStrokeStyle: 'dotted',
			datasetFill : false,
			savePng : true,
			savePngOutput: "Save",
			xAxisLabel: "Años",
			yAxisLabel: "Monto (pesos)",
			annotateDisplay : true,
			annotateLabel: "<%= v1 %> - <%= v2 %>",
		});
	});
});
