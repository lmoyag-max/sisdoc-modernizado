<?PHP
include("conexion_bd.php");

$query = "select * from expediente where  id_expediente = $txtexped";
$rs_doc = mssql_query($query);
$reg_doc = mssql_fetch_array($rs_doc);
$tot_doc = mssql_num_rows($rs_doc);
if ($tot_doc != 0)
	{
	$total=1;
	$descripcion =$reg_doc["desc_expediente"];
	}
else 
	{
	 $total= 0 ;
     $descripcion="";
	}
mssql_close($cn);

echo '<html><body onload="document.form1.submit();">';
echo '<form name="form1" method="post" action="ingreso_docto2.php">' . "\n";

echo '<input type="hidden" name="cusuario"        value= "' . $cusuario .'">' . "\n";
echo '<input type="hidden" name="idusuario"       value="' . $idusuario . '">' . "\n";
echo '<input type="hidden" name="idfuncionario"   value="' . $idfuncionario . '">' . "\n";
echo '<input type="hidden" name="num_int"         value="' . $num_int . '">' . "\n";
echo '<input type="hidden" name="flujook"         value="' . 8 . '">' . "\n";
//echo '<input type="hidden" name="num_exp"         value="' . 0 . '">' . "\n";
echo '<input type="hidden" name="totexped"        value="' . $total . '">' . "\n";
echo '<input type="hidden" name="num_exp"         value="' . $txtexped . '">' . "\n";
echo '<input type="hidden" name="descexped"       value="' . $descripcion . '">' . "\n";
echo '</form></body></html>'  . "\n";
?>