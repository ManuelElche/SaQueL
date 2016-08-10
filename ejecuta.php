<?php
session_start();
include_once("cxn.php");      
include_once("funciones.php");

ini_set("display_errors", 0);   

  
switch ($_POST['accion']) {             
    
    case "tablas" :
    
     $lista_tablas = mysqli_query($link,'SHOW TABLES FROM '.$_POST["bd"].';'); 
     $tablas.='<select id="tablas" class="form-control">';
    while ($tabla=mysqli_fetch_array($lista_tablas))
        {$tablas.= '    <option value="'.$tabla[0].'">'.$tabla[0].'</option>';}
    $tablas.='</select>';    
    echo $tablas;  
    mysqli_free_result($link);
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
     $listado = mysqli_query($link,'SELECT titulo, query FROM gesquery.favoritos WHERE user="6" ORDER BY titulo;'); 

    while ($lista=mysqli_fetch_array($listado))
        {$campos.= '<span onClick="escribeFav(this);" class="sentenciaFav">&raquo; '.utf8_encode($lista[0]).'<span class="query">'.utf8_encode($lista[1]).'</span></span><br />';}
                   
    echo $campos;  
    mysqli_free_result($link);
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
                  
    if (!preg_match('/limit [0-9]/i',$_POST["sql"]) && $_POST["sinlimit"]!="1") 
    {
     if ($_POST["limit"]=="") {$limit="LIMIT 10";} else {$limit="LIMIT ".$_POST["limit"];}
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
    echo ' <table id="resultadosql"><tr>';                                
    
    mysqli_select_db($link,$_POST["bd"]);
    if (tipoSQL($sentencia)=="1") $sentencia=$_POST["sql"]." ".$limit;

    echo "<p>SENTENCIA (tipo ".tipoSQL($sentencia)."): ".$sentencia."</p>";
    $qry=mysqli_query($link,$sentencia);
    
    $numresultados=mysqli_num_rows($qry);
    $columnas=mysqli_fetch_fields($qry);
    $ncolumnas=count($columnas);
                            
                            
                            
                            
                                      
    echo "  <tr>";         
    foreach ($columnas as $valor) {
        if (substr_count($sentencia,'.')>1) {$mostrarTabla="<br /><span>(".$valor->table.")</span>";}
        echo "<th>".$valor->name.$mostrarTabla."</th>";
    }   //// genera la cabecera 
        
    echo "  </tr>";
    
    
   while ($tabla=mysqli_fetch_array($qry)) //// genera la tabla con los resultados
        {$nfila++;  
        $limiteTexto=30; // limite de caracteres por celda  
            if ($nfila%2==0) {$parinpar="par";} else {$parinpar="inpar";}
            $tablas.= '<tr class="'.$parinpar.'">';
            for ($a=0;$a<$ncolumnas;$a++)
                {  $todo=$tabla[$a]=utf8_encode($tabla[$a]);   
                    if (strlen($tabla[$a])>$limiteTexto) {$tabla[$a]=cortaTexto($tabla[$a],$limiteTexto);}
                    
                    $textfinal=htmlspecialchars($tabla[$a]);
                    $todo=htmlspecialchars($todo);          
                    if (strlen($todo)>strlen($tabla[$a])) //// si el conenido es muy largo, lo mostramos en ventana modal
                        {$tablas.= '<td ondblclick="verInfo(this);">'.$textfinal.'<span>'.$todo.'</span></td}>';}
                        else
                        {$tablas.= '<td>'.$todo.'</td>';}
                    
                }
            $tablas.= '</tr>';
        }     
        echo $tablas;                       
        if (mysqli_error($link)) {echo '<div class="alert alert-danger">'.mysqli_error($link)."</div>";$numresultados="-1";}
        $afectadas=mysqli_affected_rows($link);
        if ($afectadas>-1 && tipoSQL($sentencia)!="1") {$numresultados=$afectadas;$msj = '<div class="alert alert-success">'.$afectadas." registro".plural($afectadas)." afectado".plural($afectadas).".</div>";}
        if ($afectadas>-1 && tipoSQL($sentencia)=="2") {$numresultados=$afectadas;$msj = '<div class="alert alert-success">'.$afectadas." registro".plural($afectadas)." modificado".plural($afectadas).".</div>";}
        if ($afectadas>-1 && tipoSQL($sentencia)=="3") {$numresultados=$afectadas;$msj = '<div class="alert alert-success">'.$afectadas." registro".plural($afectadas)." a&ntilde;adido".plural($afectadas).".</div>";}
        if ($afectadas>-1 && tipoSQL($sentencia)=="4") {$numresultados=$afectadas;$msj = '<div class="alert alert-success">'.$afectadas." registro".plural($afectadas)." eliminado".plural($afectadas).". </div>";}
        
        ejecutaqry($link,$sentencia,$numresultados);
        if ($numresultados<0) {$numresultados="0";}            
    echo $msj.' </table>
    <script>
        $("#total").html("Total registros: '.$numresultados.'");
    </script>';   
                 
    mysqli_free_result($link);
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
    echo "sin instrucciones";
    break;
    
    
}
   

                              
?>