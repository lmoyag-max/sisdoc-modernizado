<?php
$idusu = $idusuario;
$cusu = $cusuario;
$idfunc= $idfuncionario;
$flujo= $flujo_ok;
$cbo= $val_funcionario;
$cbo1= $val_procedencia;
$cbo2= $val_funcionario1;
$cbo3= $val_destino;
$flujo=8;
$x=0;
$y='';
$num_int='';

// vacias por qué?, nosé
$tipop= $tipo_procedencia;
$tipod= $tipo_destino; 
// vacias por qué?, nosé
$num = $num_int;
//echo "id_func" . $idfunc . "," . $idfuncionario;

?>
<html>
<head>
<title>PRINCIPAL.PHP</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<?php
// para ver variables tiene 55 pero se achica para que no se vea tan grande en pantalla//
//echo '<frameset rows="55,45,*" frameborder="no" border="0" framespacing="0">';
echo '<frameset rows="0,45,*" frameborder="no" border="0" framespacing="0">' . "\n";
echo '<frame  name="frame_consultas" src="frame_consultas.php" scrolling="NO" noresize >' . "\n";
echo '<frame src="frame_menuvars.php?idusuario=' . $idusu . 
       '&cusuario=' . $cusu . '&idfuncionario=' . $idfunc . '&flujook=' . $flujo .
	   '&val_funcionario=' . $cbo . '&val_procedencia=' . $cbo1 . 
	   '&val_funcionario1=' . $cbo2 . '&val_destino=' . $cbo3 .
       '&tipo_procedencia=' .	 $tipop . '&tipo_destino=' . $tipod . 
	   '&idfuncionario=' .	 $idfunc .  '&num_int=' . $num . 
	   '" name="frame_menuvars" scrolling="NO" noresize>' . "\n";
if($tramite == 1)// para que cargue en principio esta página de los tramites del documento linkeado  desde el correo 
{	
echo '<frame src="tramites_deriva.php?iddocum='.$iddocum.
     '&cusuario='.$cusu. '&idusuario='.$idusu . '&num_int=' . $num .'&flujook=' . $flujo .
	 '&val_funcionario=' . $cbo . '&val_procedencia=' . $cbo1 . 
	   '&val_funcionario1=' . $cbo2 . '&val_destino=' . $cbo3 .
       '&tipo_procedencia=' .	 $tipop . '&tipo_destino=' . $tipod .
	    '"name="mainFrame">' . "\n". '</frameset>'; 
}
else
{	   	
	echo '<frame src="frame_vistas.php" name="mainFrame">' . "\n". '</frameset>'; 
}		
	?>

<noframes><body>

</body></noframes>
</html>
