$(document).ready(function(){
          /*$("select").change(function(){

			  $(".token-input-list").remove();
			  $("#author").tokenInput('../api/person?id='+$("select").val(), {
				resultsLimit:10,
				preventDuplicates: true,
				noResultsText: "No hay resultados para la búsqueda.",
			  });
			  
			  })*/
			  
		  $("#inicio").datepicker({
          format: "yyyy",
          viewMode: "years", 
          minViewMode: "years",
          orientation: "top right",
          startDate:"2000",
          endDate:"2015",
          autoclose: true
			});
		  $("#termino").datepicker({
			  format: "yyyy",
			  viewMode: "years", 
			  minViewMode: "years",
			  orientation: "top right",
			  startDate:"2000",
			  endDate:"2015",
			  autoclose: true
		  });
		  $("#author").tokenInput("../api/personas", {
			 resultsLimit: 10,
			 searchingText: "Buscando...",
			 theme: "facebook",
			 preventDuplicates: true
			});
		  
		  $("#generar").click(function(){
			  id = $("#author").tokenInput("get");
			  prog = $("#programa").val();
			  inicio = $("#inicio").val();
			  termino = $("#termino").val();
			  // console.log(prog);
			  $.ajax({
				url : "../api/tabla_resumen",
				method:"GET",
				data:{"ids":id, "inicio":inicio, "termino":termino},
				dataType: "json",
				success : function(res){
          // console.log(ret.split("'"));
          // console.log(JSON.parse(ret));
          //var res = JSON.parse(ret);
          console.log(res.length);
          var res_html = "";
          cont = 0;
          for(r in res){
			if (cont % 2 == 0)
				res_html += "<tr class='par'>";
			else
				res_html += "<tr>";
            res_html += "<td>" + res[r].name + "</td>";
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
          $("tbody").empty();
					$("tbody").append(res_html);
          $("#tabla, #exportar").css("display", "block");
				}
			})
		  });
		  
		  $("#exportar").click(function(event) {
				tableToExcel('tabla', 'tabla resumen', 'tabla_resumen.xls')
		  }); 
      
       });

			var tableToExcel = (function () {
        var uri = 'data:application/vnd.ms-excel;base64,'
        , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
        , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
        , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
        return function (table, name, filename) {
            if (!table.nodeType) table = document.getElementById(table)
            var ctx = { worksheet: name || 'Worksheet', table: table.innerHTML }

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
