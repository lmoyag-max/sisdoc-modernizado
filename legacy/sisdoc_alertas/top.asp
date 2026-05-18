<!-- #include file ='log/log.asp'-->
<!-- #include file ='conexion/data_on.asp'-->
<html>
<head>
<title>Ministerio de Salud - Chile</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script>
<!--
function popup(url,ancho,alto,scroll){
newWin=window.open(url,"popup","resize=0,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars="+scroll+",resizable=0,width="+ancho+",height="+alto+",top=0,left=100");
   return;
newWin.focus();
}

//-->
</script>
</head>

<body bgcolor="#00509F" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<center>
  <table width="749" border="0" cellpadding="0" cellspacing="0" align="center">
  <tr> 
    <td width="700"> 
        <table width="700" border="0" cellspacing="0" cellpadding="0" height="77">
          <tr> 
            <td width="420" height="96" valign="top"><img src="images/franja-OK.gif" width="802" height="77" usemap="#Map" border="0"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr> 
      <td align="left" valign="top"> <!--------------------------- ayuda para posicionar menu --------------------->
        <div id="anchorie" style="position:relative; width:0; height:0;"></div>
  <img name="anchorns" src="images/spacer.gif" width="1" height="1" border="0"> 
  <!--------------------------- ayuda para posicionar menu --------------------->
  </td>
  </tr>
</table>
 
  <!---------------------------- LOAD COOLMENUS -------------------------------->
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
oCMenu.fromleft=140 //This is the left position of the menu. (Only in use if menuplacement below is 0 or aligned) (will change to adapt any borders) - Value: px || "%"
oCMenu.fromtop=76 //This is the left position of the menu. (Only in use if menuplacement below is 0 or aligned) (will change to adapt any borders) - Value: px || "%"
oCMenu.pxbetween=0 //How much space you want between each of the top items. - Value: px || "%"
//oCMenu.menuplacement=0 //new Array(dx,dx+115,dx+261,dx+407,dx+554)
oCMenu.menuplacement=new Array(dx+130,dx+215,dx+315,dx+425,dx+530)
//oCMenu.menuPlacement="center"


//TOP LEVEL PROPERTIES - ALL OF THESE MUST BE SPECIFIED FOR LEVEL[0]
oCMenu.level[0]=new Array() //Add this for each new level
oCMenu.level[0].width=110 //The default width for each level[0] (top) items. You can override this on each item by spesifying the width when making the item. - Value: px || "%"
oCMenu.level[0].height=25 //The default height for each level[0] (top) items. You can override this on each item by spesifying the height when making the item. - Value: px || "%"
oCMenu.level[0].bgcoloroff="#00509F" //The default background color for each level[0] (top) items. You can override this on each item by spesifying the backgroundcolor when making the item. - Value: "color"
oCMenu.level[0].bgcoloron="#B0E6FF" //The default "on" background color for each level[0] (top) items. You can override this on each item by spesifying the "on" background color when making the item. - Value: "color"
oCMenu.level[0].textcolor="White" //The default text color for each level[0] (top) items. You can override this on each item by spesifying the text color when making the item. - Value: "color"
oCMenu.level[0].hovercolor="#000066" //The default "on" text color for each level[0] (top) items. You can override this on each item by spesifying the "on" text color when making the item. - Value: "color"
oCMenu.level[0].style="padding:0px; font-family:tahoma,verdana,helvetica; font-size:10px; font-weight:bold" //The style for all level[0] (top) items. - Value: "style_settings"
oCMenu.level[0].border=1 //The border size for all level[0] (top) items. - Value: px
oCMenu.level[0].bordercolor="" //The border color for all level[0] (top) items. - Value: "color"
oCMenu.level[0].offsetX=-130 //The X offset of the submenus of this item. This does not affect the first submenus, but you need it here so it can be the default value for all levels. - Value: px
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

//EXAMPLE SUB LEVEL[2] PROPERTIES - You have to spesify the properties you want different from LEVEL[1] OR LEVEL[0] - If you want all items to look the same just remove this
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

oCMenu.makeMenu('top1','','Ministerio','','mainFrame',100,21)
        oCMenu.makeMenu('sub10','top1','Organización','encuentra.asp?cbc=158&id_bot=68&leer=1','mainFrame',100)
        oCMenu.makeMenu('sub11','top1','Autoridades','encuentra.asp?cbc=15&id_bot=68&leer=1','mainFrame',100)
        oCMenu.makeMenu('sub12','top1','Servicios de Salud','encuentra.asp?cbc=147&id_bot=68&leer=1','mainFrame',100)
		
		
oCMenu.makeMenu('top2','','Programas','','mainFrame',100,21)
	oCMenu.makeMenu('sub20','top2','Comisión Nacional de Lactancia','encuentra.asp?cbc=159&id_bot=59&leer=1','mainFrame',190)
    oCMenu.makeMenu('sub21','top2','Programa del Adolecente','encuentra.asp?cbc=18&id_bot=59&leer=1','mainFrame',190)
    oCMenu.makeMenu('sub22','top2','Programa del Adulto','encuentra.asp?cbc=34&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub23','top2','Programa del Adulto Mayor','encuentra.asp?cbc=114&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub24','top2','Programa Alcohol, Tabaco y Drogas','encuentra.asp?cbc=37&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub25','top2','Programa Ampliado de inmunizaciones','encuentra.asp?cbc=36&id_bot=59&leer=1','mainFrame',190)
    oCMenu.makeMenu('sub26','top2','Programa del Cáncer','encuentra.asp?cbc=38&id_bot=59&leer=1','mainFrame',190)
    oCMenu.makeMenu('sub27','top2','Programa de Enfermedades Cardiovasculares','encuentra.asp?cbc=39&id_bot=59&leer=1','mainFrame',190)
    oCMenu.makeMenu('sub28','top2','Programa de Farmacia','encuentra.asp?cbc=160&id_bot=59&leer=1','mainFrame',190)
    oCMenu.makeMenu('sub29','top2','Programa de Infecciones Respiratorias','encuentra.asp?cbc=161&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub201','top2','Programa de la Mujer','encuentra.asp?cbc=162&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub202','top2','Programa del Niño','encuentra.asp?cbc=163&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub203','top2','Programa de Nutrición','encuentra.asp?cbc=164&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub204','top2','Programa de Salud Mental','encuentra.asp?cbc=165&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub205','top2','Programa de Salud Visual','encuentra.asp?cbc=166&id_bot=59&leer=1','mainFrame',190)
    oCMenu.makeMenu('sub206','top2','Programa Odontológico','encuentra.asp?cbc=167&id_bot=59&leer=1','mainFrame',190)
    oCMenu.makeMenu('sub207','top2','Programa Control de Traumatismo','encuentra.asp?cbc=168&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub208','top2','Programa Control de Tuberculosis','encuentra.asp?cbc=169&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub209','top2','Programa Transplantes','encuentra.asp?cbc=170&id_bot=59&leer=1','mainFrame',190)
	oCMenu.makeMenu('sub210','top2','Programa rehabilitación integral a personas','encuentra.asp?cbc=171&id_bot=59&leer=1','mainFrame',190)






oCMenu.makeMenu('top3','','Temas de Salud','','mainFrame',110,21)
	    oCMenu.makeMenu('sub30','top3','Atención Primaria','muestra_categ.asp?cbc=151&id_bot=60&leer=2','mainFrame',170)
        oCMenu.makeMenu('sub31','top3','Epidemiología','encuentra.asp?cbc=157&id_bot=60&leer=1','mainFrame',170)
        oCMenu.makeMenu('sub32','top3','Vida Sana','encuentra.asp?cbc=149&id_bot=60&leer=1','mainFrame',170)
		oCMenu.makeMenu('sub33','top3','Salud Ambiental','encuentra.asp?cbc=150&id_bot=60&leer=1','mainFrame',170)
		oCMenu.makeMenu('sub34','top3','Estadística','encuentra.asp?cbc=153&id_bot=60&leer=1','mainFrame',170)
		oCMenu.makeMenu('sub35','top3','Conasida','encuentra.asp?cbc=156&id_bot=60&leer=1','mainFrame',170)
		oCMenu.makeMenu('sub36','top3','Inversiones','','mainFrame',170)
		oCMenu.makeMenu('sub37','top3','Emergencias y Desastres','encuentra.asp?cbc=152&id_bot=60&leer=1','mainFrame',170)
		oCMenu.makeMenu('sub38','top3','Campañas de Salud','encuentra.asp?cbc=27&id_bot=7','mainFrame',170)
		
oCMenu.makeMenu('top4','','Sitios Relacionados','info.asp?id=178','mainFrame',115,21)
	    
oCMenu.makeMenu('top5','','Contáctenos','contactenos/ingreso.asp','mainFrame',100,21)
	   
//Leave this line - it constructs the menu
                                           
//Leave these two lines! Making the styles and then constructing the menu
oCMenu.makeStyle(); oCMenu.construct()

 			
</script>
</center>
<map name="Map">
  <area shape="rect" coords="138,24,290,48" href="principal.htm" target="_parent">
</map>
</body>
</html>
