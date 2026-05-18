<?php
$idusu = $idusuario;
$cusu = $cusuario;
$idfunc= $idfuncionario;
$flujo= $flujo_ok;
$cbo= $val_funcionario;
$cbo1= $val_procedencia;
$cbo2= $val_funcionario1;
$cbo3= $val_destino;
// vacias por qué?, nosé
$tipop= $tipo_procedencia;
$tipod= $tipo_destino; 
// vacias por qué?, nosé
$num = $num_int;
?>
<html>
<head>
<title>PRINCIPAL.PHP</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<?php
echo '<frameset rows="20,80,*" frameborder="yes" border="1" framespacing="0">';
/*echo '<frame src="frame_escondido.php?idusuario=' . $idusu .
       '&cusuario=' . $cusu . '&idfuncionario=' . $idfunc . '&flujook=' . $flujo .
	   '&val_funcionario=' . $cbo . '&val_procedencia=' . $cbo1 . 
	   '&val_funcionario1=' . $cbo2 . '&val_destino=' . $cbo3 .
       '&tipo_procedencia=' .	 $tipop . '&tipo_destino=' . $tipod . 
	   '&num_int=' . $num . '" name="frame_escondido" scrolling="NO" noresize>';*/
	   
echo '<frame src="frame_consultas.php" name="frame_consultas" scrolling="SI" noresize >';
echo '<frame src="frame_menuvars.php?idusuario=' . $idusu .
       '&cusuario=' . $cusu . '&idfuncionario=' . $idfunc . '&flujook=' . $flujo .
	   '&val_funcionario=' . $cbo . '&val_procedencia=' . $cbo1 . 
	   '&val_funcionario1=' . $cbo2 . '&val_destino=' . $cbo3 .
       '&tipo_procedencia=' .	 $tipop . '&tipo_destino=' . $tipod . 
	   '&num_int=' . $num . '" name="frame_menuvars" scrolling="SI" noresize>';
	   	
//echo '<frame src="frame_superior.php" name="frame_superior" scrolling="NO" noresize >';
echo '<frame src="frame_vistas.php" name="mainFrame"></frameset>';

?>
 
<noframes><body>

</body></noframes>
</html>
