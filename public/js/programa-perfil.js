var myLine;
var myLine2;
$(document).ready(function(){
	var id = $('#content').data('id');
	var color = ["#008452", "#F7AE00", "#8493AB"];
	var i = 0;
	var d = new Date();
	var n = d.getFullYear();
	var anios = [];
	for (j=2011; j<=n;j++)
		anios.push(j);

	$.ajax({
			method: "GET",
			dataType: "json",
			url: "../../api/get_matriculados_anio_programa",
			data: {id:id},
			method:'GET'
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
		var lineChartData = {labels : anios, datasets:data};
		var ctx = document.getElementById("canvas").getContext("2d");
			myLine = new Chart(ctx).Bar(lineChartData, {
			responsive: true,
			bezierCurve : false,
			legend : true,
			datasetFill : false,
			savePng : true,
			yAxisMinimumInterval : 10,
			savePngOutput: "Save",
			xAxisLabel: "Años",
			yAxisLabel: "Cant Alumnos Matriculados",
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

$.ajax({
		method: "GET",
		dataType: "json",
		url: "../../api/get_graduados_anio_programa",
		data: {id:id},
		method:'GET'
})
.done(function( msg ) {
	console.log(msg);
	var data = [];
	$.each(msg, function(){
			item = {};
			item["data"] = this.value;
			item["label"] = this.label;
			item["fillColor"] = "#008452";
			item["strokeColor"] = "#008452";
			data.push(item);
			i++;
	});
	var lineChartData = {labels : anios, datasets:data};
	var ctx = document.getElementById("canvas2").getContext("2d");
		myLine2 = new Chart(ctx).Bar(lineChartData, {
			bezierCurve : false,
			legend : true,
			datasetFill : false,
			savePng : true,
			yAxisMinimumInterval : 1,
			savePngOutput: "Save",
			xAxisLabel: "Años",
			yAxisLabel: "Cant Alumnos Graduados",
			annotateDisplay : true,
			annotateLabel: "<%= v1 %> - <%= v3 %>",
	});
});

function done2() {
	console.log('done');
	var height = $( window ).height()/3;
	var width = $( window ).width();
	base_image = new Image();
	base_image.src = myLine.toBase64Image();
	console.log(myLine2.toBase64Image());

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
