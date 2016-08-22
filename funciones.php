<?php          
session_start();
$noPuedes='<div class="alert alert-danger"><b>ERROR!</b> No puedes ejecutar esta sentencia.</div>'; 
$noPermisos='<div class="alert alert-danger"><b>ERROR!</b> No tienes permisos para ejecutar este tipo de sentencias.</div>'; 
$noPermisosMultiples='<div class="alert alert-danger"><b>ERROR!</b> No tienes permisos para ejecutar alguna sentencia de las que has lanzado.</div>'; 
 
$bdsArray = [
    "inmovacia" => "4",  //// aquí podemos dar permisos a cada base de datos,
    "gesquery" => "2",   //// para ver lo que se puede hacer con ella de forma global
    "inmo1" => "4"      //// los niveles de pemisos son los mismos que el de usuarios (inicio de este fichero)
];



function cortaTexto($texto,$max=30) { 
   $texto2=substr($texto,0,$max);
   $index=strrpos($texto2," ");
   $texto2=substr($texto2,0,$index);
   if ($texto2=="") {$texto=substr($texto, 0,$max);} else {$texto=$texto2;} 
   $texto.="...";
   return $texto;
}




function tipoSQL($qry) {
    
    $palabras=explode(" ",$qry);

    switch (strtolower($palabras[0])) {
    case "select" : case "show" : case "describe" : case "desc" :                   $tiposql="1"; $gruposql="1";    break;
    case "insert" :                                                                 $tiposql="2"; $gruposql="2";    break;
    case "update" :                                                                 $tiposql="3"; $gruposql="2";    break;
    case "delete" :                                                                 $tiposql="4"; $gruposql="2";    break;
    case "empty" : case "alter" : case "truncate" : case "drop" : case "kill" :
    case "create" : case "optimize" : case "grant" : case "revoke" :
    case "flush" : case "lock" : case "unlock" : case "explain" : case "set" :      $tiposql="5"; $gruposql="8";    break;
    case "mysqldump" : case "mysql" :                                               $tiposql="6"; $gruposql="9";    break; 
    
    default : die($noPuedes);
    }
return $tiposql;
}
      
      
function dameBD() {    
include_once("cxn.php");
$lista_bd = mysqli_query($link,'SHOW DATABASES;');
$bds.= '
        <select class="form-control" id="bds" onChange="extrae(\'capaTablas\');">
        <option value="" disabled>Selecciona Base Datos</option>
    ';             
    while ($bd=mysqli_fetch_array($lista_bd))
    {
     if ($bd[0]==$_SESSION["bd"]) {$selected=" selected";} else {$selected="";}
        $bds.= '<option value="'.$bd[0].'"'.$selected.'>'.$bd[0].'</option>
    ';
    }
    $bds.="
        </select>
        <script>
        $(document).ready(function() { 
        extrae('capaTablas');
        });
        </script>
        ";
return $bds;
}

    
function plural($cantidad) {
    if ($cantidad!=1)
        return "s";
}

function ejecutaqry($link,$sentencia,$results) {
         if ($sentencia=="") die("SENTENCIA VACIA");
                                                                                                                               
         if(mysqli_query($link,'INSERT INTO gesquery.sentencias (user,fecha,query,results,tipo) VALUES ("'.$_SESSION["user"].'",NOW(),"'.addslashes($sentencia).'","'.$results.'","'.tipoSQL($sentencia).'");'))
         {return "OK";} else {return "ERROR";}
}

function bdSoloLectura($bdactual,$sentencia,$tipo) { 
                                  
    foreach ($GLOBALS["bdsArray"] as $bd => $nivel)
    {/// primero busca si se llama a la bd en la sentencia, si no, mira la bd seleccionada    
        $encuentra=$bd.".";
        $pos=$pos+strpos($sentencia,$encuentra);
        
        if ($pos>0 && $tipo>$nivel)
        {
            return true;
        }
    }
    
    foreach ($GLOBALS["bdsArray"] as $bd => $nivel)
    {   
           if ($bdactual==$bd && $tipo>$nivel)
                {return true;}
        
    }
}

function mensajePermisos($tipo) {
    switch ($tipo) {                                                                                                      
    case 1 : $mensaje="no puedes consultar nada sobre esta base de datos..."; break; 
    case 2 : $mensaje="no puedes insertar informaci&oacute;n en esta base de datos..."; break; 
    case 3 : $mensaje="no puedes modificar la informaci&oacute;n contenida en ella..."; break; 
    case 4 : $mensaje="no puedes borrar la informaci&oacute;n contenida en ella..."; break; 
    case 5 : $mensaje="no puedes eliminar o vaciar esta base de datos..."; break; 
    case 6 : $mensaje="no puedes exportar su informaci&oacute;n..."; break;
    }
    die('<div class="alert alert-danger"><b>ERROR POR PERMISOS DE LA BASE DE DATOS!</b><br/> - Debido a los permisos de la base de datos, '.$mensaje.'</div>');
}
                          
?>