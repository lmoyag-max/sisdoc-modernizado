<?php
$num = $folio;
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
  echo '<frame src="respuesta.php?cusuario=' . $cusuario. 
	    '&num_int=' . $num .    '"name="mainFrame">';
  ?>	  
<frame src="UntitledFrame-4"></frameset>
<noframes></noframes>
</html>
