var myLine;
$(document).ready(function(){
/*
	var d = new Date();
	var n = d.getFullYear();
	var min = n - 4;
	var color = ["#8DC153", "#F7AE00", "#0082D6"];
	var i = 0;
	var anios = [];
	for (j=min; j<=n;j++){
		if(j != 2016)
			anios.push(j);
		else
			anios.push(j+'-1');
	}
		$.ajax({
					method: "GET",
					dataType: "json",
					data:{inicio:min, termino:n},
					url: "api/get_matriculados_anio",
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
*/

		var data = {
			labels: ["2007","2008","2009","2010","2011","2012", "2013", "2014", "2015"],
			datasets: [
				{
		            label: "Matrículas",
		            fillColor: "#ff9c43",
		            strokeColor: "rgba(220,220,220,0.8)",
		            highlightFill: "rgba(220,220,220,0.75)",
		            highlightStroke: "rgba(220,220,220,1)",
		            data: [19,38,40,51,42,44,49,60,62]
		        }
		 ]

		};
		
		var ctx = document.getElementById("canvas").getContext("2d");
			myLine = new Chart(ctx).Bar(data, {
			responsive: true,
			bezierCurve : false,
			datasetFill : false,
			
			scaleOverride : true,
			scaleSteps : 5,
			scaleStepWidth : Math.ceil(Math.max.apply(Math,data.datasets[0].data)/5),
			scaleStartValue : 0,
			
			legend : true,
			xAxisLabel: "Año",
			yAxisLabel: "Número de Matriculas",
			annotateDisplay : true,
			annotateLabel: "<%= v1 %> - <%= v3 %>",
		});
});/*

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
}
};*/
