var flag =0
		var lastlink = "";
		var lasttypes = "";
		var lasttypet = "";
		var lastnode = "";
		var lastcolor = "";
		var lastsearch = "";
		function zooming(type){
			var newzoom = zoom.scale();
			var newtranslate = zoom.translate();
			if (type == 0) newzoom += 0.05;
			if (type == 1) newzoom -= 0.05;
			zoom.scale(newzoom);
			zoom.translate(newtranslate);
			vis.attr("transform","translate("+newtranslate+") scale("+newzoom+")");
		}
		function resetNetwork(){
			vis.attr("transform","scale(1)");
			zoom.scale(1).translate([0,0]);
		}
		
		function color(){
			q = []
			q[3] = $("#Q4").attr("val")
			q[2] = $("#Q3").attr("val")
			q[1] = $("#Q2").attr("val")
			q[0] = $("#Q1").attr("val")
			$("line").each(function(){
				if ($(this).attr("relation") != undefined){
					$(this).attr("color","#333")
					for (var i = 3; i >= 0; i--){
						aux = q[i].split(",")
						relation = ($(this).attr("relation")).split(",")
						for (var k=0; k<relation.length; k++){ 
							if (aux.indexOf(relation[k]) != -1){
								if ( i == 3)
									$(this).attr("color","#112233")
								if ( i == 2)
									$(this).attr("color","#223344")
								if ( i == 1)
									$(this).attr("color","#334455")
								if ( i == 0)
									$(this).attr("color","#445566")
							}
						}
					}	 
				}
			})
		}	
		
		function hide(){
			$("line").css("visibility","hidden")
			$("line[leaf=false]").css("visibility","visible")
			$("text[class='node leaf']").css("visibility","hidden")
			$("path[class='node leaf']").css("visibility","hidden")
			aux_q = ""
			$("input[class='opt_q']").each(function(i,val){
				if ( val.checked == true){
					q_aux = $("#"+val.id).attr("val")
					aux_q = aux_q + q_aux + ","
				}
			})
			aux_q = aux_q + ","
			aux_q = aux_q.replace(",,","")
			aux_q = aux_q.split(",")
			
			
			
			$("input[class='opt']").each(function(i,val){
				
				if ( val.checked == true){
					q_aux = $("#"+val.id).attr("val")
					edge = q_aux.split(",")
					$("line").each(function(){
						if ($(this).attr("relation") != undefined){
							relation = ($(this).attr("relation")).split(",")
							r_aux = $(this).attr("relation")
							source = ""
							target = ""
							if ($(this).attr("source") != undefined)
								source = ($(this).attr("source")).toLowerCase()
							if ($(this).attr("target") != undefined)
								target= ($(this).attr("target")).toLowerCase()
							
							for (var k=0; k<relation.length; k++){
								if (edge.indexOf(relation[k]) != -1 && aux_q.indexOf(relation[k]) != -1){
									$('line[relation="'+r_aux+'"]').css("visibility","visible")
									$('text[name="'+source+'"]').css("visibility","visible")
									$('text[name="'+target+'"]').css("visibility","visible")
									$('path[name="'+source+'"]').css("visibility","visible")
									$('path[name="'+target+'"]').css("visibility","visible")
									
								}
							}
						}
					})
				}
			})
		}	
		

		function nodecenter(searchRegEx){
			searchRegEx = searchRegEx.toLowerCase()
			x = ($("#vis").width())/2;
			y = ($("#vis").height())/2;
			cx = (x*0.7 - $("g[name='"+searchRegEx+"']").attr("cx"));
			cy = (y*0.7 - $("g[name='"+searchRegEx+"']").attr("cy"));
			lastcolor =  $("g[name='"+searchRegEx+"'] path").css("fill")
			$("g[name='"+searchRegEx+"'] path").css("fill","red")
			$("g[name='"+searchRegEx+"'] text").css("fill","blue")
			if (lastnode != ""){
				$("g[name='"+lastnode+"'] path").css("fill",lastcolor)
				$("g[name='"+lastnode+"'] text").css("fill","black")
			}
			lastnode = searchRegEx;
			console.log(cx)
			if (isNaN(cx)){
				resetNetwork()
			}else{
				zoom.translate([cx,cy]);
				zoom.scale(1.2);
				vis.attr("transform","translate("+[cx,cy]+") scale("+zoom.scale()+")");
			}
		}

		
		$(document).ready(function(){
			iconflag = 1;
			
			$("#help").hide()
			
			$("#helpicon").click(function(){
				if (iconflag == 1){
					$("#help").show()
					$("#help").height($("#vis").height())
					$("#help").width("300px")
					$("#helpicon").css("left","310px")
					$(".scrollbar").css("max-height",$("#vis").height()-20)
					iconflag = 0;
				}else{
					$("#help").hide()
					$("#helpicon").css("left","0")
					iconflag = 1;
				}
				
			})
			
			$(window).resize(function(){
				//~ $("#vis").css("height",$(window).height()-360)
				$("svg").css("height",$(window).height()-360)
				$("#help").height($("#vis").height())
				$(".scrollbar").css("max-height",$("#vis").height()-20)
				}
			
			)
			$(".select").hide()
			$("#year").hide()
			$("#quartile").hide()
			
			$('#node_search').bind("DOMSubtreeModified",function(){
				
				if ($( "#node_search .token-input-token p" ).text() != "" && $( "#node_search .token-input-token p" ).text() != lastsearch){
					//~ console.log($(".token-input-list").siblings("#search-network").val())
					id = $( "#node_search .token-input-token p" ).text()
					lastsearch = $( "#node_search .token-input-token p" ).text()
					console.log(id)
					expand[groups[id]] = true;
					console.log(expand)
					init();
					uniqueItems();
					nodecenter(lastsearch)
					type = $("g[name='"+lastsearch+"'] path").attr("type")
					window.location.href='#'+(id);
				}
			});
					
			$('#filter p').click(function(){
				if (flag==0){
					$("#year").show()
					$("#quartile").show();
					$(".select").show()
					$("#filter p").text("▲ FILTROS ▲")
					flag = 1
				}else{
					$(".select").hide()
					$("#year").hide()
					$("#quartile").hide();
					$("#filter p").text("▼ FILTROS ▼")
					flag = 0
				}
			});
			
			$('#filter p').click();
			
			$('#tosvg a').click(function(){
				//~ $('#tosvg a').attr("download",$("#key").text()+".svg")
							//~ .attr("href",getSVG());
			});
			
			$("#hull").switchButton({
										on_label: "<img width='20px' src='../img/visualizacion/icons/nolayer.png' />",
										off_label: "<img width='20px' src='../img/visualizacion/icons/layer.png' />"
									});
			$("#deploy").switchButton({
									on_label: "<img width='20px' src='../img/visualizacion/icons/person.png' />",
									off_label: "<img width='20px' src='../img/visualizacion/icons/institution.png' />"
								});
									
			$("#hull").on("change",function(){
				if ($("#hull").prop("checked") == true){
					$("path[class=hull]").hide()
				}else{
					$("path[class=hull]").show()
				}
			});
			$("#deploy").on("change",function(){
				if ($("#deploy").prop("checked") == true){
					$("g.nodo[name='grouped']").each(function(d){
						expand[$(this).attr("group")] = true
					})
					hide()
				}else{
					for(var i in expand){
						expand[i] = false
					}
				}
				expand[1000000] = false;
				init();
				hide()
				
			})
			
			$.ajax({
				url : "../webservice/year",
				success : function(ret){
					d = ret.split(";")
					$('#year').append('<div><b>Año</b></div>')
					for (var i=0; i<d.length; i++) {
						q = d[i].split(":")
						if ( q != ""){
							
							if (q[0] == "2014")
								$('#year').append('<input class="opt" id="y'+q[0]+'" val='+q[1]+' type="checkbox" checked /> ' + q[0] + ' ');
							else
								$('#year').append('<input class="opt" id="y'+q[0]+'" val='+q[1]+' type="checkbox" /> ' + q[0] + ' ');
						}
					}

				}
			})
			$.ajax({
				url : "../webservice/quartile",
				success : function(ret){
					d = ret.split(";")
					$('#quartile').append('<div><b>Cuartil</b></div>')
					for (var i=0; i<d.length; i++) {
						q = d[i].split(":")
						if ( q != "")
							$('#quartile').append('<input class="opt_q" id="'+q[0]+'" type="checkbox" val="'+q[1]+'" checked /> ' + q[0] + ' ');
					}
					hide()
				}
			})
			
			$('#year').click(function(){
				hide()
			})
			$('#quartile').click(function(){
				hide()
			})
			
			$("#select").click(function () {
				$('input[type=checkbox]').prop('checked',"true");
				hide()
			});
			$("#unselect").click(function () {
				$('input[type=checkbox]').prop('checked',"");
				hide()
			});
					
			
			groups = {}
			groups = $.getJSON("../data/data.json",function(d){
				for(i=0;i<d.nodes.length;i++){
					groups[d.nodes[i].short_name] = d.nodes[i].group
				}
				$("#node_search").tokenInput(d.nodes, {
					hintText: "Ingrese su búsqueda",
					noResultsText: "No hay resultados",
					searchingText: "Buscando...",
					resultsLimit: 10,
					minChars: 1,
					propertyToSearch: "short_name",
					tokenLimit: 1,
					tokenValue: "id",
					preventDuplicates: true,
					onAdd: function(){
						id = $( ".token-input-token p" ).text()
						lastsearch = $( ".token-input-token p" ).text()
						expand[groups[id]] = true;
						init();
						uniqueItems();
						nodecenter(lastsearch)
						type = $("g[name='"+lastsearch+"'] path").attr("type")
						
		
					},
					onDelete: function(){
						
						
					}
				});
				$('input[type=text]').attr('placeholder','Buscar investigador en la red');
				$("#token-input-node_search").css("min-width","300px")
				return groups
			});
			
			
		});
