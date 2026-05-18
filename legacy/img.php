<?php

//$nombrearchivo = $imagen;

//if ($nombrearchivo== null )
$archivo = split ("@",$imagen);
$largo=0;
 
/*
//// para diferenciar cuando  el  archivo es pdf 
$pos=strpos($imagen,".");
echo substr($imagen,41,3);
$extension = substr($imagen,41,3);
if ($extension =='pdf')
{
 
}*/
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
<?php echo '<a  href="'.$imagen.'">Si no ve el archivo en pantalla  pinche aquí  </a>'; ?>
<table border="0">
  <tr>
 	 <?php 	$x=0;
			for ($x=0;$x<count($archivo);$x++)	{ ?>
			<tr>
				<td>
				   <? // primera pagina 
				   if ($x == 0 )
				   {?>
					<img src="<?php echo $archivo[$x];?>"  align ="left" width="650" height="950" >
				 </td>
				<td valign="top">
				       <!--input name="Imprimir" type="button"  onClick="imprime()" value="Imprimir"   align ="right"--> 
			      <br> <input name="Cerrar"   type="button"  onClick="salir()"   value="Cerrar"     align="right" > 
			    </td>
				<? }?> 
			</tr>
				<? // paginas siguientes 
				 if ($x <> 0 )
				    {?>
					<tr><td><img src="<?php echo $archivo[$x];?>"  align ="left" width="650" height="950" ><font color="#FFFFFF">a</font></td></tr>
					<? }
					// ultima pagina 
					if ($x ==count($archivo)-1){ ?>
					<tr>
					  <td><div align="center">Fin Documento</div>
					  </td>
					</tr>
					<? }
			 }?>		  		  	
</table>
</body>
</html>
