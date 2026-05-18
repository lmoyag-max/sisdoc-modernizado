
<?php 
include("conexion_bd.php");
// verificando si el usuario es ejecutivo de facturas 
$usu_fact="select * from perfiles_facturas  where id_usuario=". $idusuario;
$r_f=mssql_query($usu_fact);
$tot =mssql_num_rows($r_f);

//echo "***funcionario:" . $idfuncionario . " ***cusuario:" . $cusuario . " **** idusuario:" . $idusuario . " **** id_dependencia:" . $id_dependencia;
	include("frame_menu_ofpartes_datos.php");
	$num_verde=count($array_verde);
	$num_amarillo=count($array_amarillo);
	$num_rojo=count($array_rojo);
	
	 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Menu TOP</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
<script>
	function actualiza(){
		parent.frames("mainFrame").location="lista_doc2.php?temp=1&cusuario=<?php echo $cusuario; 
		 ?>&idusuario=<?php echo $idusuario; ?>&idfuncionario=<?php echo $idfuncionario; ?>&txtagno=<?php echo $txtagno; ?>&tipo_frame=<?php echo $tipo_frame; 
		 ?>&num_pag=1";
	}
	

	function agrega(){
		var s1 = document.getElementById('dep');
		var id_dep_acceso = parent.id_dep_acceso;
		var nom_dep_acceso = parent.nom_dep_acceso;
		
		for (i=0;i<id_dep_acceso.length;i++){
			var nuevo = document.createElement('option');
			nuevo.value = id_dep_acceso[i];
			nuevo.text = nom_dep_acceso[i];
			if (id_dep_acceso[i]=="<?php echo $id_dependencia ?>"){
				nuevo.selected=true;
			}
			s1.add(nuevo);
		}
	}
	
	function cambio(){
		var s1 = document.getElementById('dep');
		indice = s1.selectedIndex;
		valor = s1.options[indice].value;
		//alert("indice: "+indice+" valor: "+valor);		
		location.href = 'frame_menuvars.php?idusuario=<?php echo $idusuario ?>&cusuario=<?php echo $cusuario ?>&idfuncionario=<?php echo $idfuncionario ?>&flujook=<?php echo $flujook ?>&val_funcionario=<?php echo $val_funcionario ?>&val_procedencia=<?php echo $val_procedencia ?>&val_funcionario1=<?php echo $val_funcionario1 ?>&val_destino=<?php echo $val_destino ?>&tipo_procedencia=<?php echo $tipo_procedencia ?>&tipo_destino=<?php echo $tipo_destino ?>&idfuncionario=<?php echo $idfuncionario ?>&num_int=<?php echo $num_int ?>&id_dependencia=' + valor + '&tipo_frame=<?php echo $tipo_frame ?>';
		parent.mainFrame.location = 'frame_vistas.php';
	}
	
</script>
</head>

<body onLoad="agrega();" bgcolor="#FFFFFF" background="images/azul2.jpg"  leftmargin="0" topmargin="0">
<table width="100%" border="0" background="images/azul2.jpg">
  <tr> 
     <td width="17%">
        <div id="anchorie" style="position:relative; width:0; height:0; left: 4px; top: 19px;"></div>
     </td>
     <td width="60%" align="right">
	<?php //echo $idusuario." dep:". $id_dependencia; ?>
	 <select id="dep" name="dep" onChange="cambio();">
	 </select>
     </td>
     <td width="12%">
		 <table border="0" width="100%">
		 <tr>
		 <td bgcolor="#66FF00" width="33%" align="center"><a href="lista_doc2.php?temp=1&cusuario=<?php echo $cusuario; 
		 ?>&idusuario=<?php echo $idusuario; ?>&idfuncionario=<?php echo $idfuncionario; ?>&txtagno=<?php echo $txtagno; ?>&tipo_frame=<?php echo $tipo_frame; 
		 ?>&num_pag=1" target="mainFrame"><?php echo $num_verde; ?></a>
		 </td>
		 <td bgcolor="#FFFF00" width="33%" align="center"><a href="lista_doc2.php?temp=2&cusuario=<?php echo $cusuario; 
		 ?>&idusuario=<?php echo $idusuario; ?>&idfuncionario=<?php echo $idfuncionario; ?>&txtagno=<?php echo $txtagno; ?>&tipo_frame=<?php echo $tipo_frame; 
		 ?>&num_pag=1" target="mainFrame"><?php echo $num_amarillo; ?></a>
		 </td>
		 <td bgcolor="#FF0000" width="33%" align="center"><a href="lista_doc2.php?temp=3&cusuario=<?php echo $cusuario; 
		 ?>&idusuario=<?php echo $idusuario; ?>&idfuncionario=<?php echo $idfuncionario; ?>&txtagno=<?php echo $txtagno; ?>&tipo_frame=<?php echo $tipo_frame; 
		 ?>&num_pag=1" target="mainFrame" ><?php echo $num_rojo; ?></a>
		 </td>
		 </tr>
	   </table>
     </td>
     <td width="3%">
        <div align="right"> 
        <!--img src="images/logo40.gif" width="46" height="42"-->
        <img src="imagen/logo.jpg" width="40" height="27"></div>
     </td>
     <td width="8%">
        <div align="left" ><a href="../salir.php" ><img src="images/boton_salir.gif"  alt="Salir" width="26" height="26" onMouseOver="this.style.cursor='hand';"></a></div>
    </td>
</tr>
</table>
                            
<div align="left"> 
  <!---------------------------- LOAD COOLMENUS -------------------------------->
  <p> 
    <script language=JavaScript1.2 src="js/coolmenus3.js"></script>
    <!---------------------------- CONFIGURE MENU -------------------------------->
    <script>
/*****************************************************************************
Copyright (c) 2001 Thomas Brattli (www.bratta.com)

eXperience DHTML coolMenus - Get it at www.bratta.com
Version 3.02
This script can be used freely as long as all copyright messages are
intact. 
******************************************************************************/
	var cusuario='<?php echo $cusuario;?>';
	var idusuario=<?php echo $idusuario;?>;
	var idfuncionario=<?php echo $idfuncionario;?>;
<?php 
/*********************************array verde***********************/
	echo "var array_verde=new Array(";
	for ($i=0;$i<$num_verde-1;$i++){
		echo "\"".$array_verde[$i]."\",";
	}
		echo "\"".$array_verde[$i]."\"); \n";
		
	echo "var verde_idseg=new Array(";
	for ($i=0;$i<$num_verde-1;$i++){
		echo "\"".$verde_idseg[$i]."\",";
	}
		echo "\"".$verde_idseg[$i]."\"); \n";
		
	echo "var verde_nomina=new Array(";
	for ($i=0;$i<$num_verde-1;$i++){
		echo "\"".$verde_nomina[$i]."\",";
	}
		echo "\"".$verde_nomina[$i]."\"); \n";
		
	echo "var verde_desc_tipo_doc=new Array(";
	for ($i=0;$i<$num_verde-1;$i++){
		echo "\"".$verde_desc_tipo_doc[$i]."\",";
	}
		echo "\"".$verde_desc_tipo_doc[$i]."\"); \n";

	echo "var verde_num_externo=new Array(";
	for ($i=0;$i<$num_verde-1;$i++){
		echo "\"".$verde_num_externo[$i]."\",";
	}
		echo "\"".$verde_num_externo[$i]."\"); \n";

	echo "var verde_fecha_documento=new Array(";
	for ($i=0;$i<$num_verde-1;$i++){
		echo "\"".$verde_fecha_documento[$i]."\",";
	}
		echo "\"".$verde_fecha_documento[$i]."\"); \n";

	echo "var verde_materia=new Array(";
	for ($i=0;$i<$num_verde-1;$i++){
		echo "\"".$verde_materia[$i]."\", \n";
	}
		echo "\"".$verde_materia[$i]."\"); \n";

	echo "var verde_num_interno=new Array(";
	for ($i=0;$i<$num_verde-1;$i++){
		echo "\"".$verde_num_interno[$i]."\",";
	}
		echo "\"".$verde_num_interno[$i]."\"); \n";

	echo "var verde_origen=new Array(";
	for ($i=0;$i<$num_verde-1;$i++){
		echo "\"".$verde_origen[$i]."\",";
	}
		echo "\"".$verde_origen[$i]."\"); \n";

/*****************************************************************/
		
/*********************************array amarillo***********************/
	echo "var array_amarillo=new Array(";
	for ($i=0;$i<$num_amarillo-1;$i++){
		echo "\"".$array_amarillo[$i]."\",";
	}
		echo "\"".$array_amarillo[$i]."\"); \n";
		
	echo "var amarillo_idseg=new Array(";
	for ($i=0;$i<$num_amarillo-1;$i++){
		echo "\"".$amarillo_idseg[$i]."\",";
	}
		echo "\"".$amarillo_idseg[$i]."\"); \n";
		
	echo "var amarillo_nomina=new Array(";
	for ($i=0;$i<$num_amarillo-1;$i++){
		echo "\"".$amarillo_nomina[$i]."\",";
	}
		echo "\"".$amarillo_nomina[$i]."\"); \n";
		
	echo "var amarillo_desc_tipo_doc=new Array(";
	for ($i=0;$i<$num_amarillo-1;$i++){
		echo "\"".$amarillo_desc_tipo_doc[$i]."\",";
	}
		echo "\"".$amarillo_desc_tipo_doc[$i]."\"); \n";

	echo "var amarillo_num_externo=new Array(";
	for ($i=0;$i<$num_amarillo-1;$i++){
		echo "\"".$amarillo_num_externo[$i]."\",";
	}
		echo "\"".$amarillo_num_externo[$i]."\"); \n";

	echo "var amarillo_fecha_documento=new Array(";
	for ($i=0;$i<$num_amarillo-1;$i++){
		echo "\"".$amarillo_fecha_documento[$i]."\",";
	}
		echo "\"".$amarillo_fecha_documento[$i]."\"); \n";

	echo "var amarillo_materia=new Array(";
	for ($i=0;$i<$num_amarillo-1;$i++){
		echo "\"".$amarillo_materia[$i]."\",";
	}
		echo "\"".$amarillo_materia[$i]."\"); \n";

	echo "var amarillo_num_interno=new Array(";
	for ($i=0;$i<$num_amarillo-1;$i++){
		echo "\"".$amarillo_num_interno[$i]."\",";
	}
		echo "\"".$amarillo_num_interno[$i]."\"); \n";

	echo "var amarillo_origen=new Array(";
	for ($i=0;$i<$num_amarillo-1;$i++){
		echo "\"".$amarillo_origen[$i]."\",";
	}
		echo "\"".$amarillo_origen[$i]."\"); \n";

/*****************************************************************/

/*********************************array rojo***********************/
	echo "var array_rojo=new Array(";
	for ($i=0;$i<$num_rojo-1;$i++){
		echo "\"".$array_rojo[$i]."\",";
	}
		echo "\"".$array_rojo[$i]."\"); \n";
		
	echo "var rojo_idseg=new Array(";
	for ($i=0;$i<$num_rojo-1;$i++){
		echo "\"".$rojo_idseg[$i]."\",";
	}
		echo "\"".$rojo_idseg[$i]."\"); \n";
		
	echo "var rojo_nomina=new Array(";
	for ($i=0;$i<$num_rojo-1;$i++){
		echo "\"".$rojo_nomina[$i]."\",";
	}
		echo "\"".$rojo_nomina[$i]."\"); \n";
		
	echo "var rojo_desc_tipo_doc=new Array(";
	for ($i=0;$i<$num_rojo-1;$i++){
		echo "\"".$rojo_desc_tipo_doc[$i]."\",";
	}
		echo "\"".$rojo_desc_tipo_doc[$i]."\"); \n";

	echo "var rojo_num_externo=new Array(";
	for ($i=0;$i<$num_rojo-1;$i++){
		echo "\"".$rojo_num_externo[$i]."\",";
	}
		echo "\"".$rojo_num_externo[$i]."\"); \n";

	echo "var rojo_fecha_documento=new Array(";
	for ($i=0;$i<$num_rojo-1;$i++){
		echo "\"".$rojo_fecha_documento[$i]."\",";
	}
		echo "\"".$rojo_fecha_documento[$i]."\"); \n";

	echo "var rojo_materia=new Array(";
	for ($i=0;$i<$num_rojo-1;$i++){
		echo "\"".$rojo_materia[$i]."\",";
	}
		echo "\"".$rojo_materia[$i]."\"); \n";

	echo "var rojo_num_interno=new Array(";
	for ($i=0;$i<$num_rojo-1;$i++){
		echo "\"".$rojo_num_interno[$i]."\",";
	}
		echo "\"".$rojo_num_interno[$i]."\"); \n";

	echo "var rojo_origen=new Array(";
	for ($i=0;$i<$num_rojo-1;$i++){
		echo "\"".$rojo_origen[$i]."\",";
	}
		echo "\"".$rojo_origen[$i]."\"); \n";

/*****************************************************************/
?>	
function tama_letra(tamagno)
{
    for(d=0;d<top.window.mainFrame.document.all.length;++d)
    {
       cadena=top.window.mainFrame.document.all.item(d).id;
       if(cadena.substring(0,9)!='divoCMenu') {
         top.window.mainFrame.document.all.item(d).style.fontSize=tamagno;
       }
    }

}


function baja_letra()
{
    tamanum=document.form_menuvars.tama_letra.value;
    if(tamanum>11)
    {
      tamanum=tamanum--;
      document.form_menuvars.tama_letra.value=tamanum;
      tamacad=tamanum+'px';
      for(d=0;d<top.window.mainFrame.document.all.length;++d)
      {
         cadena=top.window.mainFrame.document.all.item(d).id;
         if(cadena.substring(0,9)!='divoCMenu') {
           top.window.mainFrame.document.all.item(d).style.fontSize=tamacad;
         }
      }
    }  
}

function sube_letra()
{
    tamanum=document.form_menuvars.tama_letra.value;
    if(tamanum<24)
    {
      tamanum=tamanum++;
      document.form_menuvars.tama_letra.value=tamanum;
      tamacad=tamanum+'px';
      for(d=0;d<top.window.mainFrame.document.all.length;++d)
      {
         cadena=top.window.mainFrame.document.all.item(d).id;
         if(cadena.substring(0,9)!='divoCMenu') {
           top.window.mainFrame.document.all.item(d).style.fontSize=tamacad;
         }
      }
    }  
}


</script>
    <script class="boton_buscar">
/*****************************************************************************
Default browsercheck - Leave this one
******************************************************************************/

function lib_bwcheck(){ //Browsercheck (needed)
	this.ver=navigator.appVersion; 
	this.agent=navigator.userAgent;
	this.dom=document.getElementById?1:0
	this.ie5=(this.ver.indexOf("MSIE 5")>-1 && this.dom)?1:0;
	this.ie6=(this.ver.indexOf("MSIE 6")>-1 && this.dom)?1:0;
	this.ie7=(this.ver.indexOf("MSIE 7")>-1 && this.dom)?1:0; // new line
	this.ie4=(document.all && !this.dom)?1:0;
	this.ie=this.ie4||this.ie5||this.ie6||this.ie7 // new line
	this.mac=this.agent.indexOf("Mac")>-1
	this.opera5=this.agent.indexOf("Opera 5")>-1
	this.ns6=(this.dom && parseInt(this.ver) >= 5) ?1:0; 
	this.ns4=(document.layers && !this.dom)?1:0;
	this.bw=(this.ie7 || this.ie6 || this.ie5 || this.ie4 || this.ns4 || this.ns6 || this.opera5 || this.dom) // new line
	return this
}
var bw=new lib_bwcheck() //Making browsercheck object

var dx=0;
var dy=0;
if (bw.ns4) {
var d = document.images["anchorns"]
dx = d.x
dy = d.y
}

var dx=+10;
var dy=0;
if (bw.ie5) {
var d = document.all["anchorie"]
dx = d.offsetLeft
dy = d.offsetTop
}

var mDebugging=2 //General debugging variable. Set to 0 for no debugging, 1 for alerts or 2 for status debugging.
oCMenu=new makeCoolMenu("oCMenu") //Making the menu object. Argument: menuname
oCMenu.useframes=1 //Do you want to use the menus as coolframemenu or not? (in frames or not) - Value: 0 || 1
oCMenu.frame="mainFrame" //The name of your main frame (where the menus should appear). Leave empty if you're not using frames - Value: "main_frame_name"
oCMenu.useclick=0 //If you want the menu to be activated and deactivated onclick only set this to 1. - Value: 0 || 1
oCMenu.useNS4links=1  
oCMenu.NS4padding=2 //After adding the "hover effect" for netscape as well, all styles are lost. But if you want padding add it here.
oCMenu.checkselect=1 //If you have select boxes close to your menu the menu will check for that and hide them if they are in the way of the menu.
oCMenu.offlineUrl="file:///C|/Inetpub/wwwroot/dhtmlcentral/" //Value: "path_to_menu_file_offline/"
oCMenu.onlineUrl="http://www.dhtmlcentral.com/coolmenus/examples/withframes/" //Value: "path_to_menu_file_online/"
oCMenu.pagecheck=1 //Do you want the menu to check whether any of the subitems are out of the bouderies of the page and move them in again (this is not perfect but it hould work) - Value: 0 || 1
oCMenu.checkscroll=1 //Do you want the menu to check whether the page have scrolled or not? For frames you should always set this to 1. You can set this to 2 if you want this feature only on explorer since netscape doesn't support the window.onscroll this will make netscape slower (only if not using frames) - Value: 0 || 1 || 2
oCMenu.resizecheck=1 //Do you want the page to reload if it's resized (This should be on or the menu will crash in Netscape4) - Value: 0 || 1
oCMenu.wait=1000 //How long to wait before hiding the menu on mouseout. Netscape 6 is a lot slower then Explorer, so to be sure that it works good enough there you should not have this lower then 500 - Value: milliseconds

//Background bar properties
oCMenu.usebar=1 //If you want to use a background-bar for the top items set this on - Value: 1 || 0
oCMenu.barcolor="" //The color of the background bar - Value: "color"
//oCMenu.barWidth="100%"
oCMenu.barwidth="menu" //The width of the background bar. Set this to "menu" if you want it to be the same width as the menu. (this will change to match the border if you have one) - Value: px || "%" || "menu"
oCMenu.barheight="menu" //The height of the background bar. Set this to "menu" if you want it to be the same height as the menu. (this will change to match the border if you have one) - Value: px || "%" || "menu"
oCMenu.barx="menu" //The left position of the bar. Set this to "menu" if you want it be the same as the left position of the menu. (this will change to match the border if you have one)  - Value: px || "%" || "menu"
oCMenu.bary="menu" //The top position of the bar Set this to "menu" if you want it be the same as the top position of the menu. (this will change to match the border if you have one)  - Value: px || "%" || "menu"
oCMenu.barinheritborder=0 //Set this to 1 if you want the bar to have the same border as the top menus - Value: 0 || 1

//Placement properties
oCMenu.rows=1 //This controls whether the top items is supposed to be laid out in rows or columns. Set to 0 for columns and 1 for row - Value 0 || 1
oCMenu.fromleft=50//This is the left position of the menu. (Only in use if menuplacement below is 0 or aligned) (will change to adapt any borders) - Value: px || "%"
oCMenu.fromtop=25 //This is the left position of the menu. (Only in use if menuplacement below is 0 or aligned) (will change to adapt any border??†??† †??A?is) - Value: px || "%"
oCMenu.pxbetween=0 //How much space you want between each of the top items. - Value: px || "%"
//oCMenu.menuplacement=0 //new Array(dx,dx+115,dx+261,dx+407,dx+554)
oCMenu.menuplacement=new Array(dx+40,dx+115,dx+200,dx+290,dx+370)
//oCMenu.menuPlacement="center"


//TOP LEVEL PROPERTIES - ALL OF THESE MUST BE SPECIFIED FOR LEVEL[0]
oCMenu.level[0]=new Array() //Add this for each new level
oCMenu.level[0].width=110 //The default width for each level[0] (top) items. You can override this on each item by spesifying the width when making the item. - Value: px || "%"
oCMenu.level[0].height=25 //The default height for each level[0] (top) items. You can override this on each item by spesifying the height when making the item. - Value: px || "%"
oCMenu.level[0].bgcoloroff="#0057E6" //The default background color for each level[0] (top) items. You can override this on each item by spesifying the backgroundcolor when making the item. - Value: "color"
oCMenu.level[0].bgcoloron="#0057E6" //The default "on" background color for each level[0] (top) items. You can override this on each item by spesifying the "on" background color when making the item. - Value: "color"
oCMenu.level[0].textcolor="White" //The default text color for each level[0] (top) items. You can override this on each item by spesifying the text color when making the item. - Value: "color"
oCMenu.level[0].hovercolor="#FFFF00" //The default "on" text color for each level[0] (top) items. You can override this on each item by spesifying the "on" text color when making the item. - Value: "color"
oCMenu.level[0].style="padding:0px; font-family:tahoma,verdana,helvetica; font-size:10px; font-weight:bold" //The style for all level[0] (top) items. - Value: "style_settings"
oCMenu.level[0].border=1 //The border size for all level[0] (top) items. - Value: px
oCMenu.level[0].bordercolor="" //The border color for all level[0] (top) items. - Value: "color"
oCMenu.level[0].offsetX=-10 //The X offset of the submenus of this item. This does not ??†??† †???????affect the first submenus, but you need it here so it can be the default value for all levels. - Value: px
oCMenu.level[0].offsetY=0 //The Y offset of the submenus of this item. This does not affect the first submenus, but you need it here so it can be the default value for all levels. - Value: px
oCMenu.level[0].NS4font="trebuchet ms,arial,helvetica"
oCMenu.level[0].NS4fontSize="2"

/*New: Added animation features that can be controlled on each level.*/
oCMenu.level[0].clip=0 //Set this to 1 if you want the submenus of this level to "slide" open in a animated clip effect. - Value: 0 || 1
oCMenu.level[0].clippx=0 //If you have clip spesified you can set how many pixels it will clip each timer in here to control the speed of the animation. - Value: px 
oCMenu.level[0].cliptim=0 //This is the speed of the timer for the clip effect. Play with this and the clippx to get the desired speed for the clip effect (be carefull though and try and keep this value as high or possible or you can get problems with NS4). - Value: milliseconds
//Filters - This can be used to get some very nice effect like fade, slide, stars and so on. EXPLORER5.5+ ONLY - If you set this to a value it will override the clip on the supported browsers
oCMenu.level[0].filter=0 //VALUE: 0 || "filter specs"

oCMenu.level[0].align="bottom" //Value: "top" || "bottom" || "left" || "right" 

//EXAMPLE SUB LEVEL[1] PROPERTIES - You have to spesify the properties you want different from LEVEL[0] - If you want all items to look the same just remove this
oCMenu.level[1]=new Array() //Add this for each new level (adding one to the number)
oCMenu.level[1].width=0 //150
oCMenu.level[1].height=20
oCMenu.level[1].style="padding:2px; font-family:trebuchet ms, arial,helvetica; font-size:10px; font-weight:bold"
oCMenu.level[1].align="bottom" 
oCMenu.level[1].offsetX=-(oCMenu.level[0].width-2) // +50
oCMenu.level[1].offsetY=0
oCMenu.level[1].border=1 
oCMenu.level[1].bordercolor="#000066"

//EXAMPLE SUB LEVEL[2] PROPERTIES - You have to spesify ??†??† †???????the properties you want different from LEVEL[1] OR LEVEL[0] - If you want all items to look the same just remove this
oCMenu.level[2]=new Array() //Add this for each new level (adding one to the number)
oCMenu.level[2].width=0 // 150
oCMenu.level[2].height=20           
oCMenu.level[2].style="padding:2px; font-family:tahoma,arial,helvetica; font-size:10px; font-weight:bold"
oCMenu.level[2].align="bottom" 
oCMenu.level[2].offsetX=-(oCMenu.level[1].width-10) // -50
oCMenu.level[2].offsetY=0
oCMenu.level[2].border=1 
oCMenu.level[2].bordercolor="#336666"
oCMenu.level[2].NS4font="trebuchet ms,arial,helvetica"
oCMenu.level[2].NS4fontSize="1"


/******************************************
Menu item creation:
myCoolMenu.makeMenu(name, parent_name, text, link, target, width, height, regImage, overImage, regClass, overClass , align, rows, nolink, onclick, onmouseover, onmouseout) 
*************************************/
//cm_makeMenu(name,parent,text,link,target,width,height,img1,img2,bgcoloroff,bgcoloron,textcolor,hovercolor,onclick,onmouseover,onmouseout)
oCMenu.makeMenu('top1','','Ingresar','','mainFrame',91,21) //,'images/mnu_ingreso.gif')
	   oCMenu.makeMenu('sub10','top1','Ingreso Documento','ingreso_docto2.php?cusuario=<?php echo $cusuario;	?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int;?>&num_exp=<?php echo 0;?>','mainFrame',150)
      //oCMenu.makeMenu('sub10','top1','Nuevo Documento','ingreso_docto1.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int;?>','mainFrame',150)
	//  oCMenu.makeMenu('sub11','top1','Ingreso Oficina de partes','ingreso_ofpartes_k.php?cusuario=<?php echo $cusuario;	?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int;?>&num_exp=<?php echo 0;?>&sw_ext=0','mainFrame',150)
		//oCMenu.makeMenu('sub11','top1','Documento Of. de Partes','ingreso_ofpartes.php?cusuario=<?php echo $cusuario;?>
		//&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>
		//&num_int=<?php echo $num_int;?>&sw_ext=0','mainFrame',150)
		oCMenu.makeMenu('sub12','top1','Modificación de Documento','modifica_docto.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int;?>','mainFrame',150)		
		oCMenu.makeMenu('sub13','top1','Modificación de Tramite','busca_tramites.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>','mainFrame',150)		
	  //oCMenu.makeMenu('sub14','top1','oficina de partes con expediente','ingreso_ofpartes_k.php?cusuario=<?php echo $cusuario;	?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int;?>&num_exp=<?php echo 0;?>&sw_ext=0','mainFrame',150)

//oCMenu.makeMenu('sub14','top1','ingreso_expediente','ingreso_docto2.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int;?>&num_exp=<?php echo 0;?>','mainFrame',150)

oCMenu.makeMenu('top2','','Gestionar','','mainFrame',91,21) //,'images/mnu_consulta.gif')
        oCMenu.makeMenu('sub20','top2','Documentos por Recepcionar','multi_recep.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>','mainFrame',150)
	    oCMenu.makeMenu('sub21','top2','Documentos Recepcionados','multi_recib.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>','mainFrame',150)
        oCMenu.makeMenu('sub23','top2','Documentos Derivados','busca_docto.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&sw_cons=<?php echo 0;?>
		&flujook=<?php echo $flujook;?>','mainFrame',
150)
	       //oCMenu.makeMenu('sub22','top2','Derivar Facturas con Docto','respuesta_multiple.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&num_int=<?php echo $num_int;?>&flujook=<?php echo $flujook;?>','mainFrame',150)

		oCMenu.makeMenu('sub24','top2','Documentos por Despachar','multi_pages.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>','mainFrame',
150)
 oCMenu.makeMenu('sub25','top2','Documentos a relacionar','muestra_documento_a_relacionar.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int?>&sw_cons=<?php echo 1;?>&avanza=<?php echo 1;?>','mainFrame',150)		


oCMenu.makeMenu('top3','','Consultar','','mainFrame',95,21)//,'images/mnu_informes.gif')
      
      oCMenu.makeMenu('sub30','top3','Búsqueda Avanzada de Documentos','busca_docto.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int?>&sw_cons=<?php echo 1;?>&avanza=<?php echo 0;?>&avanzada=<?php echo 1;?>','mainFrame',200)
      oCMenu.makeMenu('sub31','top3','Búsqueda por Nómina','buscanomina.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>
		','mainFrame',200)
      oCMenu.makeMenu('sub030','top3','Búsqueda Global de Documentos','busca_docto.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int?>&sw_cons=<?php echo 1;?>&avanza=<?php echo 1;?>','mainFrame',200)		
		oCMenu.makeMenu('sub32','top3','Expedientes','busca_expediente2.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&opcion=1','mainFrame',200)
		oCMenu.makeMenu('sub33','top3','Invitaciones','buscainvitacion.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&sw_fecha=<?php echo 0;?>
		','mainFrame',200)
		 oCMenu.makeMenu('sub34','top3','Documentos Pendientes','busca_pend.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>
		','mainFrame',200)
		oCMenu.makeMenu('sub35','top3','Busqueda de Documentos Cerrados','busca_docto.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>
		&num_int=<?php echo $num_int?>&sw_cons=<?php echo 2;?>','mainFrame',200)
		oCMenu.makeMenu('sub36','top3','Gestión de documentos','busca_gestion.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>
		','mainFrame',200)
		 oCMenu.makeMenu('sub37','top3','Busquedas Tramites Despachados','busca_tramites_despachados.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>
		','mainFrame',200)
		oCMenu.makeMenu('sub38','top3','Facturas Pendientes','busca_pend_fact.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>
		','mainFrame',200)
	
		
oCMenu.makeMenu('top4','','Eliminar','','mainFrame',95,21)//,'images/mnu_informes.gif')
	    oCMenu.makeMenu('sub40','top4','Eliminar Documentos','busca_docto_el.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>
		&num_int=<?php echo $num_int?>&sw_cons=<?php echo 1;?>','mainFrame',200)
		oCMenu.makeMenu('sub41','top4','Eliminar Trámites','busca_docto_el.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>
		&num_int=<?php echo $num_int?>&sw_cons=<?php echo 2;?>','mainFrame',200)
	      
	      <?php	
	      if ($tot==1)
		{?>
	   	oCMenu.makeMenu('top5','','Módulo Facturas','','mainFrame',95,200)//,'images/mnu_informes.gif')
	    	oCMenu.makeMenu('sub51','top5','Ingreso Facturas','ingreso_facturas.php?cusuario=<?php echo $cusuario;	?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo 0;?>&num_exp=<?php echo 0;?>','mainFrame',200)
		oCMenu.makeMenu('sub52','top5','Modificación Factura','modifica_factura.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int;?>','mainFrame',200)		
		oCMenu.makeMenu('sub53','top5','Modificación Tramite de factura','busca_tramites_facturas.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>','mainFrame',200)		
	    	oCMenu.makeMenu('sub54','top5','Eliminar Factura','busca_factura_el.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>
		&num_int=<?php echo $num_int?>&sw_cons=<?php echo 1;?>','mainFrame',200)
		oCMenu.makeMenu('sub55','top5','Eliminar Trámites Facturas','busca_factura_el.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>
		&num_int=<?php echo $num_int?>&sw_cons=<?php echo 2;?>','mainFrame',200)
		oCMenu.makeMenu('sub56','top5','Consulta Facturas','busca_factura_prueba.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int?>&sw_cons=<?php echo 1;?>&avanza=<?php echo 1;?>','mainFrame',200)
	    	oCMenu.makeMenu('sub57','top5','Facturas por Despachar','multi_pages_facturas.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>','mainFrame',200)
	   	oCMenu.makeMenu('sub58','top5','Buscar facturas en alertas','busca_documento_alertas_facturas1.php?cusuario=<?php echo $cusuario;	?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo 0;?>&num_exp=<?php echo 0;?>&id_tema=<?php echo $id_tema;?>','mainFrame',200)
       		oCMenu.makeMenu('sub59','top5','Buscar nomina','buscanomina_facturas.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>','mainFrame',200)
	   	oCMenu.makeMenu('sub60','top5','Busqueda de Facturas Cerradas','busca_factura.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>
		&num_int=<?php echo $num_int?>&sw_cons=<?php echo 2;?>','mainFrame',200)
		oCMenu.makeMenu('sub61','top5','Facturas a relacionar','relacionar_facturas.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int;?>&sw_cons=<?php echo 1;?>&menucons=<?php echo 6;?>&avanza=<?php echo 1;?>','mainFrame',200)		

	       	    	
		    <?} 
		  if ($tot ==0)
		  {?>
	    oCMenu.makeMenu('top5','','Módulo Facturas','','mainFrame',95,200)//,'images/mnu_informes.gif')
	 	oCMenu.makeMenu('sub53','top5','Modificación Tramite de factura','busca_tramites_facturas.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>','mainFrame',200)		
		oCMenu.makeMenu('sub54','top5','Eliminar Trámites Facturas','busca_factura_el.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>
		&num_int=<?php echo $num_int?>&sw_cons=<?php echo 2;?>','mainFrame',200)
	    oCMenu.makeMenu('sub55','top5','Consulta Facturas','busca_factura_prueba.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int?>&sw_cons=<?php echo 1;?>&avanza=<?php echo 1;?>','mainFrame',200)
	    oCMenu.makeMenu('sub56','top5','Facturas por Despachar','multi_pages_facturas.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>','mainFrame',200)
        oCMenu.makeMenu('sub57','top5','Buscar nomina','buscanomina_facturas.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>','mainFrame',200)
		oCMenu.makeMenu('sub58','top5','Busqueda de Facturas Cerradas','busca_factura.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>
		&num_int=<?php echo $num_int?>&sw_cons=<?php echo 2;?>','mainFrame',200)
	   <?}
	   ?>		  	
	
		
		//oCMenu.makeMenu('top4','','Ayuda','ayuda.htm','mainFrame',95,21) //,'images/mnu_ayuda.gif')

/*
oCMenu.makeMenu('top5','','Fuentes','','mainFrame',95,51) //,'images/mnu_ayuda.gif')
	    oCMenu.makeMenu('sub50','top5','Tamaño de Fuente 8px','','mainFrame',150,'','','','','','','','tama_letra("8px")')
            oCMenu.makeMenu('sub51','top5','Tamaño de Fuente 11px','','mainFrame',150,'','','','','','','','tama_letra("11px")')
	    oCMenu.makeMenu('sub52','top5','Fuente Normal','','mainFrame',150,'','','','','','','','tama_letra("14px")')
            oCMenu.makeMenu('sub53','top5','Tamaño de Fuente 16px','','mainFrame',150,'','','','','','','','tama_letra("16px")')
            oCMenu.makeMenu('sub54','top5','Tamaño de Fuente 19px','','mainFrame',150,'','','','','','','','tama_letra("19px")')
            oCMenu.makeMenu('sub55','top5','Tamaño de Fuente 21px','','mainFrame',150,'','','','','','','','tama_letra("21px")')
	    oCMenu.makeMenu('sub56','top5','Tamaño de Fuente 23px','','mainFrame',150,'','','','','','','','tama_letra("23px")')
 */                                             //text                    lk  target,    w    h i1,i2,bf,bo,tx,hc.''

            
//cm_makeMenu(name,parent,text,link,target,width,height,img1,img2,bgcoloroff,bgcoloron,textcolor,hovercolor,onclick,onmouseover,onmouseout){

	    
	   
//Leave this line - it constructs the menu
                                           
//Leave these two lines! Making the styles and then constructing the menu
oCMenu.makeStyle(); oCMenu.construct()

 			
</script>
  </p>
  

</div>
<?php
$variable1 = $var1;
$variable2 = $var2;
$variable3 = $var3;
$variable4 = $var4;
$variable5 = $var5;
$variable6 = $var6;
$variable7 = $var7;
$variable8 = $var8;
$variable9 = $var9;
$variable10 = $var10;
$variable11 = $var11;
echo '<form method="post" name="form_menuvars" id="form_menuvars">';
echo '<table width="75%" border="0">';
echo '<tr>';
echo '<td></td>';
echo '<td><input name="variable1" type="hidden" id="variable1" value="' . $variable1 . '"></td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>'; 
echo '<td></td>';
echo '<td><input name="variable2" type="hidden" id="variable2" value="' . $variable2 . '"></td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input name="variable3" type="hidden" id="variable3" value="' . $variable3 . '">';
echo '</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input name="variable4" type="hidden" id="variable4" value="' . $variable4 . '">';
echo '</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input name="variable5" type="hidden" id="variable5" value="' . $variable5 . '">';
echo '</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input name="variable6" type="hidden" id="variable6" value="' . $variable6 . '">';
echo '</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input name="variable7" type="hidden" id="variable7" value="' . $variable7 . '">';
echo '</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input name="variable8" type="hiAdden" id="variable8" value="' . $variable8 . '">';
echo '</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input name="variable9" type="hidden" id="variable9" value="' . $variable9 . '">';
echo '</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input name="variable10" type="hidden" id="variable10" value="' . $variable10 . '">';
echo '</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '<tr>';
echo '<td><input name="tam_letra" type="hidden" id="tama_letra" value="16"></td>';
echo '<td><input name="variable11" type="hidden" id="variable11" value="'. $variable11 .'">';
echo '</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';
echo '</table>';
echo '<p>&nbsp; </p>';
echo '</form>';
?>
</body>
</html>

