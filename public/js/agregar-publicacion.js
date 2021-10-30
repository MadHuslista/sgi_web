$(document).ready(function(){

$("#guardar").click(function(event){
           event.preventDefault();
           var autor = $('#autor').select2('val');
           var autores = $('#autores').select2('val');
           var journal = $('#journal').select2('val');
           var anio = $('#anio').val();
           var doi = $('#doi').val();
           var titulo = $('#titulo').val();
           var keywords = $('#keywords').val();
           var volumen = $('#volumen').val();
           var otrosautores = $('#otrosautores').val();
           var tipo = $('#atriculo').val();

           $.ajax();
  });
});
