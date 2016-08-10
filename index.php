<?php               
session_start();
$_SESSION["user"]="6";    
$_SESSION["nivelPermisos"]="3";

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
<h3>Sentencias SQL</h3>         
<div id="formulario" class="form">       


    <div class="menuIzq">
            <?php echo dameBD();?>   

        <div id="capaTablas">
          <select class="form-control" id="tablas">
            <option value="">...</option>
          </select>   
        </div>
                                  
          <input type="text" id="campo" class="form-control" placeholder="Buscar campos...">
          <div id="buscacampos"></div><br />
        <input type="checkbox" style="vertical-align:text-bottom;" id="sinlimit"> Sin límite    
    </div>         

                  
    <div class="menuOpciones">                                                                     
        <input type="button" class="btn btn-sm btn-success" id="botonLanzar" value="Lanzar (F9)">
        <input type="button" class="btn btn-sm btn-primary" id="botonEstructurar" value="Estructurar (F10)">
        <input type="button" class="btn btn-sm btn-info" id="botonHistorico" value="Hist&oacute;rico (F8)"> 
        <input type="button" class="btn btn-sm btn-danger" id="botonBorrar" value="Borrar (F7)">
        <input type="button" class="btn btn-sm btn-warning" id="botonFavoritos" value="Favoritos">  
    </div>
        <div id="capaFavoritos"></div>
        <div id="textoCompleto"></div>

    <div id="editor"></div>


</div>

<div id="resultado"></div>
<div id="total">Total registros: 0</div>

<script src="ace/ace.js" type="text/javascript" charset="utf-8"></script>
<script>
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/chrome");
    editor.getSession().setMode("ace/mode/sql");
</script>
</body>
</html>