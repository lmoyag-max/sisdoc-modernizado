<?php  include("../conexion_bd.php"); ?>
<html>
<head>
<title>Listado de Dependencias</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
body {
	border:		0;
	background:	White;
    font-family:    Arial, Helvetica, sans-serif;
	font-size: 10px;
}
TD      {font-family:Arial, Helvetica, sans-serif;font-size:x-small;color:black}
H1,H2   {font-family:Arial, Helvetica, sans-serif}
H1      {font-size:15px;font-weight:600}
H2      {font-size:13px;font-weight:400;color:black}
TH      {font-family:Arial, Helvetica, sans-serif;font-size:x-small;
         color:white;background-color:#0080C0;
         text-align:center
        }
}
</style>
</head>
<body onLoad="carga()">
	<table width="50%" border="1" align="center">
    <tr bgcolor="#C9DEEF"> 
      <th height="38" colspan="2" valign="top">Listado de Dependencias</th>
    </tr>
    <?php
		  $rs_dependencia = mssql_query("SELECT desc_dependencia, vigencia FROM dependencia order by desc_dependencia");
		  $filas=mssql_num_rows($rs_dependencia) - 1;
		  $reg_dep=mssql_fetch_row($rs_dependencia);
		  echo '<tr><td width="30%">Dependencias en la BDD</td><td valign="top">';
		  for ($i = 0; $i <= $filas;  $i++)
			{ 
			   echo $reg_dep[0];
			   if(is_null($reg_dep[1])){
				   echo ' <span style="color:green"><strong>(vigente)</strong></span><br/>';
			   }else{
				   echo ' <span style="color:red"><strong>(sin vigencia)</strong></span><br/>';
			   }
			   $reg_dep = mssql_fetch_row($rs_dependencia);
			}
		echo '</td></tr>';
    ?>
    </table>
<p>&nbsp;</p>
</body>
</html>
<?php mssql_close($cn); ?>