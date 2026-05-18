<?PHP 

$Txtfechaofic="280306";
echo "fecha oficial " . $Txtfechaofic. "<br>";
if(ereg("-",$Txtfechaofic))
{ //echo "encontrado" ;
}
else 
{
echo "Cadena No Encontrada" ; 
$dia=substr($Txtfechaofic,1,2);
$mes =subst($Txtfechaofic,3,2);
if (len($Txtfechaofic)==6)
{$año= substr($Txtfechaofic,5,2);}
else  if (len(Txtfechaofic==8))
 { $año= substr($Txtfechaofic,5,2);}

echo "fecha " . $dia .'-'.$mes .'-' . $año;
}



?>
 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

</body>
</html>

<?php 


?>
