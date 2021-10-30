var tabla = "";

$(document).ready(function(){
	stacked(1)


	$("#opt input[type=radio]").click(function(){
		if($('input:radio[type=radio]:checked').val() == "1"){
			$("#chart1").remove();
			$("#graph").append('<svg id="chart1"></svg>');
			$("#exportar").css("display", "block");
			stacked(1)
		}
		if($('input:radio[type=radio]:checked').val() == "2"){
			$("#chart1").remove();
			$("#graph").append('<svg id="chart1"></svg>');
			$("#exportar").css("display", "none");
			stacked(2)
		}
		if($('input:radio[type=radio]:checked').val() == "3"){
			$("#chart1").remove();
			$("#graph").append('<svg id="chart1"></svg>');
			$("#exportar").css("display", "none");
			stacked(3)
		}
	});

	$('#tosvg a').click(function(){
		//~ $('#tosvg a').attr("download",$("#key").text()+".svg")
							//~ .attr("href",getSVG());
	});



			function foo(opt) {
    var jqXHR = $.ajax({
        url : "../api/productividad",
        method: "POST",
        data: {options: opt, "csrf_token": $("#csrf_token").val()},
        async: false
    });
    //~ console.log(jqXHR.responseText)

    return jqXHR.responseText;
}

function stacked(opt){

	var histcatexplong = JSON.parse(foo(opt));
	tabla = histcatexplong;
	console.log(histcatexplong)
	var colors = d3.scale.category20();
	keyColor = function(d, i) {return colors(d.key)};

	var chart;
	nv.addGraph(function() {
	  chart = nv.models.stackedAreaChart()
				   // .width(600).height(500)
					.useInteractiveGuideline(true)
					.x(function(d) { return d[0] })
					.y(function(d) { return d[1] })
					.color(keyColor).controlLabels({stacked: "Stacked"})
            .duration(300);
					//.clipEdge(true);
	//~ console.log(nv)
	// chart.stacked.scatter.clipVoronoi(false);

	  chart.xAxis
		  .tickFormat(function(d) { return d3.time.format("%Y")(new Date(d,0)) });

	  chart.yAxis
		  .tickFormat(d3.format(',.1f'));

	  d3.select('#chart1')
		.datum(histcatexplong)
		.transition().duration(1000)
		.call(chart)
		// .transition().duration(0)
		.each('start', function() {
			setTimeout(function() {
				d3.selectAll('#chart1 *').each(function() {
				  //~ console.log('start',this.__transition__, this)
				  // while(this.__transition__)
				  if(this.__transition__)
					this.__transition__.duration = 1;
				})
			  }, 0)
		  })
		// .each('end', function() {
		//         d3.selectAll('#chart1 *').each(function() {
		//           console.log('end', this.__transition__, this)
		//           // while(this.__transition__)
		//           if(this.__transition__)
		//             this.__transition__.duration = 1;
		//         })});

	  nv.utils.windowResize(chart.update);

	  // chart.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });

	  return chart;
	});
	}$("#exportar").click(function(event) {
				var tabla_html = "<tbody>";
				$.each(tabla, function() {
					tabla_html += "<tr><td>"+this.key+"</td></tr>";
					tabla_html += "<tr><td>Año</td><td>Cantidad Publicaciones</td></tr>";
					console.log(this);
					$.each(this.values, function() {
						tabla_html += "<tr><td>"+this[0]+"</td><td>"+this[1]+"</td></tr>";
						console.log(this);
					});
				});
				tabla_html += "</tbody>";
				console.log(tabla_html);

				tableToExcel(tabla_html, 'tabla resumen', 'tabla_resumen.xls')
		  });

       });

			var tableToExcel = (function () {
        var uri = 'data:application/vnd.ms-excel;base64,'
        , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
        , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
        , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
        return function (table, name, filename) {

            var ctx = { worksheet: name || 'Worksheet', table: table }

            document.getElementById("dlink").href = uri + base64(format(template, ctx));
            document.getElementById("dlink").download = filename;
            document.getElementById("dlink").click();

        }
    	})()

    	function utf8_to_b64( str ) {
			    return window.btoa(encodeURIComponent( escape( str )));
			}

			function b64_to_utf8( str ) {
			    return unescape(decodeURIComponent(window.atob( str )));
			}

			function encode_utf8(s) {
			  return unescape(encodeURIComponent(s));
			}

			function decode_utf8(s) {
			  return decodeURIComponent(escape(s));
			}
