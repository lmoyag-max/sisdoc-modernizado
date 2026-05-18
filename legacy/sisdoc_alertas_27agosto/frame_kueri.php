
<?php

include("conexion_bd.php");
global $cn, $varSql;

$id_dep = $cod_dep;
$desd=$des_d;
$desf=$des_f;
$prod=$pro_d;
$prof=$pro_f;
echo "valores" . $id_dep . "," . $desd . "," . $desf . "," . $prod . "," . $prof;

//  ***************** DESTINO EXTERNO ********
if ($sw=="E")
{
if(isset($id_dep)) {
$rs_dep_ext = mssql_query("SELECT * FROM dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg = mssql_num_rows($rs_dep_ext);

// inicializa en -1 para cuando sume 1 quede en Cero y agregar datos  a la combo del otro frame (mainFrame)
// posteriormente define el tamaño o largo del combo según el total de registros encontrados
$i=0;
echo "<script>\n";

$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep_ext = mssql_fetch_array($rs_dep_ext))
   {
        $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep_ext[id_dependencia_externa] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep_ext[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $desd ."';\n";	
echo "</script>\n";
$reg_dep_ext.close;
$rs_dep_ext.close;
}
}
else
// ********************** PROCEDENCIA EXTERNA **************
if ($sw=="PE")
{
if(isset($id_dep)) {
$rs_dep_ext = mssql_query("SELECT * FROM dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg = mssql_num_rows($rs_dep_ext);
$i=0;
echo "<script>\n";

$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep_ext = mssql_fetch_array($rs_dep_ext))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep_ext[id_dependencia_externa] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep_ext[desc_dependencia_externa] . "';\n";
  }

echo "</script>\n";  
$reg_dep_ext.close;
$rs_dep_ext.close;
}
}
else
// ********************** PROCEDENCIA INTERNA **********************

if ($sw=="I" and $cod_dep==1)
 {
//$rs_dep = mssql_query("SELECT * FROM dependencia order by desc_dependencia", $cn);
$rs_dep= mssql_query("select dependencia.*
		from dependencia, acceso where acceso.id_dependencia = dependencia.id_dependencia and acceso.id_usuario = " . $prod, $cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n"; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep[desc_dependencia] . "';\n";
	}
echo "</script>\n"; 
$reg_dep.close;
$rs_dep.close;
}

else
// ******************* DESTINO INTERNO ************************

if ($sw=="I" and $cod_dep==0)
 {
$rs_dep = mssql_query("SELECT * FROM dependencia order by desc_dependencia", $cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n";
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep_ext = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep_ext[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep_ext[desc_dependencia] . "';\n";
	}
echo "</script>\n";
$reg_dep_ext.close;
$rs_dep.close;
}

else
//  **************** COMBO PROCEDENCIA *********************

if ($sw=="")
{

if(isset($id_dep)) {
$op="I";

//$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $id_dep, $cn);
//$descriptor_query="exec ingreso_descriptor '" . $vector[$x] . "','" . $Id_Documento . "','" . $op . "'";
$rs_funcionario = "exec busca_funcionario '" . $id_dep . "','" . $op . "'";

$Totreg = mssql_num_rows($rs_funcionario);
$i=0;
echo "<script>\n";

$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options.length=" . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].text='-------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
       $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].text='" . $reg_funcionario[nombres] . "';\n";
	}
echo "</script>\n";	
$reg_funcionario.close;
$rs_funcionario.close;
}
}

else
//  ******** COMBO FUNCIONARIO PROCEDENCIA Y DESTINO QUEDA MARCADO EL QUE VENIA SELECCIONADO ***********

if ($sw=="II")
{
$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $prod, $cn);
$Totreg = mssql_num_rows($rs_funcionario);
$i=0;
echo "<script>\n";

$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options.length=" . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].text='-------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
       $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].text='" . $reg_funcionario[nombres] . "';\n";
	}


echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.selectedIndex='" . $prof ."';\n";
echo "</script>\n";	
$reg_funcionario.close;
$rs_funcionario.close;

$rs_funcionario2 = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $desd, $cn);
$Totreg2 = mssql_num_rows($rs_funcionario2);
$i=0;
echo "<script>\n";

$total_rec2 = $Totreg2+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options.length=" . $total_rec2 . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].text='-------';\n";

while($reg_funcionario2 = mssql_fetch_array($rs_funcionario2))
   {
       $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].value='" . $reg_funcionario2[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].text='" . $reg_funcionario2[nombres] . "';\n";
	}


echo " parent.mainFrame.document.form1.Cbo_Func_Destino.selectedIndex='" . $desf ."';\n";
echo "</script>\n";	
$reg_funcionario2.close;
$rs_funcionario2.close;
}
else
//  ********** COMBO PROCEDENCIA Y DESTINO EXTERNO SACA EL QUE VENIA SELECCIONADO **************

if ($sw=="EE")
{
$rs_dep =mssql_query("SELECT * FROM dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n"; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep[id_dependencia_externa] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Procedencia.selectedIndex='" . $prod."';\n";	
echo "</script>\n"; 
$reg_dep.close;
$rs_dep.close;

$rs_dep1 =mssql_query("SELECT * FROM dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg=0;
$Totreg = mssql_num_rows($rs_dep1);
$i=0;
echo "<script>\n";
$total_rec=0; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep1 = mssql_fetch_array($rs_dep1))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep1[id_dependencia_externa] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep1[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $desf."';\n";	
echo "</script>\n"; 
$reg_dep1.close;
$rs_dep1.close;
}
else
// ********* COMBO PROCEDENCIA EXTERNA Y DESTINO INTERNO SACA EL QUE VENIA SELECCIONADO ****************

if ($sw=="EI")
{
$rs_dep =mssql_query("SELECT * FROM dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n"; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep[id_dependencia_externa] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Procedencia.selectedIndex='" . $desd."';\n";	
echo "</script>\n"; 
$reg_dep.close;
$rs_dep.close;

$rs_dep1 = mssql_query("SELECT * FROM dependencia order by desc_dependencia", $cn);
$Totreg=0;
$Totreg = mssql_num_rows($rs_dep1);
$i=0;
echo "<script>\n"; 
$total_rec=0;
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep1 = mssql_fetch_array($rs_dep1))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep1[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep1[desc_dependencia] . "';\n";
	}

echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $prod ."';\n";	
echo "</script>\n";
$reg_dep1.close;
$rs_dep1.close;

$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia ="  . $prof, $cn);
$Totreg = mssql_num_rows($rs_funcionario);
$i=0;
echo "<script>\n"; 
$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options.length=" . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].text='-------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
  $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].text='" . $reg_funcionario[nombres] . "';\n";
	}

if($desf >0){
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.selectedIndex='" . $desf ."';\n";
}
echo "</script>\n";	
$reg_funcionario.close;
$rs_funcionario.close;
}

else
// ********* COMBO PROCEDENCIA INTERNA Y DESTINO EXTERNO SACA EL QUE VENIA SELECCIONADO ***********

if ($sw=="IE")
{
$rs_dep =mssql_query("select dependencia.* from dependencia, acceso
where acceso.id_dependencia = dependencia.id_dependencia and acceso.id_usuario =$id_dep",$cn);
$Totreg = mssql_num_rows($rs_dep);
$i=0;
echo "<script>\n"; 
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Procedencia.options[0].text='-------------';\n";

while($reg_dep = mssql_fetch_array($rs_dep))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].value='" . $reg_dep[id_dependencia] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Procedencia.options[" . $i . "].text='" . $reg_dep[desc_dependencia] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Procedencia.selectedIndex='" . $prod ."';\n";	
echo "</script>\n"; 
$reg_dep.close;
$rs_dep.close;

$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $prof, $cn);
$Totreg = mssql_num_rows($rs_funcionario);
$i=0;
echo "<script>\n";

$total_rec = $Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options.length=" . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[0].text='-------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
       $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.options[" . $i . "].text='" . $reg_funcionario[nombres] . "';\n";
	}

if($desf >0){
echo " parent.mainFrame.document.form1.Cbo_Func_Procedencia.selectedIndex='" . $desf ."';\n";
}
echo "</script>\n";	
$reg_funcionario.close;
$rs_funcionario.close;

$rs_dep1 = mssql_query("SELECT * FROM dependencia_externa order by desc_dependencia_externa", $cn);
$Totreg=0;
$Totreg = mssql_num_rows($rs_dep1);
$i=0;
echo "<script>\n"; 
$total_rec=0;
$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Destinatario.options[0].text='-------------';\n";

while($reg_dep1 = mssql_fetch_array($rs_dep1))
   {
        $i = $i + 1;
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].value='" . $reg_dep1[id_dependencia_externa] . "';\n";
       echo "parent.mainFrame.document.form1.Cbo_Destinatario.options[" . $i . "].text='" . $reg_dep1[desc_dependencia_externa] . "';\n";
	}
echo " parent.mainFrame.document.form1.Cbo_Destinatario.selectedIndex='" . $desd ."';\n";	
echo "</script>\n"; 
$reg_dep1.close;
$rs_dep1.close;
}
else
// ************* LLENA COMBO DESTINATARIO **********************

if ($sw=="F")
{
if(isset($id_dep)) {
 
$rs_funcionario = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $id_dep, $cn);
$Totreg = mssql_num_rows($rs_funcionario);
$i=0;
echo "<script>\n";

$total_rec=$Totreg+1;
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options.length= " . $total_rec . ";\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].value='0';\n";
echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[0].text='-------------';\n";

while($reg_funcionario = mssql_fetch_array($rs_funcionario))
   {
        $i = $i + 1;
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].value='" . $reg_funcionario[rut] . "';\n";
       echo " parent.mainFrame.document.form1.Cbo_Func_Destino.options[" . $i . "].text='" . $reg_funcionario[nombres] . "';\n";
	}
echo "</script>\n";	
$reg_funcionario.close;
$rs_funacionario.close;
}
} 
?>


<html>
<head>
<title>Menu TOP</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#00EC00">
</body>
</html>
