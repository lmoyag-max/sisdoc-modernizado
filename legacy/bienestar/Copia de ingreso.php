<?php
$rut_fun = $rut_fun;
$rut_c = $rut;
$pat_fun= $pat_fun;
$mat_fun= $mat_fun;
$nom_fun= $nom_fun;
$dir_fun=$dir_fun;
$car_fun=$car_fun;
$reg_f=$reg_f;
$com_fun=$com_fun;
$dep_fun=$dep_fun;
$ane_fun=$ane_fun;
$est_fun=$est_fun;
$flujo= $flujo_ok;
//echo "reg" . $reg_f;
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
  echo '<frame src="ingreso_bienestar.php?rut_fun=' . $rut_fun .
       '&pat_fun=' . $pat_fun . '&mat_fun=' . $mat_fun . '&flujook=' . $flujo .
	   '&nom_fun=' . $nom_fun . '&rut_c=' . $rut_c . 
	   '&dir_fun=' . $dir_fun . '&region=' . $reg_f .
	   '&comuna=' . $com_fun . '&gra_fun=' . $gra_fun .
	   '&ane_fun=' . $ane_fun .	 '&car_fun=' . $car_fun .
	   '&dep_fun=' . $dep_fun .	'&est_fun=' . $est_fun .   
	   '"name="mainFrame">';
  ?>	  
<frame src="UntitledFrame-2"></frameset>
<noframes><body>

</body></noframes>
</html>
