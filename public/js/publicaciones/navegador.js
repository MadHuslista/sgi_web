var anio = "2014";
var d = new Date();
	var n = d.getFullYear();
	for (i=2000;i<=n;i++){
		$(".btn-group").append('<button type="button" class="btn btn-default year" id="'+i+'">'+i+'</button>')
	}
  var DATA;
  var margin = {top: 30, right: 20, bottom: 30, left: 20},
	  width = 960 - margin.left - margin.right,
	  barHeight = 20,
	  barWidth = width * .8;

  var i = 0,
	  duration = 400,
	  root;

  var tree = d3.layout.tree()
	  .nodeSize([0, 20]);

  var diagonal = d3.svg.diagonal()
	  .projection(function(d) { return [d.y, d.x]; });

  var svg = d3.select("#container").append("svg")
	  .attr("width", width + margin.left + margin.right)
	.append("g")
	  .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

  load_year("2014");

  $(".year").click(function(){
	 anio = $(this).attr("id");
	load_year($(this).attr("id"));
  });

  function load_year(year){
	d3.json("../api/year_grafico?year=" + year, function(error, flare) {
	  flare.x0 = 0;
	  flare.y0 = 0;
	  DATA = update(root = flare);
	  DATA = DATA.children;
	  DATA.forEach(function(d){console.log(d);click(d)})
	});
  }

  function update(source) {

	// Compute the flattened node list. TODO use d3.layout.hierarchy.
	var nodes = tree.nodes(root);

	var height = Math.max(500, nodes.length * barHeight + margin.top + margin.bottom);

	d3.select("svg").transition()
		.duration(duration)
		.attr("height", height);

	d3.select(self.frameElement).transition()
		.duration(duration)
		.style("height", height + "px");

	// Compute the "layout".
	nodes.forEach(function(n, i) {
	  n.x = i * barHeight;
	});

	// Update the nodes�
	var node = svg.selectAll("g.node")
		.data(nodes, function(d) { return d.id || (d.id = ++i); });

	var nodeEnter = node.enter().append("g")
		.attr("class", "node")
		.attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; })
		.style("opacity", 1e-6);

	// Enter any new nodes at the parent's previous position.
	nodeEnter.append("rect")
		.attr("y", -barHeight / 2)
		.attr("class",function(d){return d.name})
		.attr("height", barHeight)
		.attr("width", barWidth)
		.style("fill", color)
		.on("click", click);

	nodeEnter.append("text")
		.attr("dy", 3.5)
		.attr("dx", 5.5)
		.text(function(d) { return d.name; });

	// Transition nodes to their new position.
	nodeEnter.transition()
		.duration(duration)
		.attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; })
		.style("opacity", 1);

	node.transition()
		.duration(duration)
		.attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; })
		.style("opacity", 1)
	  .select("rect")
		.style("fill", color);

	// Transition exiting nodes to the parent's new position.
	node.exit().transition()
		.duration(duration)
		.attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
		.style("opacity", 1e-6)
		.remove();

	// Update the links
	var link = svg.selectAll("path.link")
		.data(tree.links(nodes), function(d) { return d.target.id; });

	// Enter any new links at the parent's previous position.
	link.enter().insert("path", "g")
		.attr("class", "link")
		.attr("d", function(d) {
		  var o = {x: source.x0, y: source.y0};
		  return diagonal({source: o, target: o});
		})
	  .transition()
		.duration(duration)
		.attr("d", diagonal);

	// Transition links to their new position.
	link.transition()
		.duration(duration)
		.attr("d", diagonal);

	// Transition exiting nodes to the parent's new position.
	link.exit().transition()
		.duration(duration)
		.attr("d", function(d) {
		  var o = {x: source.x, y: source.y};
		  return diagonal({source: o, target: o});
		})
		.remove();

	// Stash the old positions for transition.
	nodes.forEach(function(d) {
	  d.x0 = d.x;
	  d.y0 = d.y;
	});
	
	return source
  }

  // Toggle children on click.
  function click(d) {

	if(d.depth == 4){
	  window.open("http://scholar.google.es/scholar?hl=es&q=" + d.name);
	}else{
	  if (d.children) {
		d._children = d.children;
		d.children = null;
	  } else {
		d.children = d._children;
		d._children = null;
	  }
	  update(d);
	}
  }
  
  function color(d) {
	Q = ["Q1","Q2","Q3","Q4","CUARTIL NO DEFINIDO"]
	if (Q.indexOf(d.name) <= -1){
		try{
			if (d.parent.name == "Q1")
				return "blue"
			if (d.parent.name == "Q2")
				return "green"
			if (d.parent.name == "Q3")
				return "orange"
			if (d.parent.name == "Q4")
				return "red"
			if (d.parent.name == "CUARTIL NO DEFINIDO")
				return "purple"
		}catch(e){
			var a = 1
		}
	}
	if (Q.indexOf(d.name) > -1){
		if (d.name == "Q1")
			return "blue"
		if (d.name == "Q2")
			return "green"
		if (d.name == "Q3")
			return "orange"
		if (d.name == "Q4")
			return "red"
		if (d.name == "CUARTIL NO DEFINIDO")
			return "purple"
	}
	if(d.depth == 4)
		return "#FFF";
	if(d.depth == 1)
		return "#BBB";
	if(d.depth == 0)
		return "#666";
	  
  }
  
  $("#generar").click(function(){
			  // console.log(prog);
			  $.ajax({
				url : "../api/year_grafico?year=" + anio,
				type:"json",
				method:"GET",
				success : function(ret){
				  ret = JSON.parse(ret);
				  var res_html = "<tbody>";
				  for (var i = 0; i < ret.children.length; i++){
					  res_html += "<tr><td>"+ret.children[i].name+"</td></tr>";
					  console.log(ret.children[i].name);
					  for(var j = 0; j < ret.children[i].children.length; j++){
						  console.log(ret.children[i].children[j].name);
						  res_html += "<tr><td>"+ret.children[i].children[j].name+"</td></tr>";
						  for (var k = 0; k < ret.children[i].children[j].children.length; k++){
							     console.log(ret.children[i].children[j].children[k].name);
							     res_html += "<tr><td>"+ret.children[i].children[j].children[k].name+"</td>";
							     for (var l = 0; l < ret.children[i].children[j].children[k].children.length; l++){
									 console.log(ret.children[i].children[j].children[k].children[l].name);
									 res_html += "<td>"+ret.children[i].children[j].children[k].children[l].name+"</td></tr><tr><td></td>";
								  }
								  res_html += "</tr>";
						  }
					   }
				  }
				  res_html+= "</tbody>";
				  console.log(res_html);
				  // console.log(JSON.parse(ret));
				  /*var res = JSON.parse(ret);
				  // console.log(res.length);
				  var res_html = "";
				  for(r in res){
					res_html += "<tr>";
					res_html += "<td>" + res[r].name + "</td>";
					res_html += "<td>" + res[r].date + "</td>";
					res_html += "<td>" + res[r].isi + "</td>";
					res_html += "<td>" + res[r].nisi + "</td>";
					res_html += "<td>" + res[r].fondecyt + "</td>";
					res_html += "<td>" + res[r].fondecyt_r + "</td>";
					res_html += "<td>" + res[r].fondef + "</td>";
					res_html += "<td>" + res[r].other + "</td>";
					res_html += "</tr>";
					cont++;
				  }
				  // console.log(res_html);
				  $("#tabla tbody").empty();
							$("#tabla tbody").append(res_html);
				  $("#tabla, #exportar").css("display", "block");*/
				  
				  tableToExcel(res_html, 'tabla resumen', 'tabla_resumen.xls')
				}
			}) 
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

