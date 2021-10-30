function createSlider() {
    $( "#slider" ).slider({
      value:2012,
      min: 2011,
      max: 2012,
      step: 1,
      slide: function( event, ui ) {
        $( "#date-range" ).text( ui.value);
      }
    });
    $( "#date-range" ).text($( "#slider" ).slider( "value"));
  }
function upper (text) {
	var aux = text.split(" ")
	text = ""
	aux.forEach(function(char){
		char = char.toLowerCase();
		if (char.length > 3 || char == 'q1' || char == 'q2' || char == 'q3' || char == 'q4') text = text+ " " + char.charAt(0).toUpperCase() + char.slice(1);
		else text = text + " " + char
	});
    return text;
}
function sun_load(year){
	var cjson = [];
	$.ajax({
				url : "../api/departamentos_color",
				method:"GET",
				dataType: "json",
				success : function(data){
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
					activation = sequences("../api/sunburst?year=" + year,1,1,cjson);
					  createSlider();
					  $("#ano").html(year);

				}
			});

}
$(".year").click(function(){
    sun_load($(this).attr("id"));
  });
$(document).ready(function(){
	sun_load(2014);

});
