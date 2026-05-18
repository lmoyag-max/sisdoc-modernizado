<?php  include("../conexion_bd.php"); ?>
<html>
<head>
<title>Lista de Usuarios</title>
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
<body>
	<table width="75%" border="1" align="center">
	<tr bgcolor="#C9DEEF"> 
		<th height="38" colspan="3" valign="top">Lista de Usuarios</th>
	</tr>
	<?php
		$rs_usuarios = mssql_query("SELECT funcionario.rut+'-'+funcionario.dig,funcionario.apellidos+' '+funcionario.nombres,dependencia.desc_dependencia,funcionario.vigencia FROM funcionario, dependencia WHERE funcionario.id_dependencia=dependencia.id_dependencia ORDER BY funcionario.apellidos");
		$filas=mssql_num_rows($rs_usuarios)-1;
		$reg_usuarios=mssql_fetch_row($rs_usuarios);
		echo '<tr><td>Rut</td><td>Nombre</td><td>Dependencia</td></tr>';
		for ($i = 0; $i <= $filas;  $i++) { 
			echo '<tr><td>'.$reg_usuarios[0].'</td><td>'.$reg_usuarios[1];
			if(is_null($reg_usuarios[3])){
				echo ' <span style="color:green"><strong>(vigente)</strong></span></td>';
			}else{
				echo ' <span style="color:red"><strong>(sin vigencia)</strong></span></td>';
			}
			echo '<td>'.$reg_usuarios[2].'</td>';
			$reg_usuarios = mssql_fetch_row($rs_usuarios);
		}
		echo '</td></tr>';
	?>
	</table>
<p>&nbsp;</p>
</body>
</html>
<?php mssql_close($cn); ?>