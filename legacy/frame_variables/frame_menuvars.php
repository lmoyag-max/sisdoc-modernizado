<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Menu TOP</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/estilo_doc.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" background="images/azul2.jpg"  leftmargin="0" topmargin="0">
<?php echo '<form method="post" name="form_menuvars" id="form_menuvars">';?>
<?php echo '<table width="75%" border="0">';?>
<?php echo '<tr>';?> 
<?php echo '<td>idusuario</td>';?>
<?php echo '<td><input name="idusuario" type="text"  value="' . $idusuario . '"></td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?> 
<?php echo '<td>cusuario</td>';?>
<?php echo '<td><input name="cusuario" type="text" id="cusuario" value="' . $cusuario . '"></td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?>
<?php echo '<td>idfuncionario</td>';?>
<?php echo '<td><input name="idfuncionario" type="text" id="idfuncionario" value="' . $idfuncionario . '">';?>
<?php echo '</td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?>
<?php echo '<td>flujook</td>';?>
<?php echo '<td><input name="flujook" type="text" id="flujook" value="' . $flujook . '">';?>
<?php echo '</td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?> 
<?php echo '<td>val_funcionario</td>';?>
<?php echo '<td><input name="val_funcionario" type="text" id="val_funcionario" value="' . $val_funcionario . '">';?>
<?php echo '</td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?>
<?php echo '<td>val_procedencia</td>';?>
<?php echo '<td><input name="val_procedencia" type="text" id="val_procedencia" value="' . $val_procedencia . '">';?>
<?php echo '</td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?> 
<?php echo '<td>val_funcionario1</td>';?>
<?php echo '<td><input name="val_funcionario1" type="text" id="val_funcionario1" value="' . $val_funcionario1 . '">';?>
<?php echo '</td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?> 
<?php echo '<td>val_destino</td>';?>
<?php echo '<td><input name="val_destino" type="text" id="val_destino" value="' . $val_destino . '">';?>
<?php echo '</td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?> 
<?php echo '<td>tipo_procedencia</td>';?>
<?php echo '<td><input name="tipo_procedencia" type="text" id="tipo_procedencia" value="' . $tipo_procedencia . '">';?>
<?php echo '</td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?> 
<?php echo '<td>tipo_destino</td>';?>
<?php echo '<td><input name="tipo_destino" type="text" id="tipo_destino" value="' . $tipo_destino . '">';?>
<?php echo '</td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '<tr>';?>
<?php echo '<td>num_int</td>';?>
<?php echo '<td><input name="num_int" type="text" id="num_int" value="'. $num_int .'">';?>
<?php echo '</td>';?>
<?php echo '<td>&nbsp;</td>';?>
<?php echo '</tr>';?>
<?php echo '</table>';?>
<?php echo '<p>&nbsp; </p>';?>
<?php echo '</form>';?>
  <p>&nbsp;</p>
  <p>&nbsp; </p>
<table width="100%" border="0" background="images/azul2.jpg">
  <tr> 
   	<td width="17">
 <div id="anchorie" style="position:relative; width:0; height:0; left: 4px; top: 19px;"></div>
  <img name="anchorns" src="images/spacer.gif" width="1" height="1" border="0"> </td>
<td width="776"><div align="right"><img src="images/logo40.gif" width="46" height="42"></div></td>
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
</script>
    <script>
/*****************************************************************************
Default browsercheck - Leave this one
******************************************************************************/
function lib_bwcheck(){ //Browsercheck (needed)
	this.ver=navigator.appVersion; this.agent=navigator.userAgent
	this.dom=document.getElementById?1:0
	this.ie5=(this.ver.indexOf("MSIE 5")>-1 && this.dom)?1:0;
	this.ie6=(this.ver.indexOf("MSIE 6")>-1 && this.dom)?1:0;
	this.ie4=(document.all && !this.dom)?1:0;
	this.ie=this.ie4||this.ie5||this.ie6
	this.mac=this.agent.indexOf("Mac")>-1
	this.opera5=this.agent.indexOf("Opera 5")>-1
	this.ns6=(this.dom && parseInt(this.ver) >= 5) ?1:0; 
	this.ns4=(document.layers && !this.dom)?1:0;
	this.bw=(this.ie6 || this.ie5 || this.ie4 || this.ns4 || this.ns6 || this.opera5 || this.dom)
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
oCMenu.menuplacement=new Array(dx+40,dx+115,dx+200,dx+290)
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
oCMenu.makeMenu('top1','','Ingreso','','mainFrame',91,21) //,'images/mnu_ingreso.gif')
        oCMenu.makeMenu('sub10','top1','Nuevo Documento','ingreso_docto1.php?cusuario=<?php echo $cusuario;?>&idusuario=<?php echo $idusuario;?>&idfuncionario=<?php echo $idfuncionario;?>&flujook=<?php echo $flujook;?>&num_int=<?php echo $num_int;?>','mainFrame',100)
		
		
oCMenu.makeMenu('top2','','Consulta','','mainFrame',91,21) //,'images/mnu_consulta.gif')
	oCMenu.makeMenu('sub20','top2','Documentos Recepcionados','encuentra.asp?cbc=159&id_bot=59&leer=1','mainFrame',150)
    oCMenu.makeMenu('sub21','top2','Documentos por Recpecionar','encuentra.asp?cbc=18&id_bot=59&leer=1','mainFrame',150)
    oCMenu.makeMenu('sub22','top2','Documentos en Trámite','encuentra.asp?cbc=34&id_bot=59&leer=1','mainFrame',150)
	oCMenu.makeMenu('sub23','top2','Documentos por Despachar','encuentra.asp?cbc=114&id_bot=59&leer=1','mainFrame',150)


oCMenu.makeMenu('top3','','Informes','','mainFrame',95,21)//,'images/mnu_informes.gif')
	    oCMenu.makeMenu('sub30','top3','Documento en Trámite','muestra_categ.asp?cbc=151&id_bot=60&leer=2','mainFrame',150)
        oCMenu.makeMenu('sub31','top3','Invitaciones','encuentra.asp?cbc=157&id_bot=60&leer=1','mainFrame',150)
        oCMenu.makeMenu('sub32','top3','Documentos Cerrados','encuentra.asp?cbc=149&id_bot=60&leer=1','mainFrame',150)
		
oCMenu.makeMenu('top4','','Ayuda','info.asp?id=178','mainFrame',95,21) //,'images/mnu_ayuda.gif')
	    
	   
//Leave this line - it constructs the menu
                                           
//Leave these two lines! Making the styles and then constructing the menu
oCMenu.makeStyle(); oCMenu.construct()

 			
</script>
  </p>
  

</div>
</body>
</html>

