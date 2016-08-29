<?php
session_start();
include_once("cxn.php");      
include_once("funciones.php");   

ini_set("display_errors", 1);   
$limitePorDefecto=10;
  
switch ($_POST['accion']) {             
    
    case "tablas" :
    
     $lista_tablas = mysqli_query($link,'SHOW TABLES FROM '.$_POST["bd"].';'); 
     $tablas.='<select id="tablas" class="form-control">';
    while ($tabla=mysqli_fetch_array($lista_tablas))
        {$tablas.= '    <option value="'.$tabla[0].'">'.$tabla[0].'</option>';}
    $tablas.='</select>';    
    echo $tablas;  
    mysqli_free_result($lista_tablas);
    break;

    
    
    
    
    
    
    
    
    
    
    case "campos" :
     mysqli_select_db($link,$_POST["bd"]);
     $listado = mysqli_query($link,'SHOW COLUMNS FROM '.$_POST["tabla"].';'); 

    while ($lista=mysqli_fetch_array($listado))
        {$campos.= '"'.$lista[0].'":"'.$lista[1].'",';}
                   
    echo '<script>var camposTabla = {'.$campos.'};</script>';  
    mysqli_free_result($link);
    break;
    
    
    
    
    case "favoritos" :
     mysqli_select_db($link,"gesquery");
     $listado = mysqli_query($link,'SELECT titulo, query, id FROM gesquery.favoritos WHERE user="'.$_SESSION["user"].'" AND tipo="1" ORDER BY titulo;'); 

    while ($lista=mysqli_fetch_array($listado))
        {$campos.= '<div id="fav'.$lista[2].'"><span onClick="escribeFav(this);" class="sentenciaAnadir">&nbsp;&nbsp;&raquo; '.utf8_encode($lista[0]).'<span class="query">'.utf8_encode($lista[1]).'</span></span> <div class="delete" onClick="borrarFavorito('.$lista[2].');" title="Eliminar"></div></div>';}
                   
    echo $campos;  
    mysqli_free_result($listado);
    break; 
    
    
    
    case "addfavorito" :
     if ($_POST["titulo"]=="" || $_POST["query"]=="" || $_SESSION["user"]=="") die();
     
     if(mysqli_query($link,'INSERT INTO gesquery.favoritos (titulo,query,user) VALUES ("'.utf8_decode($_POST["titulo"]).'","'.addslashes($_POST["query"]).'","'.$_SESSION["user"].'");'))
         {
         $id=mysqli_insert_id($link);         
    echo '
    <script>                                                        
    $("#capaFavoritos").append("<div id=\"fav'.$id.'\"><span onclick=\"escribeFav(this);\" class=\"sentenciaAnadir\">&nbsp;&nbsp;&nbsp;&raquo; '.addslashes($_POST["titulo"]).'<span class=\"query\">'.addslashes($_POST["query"]).'</span> <div class=\"delete\" onClick=\"borrarFavorito('.$id.',2)\" title=\"Eliminar\"></div></div>");
    alert ("Sentencia guardada en tus favoritos!"); 
    </script>'; 
    }
    else {die ("ERROR AL GUARDAR");}    
     
    break;
    
    
    
    case "borrarfavorito" :  ////esta se usa tanto para borrar favorito como para borrar accesos directos
     if ($_POST["idfav"]=="") die("ERROR 1");
     
     if(mysqli_query($link,'DELETE FROM gesquery.favoritos WHERE id='.$_POST["idfav"].' LIMIT 1'))
         {
         $id=$_POST["idfav"]; 

         if ($_POST["tipo"]=="2") {
         $ocultaAcceso='   
         $("#favmini'.$id.'").html("");
         $("#favmini'.$id.'").css("display","none");
         
         ';}
    
    echo '
    <script>                                                                            
    $("#fav'.$id.'").html("");
    $("#fav'.$id.'").css("display","none");
    '.$ocultaAcceso.'
    </script>';  
    

    }
    else {die ("ERROR 2");}    
     
    break;
    
    
    
    
    case "adirectos" :
     mysqli_select_db($link,"gesquery");
     $listado = mysqli_query($link,'SELECT titulo, query, id FROM gesquery.favoritos WHERE user="'.$_SESSION["user"].'" and tipo="2" ORDER BY titulo;'); 

    while ($lista=mysqli_fetch_array($listado))
        {$campos.= '<div id="fav'.$lista[2].'"><span class="sentenciaAnadir">&nbsp;&nbsp;&nbsp;&raquo; '.utf8_encode($lista[0]).'</span> <div class="delete" onClick="borrarFavorito('.$lista[2].',2);" title="Eliminar"></div></div>';}
                   
    echo $campos;  
    mysqli_free_result($listado);
    break;                                                                   
    
    
    
    case "addadirectos" :
     if ($_POST["titulo"]=="" || $_SESSION["user"]=="") die();
     
     if(mysqli_query($link,'INSERT INTO gesquery.favoritos (titulo,user,tipo) VALUES ("'.utf8_decode($_POST["titulo"]).'","'.$_SESSION["user"].'","2");'))
         {
         $id=mysqli_insert_id($link);         
    echo '
    <script>                                                        
    $("#capaAccesosDirectos").append("<div id=\"fav'.$id.'\"><span class=\"sentenciaAnadir\">&nbsp;&nbsp;&nbsp;&raquo; '.addslashes($_POST["titulo"]).'<span class=\"query\">'.addslashes($_POST["query"]).'</span> <div class=\"delete\" onClick=\"borrarFavorito('.$id.')\" title=\"Eliminar\"></div></div>");
    $("#accesosDirectos").append("<div class=\"use\" id=\"favmini'.$id.'\" onClick=\"use(\''.addslashes($_POST["titulo"]).'\');\">'.addslashes($_POST["titulo"]).' | </div>");
    alert ("Acceso directo a\u00F1adido!"); 
    </script>'; 
    }   
    
    else {die ("ERROR AL GUARDAR");}    
     
    break;
    
    
    
    case "use" :
     $_SESSION["bd"]=$_POST["bd"];
    echo '
    <script>                   
        $("#bds").val("'.$_SESSION["bd"].'"); 
        extrae("capaTablas"); 
    </script>'; 
     
    break;
    
    
    
    
    
    
    
    
    case "consulta" :
    $sentencia=$_POST["sql"]; 
    
    $puntosYcoma=substr_count($sentencia,";");
        
if ($puntosYcoma>1) 
{   $sentencias=explode(";",$_POST["sql"]);

foreach ($sentencias as $sentencia)
{ ///este foreach analiza que el user tenga permisos para lanzar todas, y que no haya ningún select, ni drops, etc ya que estas sentencias no tendrían sentido o son peligrosas
    $sentencia=trim($sentencia); 
    if ($sentencia=="") continue;
    if (tipoSQL($sentencia)==1) die ('<div class="alert alert-danger">No tiene sentido que lances varias consultas con un SELECT... elimina dichas consultas y vuelve a lanzarlas.</div>');
    if (tipoSQL($sentencia)>4) die ('<div class="alert alert-danger">Este tipo de consultas tan delicadas no se permiten lanzar de manera m&uacute;ltiple, debes lanzarlas una a una.</div>');
    
    if (tipoSQL($sentencia)>$_SESSION["nivelPermisos"])
    { 
            ejecutaqry($link,$sentencia,"-2");
            die ($noPermisosMultiples); 
    }
    else
    {  
        if (bdSoloLectura($_POST["bd"],$sentencia,tipoSQL($sentencia))) {
            ejecutaqry($link,$sentencia,"-3");
            die (mensajePermisos(tipoSQL($sentencia)));
        }                                                                             
    } 
    
    
}

foreach ($sentencias as $sentencia)  ///este lanza las query,
{   $sentencia=trim($sentencia); 
    if ($sentencia=="") continue; // por si vienen varios ; seguidos 
              
    if (!preg_match('/limit [0-9]/i',$sentencia) && $_POST["sinlimit"]!="1") 
    {
     if ($_POST["limit"]=="") {$limit="LIMIT ".$limitePorDefecto;} else {$limit="LIMIT ".$_POST["limit"];}
    }
 
    echo ' <table id="resultadosql"><tr>';                                
    
    mysqli_select_db($link,$_POST["bd"]);

    //echo "<p>SENTENCIA (tipo ".tipoSQL($sentencia)."): ".$sentencia."</p>";
    $qry=mysqli_query($link,$sentencia);
                     
  if (mysqli_error($link)) {$errores .= ' - '.mysqli_error($link).'<br />';$numresultados="-1";}
        $afectadas=mysqli_affected_rows($link);   
        
        if ($afectadas>-1 && tipoSQL($sentencia)=="2") {$numresultados=$afectadas;$afectadas2=$afectadas2+$afectadas;$msj2 = '<div class="alert alert-success">'.$afectadas2." registro".plural($afectadas2)." a&ntilde;adido".plural($afectadas2).".</div>";}
        if ($afectadas>-1 && tipoSQL($sentencia)=="3") {$numresultados=$afectadas;$afectadas3=$afectadas3+$afectadas;$msj3 = '<div class="alert alert-success">'.$afectadas3." registro".plural($afectadas3)." modificado".plural($afectadas3).".</div>";}
        if ($afectadas>-1 && tipoSQL($sentencia)=="4") {$numresultados=$afectadas;$afectadas4=$afectadas4+$afectadas;$msj4 = '<div class="alert alert-success">'.$afectadas4." registro".plural($afectadas4)." eliminado".plural($afectadas4).". </div>";}
          
        ejecutaqry($link,$sentencia,mysqli_affected_rows($link));
        
       
} /// foreach     
if ($errores!="") echo '<div class="alert alert-danger"><b>SENTENCIAS CON ERRORES:</b><br />'.$errores."</div>";         
}      
else
{
    
    if (!preg_match('/limit [0-9]/i',$_POST["sql"]) && $_POST["sinlimit"]!="1") 
    {
     if ($_POST["limit"]=="") {$limit="LIMIT ".$limitePorDefecto;} else {$limit="LIMIT ".$_POST["limit"];}
    }

    if (tipoSQL($sentencia)>$_SESSION["nivelPermisos"])
    {
            ejecutaqry($link,$sentencia,"-2");
            die ($noPermisos); 
    }
    else
    {
        if (bdSoloLectura($_POST["bd"],$sentencia,tipoSQL($sentencia))) {
            ejecutaqry($link,$sentencia,"-3");
            die (mensajePermisos(tipoSQL($sentencia)));
        }                                                                             
    }
    echo ' <table id="resultadosql">
        <thead><tr>';                                
    
    mysqli_select_db($link,$_POST["bd"]);
    if (tipoSQL($sentencia)=="1") $sentencia=$_POST["sql"]." ".$limit;

    echo "<p>SENTENCIA (tipo ".tipoSQL($sentencia)."): ".$sentencia."</p>";
    $qry=mysqli_query($link,$sentencia);
    
    if (!mysqli_error($link) && tipoSQL($sentencia)==1) {  
        $numresultados=mysqli_num_rows($qry);
        $columnas=mysqli_fetch_fields($qry);
        $ncolumnas=count($columnas); 
            
                                      
          
    foreach ($columnas as $valor) {
        if (substr_count($sentencia,'.')>1) {$mostrarTabla="<br /><span>(".utf8_encode($valor->table).")</span>";}
        echo "<th>".utf8_encode($valor->name).$mostrarTabla."</th>";
    }   //// genera la cabecera 
                            
    echo "  </tr>
        </thead>
        <tbody>";
    
    
   while ($tabla=mysqli_fetch_array($qry)) //// genera la tabla con los resultados
        {$nfila++;  
        $limiteTexto=30; // limite de caracteres por celda                       
            if ($nfila%2==0) {$parinpar="par";} else {$parinpar="inpar";}
            $tablas.= '<tr class="'.$parinpar.'">';
            for ($a=0;$a<$ncolumnas;$a++)
                {
                    if ($tabla[$a] === NULL) {$tablas.= '<td class="null">(NULL)</td>';continue;}
                    $todo=$tabla[$a]=utf8_encode($tabla[$a]);   
                    if (strlen($tabla[$a])>$limiteTexto) {$tabla[$a]=cortaTexto($tabla[$a],$limiteTexto);}
                    
                    
                    $textfinal=htmlspecialchars($tabla[$a]);                           
                    $todo=htmlspecialchars($todo);          
                    if (strlen($todo)>strlen($tabla[$a])) //// si el conenido es muy largo, lo mostramos en ventana modal
                        {$tablas.= '<td ondblclick="verInfo(this);">'.$textfinal.'<span>'.$todo.'</span></td}>';}
                        else
                        {$tablas.= '<td>'.$todo.'</td>';}
                    
                }
            $tablas.= '</tr>';          
        }  ///// fin del while montando la tabla con los resultados   
    }   //// fin si ha dado error mysli_query de $qry
                         
        echo $tablas;                
        if (mysqli_error($link)) {echo '<div class="alert alert-danger">'.mysqli_error($link)."</div>";$numresultados="-1";} elseif (tipoSQL($sentencia)==1) {mysqli_free_result($qry);}
        $afectadas=mysqli_affected_rows($link);
        
        if ($afectadas>-1 && tipoSQL($sentencia)!="1") {$numresultados=$afectadas;$msj = '<div class="alert alert-success">'.$afectadas." registro".plural($afectadas)." afectado".plural($afectadas).".</div>";}
        if ($afectadas>-1 && tipoSQL($sentencia)=="2") {$numresultados=$afectadas;$msj = '<div class="alert alert-success">'.$afectadas." registro".plural($afectadas)." a&ntilde;adido".plural($afectadas).".</div>";}
        if ($afectadas>-1 && tipoSQL($sentencia)=="3") {$numresultados=$afectadas;$msj = '<div class="alert alert-success">'.$afectadas." registro".plural($afectadas)." modificado".plural($afectadas).".</div>";}
        if ($afectadas>-1 && tipoSQL($sentencia)=="4") {$numresultados=$afectadas;$msj = '<div class="alert alert-success">'.$afectadas." registro".plural($afectadas)." eliminado".plural($afectadas).". </div>";}
        
        ejecutaqry($link,$sentencia,$numresultados);        
}         
  
        if ($numresultados<0) {$numresultados="0";}            
    echo $msj.$msj2.$msj3.$msj4.'
        </tbody>
    </table>
    
    <script>
        $("#total").html("Total registros: '.$numresultados.'");
    </script>';   
                                                                
    break;
    
    
    
    
    
    
             
    
    case "historico" :
    
     $qry = mysqli_query($link,'SELECT * FROM gesquery.sentencias WHERE user="'.$_SESSION["user"].'" ORDER BY id DESC;'); 
     $tablafinal.='<table id="historico">
        <tr>
            <th>Usuario</th>
            <th>Sentencia SQL</th>
            <th>Fecha y Hora</th>
            <th>Resultado</th>         
        </tr>
        ';
    while ($row=mysqli_fetch_assoc($qry))
        {
         switch ($row["tipo"]) {
             case 1 :  $estilo=' class="alert-info" ';    break;
             case 2 : case 3 : $estilo=' class="alert-success" '; break;
             case 4 : $estilo=' class="alert-warning" '; break;
             default : $estilo=""; break;
         }            
                         
         $resultado=$row["results"];
         if ($row["tipo"]=="1") {$resultado=$row["results"]." visto".plural($row["results"]);};
         if ($row["tipo"]=="2") {$resultado=$row["results"]." insertado".plural($row["results"]);};
         if ($row["tipo"]=="3") {$resultado=$row["results"]." modificado".plural($row["results"]);};
         if ($row["tipo"]=="4") {$resultado=$row["results"]." eliminado".plural($row["results"]);};
         if ($row["results"]=="-1") {$estilo=' class="alert-danger" ';$resultado="- Error en SQL -";};        
         if ($row["results"]=="-2") {$estilo=' class="alert-danger" ';$resultado="- Error Permisos Usuario -";};
         if ($row["results"]=="-3") {$estilo=' class="alert-danger" ';$resultado="- Error de Permisos de BD -";};
          
            $tablafinal.= '
        <tr '.$estilo.'>
            <td style="width:10%;">'.$row["user"].'</td>
            <td style="font-size:.7em;">'.$row["query"].'</td>
            <td style="width:13%;">'.$row["fecha"].'</td>
            <td style="width:16%;">'.$resultado.'</td>
        </tr>
        ';}
    $tablafinal.='</table>';    
    echo $tablafinal;  
    mysqli_free_result($link);
    break;
    
    
    
    
    
    
    
    
    default : 
    echo "Sin instrucciones...";
    break;
    
    
}
   

                              
?>