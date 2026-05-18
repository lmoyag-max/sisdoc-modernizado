<html>
<head>
<script language="JavaScript">

function revisa_check(filas) {

  var x=0;
  //filas=100;
  arregloint="";
  arreglo1="";
  for (k=0;k<filas;k++)
  {
     if (document.form1.checkbox[k].checked)
     {
       arreglo1=arreglo1+document.form1.checkbox[k].value+"@";
      x=x+1;
	 }
  }
 //document.form1.arregloint.value=x + "@" + arreglo1;

 var vArreglo = x + "@" + arreglo1;

 window.returnValue = vArreglo;
 window.close();		
}
        
</script>

<script language="javascript">
	 //  document.write('argumento : ' + window.dialogArguments);
</script>

<title>www.minsal.cl - SISDOC</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<?php 
include("conexion_bd.php");
include("carga_tablas.php");

$arr=$arr1;

$vector = split ("@",$arr);
$largo=0;
$largo= $vector[0];
$n=1;
$chequeo=0;


$rs_servicio= mssql_query("SELECT * FROM descriptor order by desc_descriptor", $cn);
$nRows = mssql_num_rows($rs_servicio);
echo '<body leftmargin="30">';

echo '<table width="100%"><tr>';
echo '<td><div align="center"><b><font size="4" face="Verdana, Arial, Helvetica, sans-serif"> Seleccione Descriptor </font></b></div></td>';
echo '</table></tr>';
echo '<font size="1" face="Verdana, Arial, Helvetica, sans-serif">Descriptores Seleccionados: ' . $largo  . '</font>';
echo '<form name="form1">';
echo '<table width="100%" border=1 cellpadding="0" cellspacing="1" bordercolor="#6699FF">';
$k=0;
$nReg=0;
echo '<tr>';
while($reg_servicio = mssql_fetch_array($rs_servicio)) { 

for($n=1;$n <=$largo;$n++) {
	if($vector[$n]==$reg_servicio["id_descriptor"]) {
		$chequeo=1;
		break;
	}
	else {
	$chequeo=0; }
	
}

if($chequeo==1) {
?>
<td><font size="1" face="Verdana, Arial, Helvetica, sans-serif">
<input type="checkbox" name="checkbox" value="<?php echo $reg_servicio["id_descriptor"];?>"checked>
<?php echo $reg_servicio["desc_descriptor"]; ?>
</font></td>
<?php
}
else
{ ?>
<td><font size="1" face="Verdana, Arial, Helvetica, sans-serif">
<input type="checkbox" name="checkbox" value="<?php echo $reg_servicio["id_descriptor"];?>">
<?php echo $reg_servicio["desc_descriptor"]; ?>
</font></td>
<?php
}
?>


<?php 
$nReg=$nReg+1;
if($nReg==4){
   $nReg=0;
   echo '</tr>';
   echo '<tr>';
}
}
?> 
</tr>
</table>
<center>
      
<table width="100%" border="0">
    <tr>
      <td> <div align="center">
      <input type="button" name="Submit" value="Aceptar" class="botones" onClick="revisa_check(<?php echo $nRows; ?>);">
      </div>
      </td>
    </tr>
  </table>
  </center>
</form>      
</body>
</html>