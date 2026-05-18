<?php

//$nombrearchivo = $imagen;

//if ($nombrearchivo== null )
$archivo = split ("@",$imagen);
$largo=0;

/*if ($vector[0]==0 )
{
echo '<script>';
echo 'alert(" No Existe documento scaneado " )';
echo '</script>';
}
else
{*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>

 <script language="JavaScript" type="text/javascript">
 
function MuestraEsconde(LaLayer,ElAtributo) 
{
if (parseInt(navigator.appVersion) > 3) 
  {
		eval(layerVar + '["' + LaLayer + '"]' + styleVar + '.visibility = "' + ElAtributo + '"');
  }
}

function imprime() 
{
//  MuestraEsconde('impresion','hidden');
 // MuestraEsconde('volver','hidden');
  window.print();
  //MuestraEsconde('impresion','visible');
  //MuestraEsconde('volver','visible');
   
}
function salir()
{
 window.close();
}

 </script>
<style>div.break {page-break-before:always}</style> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<table width="82%" height="800" border="0">
  <tr>
 	 <?php 	$x=0;
			for ($x=0;$x <=1;$x++)	{ ?>
			<tr><td><img src="<?php echo $archivo[$x];?>"  align ="left" width="650" height="750" ><? }?> 
    <!--td><img src="<--?php echo $nombrearchivo ;?>"  align ="left" width="650" height="750"--> 
      			<table width="14%" border="0">
			        <tr>
			          <td><input name="Imprimir" type="button"  onClick="imprime()" value="Imprimir"   align ="right" ></td>
			        </tr>
			        <tr> 
			          <td><input name="Cerrar"   type="button"  onClick="salir()"   value="Cerrar"  align="right" ></td>
			        </tr>
		      </table>
	  		</td>
			</tr>
  </tr>
</table>



  </body>
</html>
<!--? }?-->