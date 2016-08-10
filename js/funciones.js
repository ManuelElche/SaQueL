$(document).ready(function() { 
        editor.insert("SELECT * FROM ");
        
        $(document).keydown(function(event){                     
            if (event.which==119) {historico();}   // F8
            if (event.which==121) {estructurar();} // F10 
            if (event.which==118) {editor.setValue();} // F7 
        });
        
        $("#editor").keydown(function(event){                
            if (event.which==120) {lanzar();}      // F9                                               
        })
        
        $("#idagencia").keydown(function(event){                
            if (event.which==13 || event.which==32) { // 13=>tecla intro, 32=>tecla espacio
            use("inmo"+$("#idagencia").val());
            } 
        });         
        //alert("El código de la tecla " + String.fromCharCode(event.which) + " es: " + event.which); // para ver el c�digo
               
                                  
$("#campo").keyup(function(event){
        var listaCampos="";
        var textbusca=$('#campo').val().trim();
        
        if (textbusca=="") {$("#buscacampos").css("display","none");return false;}
     

  for(nombreCampo in camposTabla) {    
  var campo="."+nombreCampo;
    
    if (campo.toLowerCase().indexOf(textbusca.toLowerCase())>0) {
        var listaCampos=listaCampos+' - <a class="listcampo">'+nombreCampo+'</a> [<em class="peq">'+camposTabla[nombreCampo]+'</em>]<br />';
    }
  } 

if (listaCampos=="") {listaCampos='<span class="peq">No hay campos con &quot;<b>'+textbusca+'</b>&quot;</span>';}  
$("#buscacampos").html(listaCampos);
$("#buscacampos").css("display","inline-block");
$(".listcampo").mousedown(function() {editor.insert($(this).html());});   
        }); 
                       
                      
                      
    
                       
                                  
function estructurar() {
    
    var consulta = editor.getValue(); 
    consulta=consulta.replace(/select /gi, 'SELECT\r\n\t');                                                              
    consulta=consulta.replace(/ from/gi, '\r\n\tFROM');                                                              
    consulta=consulta.replace(/ inner/gi, '\r\n\tINNER');                                                              
    consulta=consulta.replace(/ left/gi, '\r\n\tLEFT');                                                              
    consulta=consulta.replace(/ right/gi, '\r\n\tRIGHT');                                                              
    consulta=consulta.replace(/ where/gi, '\r\n\tWHERE');                                                              
    consulta=consulta.replace(/ set/gi, '\r\n\t\tSET');                                                              
    consulta=consulta.replace(/ on/gi, '\r\n\t\tON');
    consulta=consulta.replace(/ group/gi, '\r\n\tGROUP');
    consulta=consulta.replace(/ order/gi, '\r\n\tORDER');
    consulta=consulta.replace(/ limit/gi, '\r\n\tLIMIT');
    consulta=consulta.replace(/ offset/gi, '\r\n\tOFFSET');      
    consulta=consulta.replace(/, /gi, ',\r\n\t');      
    editor.setValue(consulta); 
}  

function lanzar() {
    var consulta = editor.getValue();
    var consulta = consulta.trim();  
    var consulta=consulta.replace(/\s+/g, ' ');  
    
    compruebaUse = consulta.split(" ", 2); ////  Comprueba si es USE y cambia el <select> de la base de datos
    if (compruebaUse[0]=="use") {use(compruebaUse[1]);exit();}
     
    if ($('#sinlimit').is(':checked')) {var sinlimit=1;} else {var sinlimit=0;}
        
        var parametros = {
                "accion" : "consulta",
                "sql" : consulta,
                "sinlimit" : sinlimit,
                "bd" : $('#bds').val(),
                "tabla" : $('#tablas').val(),
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#resultado").html(response);
                }
        });
}

function use(bdatos) {
var bdatos=bdatos.trim();
window.esta=0;
$("#bds>option").map(function() {
var optionBD=$(this).val(); 
    if (optionBD==bdatos)
    window.esta++;
}); 

if (window.esta==0)
    {alert("No existe la base de datos "+bdatos+"!"); exit();}

        var parametros = {
                "accion" : "use",      
                "bd" : bdatos
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#resultado").html(response);
                }
        });         
}

function historico() {  
        var parametros = {
                "accion" : "historico"
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#resultado").html(response);
                }
        });
}

$("#botonLanzar").click(lanzar);
$("#botonEstructurar").click(estructurar);
$("#botonHistorico").click(historico);                             
$("#bds").change(function() {use($('#bds').val());exit();});  
$("#botonBorrar").click(function() {editor.setValue("");});
$("#campo").blur(function() {$("#buscacampos").css("display","none");});   

$("#botonFavoritos").click(function() { 
    if ($('#capaFavoritos').css('display')=="block")
    {$("#capaFavoritos").css("display","none");}
    else
    {favoritos();}
});                                                                                     
                                                                         
$(window).click(function() {$("#capaFavoritos").css("display","none");});   ////oculta capas innecesarias
$(window).click(function() {$("#textoCompleto").css("display","none");});   ////oculta capas innecesarias
$('#capaFavoritos').click(function(event){event.stopPropagation();});       ////si se pulsa aqui no se ocultar� 
$('#textoCompleto').click(function(event){event.stopPropagation();});       ////si se pulsa aqui no se ocultar� 


  

$("#campo").focus(function() { //// crea la variable campos con todo
 if ($('#tablas').val()=="")
    {alert ("Primero debes seleccionar una tabla");}
    else
    {   var parametros = {
                "accion" : "campos",  
                "bd" : $('#bds').val(),
                "tabla" : $('#tablas').val(),
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#buscacampos").html(response);
                }
        });
        
    }
}); 


      
});        /// fin document ready global


function escribeFav(querys) {//// escribe la sql seleccionada en el editor
    var querySpan = $(querys).children('span').text();
    editor.setValue(querySpan);   
    $("#capaFavoritos").css("display","block");         
}; 

function verInfo(objeto) {//// escribe la sql seleccionada en el editor
    var textoCompleto = $(objeto).children('span').text(); 
    $("#textoCompleto").text(textoCompleto);         
    $("#textoCompleto").css("display","block");         
};                                                                                         

function extrae(capa){ //// saca las tablas de la base de datos seleccionada    
        var parametros = {
                "accion" : "tablas",
                "bd" : $('#bds').val()
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#"+capa).html(response);
                }
        });
}  


function favoritos(accion){    
        var parametros = {
                "accion" : "favoritos"
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#capaFavoritos").html(' + A&ntilde;adir sentencia actual a Favoritos<br />'+response);
                        $("#capaFavoritos").css("display","block");
                }
        });
}                                   
