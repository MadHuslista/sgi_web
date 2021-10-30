$("#exportar_tabla").on('click', function(){
		$("#tabla").table2excel({
			exclude: ".noExl",
			name: "Worksheet Name",
			filename: "Docentes con grado magister en programas phd"
		});
});


$("#exportar_grafico").on('click', function(){
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

    canvas2.toBlob(function(blob) {
        saveAs(blob, "grafico.png");
    }, "image/png");

});
