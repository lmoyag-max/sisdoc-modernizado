<?php
include("variables.php");
include("conexion_bd.php");
include("carga_tablas.php");
global $Confidencial;
global $fun;

$usua=$cusuario;
$xx=$idusuario;
$iddoc=$iddocum;
$idseg=$idseguim; 
$idfunc=$idfuncionario;
$flag = 2 ;
$id_func_proc=0;
$id_proc=0;
$val_funcionario=0;
$val_funcionario1=0;
$dedonde=$origen;
$dep=5;
echo "usuario" . $idusuario;
// $txtagnor  arrastra el año  ingreado en documentos recepcionados  y $txtnomina la nomina de documentos recepcionados //
$fecha_x = date("d-m-Y");

//echo "idusu" . $idusuario . "*** usu " . $cusuario . "** acc " . $accion . "** fun " . $idfuncionario . "** docu " . $iddocum . "** seg " . $idseguim ;
$rs_funcionario = mssql_query("SELECT id_dependencia FROM funcionario where id_dependencia = " . $dep ." and vigencia is null", $cn);

$nrowsfunc= mssql_num_rows($rs_funcionario);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?php $iddocum; ?>< Edicion></title>
<script language="JavaScript" type="text/javascript">

var sw_multiple = 0;
var sw_func    =0 ;
var sw_variosfunc =0;
var cont_arreglo;
var cont_arreglo1;
var z=0;
var arreglo2 ="";
var arreglo1="";
var arreglo3="";
var arreglo4="";
var ar_descrip =new Array();

function ver_func()
{

if(document.form1.radiodestino[0].checked==true )
	{
	document.form1.tipo_destino.value ="I";
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
    //alert ("f    " + document.form1.fun.value);
   MM_showHideLayers('LayerFunc','','show');
	}
document.form1.val_funcionario1.value=0;
sw_func = 1;	
}
function muestra(cod)
{
z=0;
 {ar_descrip[z]= cod;
 z=z+1;
 }
}       

function ver_check(filas) 
{
  var x=0;
  if(document.form1.radiodestino[0].checked==true)
  {
  for (k=0;k<filas;k++)
  {
     if (document.form1.casilla2[k].checked)
     {
	  x=x+1;
	 }
  }
  }
  else
  if(document.form1.radiodestino[1].checked==true)
  {
  for (k=0;k<filas;k++)
  {
     if (document.form1.casilla3[k].checked)
     {
	 x=x+1;
	 }
  }
  }
  if (x!=0)
  {	
	document.form1.Cbo_Func_Destino.disabled=true;
	document.form1.Cbo_Destinatario.disabled=true;
  }
  else
  {
	document.form1.Cbo_Func_Destino.disabled=false;
	document.form1.Cbo_Destinatario.disabled=false;
  }
}

function ver_checkfunc(filas) 
{
/
  var x=0;

  for (k=0;k<filas;k++)
  {
     if (document.form1.casilla4[k].checked)
     {
	  x=x+1;
	 }
  }
}

</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!---
if (parseInt(navigator.appVersion) > 3) {
	if (navigator.appName == "Netscape") {
		layerVar="document.layers";
		styleVar="";
	}else{
		layerVar="document.all";
		styleVar=".style";
	}
}

function funcion()
{
  //MM_showHideLayers('layer_com','','show');
  MuestraEsconde('LayerFunc','visible');
}

function MuestraEsconde(LaLayer,ElAtributo) {
	if (parseInt(navigator.appVersion) > 3) {
		eval(layerVar + '["' + LaLayer + '"]' + styleVar + '.visibility = "' + ElAtributo + '"');
	}
}
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_showHideLayers() { //v6.0
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}
//-->
</script>
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" link="#CCCCCC" topmargin="0" >
<form name="form1" method="post" >
<center>
    <table width="51%" border="1" cellspacing="0" cellpadding="0">
      <tr> 
        <td width="508" height="93"> 
		  <table width="100%" border="0" cellspacing="1" cellpadding="1">
            <tr> 
              <!-- variable destino viene del programa de l grabar  que trae el ultimo  destino que se ha seleccionado en caso que sea con opcion de ambos -->
              <td width="180" height="207"><font face="Arial"> 
                <table width="267%" border="1" bgcolor="#E6EEFF">
                  <tr> 
                    <td height="23">
					 <div align="left" onClick="MM_showHideLayers('LayerFunc','','hide')";"ver_checkfunc(<?php echo $nrowsfunc;?>)"> 
                        <strong>Aceptar</strong></div></td>
                  </tr>
                  <tr> 
                    <td height="159"> 
                      <?php 
					  $k=0;echo $val_destino ;
					  $funci = mssql_query("SELECT * FROM funcionario where id_dependencia = " . $dep ." and vigencia is null order by nombres,apellidos", $cn);
					  while($reg_funci = mssql_fetch_array($funci)) { ?>
                      <input type="checkbox" name="casilla4" value="<?php echo $reg_funci["id_dependencia"];?>" onClick="javascript:muestra(<?php echo $reg_funci["id_dependencia"];?>);"> 
                      <?php echo trim($reg_funci["nombres"] ) ." " . trim($reg_funci["apellidos"] ). "<br>"; }?> 
                    </td>
                  </tr>
                </table>
                
                </font> </td>
            </tr>
          </table></td>
      </tr>
    </table>
  </center>
  </form>
</body>
</html>
