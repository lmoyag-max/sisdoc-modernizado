<?php
include("conexion_bd.php");
 // ingreso de las comunas en el caso que se seleccione una region 
 if ($dato=="IB")
 
 {
 $query="SELECT * FROM comuna where reg_codigo= " . $id_reg ;

$rs_comuna=mssql_query($query, $cn);

$Totreg = mssql_num_rows($rs_comuna);
$i=0;
echo "<script>\n";
$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.cmbcomuna.options.length=" . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.cmbcomuna.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.cmbcomuna.options[0].text='-------';\n";

while($reg_comuna = mssql_fetch_array($rs_comuna))
   {
       $i = $i + 1;
       echo " parent.mainFrame.document.form1.cmbcomuna.options[" . $i . "].value='" . $com_codigo. "';\n";
       echo " parent.mainFrame.document.form1.cmbcomuna.options[" . $i . "].text='" . $com_descripcion. "';\n";
	  
	}
echo "</script>\n";	
$reg_comuna.close;
$rs_comuna.close;
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
