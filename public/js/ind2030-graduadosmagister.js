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

				$.redirect("../indicadores2030/graduados-magister",{ inicio: $('#fechainicio').val(), termino: $('#fechatermino').val()}, 'GET');
		});


	var color = ["rgba(220,220,220,1)", "rgba(151,187,205,1)", "rgba(193,17,17,0.8)"];

	var i = 0;
	var anios = [];
	for (j=inicio; j<=termino;j++)
		anios.push(j);

	$.ajax({
			method: "GET",
			dataType: "json",
			url: "../api/get_graduados_magister",
	})
	.done(function( msg ) {
			console.log(msg);
			var data = [];
	    $.each(msg, function(){
	        item = {};
	        item["data"] = this.value;
	        item["label"] = this.label;
	        item["pointColor"] = color[i];
					item["strokeColor"] = color[i];
	        data.push(item);
					i++;
	    });
			var lineChartData = {labels : anios, datasets:data};
			var ctx = document.getElementById("canvas").getContext("2d");
			  myLine = new Chart(ctx).Line(lineChartData, {
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
/*var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
var myLine;

		var lineChartData = {
			labels : anios,
			datasets : [
				{
					label: "My First dataset",
					fillColor : "rgba(220,220,220,0.2)",
					strokeColor : "rgba(220,220,220,1)",
					pointColor : "rgba(220,220,220,1)",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(220,220,220,1)",
					data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
				},
				{
					label: "My Second dataset",
					fillColor : "rgba(151,187,205,0.2)",
					strokeColor : "rgba(151,187,205,1)",
					pointColor : "rgba(151,187,205,1)",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(151,187,205,1)",
					data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
				},
				{
					fillColor : "rgba(151,187,205,0.5)",
					strokeColor : "rgba(193,17,17,0.8)",
					highlightFill : "rgba(151,187,205,0.75)",
					highlightStroke : "rgba(151,187,205,1)",
					data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
				}
			]
		}
	window.onload = function(){
		var ctx = document.getElementById("canvas").getContext("2d");
		  myLine = new Chart(ctx).Line(lineChartData, {
			responsive: true,
			bezierCurve : false,
			onAnimationComplete: done
		});
	}*/
