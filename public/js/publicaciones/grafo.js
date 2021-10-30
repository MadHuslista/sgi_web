var w = 850,
          h = 600,
          rx = w / 2,
          ry = h / 2,
          m0,
          rotate = 0;
var colors= [];


$(document).ready(function(){


      var splines = [];
	var d = new Date();
		var n = d.getFullYear();
		for (i=2000;i<=n;i++){
			$(".btn-group").append('<button type="button" class="btn btn-default year" id="'+i+'">'+i+'</button>')
		}
		$(".btn-group").append('<button type="button" class="btn btn-default year" id="acum">ACUMULADO</button>')
      // $(".node a").tooltip({
      //     placement : 'right'
      // });

			$('#tosvg a').click(function(){
				//~ $('#tosvg a').attr("download",$("#key").text()+".svg")
							//~ .attr("href",getSVG());
			});

			$(".year").click(function(){
        load_graph($(this).attr("id"));
        $("#anob").html('<b>' + $(this).attr("id") + '</b>')
      });
	  });
	  $.ajax({
				url : "../api/departamentos_color",
				method:"POST",
				dataType: "json",
				success : function(data){
					var cjson = [];
					item = {};
					item["Q1"] = "#FF0000";
					item["Q2"] = "#238E23";
					item["Q3"] = "#0000FF";
					item["Q4"] = "#FF00FF";

					for (i = 0; i < data.length; i++){
					console.log(data[i].color);
					item[data[i].name] = data[i].color;

					}
					cjson.push(item);

					colors = cjson;

				}
			});
	// Chrome 15 bug: <http://code.google.com/p/chromium/issues/detail?id=98951>
      var svg;
      load_graph(2014);
      var div;
		var normalize = (function() {
        var from = "ÃƒÃ€ÃÃ„Ã‚ÃˆÃ‰Ã‹ÃŠÃŒÃÃÃŽÃ’Ã“Ã–Ã”Ã™ÃšÃœÃ›Ã£Ã Ã¡Ã¤Ã¢Ã¨Ã©Ã«ÃªÃ¬Ã­Ã¯Ã®Ã²Ã³Ã¶Ã´Ã¹ÃºÃ¼Ã»Ã‘Ã±Ã‡Ã§",
            to   = "AAAAAEEEEIIIIOOOOUUUUaaaaaeeeeiiiioooouuuunncc",
            mapping = {};

        for(var i = 0, j = from.length; i < j; i++ )
            mapping[ from.charAt( i ) ] = to.charAt( i );

        return function( str ) {
            var ret = [];
            for( var i = 0, j = str.length; i < j; i++ ) {
                var c = str.charAt( i );
                if( mapping.hasOwnProperty( str.charAt( i ) ) )
                    ret.push( mapping[ c ] );
                else
                    ret.push( c );
            }
            return ret.join( '' );
        }
      })();
	var cluster = d3.layout.cluster()
          .size([360, ry - 120])
          .sort(function(a, b) { return d3.ascending(a.key, b.key); });

    var bundle = d3.layout.bundle();

	 var line = d3.svg.line.radial()
          .interpolate("bundle")
          .tension(.85)
          .radius(function(d) { return d.y; })
          .angle(function(d) { return d.x / 180 * Math.PI; });
      function mouse(e) {
        return [e.pageX - rx, e.pageY - ry];
      }

      function mousedown() {
        m0 = mouse(d3.event);
        d3.event.preventDefault();
      }

      function mousemove() {
        if (m0) {
          var m1 = mouse(d3.event),
              dm = Math.atan2(cross(m0, m1), dot(m0, m1)) * 180 / Math.PI;
          div.style("-webkit-transform", "translateY(" + (ry - rx) + "px)rotateZ(" + dm + "deg)translateY(" + (rx - ry) + "px)");
        }
      }

      function mouseup() {
        if (m0) {
          var m1 = mouse(d3.event),
              dm = Math.atan2(cross(m0, m1), dot(m0, m1)) * 180 / Math.PI;

          rotate += dm;
          if (rotate > 360) rotate -= 360;
          else if (rotate < 0) rotate += 360;
          m0 = null;

          div.style("-webkit-transform", null);

          svg
              .attr("transform", "translate(" + rx + "," + ry + ")rotate(" + rotate + ")")
            .selectAll("g.node text")
              .attr("dx", function(d) { return (d.x + rotate) % 360 < 180 ? 8 : -8; })
              .attr("text-anchor", function(d) { return (d.x + rotate) % 360 < 180 ? "start" : "end"; })
              .attr("transform", function(d) { return (d.x + rotate) % 360 < 180 ? null : "rotate(180)"; });
        }
      }

      function mouseover(d) {
        svg.selectAll("path.link.target-" + d.key.replace(/ /g, ''))
            .classed("target", true)
            .each(updateNodes("source", true));

        svg.selectAll("path.link.source-" + d.key.replace(/ /g, ''))
            .classed("source", true)
            .each(updateNodes("target", true));
      }

      function mouseout(d) {
        svg.selectAll("path.link.source-" + d.key.replace(/ /g, ''))
            .classed("source", false)
            .each(updateNodes("target", false));

        svg.selectAll("path.link.target-" + d.key.replace(/ /g, ''))
            .classed("target", false)
            .each(updateNodes("source", false));
      }

      function updateNodes(name, value) {
        return function(d) {
          if (value) this.parentNode.appendChild(this);
          svg.select("#node-" + d[name].key.replace(/ /g, '')).classed(name, value);
        };
      }

      function cross(a, b) {
        return a[0] * b[1] - a[1] * b[0];
      }

      function dot(a, b) {
        return a[0] * b[0] + a[1] * b[1];
      }



      function load_graph(year){
        d3.select("#grafo_circular div").remove();

        div = d3.select("#grafo_circular").insert("div", "h2")
            .style("width", w + "px")
            .style("height", h + "px")
            .style("-webkit-backface-visibility", "hidden");

        svg = div.append("svg:svg")
            .attr("width", w)
            .attr("height", h)
          .append("svg:g")
            .attr("transform", "translate(" + rx + "," + ry + ")");

        svg.append("svg:path")
            .attr("class", "arc")
            .attr("d", d3.svg.arc().outerRadius(ry - 120).innerRadius(0).startAngle(0).endAngle(2 * Math.PI))
            .on("mousedown", mousedown);

        d3.json("../api/circular?year="+year, function(classes) {

          var nodes = cluster.nodes(packages.root(classes)),
              links = packages.imports(nodes),
              splines = bundle(links);

          // INDICE DE COLABORACION
          var interd = 0;
          var intrad = 0;
          var indice = 0;
          var enlaces = 0;
          var coef = 0;
          for(i=0; i<nodes.length; i++){
            if(nodes[i].imports){
              enlaces += nodes[i].imports.length;
              if(nodes[i].imports.length>0){
                for(j=0; j<nodes[i].imports.length; j++){
                  if(nodes[i].imports[j].split('.')[0]==nodes[i].name.split('.')[0]) intrad++;
                  else interd++;
                }
              }
            }
          }
          enlaces /= 2;
          interd /= 2;
          intrad /= 2;
          var posible = nodes.length*(nodes.length-1)/2
          if(posible != 0){
            coef = enlaces/posible*100;
            coef = coef.toFixed(2);
          }
          if(interd+intrad != 0){
            indice = (interd / (intrad+interd))*100;
            indice = indice.toFixed(2);
          }
          $("#indice").html("En este grafo, la colaboración real corresponde a un <b>" + coef + "%</b> respecto a colaboración potencial. Un <b>" + indice + "%</b> de la colaboración real se da entre departamentos.");
          // ----------------------------------------

          var path = svg.selectAll("path.link")
              .data(links)
            .enter().append("svg:path")
              .attr("class", function(d) { return "link source-" + d.source.key.replace(/ /g, '') + " target-" + d.target.key.replace(/ /g, ''); })
              .attr("d", function(d, i) { return line(splines[i]); });

          svg.selectAll("g.node")
              .data(nodes.filter(function(n) { return !n.children; }))
            .enter().append("svg:g")
              .attr("class", function(d){ return "node " + normalize(d.name.split(".")[0]) })
              .attr("id", function(d) { return "node-" + d.key.replace(/ /g, ''); })
              .attr("transform", function(d) { return "rotate(" + (d.x - 90) + ")translate(" + d.y + ")"; })
            .append("svg:text")
              .attr("dx", function(d) { return d.x < 180 ? 8 : -8; })
              .attr("dy", ".31em")
              .attr("text-anchor", function(d) { return d.x < 180 ? "start" : "end"; })
              .attr("transform", function(d) { return d.x < 180 ? null : "rotate(180)"; })
              .text(function(d) { return d.key; })
              .on("mouseover", mouseover)
              .on("mouseout", mouseout)
            .append("svg:title")
              .text(function(d) { if(d.name.split('.')[0]=='O') return "O. CIVILES"; else if(d.name.split('.')[0]=='ING') return "ING. QUÃMICA"; else return d.name.split('.')[0]});

          d3.select("input[type=range]").on("change", function() {
            line.tension(this.value / 100);
            path.attr("d", function(d, i) { return line(splines[i]); });
          });
        });

        d3.select(window)
            .on("mousemove", mousemove)
            .on("mouseup", mouseup);

      }
