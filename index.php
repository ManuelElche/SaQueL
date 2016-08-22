<?php               
session_start();
$_SESSION["user"]="6";    
$_SESSION["nivelPermisos"]="2";

//////////////////////////////////////////////////////////////////
/////////////    NIVELES DE PERMISOS (RECURSIVOS)   //////////////
/////////////***************************************//////////////
/////////////    0    BLOQUEADO                     //////////////
/////////////    1    SELECT                        //////////////
/////////////    2    INSERT                        //////////////
/////////////    3    UPDATE                        //////////////
/////////////    4    DELETE                        //////////////
/////////////    5    EMPTY, DROP, TRUNCATE...      //////////////
/////////////    6    MYSQLDUMP                     //////////////
//////////////////////////////////////////////////////////////////
    
include_once("funciones.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>             
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SaQueL</title>                                                  
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/estilos.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/funciones.js"></script>
</head>
<body>                   
<h3>Inmovilla SQL</h3>         
<div id="formulario" class="form">       


    <div class="menuIzq">
            <?php echo dameBD();?>   

            
        <div id="capaTablas" class="btn-group-justified" style="width:90%;float:left;">
          <select class="form-control" id="tablas">
            <option value="">...</option>
          </select>                            
        </div>  
        <div style="width:8%;float:left;margin-left:2%;">
           <input type="button" class="btn btn-info escribeTexto" id="escribeTexto" value=">">
        </div>    
                                  
          <input type="text" id="campo" class="form-control" style="margin-bottom:8px;" placeholder="Buscar campos...">
          <div id="buscacampos"></div> 
          <div class="peq">
            <span class="peq">Accesos Directos:</span><br />      
                inmo<input type="text" id="idagencia" maxlength="4"> |               
                <div class="use" onClick="use('inmoweb');">inmoweb</div> |  
                <div class="use" onClick="use('mls');">mls</div> |  
                <div class="use" onClick="use('bdweb');">bdweb</div> |  
                <div class="use" onClick="use('gesquery');">gesquery</div> +    
                <div class="use" onClick="use('inmociudad');">inmociudad</div>   
          </div>
    </div>         

                  
    <div class="menuOpciones">                                                                          
        <input type="button" class="btn btn-sm btn-success" id="botonLanzar" value="Lanzar (F9)">
        <input type="button" class="btn btn-sm btn-primary" id="botonEstructurar" value="Estructurar (F10)">
        <input type="button" class="btn btn-sm btn-info" id="botonHistorico" value="Hist&oacute;rico (F8)"> 
        <input type="button" class="btn btn-sm btn-danger" id="botonBorrar" value="Borrar (F7)">
        <input type="button" class="btn btn-sm btn-warning" id="botonFavoritos" value="Favoritos">  
        <input type="button" class="btn btn-sm btn-default" id="botonNew" value="Nueva ventana">  
        <input type="hidden" id="v" value="<?php if ($_GET['v']=="") {echo "1";} else {echo $_GET['v'];};?>">  
    </div>                                                                                              
        
        <div id="capaFavoritos"></div>
        <div id="textoCompleto"></div>
        <div id="sinlimite"><input type="checkbox" style="vertical-align:text-bottom;" id="sinlimit"> <span>Sin l&iacute;mite</span></div>

    <div id="editor"></div>


</div>

<div id="resultado"></div>
<div id="total">Total registros: 0</div>

<script src="ace/ace.js" type="text/javascript" charset="utf-8"></script>
<script>
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/chrome");
    editor.getSession().setMode("ace/mode/mysql");
</script>
</body>
</html>