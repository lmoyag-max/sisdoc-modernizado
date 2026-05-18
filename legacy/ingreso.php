<?php
$idusu = $idusuario;
$cusu = $cusuario;
$idfunc= $idfuncionario;
$flujo= $flujo_ok;
$cbo= $val_funcionario;
$cbo1= $val_procedencia;
$cbo2= $val_funcionario1;
$cbo3= $val_destino;
$tipop= $tipo_procedencia;
$tipod= $tipo_destino; 
$num = $num_int;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<frameset rows="45,*" frameborder="NO" border="0" framespacing="0">
  <frame src="frame_consultas.php" name="topFrame" scrolling="NO" noresize >
   <?php 
  echo '<frame src="ingreso_docto1.php?idusuario=' . $idusu .
       '&cusuario=' . $cusu . '&idfuncionario=' . $idfunc . '&flujook=' . $flujo .
	   '&val_funcionario=' . $cbo . '&val_procedencia=' . $cbo1 . 
	   '&val_funcionario1=' . $cbo2 . '&val_destino=' . $cbo3 .
       '&tipo_procedencia=' .	 $tipop . '&tipo_destino=' . $tipod . 
	   '&num_int=' . $num . '"name="mainFrame">';
  ?>	  
<frame src="UntitledFrame-2"></frameset>
<noframes><body>

</body></noframes>
</html>
