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
        
        $("#idvolcado").keydown(function(event){                
            if (event.which==13 || event.which==32) { // 13=>tecla intro, 32=>tecla espacio
            use("volcado"+$("#idvolcado").val());
            } 
        });          
        //alert("El cÃ³digo de la tecla " + String.fromCharCode(event.which) + " es: " + event.which); // para ver el código
               
                                  
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
$(".listcampo").mousedown(function() {editor.insert('`'+$(this).html()+'`');});   
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
    var consulta=limpia(editor.getValue());
    
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
                beforeSend: function () {
                        $("#resultado").html('<span class="loading"></span>');
                }, 
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

$("#addFav").click(guardaFavorito);
$("#addAD").click(guardaAD);        
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

$("#botonADirectos").click(function() { 
    if ($('#capaAccesosDirectos').css('display')=="block")
    {$("#capaAccesosDirectos").css("display","none");}
    else
    {accesosDirectos();}
});     

$("#botonNew").click(function() {
    var v=parseInt($("#v").val());
    v=v+1;
    window.open("?v="+v);
});      

$("#escribeTexto").click(function() {
    var ebd=$("#bds").val();
    var etabla=$("#tablas").val();
    var query='SELECT * FROM `'+ebd+'`.`'+etabla+'` ';
    editor.setValue(query); 
});        
 
$("#sinlimite span").click(function() {
    if ($('input#sinlimit').is(':checked')) {
    $( "input#sinlimit" ).prop("checked",false);
    }
    else
    {
    $( "input#sinlimit" ).prop("checked",true);
    }

});                                                                             
                                                                         
$(window).click(function() {$("#capaFavoritos").css("display","none");});   ////oculta capas innecesarias
$(window).click(function() {$("#textoCompleto").css("display","none");});   ////oculta capas innecesarias
$('#capaFavoritos').click(function(event){event.stopPropagation();});       ////si se pulsa aqui no se ocultará 
$('#textoCompleto').click(function(event){event.stopPropagation();});       ////si se pulsa aqui no se ocultará 


  

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


function cerrar(capa) {      
    capa.parentElement.style.display='none';
};  

function use(bdatos) {
var bdatos=bdatos.trim();
window.esta=0;
$("#bds>option").map(function() {
var optionBD=$(this).val(); 
    if (optionBD==bdatos)
    window.esta++;
}); 

if (window.esta==0)
    {alert("No existe la base de datos \u00AB"+bdatos+"\u00BB"); exit();}

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

function limpia(query) {  
    var query = query.trim();  
    query=query.replace(/\s+/g, ' ');
    return query; 
}


function guardaFavorito() {  
    var consulta=limpia(editor.getValue());
    var tituloSQL = prompt("Escribe un título para la sentencia", "");     
        tituloSQL=tituloSQL.trim(); 
      
        var parametros = {
                "accion" : "addfavorito",
                "titulo" : tituloSQL,
                "query" : consulta
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#resultado").html(response);
                }
        });      
};  


function borrarFavorito(idfav,tipo) { 
     if (tipo==2) {var mensaje='este acceso directo';} else {var mensaje='esta sentencia de favoritos';}
     
    if (confirm("¿Estás seguro de eliminar "+mensaje+"?")) {  
      
        var parametros = {
                "accion" : "borrarfavorito",
                "idfav" : idfav,
                "tipo" : tipo
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#resultado").html(response);
                }
        }); 
    };         
};  


function escribeFav(querys) {//// escribe la sql seleccionada en el editor
    var querySpan = $(querys).children('span').text();
    editor.setValue(querySpan);   
    $("#capaFavoritos").css("display","block");         
}; 


function guardaAD() {                      
    var titulo = prompt("Escribe el nombre de la base de datos para el acceso directo", "");   
        titulo=limpia(titulo);
            
        var parametros = {
                "accion" : "addadirectos",
                "titulo" : titulo
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#resultado").html(response);
                }
        });      
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
                        $("#capaFavoritos").html('<div class="botonCerrar" onClick="cerrar(this);"></div><span id="addFav" onClick="guardaFavorito();">+ A&ntilde;adir sentencia actual a Favoritos</span><br />'+response);
                        $("#capaFavoritos").css("display","block");
                }
        });
}  


function accesosDirectos(){    
        var parametros = {
                "accion" : "adirectos"
        };
        $.ajax({
                data:  parametros,
                url:   'ejecuta.php',
                type:  'post', 
                success:  function (response) {
                        $("#capaAccesosDirectos").html('<div class="botonCerrar" onClick="cerrar(this);"></div><h4>Accesos Directos:</h4><span id="addAD" onClick="guardaAD();">+ A&ntilde;adir nuevo acceso directo</span><br />'+response);
                        $("#capaAccesosDirectos").css("display","block");
                }
        });
}           