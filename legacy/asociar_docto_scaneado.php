<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;
$idseg=$idseguim;
$fun=$idfuncionario;
$flujo = 8;
$numint=0;
$nombre_pantalla="";
$dedonde=$origen;
$txtnomina =$txtnomina;
$iddocum=$iddocum;
$fecha_x = date("d-m-Y");
$archivos_asociados="exec documento_scanner '" . $iddoc ."'";
$archi =mssql_query($archivos_asociados, $cn);
$registro=mssql_fetch_array($archi);
$rs_documento="exec documento_referencia '" . $iddoc . "','" . $idseg . "'";
$qq = mssql_query($rs_documento,$cn); 
$rs=mssql_fetch_array($qq);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language=javascript>
<!--
function valida_cadena(cadena,objeto)
{ //-----------------------------
 
    var i;
    var car_permit;
    var retorno;
    retorno = true;
    car_permit = "abcdefghijklmnñopqrstuvwxyz-_0123456789().# ";
    cadena = cadena.toLowerCase(cadena);
    for ( i=0; i < cadena.length; i++) { 
    if (car_permit.indexOf(cadena.charAt(i)) < 0) {
	    retorno = false; }
    }  
        
    if (!retorno)
    {
       alert("El archivo tiene caracteres inválidos, no debe ir con acento");
       
	   //objeto.focus();
    }
    return retorno;
} 

function SacaNombreArchivo(archivo)
{
  var ultimoSlash;// la posicion de el último slash in la ruta completa
  var nombreArchivo;  // el nombre del archivo
  var separador; 
  ultimoSlash = archivo.lastIndexOf('\\');
  nombreArchivo = archivo.substring(ultimoSlash+1,archivo.length);
  return nombreArchivo;
} // SacaNombreArchivo 
 
function sube_imagen()
{	
        var subira = false;
        var subext = true;
        var archivo = document.subiendo.UploadedFile.value;
        var extension = "";
        var largo = archivo.length;
        if (largo>4) {
          extension = archivo.substr(largo-4,4);
          extension = extension.toUpperCase();
        } else subext = false;

        subira = true;
        if(archivo == "") { 
          subira = false;
          //alert('Debe especificar un doc (DOC) antes de hacer click en "Subir"');
          alert('Debe especificar un documento(JPG) antes de hacer click en "Agregar Archivo"');
        }
        nombreArchivo=SacaNombreArchivo(archivo);
		if (subira && !valida_cadena(nombreArchivo,''))
		{
			subira= false;
		}
        /*if(subira && (!subext || extension!=".DOC")) { 
          subira = false;
          alert('Debe especificar un documento WORD (.DOC)\n No es soportada la extensión '+extension);
        }*/
       if (subira) 
	   {
	 /*     if (document.subiendo.txtdocumento.value=="")
		   {
		    alert("Debe ingresar una breve descripción del documento");
		   }
		  else 	
		   {
		*/  
		   document.subiendo.submit(); 
		   //}
		   
		}
}
function salir()
{

document.subiendo.action="busca_documentos_a_scanear.php";
document.subiendo.submit();
}


//-->
</script>

<title>Asociar documento  scaneado</title>



<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0">
<center>
<form name="subiendo" ENCTYPE="multipart/form-data" ACTION="subir.php" METHOD=POST>
    <table width="650" border="1" cellpadding="2" cellspacing="0" bgcolor="#3399FF">
      <tr>
        <td><div align="center"><font color="#FFFFFF" size="4"><b>ASOCIA DOCUMENTO CON ARCHIVO</b></font></div></td>
      </tr>
    </table>
    <table width="650" border="1" cellpadding="1" cellspacing="0" bgcolor="#e6eeff">
      <tr> 
        <td bgcolor="#cadbff"> <table width="100%" border="0" cellspacing="1" cellpadding="2">
            <tr bgcolor="#e6eeff"> 
              <td height="15" colspan="5"><font color="#7777FF"><strong>INFORMACION 
                DOCUMENTO DE REFERENCIA</strong></font></td>
              <td height="15"><div align="right"><strong><font color="#0000A0" size="1"><? echo "Usuario : " . $cusuario?></font></strong></div></td>
            </tr>
            <tr> 
              <td width="129" height="15"><font color="#804040"><b>Tipo de Docto</b> 
                </font></td>
              <td width="171" height="15" > <font color="#804040"><? echo $rs[desc_tipo_documento]; ?> 
                </font></td>
              <td width="72" height="15"><font color="#804040"><strong>N&ordm; 
                Interno</strong> <b></b></font></td>
              <td height="15"> <font color="#804040"><font color="#804040"><? echo $rs[num_interno];?></font> 
                </font></td>
              <td height="15"><font color="#804040"><b>Medio</b></font></td>
              <td height="15"><font color="#804040"> 
                <? 
                If($rs["medio"]=="P")
                {
		   		echo "Papel";
				}
				else
				if ($rs["medio"]=="C")
				{
		   		echo "Copia";
		 		}
				else
				if ($rs["medio"]=="F")
		    	{
		    	echo "Fax";
		    	}   
				else
		 		{
	 		    echo "Video";
		 		}
		 		?>
                </font> </td>
            </tr>
            <tr> 
              <td width="129" height="18"><font color="#804040"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></font></td>
              <td width="171" height="18"> <font color="#804040"> 
                <?php $fec_doc=strtotime($rs["fecha_documento"]);
		             $fech_doc=date("d/m/Y",$fec_doc);
     				echo $fech_doc;?>
                </font></td>
              <td width="72" height="18"><font color="#804040"><b>N&ordm; Oficial<font size="4" face="Arial"> 
                </font></b></font></td>
              <td width="80" height="18"> <font color="#804040"><?php echo $rs[num_oficial];?> 
                </font></td>
              <td width="56"><font color="#804040"><b>Original</b></font></td>
              <td width="105"><font color="#804040"><font color="#804040"><? echo $rs[original];?></font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr valign="middle"> 
              <td width="128" height="18"><font color="#804040"><b>Procedencia</b> 
                </font></td>
              <td width="172" height="18"><font color="#804040"><? echo $rs[procedencia];?><b></b></font></td>
              <td width="73" height="18"> <font color="#804040"><strong>N&ordm; 
                Externo </strong></font></td>
              <td width="250" height="18"><font color="#804040"><font size="4" face="Arial"> 
                </font><font color="#804040"> </font><font color="#804040"><? echo $rs[num_externo]; ?></font><font size="4" face="Arial"> 
                </font></font></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="2" cellspacing="1">
            <tr> 
              <td width="128" height="22"><font color="#804040"><b>Materia</b> 
                </font></td>
              <td width="505"> <font color="#804040"> <? echo $rs[materia];?> 
                </font></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td> <table width="100%" border="0">
            <tr> 
              <td height="29"><font color="#7777FF"><strong>ASOCIAR DOCUMENTO</strong></font></td>
            </tr>
          </table>
          <table width="100%" height="110" border="1" cellpadding="1" cellspacing="0">
            <tr> 
              <td height="108"> <table width="100%" border="0">
                  <tr> 
                    <td width="31%" height="29"><font color="#7777FF"><strong>BUSCAR 
                      ARCHIVO</strong></font><br> <br> </td>
                    <td width="69%"><INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="1000000"> 
                      <INPUT NAME="UploadedFile" size="35" TYPE="file" ></td>
                  </tr>
                </table>
                <p>&nbsp;</p>
                <p>&nbsp;</p></td>
            </tr>
          </table>
          <table border="1">
            <tr> 
              <td height="54" colspan="2"><strong>Archivos Subidos :</strong> 
                <font color="#804040"><? echo $registro["archivo"];?></font> </td>
            </tr>
            <tr> 
              <td width="351"><div align="center"> 
                  <INPUT TYPE="button" VALUE="Agregar Archivo" onclick="sube_imagen();">
                  <input type="hidden" name="id_documento"   value="<? echo $iddocum;?>">
				  <input type="hidden" name="idseguim"       value="<? echo $idseg;?>">
                  <input type="hidden" name="idusuario"      value="<? echo $idusuario;?>">
                  <input type="hidden" name="cusuario"       value="<? echo $cusuario;?>">
                  <input type="hidden" name="idfuncionario"  value="<? echo $idfuncionario;?>">
                  <input type="hidden" name="Txt_fecha_fin"  value="<? echo $Txt_fecha_fin;?>">
                  <input type="hidden" name="Txt_fecha_ini"  value="<? echo $Txt_fecha_ini;?>">
                  <input type="hidden" name="destino"        value="<? echo $destino;?>">
                  <input type="hidden" name="cbo_esc_dest"   value="<? echo $cbo_esc_dest  ;?>">
                  <input type="hidden" name="Cbo_Tipo_Docto" value="<? echo $Cbo_Tipo_Docto;?>">
                  <input type="hidden" name="TxtInterno"     value="<? echo $TxtInterno;?>">
                  <input type="hidden" name="TxtExterno"     value="<? echo $TxtExterno;?>">
                  <input type="hidden" name="TxtOficial"     value="<? echo $TxtOficial;?>">
                </div></td>
              <td width="283" align="right"> 
			     <INPUT TYPE="button" VALUE="Salir"   onclick="salir();">
			  </td>
            </tr>
          </table></td>
      </tr>
    </table>
    </form>
  </center>
</body>
</html>
