<? include("conexion_bd.php");
?>
<html>
<head>
<title>Encuesta sisdoc  </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript1.2">

/*---------------------------- INCIO FUNCIONES PROPIAS DEL SISTEMA ---------------------------------------*/
function salida()
{
window.close();
}
function guardar()
{
sw_ok=true;

 if (document.form1.respuesta.value !="B" && document.form1.respuesta.value !="M" && document.form1.respuesta.value !="A")
   {
	 sw_ok=false ;
     alert("Debe contestar encuesta");
	}
	if (sw_ok==true)
	  document.form1.submit();
}


</script>
</head>
<body>
<form name="form1" method="post" action='guardar_encuesta.php'>
  <div align="center">
    <table width="60%" border="0" >
      <tr> 
        <td ><div align="center" ><font color="#0000A0" size="5" >Encuesta de Conocimientos </font></div></td>
	  </tr>
    </table>
    <table width="60%" border="0">
      <tr> 
        <td height="21" style="color:#FFFFFF;background-color:#6B8EC6;">Se les solicita que indiquen en que nivel de conocimientos 
          se encuentra con el Sistema de Documentos seg&uacute;n la siguiente 
          clasificaci&oacute;n : </td>
      </tr>
    </table>
    <table width="60%" border="1" >
      <tr> 
        <td > <table width="100%" border="0"  bgcolor="#EAF1FF">
            <tr> 
              <td height="41" ><strong><font face="Arial, Helvetica, sans-serif" size="2">B&aacute;sico 
                :</font></strong><font face="Arial, Helvetica, sans-serif" size="2">Ingresar 
                y despachar documentos nuevos, Recepcionar n&oacute;minas, Modificar 
                n&oacute;minas, Derivar documentos recepcionados y despachar, 
                Consultar n&oacute;minas, Modificar tr&aacute;mites.</font></td>
            </tr>
          </table>
		  
          <table width="100%" border="0" bgcolor="#EAF1FF">
            <tr> 
              <td>&nbsp;</td>
            </tr>
            <tr> 
              <td><strong><font size="2" face="Arial, Helvetica, sans-serif">Medio :</font></strong><font size="2" face="Arial, Helvetica, sans-serif"> 
                Ingresar y despachar documentos nuevos, Recepcionar n&oacute;minas, 
                Modificar n&oacute;minas,Derivar documentos recepcionados y despachar, 
                Consultar n&oacute;minas, Modificar tr&aacute;mites, Derivar desde 
                las consultas, Modificar documentos, Eliminar tr&aacute;mites, 
                Archivar documentos.</font></td>
            </tr>
          </table>
          <table width="100%" border="0" bgcolor="#EAF1FF">
            <tr> 
              <td height="21">&nbsp;</td>
            </tr>
          </table>
          <table width="100%" border="0" bgcolor="#EAF1FF">
            <tr> 
              <td><strong><font size="2" face="Arial, Helvetica, sans-serif">Avanzado :</font></strong><font size="2" face="Arial, Helvetica, sans-serif"> 
                Ingresar y despachar documentos nuevos, Recepcionar n&oacute;minas, 
                Modificar n&oacute;minas,Derivar documentos recepcionados y despachar, 
                Consultar n&oacute;minas, Modificar tr&aacute;mites, Derivar desde 
                las consultas, Modificar documentos, Eliminar tr&aacute;mites, 
                Archivar documentos, Eliminar documentos, Modificar n&oacute;mina, 
                Consultar documentos por distintos criterios, Consultar documentos 
                pendientes, Consultar documentos despachados.</font></td>
            </tr>
          </table></td>
      </tr>
    </table>
    <table width="60%" border="1" bgcolor="#EAF1FF">
      <tr> 
        <td width="10%"><div align="right"><strong><font size="2" face="Arial, Helvetica, sans-serif">B&aacute;sico</font></strong></div></td>
        <td width="20%"><input type="radio" name="res"  onClick="document.form1.respuesta.value ='B';"></td>
        <td width="14%"><div align="right"><strong><font size="2" face="Arial, Helvetica, sans-serif">Medio</font> 
            </strong></div></td>
        <td width="21%"><input type="radio" name="res"  onClick="document.form1.respuesta.value ='M';"></td>
        <td width="14%"><div align="right"><strong><font size="2" face="Arial, Helvetica, sans-serif">Avanzado</font></strong></div></td>
        <td width="21%"><input type="radio" name="res"  onClick="document.form1.respuesta.value ='A';"></td>
      </tr>
    </table>
    <table width="60%" border="1" bgcolor="#EAF1FF">
      <tr> 
        <td width="48%"><div align="right"> 
            <input  type="button"  style="color:#FFFFFF;background-color:#6B8EC6;"name="grabar"  value="Grabar" onClick="guardar()" >
            </div></td>
        <td width="52%"><input  type="button" style="color:#FFFFFF;background-color:#6B8EC6;" name="salir" value="Salir"  onClick="salida()"></td>
      </tr>
    </table>
  </div>
  <p align="center">&nbsp;</p>
  <p align="center">&nbsp;</p>
  <p align="center">&nbsp;</p>
  <!-- datos encuesta -->
  <div align="center">
    <input type="hidden" name="respuesta" >
    <!-- datos de paso para que se vayan al menú correspondiente -->
    <input type="hidden" name="tot_dep" value="<?php echo $tot_dep ;?>">
    <input type="hidden" name="tipo_menu" value="<?php echo $tipo_menu ;?>">
    <input type="hidden" name="totoirs" value="<?php echo $totoirs ;?>">
    <input type="hidden" name="aux_rut" value="<?php echo $aux_rut ;?>">
    <input type="hidden" name="cusuario" value="<?php echo $cusuario ;?>">
    <input type="hidden" name="idusuario" value="<?php echo $idusuario;?>">
    <input type="hidden" name="idfuncionario" value="<?php echo $idfuncionario;?>">
    <input type="hidden" name="flujo_ok" value="<?php echo  $flujo_ok;?>">
    <input type="hidden" name="val_funcionario" value="<?php echo $val_funcionario;?>">
    <input type="hidden" name="val_procedencia" value="<?php echo $val_procedencia ;?>">
    <input type="hidden" name="val_funcionario1" value="<?php echo $val_funcionario1 ;?>">
    <input type="hidden" name="val_destino" value="<?php echo $val_destino ;?>">
    <input type="hidden" name="tipo_procedencia" value="<?php echo $tipo_procedencia;?>">
    <input type="hidden" name="tipo_destino" value="<?php echo $tipo_destino ;?>">
    <input type="hidden" name="num_int" value="<?php echo $num_int ;?>">
    <input type="hidden" name="id_dependencia" value="<?php echo $id_dependencia;?>">
    <input type="hidden" name="tipo_frame" value="<?php echo $tipo_frame ;?>">
  </div>
</form>
</body>
</html>
