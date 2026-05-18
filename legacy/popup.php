<html>
<head>
  <title>Base para popup</title>
  
      <script language="javascript">
  //<!--
  var _popup=null;

function cierraPopup()
{
  if(_popup && !_popup.closed && _popup.open)
  {
      _popup.close();
	  _popup=null;
  }
}
function abrePopup(dir_url,nombre,modo)
{
  cierraPopup();
  _popup=window.open(dir_url,nombre,modo);
  //_popup.document.open();
  //_popup.document.write(_pagina[i]);
  //_popup.document.close();
  _popup.focus();
}

  function envialos()
  {
    document.formulario.submit();
  }



  //-->
  
  </script>

</head>

<body>
<br><br><br>


<font color="#8000FF">
<?php
$iddocum=933;
$idseguim=990;
$cusuario="ximena";
echo '<font color="#8000FF"><a href="javascript:abrePopup(\'documento_de_referencia.php?cusuario=' .  $cusuario . '&iddocum=' . $iddocum . '&idseguim=' . $idseguim . '\',\'_blank\',\'width=630,height=250\');">Prueba POPUP</a></font>';
//echo '<font color="#8000FF"><a href="javascript:window.open(\'documento_de_referencia.php?cusuario=' .  $cusuario . '&iddocum=' . $iddocum . '&idseguim=' . $idseguim . '\');">Prueba POPUP</a></font>';

?>

</body>
</html>