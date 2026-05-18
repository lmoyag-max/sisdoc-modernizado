<html> 
<head> 
<title>Prueba menuo</title> 
<script>
function mOvr(src,clrOver) {
if (!src.contains(event.fromElement)) {
src.style.cursor = 'hand';
src.bgColor = clrOver;
}
}
function mOut(src,clrIn) {
if (!src.contains(event.toElement)) {
src.style.cursor = 'default';
src.bgColor = clrIn;
}
}
function mClk(src) {
if(event.srcElement.tagName=='TD'){
src.children.tags('A')[0].click();
}
}
function llama_principal()
{
document.form1.submit();
<?php echo 'location.href="principal.php?idusuario='.$idusuario. "&cusuario=". $cusuario . "&idfuncionario=" .$idfuncionario . "&flujo_ok=" . $flujo_ok . "&val_funcionario=" . $val_funcionario ."&val_procedencia=" . $val_procedencia ."&val_funcionario1=" . $val_funcionario1."&val_destino=" . $val_destino ."&tipo_procedencia=" ."' $tipo_procedencia'" ."&tipo_destino=" ."' $tipo_destino '"."&num_int=" . $num_int ."&id_dependencia=" . $id_dependencia."&tipo_frame=1\";"; ?>		
 
}

function llama_principalofpartes()
{
document.form1.submit();
<?php echo 'location.href="principal_ofpartes.php?idusuario='.$idusuario. "&cusuario=". $cusuario . "&idfuncionario=" .$idfuncionario . "&flujo_ok=" . $flujo_ok . "&val_funcionario=" . $val_funcionario ."&val_procedencia=" . $val_procedencia ."&val_funcionario1=" . $val_funcionario1."&val_destino=" . $val_destino ."&tipo_procedencia=" .$tipo_procedencia ."&tipo_destino="  .$tipo_destino ."&num_int=" . $num_int."&id_dependencia=" . $id_dependencia."&tipo_frame=" . $tipo_frame."\";"; ?>		
 
}
</script>





</head> 
<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0">
<center>
<form name="form1" method="post" >
<table align="center"  height="160" border="1">
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<tr>
<td align="center" height="10"> <strong><?php echo "MENU OFICINA DE PARTES" ;?></strong></td>
</tr>
<tr >
<!---td   onclick="mClk(this);"  onmouseout="mOut(this,'#498aa8');" onmouseover="mOvr(this,'#f18b59');" vAlign="center" width="200" style="border-bottom: 1px solid rgb(0,0,0); padding-left: 20; padding-top: 1; padding-bottom: 1" bgcolor="#475B70" height="1" ><a style="COLOR: rgb(255,255,255); TEXT-DECORATION: none" href="principal.php" ><font face="Verdana" size="2">Ingreso Documentos Oficina </font></a></td-->
<td   onclick="mClk(this);llama_principal();"  onmouseout="mOut(this,'#498aa8');" onMouseOver="mOvr(this,'#f18b59');" vAlign="center" width="200" style="border-bottom: 1px solid rgb(0,0,0); padding-left: 20; padding-top: 1; padding-bottom: 1" bgcolor="#475B70" height="1" ><a style="COLOR: rgb(255,255,255); TEXT-DECORATION: none"  ><font face="Verdana" size="2">Ingreso Documentos Oficina </font></a></td>
</tr>


<tr>
<td onClick="mClk(this);llama_principalofpartes()"  onmouseout="mOut(this,'#498aa8');" onMouseOver="mOvr(this,'#f18b59');" vAlign="center" width="200" style="border-bottom: 1px solid rgb(0,0,0); padding-left: 20; padding-top: 1; padding-bottom: 1" bgcolor="#475B70" height="1"  ><a style="COLOR: rgb(255,255,255); TEXT-DECORATION: none"><font face="Verdana" size="2">Ingreso Documentos Externos  </font></a></td>
</tr>


<!--tr>
<td onclick="mClk(this);" onmouseout="mOut(this,'#498aa8');" onmouseover="mOvr(this,'#f18b59');" vAlign="center" width="171" style="border-bottom: 1px solid rgb(0,0,0); padding-left: 6; padding-top: 1; padding-bottom: 1" bgcolor="#475B70" height="1"><a style="COLOR: rgb(255,255,255); TEXT-DECORATION: none" href="../../index.html"><font face="Verdana" size="1">Mi web</font></a></td>
</tr-->


</table>
</form>

  
</body> 
</html> 
