$(document).ready(function(){
	var pub = false;
	var proy = false;
	var exa = false;
	var exl = false;
	var otros = false;
	var ada = false;
	var baseurl = $("#body").data("baseurl");
	var id = $("#myChart").attr("data-id");
	console.log(id);
	$.ajax({
		url : baseurl+"/api/get_publicaciones",
		type : 'GET',
		data : {"id":id},
		contentType : 'application/json utf-8',
		dataType : 'json',
		success : function(ret){
			console.log(ret.pubs);
			var d = new Date();
			var n = d.getFullYear();
			var labels = [];
			for (i = 2007; i <= n; i++)
				labels.push(i);
			var data = {
				labels:labels,
				datasets: [
				{
					label: "Publicaciones por año",
					fillColor: "rgba(151,187,205,0.2)",
					strokeColor: "rgba(151,187,205,1)",
					pointColor: "rgba(151,187,205,1)",
					pointStrokeColor: "#fff",
					pointHighlightFill: "#fff",
					pointHighlightStroke: "rgba(151,187,205,1)",
					data: ret.pubs
				},
				]
			};

			var data3 = {
				labels:labels,
				datasets: [
				{
					label: "Proyectos por año",
					fillColor: "rgba(151,187,205,0.2)",
					strokeColor: "rgba(151,187,205,1)",
					pointColor: "rgba(151,187,205,1)",
					pointStrokeColor: "#fff",
					pointHighlightFill: "#fff",
					pointHighlightStroke: "rgba(151,187,205,1)",
					data: ret.proyectos
				},
				]
			};

			var options = {
				//Boolean - Whether to fill the dataset with a colour
				datasetFill : true,
			};
			var options2 = {
				//Boolean - Whether we should show a stroke on each segment
				segmentShowStroke : false,
			};

			// Get context with jQuery - using jQuery's .get() method.
			if($("#cant-pub").data('val') > 0){
				var ctx = $("#myChart").get(0).getContext("2d");
				var myLineChart = new Chart(ctx).Line(data, options);
			}

			if($("#cant-proyectos").data('val') > 0){
				var ctx3 = $("#myChart3").get(0).getContext("2d");
				var myLineChartProyecto = new Chart(ctx3).Line(data3, options);
			}
		},
		error: function(e){
			console.log(e);
		}
	});

	$("#verpub").on("click", function(){
		if( false == pub){
			$('html,body').animate({
				scrollTop: $("#table_pub").offset().top
			}, 1000);
			pub = true;
		}
		else{
			$('html,body').animate({
				scrollTop: $("#titlepub").offset().top
			}, 1000);
			pub = false;
		}
	});

	$("#verproy").on("click", function(){
		if( false == proy){
			$('html,body').animate({
				scrollTop: $("#ret-proy").offset().top
			}, 1000);
			proy = true;
		}
		else{
			$('html,body').animate({
				scrollTop: $("#titleproy").offset().top
			}, 1000);
			proy = false;
		}
	});

	$("#vertes").on("click", function(){
		$.ajax({
			url : baseurl+"/api/get_tesis_persona",
			type : 'GET',
			data : {"id":id},
			contentType : 'application/json utf-8',
			dataType : 'json',
			success : function(data){
				var html = "";
				console.log(data);
				if ("2" == data.res){
					html+="<p>La persona no registra tesis</p>";
				}
				else if("1" == data.res){
					html+='<table id="table_pub" class="table"><thead><th>Estudiante</th><th>Participacion</th><th>Tipo</th><th>Año</th></thead><tbody>';
					$.each(data.data, function(){
						html += "<tr><td>"+this.title+"</td><td>"+this.student_name+"</td><td>"+this.type+"</td><td>"+this.year+"</td></tr>";
					});
					html +="</tbody></table>";
					console.log(html);
				}
				$("#ret-tes").html(html);
				if( false == proy){
					$('html,body').animate({
						scrollTop: $("#ret-tes").offset().top
					}, 1000);
					proy = true;
				}
				else{
					$('html,body').animate({
						scrollTop: $("#titletes").offset().top
					}, 1000);
					proy = false;
				}
			}
		});
	});

	$("#vercon").on("click", function(){
		$.ajax({
			url : baseurl+"api/get_congresos_persona",
			type : 'GET',
			data : {"id":id},
			contentType : 'application/json utf-8',
			dataType : 'json',
			success : function(data){
				var html = "";
				console.log(data);
				if ("2" == data.res){
					html+="<p>La persona no registra congresos</p>";
				}
				else if("1" == data.res){
					html+='<table id="table_pub" class="table"><thead><th>Congreso</th><th>Tema</th><th>Año</th><th>País</th><th>Ciudad</th></thead><tbody>';
					$.each(data.data, function(){
						html += "<tr><td>"+this.congress+"</td><td>"+this.title+"</td><td>"+this.year+"</td><td>"+this.country+"</td><td>"+this.city+"</td></tr>";
					});
					html +="</tbody></table>";
					console.log(html);
				}
				$("#ret-con").html(html);
				if( false == proy){
					$('html,body').animate({
						scrollTop: $("#ret-con").offset().top
					}, 1000);
					proy = true;
				}
				else{
					$('html,body').animate({
						scrollTop: $("#titlecon").offset().top
					}, 1000);
					proy = false;
				}
			}
		});
	});
	$("#verExa").on('click', function(){
		if( false == exa){
			$('html,body').animate({
				scrollTop: $("#titleExa").offset().top
			}, 1000);
			exa = true;
		}
		else{
			$('html,body').animate({
				scrollTop: $("#titleExa").offset().top
			}, 1000);
			exa = false;
		}
	});
	$("#verExl").on('click', function(){
		if( false == exl){
			$('html,body').animate({
				scrollTop: $("#titleExl").offset().top
			}, 1000);
			exl = true;
		}
		else{
			$('html,body').animate({
				scrollTop: $("#titleExl").offset().top
			}, 1000);
			exl = false;
		}
	});
	$("#verAda").on('click', function(){
		if( false == ada){
			$('html,body').animate({
				scrollTop: $("#titleAda").offset().top
			}, 1000);
			ada = true;
		}
		else{
			$('html,body').animate({
				scrollTop: $("#titleAda").offset().top
			}, 1000);
			ada = false;
		}
	});
	$("#verOtros").on('click', function(){
		if( false == otros){
			$('html,body').animate({
				scrollTop: $("#titleOtros").offset().top
			}, 1000);
			otros = true;
		}
		else{
			$('html,body').animate({
				scrollTop: $("#titleOtros").offset().top
			}, 1000);
			otros = false;
		}
	});
});
