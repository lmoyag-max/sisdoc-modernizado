var uagent;	var opsystem;var IE4B=false;	var NS4B=false;	var NS6B=false;	var OP5B=false;	var AOLB=false;var MsWinS=false;	var MacOS=false;	var ULinS=false;
var majorver;	majorver = parseInt(navigator.appVersion);if(majorver>=4){}uagent = window.navigator.userAgent.toLowerCase();opsystem = window.navigator.platform.toLowerCase();
if (opsystem.indexOf('win') != -1)MsWinS = true;else if (opsystem.indexOf('mac') != -1)MacOS = true;else if (opsystem.indexOf('unix') != -1 || opsystem.indexOf('linux') != -1 || opsystem.indexOf('sun') != -1)
ULinS = true;NS4B=((document.layers)?true:false);IE4B=((document.all)?true:false);NS6B=((document.getElementById)&&(!IE4B))?true:false;OP5B=(uagent.indexOf('Opera') != -1)?true:false;if(OP5B==true){NS6B=true;IE4B=false;}
if ((uagent.indexOf('aol')) != -1 )AOLB=true;var sttus=false;var cdobj=null;var ns6scroll=0;
function smenuhm(){if(sttus){
if(IE4B){dmenud.style.pixelLeft=-210;dmenud2.style.pixelLeft=0;document.menubr.src="menu1.gif";}
else if(NS4B){document.dmenud.visibility = "hide";document.dmenud2.pageX=0;
document.dmenud.pageY=60;document.dmenud2.document.menubr.src="menu1.gif";}
else if(NS6B){cdobj=document.getElementById('dmenud');cdobj2=document.getElementById('dmenud2');
cdobj.style.left="-210px";cdobj2.style.left="0px";document.menubr.src="menu1.gif";}
sttus=false;}
else{
if(IE4B){dmenud.style.pixelLeft=0;dmenud2.style.pixelLeft=212;document.menubr.src="menu2.gif";}
else if(NS4B){document.dmenud.visibility = "show";document.dmenud.pageX=0;
document.dmenud2.pageX=212;document.dmenud2.document.menubr.src="menu2.gif";}
else if(NS6B){cdobj=document.getElementById('dmenud');cdobj2=document.getElementById('dmenud2');
cdobj.style.left="0px";cdobj2.style.left="212px";document.menubr.src="menu2.gif";}
sttus=true;}
}
function zscrollf(){wscrollf();setTimeout('zscrollf()',500);}
function wscrollf(){
if(IE4B){	dmenud.style.pixelTop=document.body.scrollTop+60;	dmenud2.style.pixelTop=document.body.scrollTop+60;}
else if(NS4B){	document.dmenud.top=window.pageYOffset+60;	document.dmenud2.top=window.pageYOffset+60;}
	else if(NS6B)	{if(ns6scroll!=(window.pageYOffset+40)){		cdobj=document.getElementById('dmenud');		cdobj.style.top=window.pageYOffset+60+"px";		cdobj2=document.getElementById('dmenud2');		cdobj2.style.top=window.pageYOffset+60+"px";ns6scroll=window.pageYOffset+60;}	}
}

function dmover(hobj,idstr){
	if(IE4B)	{		hobj.className='dhmcssover';		hobj.style.cursor = 'hand';	}
	else if(NS6B)	{		cobj2=document.getElementById(''+idstr);		cobj2.className='dhmcssover';cobj2.style.cursor='pointer';	}
}
function dmdown(hobj,idstr,lstr,trg){if(trg==''){trg='_self';}
	if(IE4B)	{		hobj.className='dhmcssdown';window.open(''+lstr,''+trg);	}
	else if(NS6B)	{		cobj2=document.getElementById(''+idstr);		cobj2.className='dhmcssdown';window.open(''+lstr,''+trg);	}
}
function dmout(hobj,idstr){	if(IE4B)	{		hobj.className='dhmcss';	}
	else if(NS6B)	{		cobj2=document.getElementById(''+idstr);		cobj2.className='dhmcss';	}
}


if(IE4B||NS6B){document.write('<div id="dmenud" name="dmenud" style="position:absolute;overflow:hidden;background:#7F7F7F;left:0; top:60;width:210;">');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td1" class=dhmcss  onmouseover="dmover(this,\'td1\')" onmousedown="dmdown(this,\'td1\',\'http://www.scriptocean.com\',\'_self\')" onmouseout="dmout(this,\'td1\')">&nbsp; Inicio</div>');document.write('</div>');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td2" class=dhmcss  onmouseover="dmover(this,\'td2\')" onmousedown="dmdown(this,\'td2\',\'http://www.scriptocean.com/flashn.html\',\'_self\')" onmouseout="dmout(this,\'td2\')">&nbsp; Ingreso Nuevo Documento</div>');document.write('</div>');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td3" class=dhmcss  onmouseover="dmover(this,\'td3\')" onmousedown="dmdown(this,\'td3\',\'#\',\'_self\')" onmouseout="dmout(this,\'td3\')">&nbsp; CONSULTA</div>');document.write('</div>');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td4" class=dhmcss  onmouseover="dmover(this,\'td4\')" onmousedown="dmdown(this,\'td4\',\'http://www.scriptocean.com/dticker.html\',\'_self\')" onmouseout="dmout(this,\'td4\')">&nbsp; Documentos Recepcionados</div>');document.write('</div>');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td5" class=dhmcss  onmouseover="dmover(this,\'td5\')" onmousedown="dmdown(this,\'td5\',\'http://www.scriptocean.com/flashscroll/index.html\',\'_self\')" onmouseout="dmout(this,\'td5\')">&nbsp; Documentos por  Recepcionados</div>');document.write('</div>');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td6" class=dhmcss  onmouseover="dmover(this,\'td6\')" onmousedown="dmdown(this,\'td6\',\'http://www.scriptocean.com/hmenu.html\',\'_self\')" onmouseout="dmout(this,\'td6\')">&nbsp; Documentos en Trámite</div>');document.write('</div>');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td7" class=dhmcss  onmouseover="dmover(this,\'td7\')" onmousedown="dmdown(this,\'td7\',\'#\',\'_self\')" onmouseout="dmout(this,\'td7\')">&nbsp; INFORMES</div>');document.write('</div>');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td8" class=dhmcss  onmouseover="dmover(this,\'td8\')" onmousedown="dmdown(this,\'td8\',\'http://www.scriptocean.com/menu/index.html\',\'_self\')" onmouseout="dmout(this,\'td8\')">&nbsp; Documentos en Trámite</div>');document.write('</div>');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td9" class=dhmcss  onmouseover="dmover(this,\'td9\')" onmousedown="dmdown(this,\'td9\',\'http://www.scriptocean.com/popmenu.html\',\'_self\')" onmouseout="dmout(this,\'td9\')">&nbsp; Documentos Cerrados</div>');document.write('</div>');
document.write('<div style="position:relative;width:208;left:0; top:0;margin:1px;">');document.write('<div id="td10" class=dhmcss  onmouseover="dmover(this,\'td10\')" onmousedown="dmdown(this,\'td10\',\'http://www.scriptocean.com/text.html\',\'_self\')" onmouseout="dmout(this,\'td10\')">&nbsp; Invitaciones</div>');document.write('</div>');

document.write('</div>');
document.write('<div id="dmenud2" name="dmenud2" style="position:absolute;left:212; top:60;width:20;"><a href="javascript:smenuhm()" onfocus=this.blur()> <img src="menu1.gif" name="menubr" width=20 border="0"></a></div>');}

function gsol(){document.dmenud.visibility = "hide";document.dmenud2.pageX=0;}
function strt(){	if(IE4B)	{dmenud.style.pixelLeft=-210;dmenud2.style.pixelLeft=0;window.onscroll = wscrollf;	}
else if(NS6B){cdobj=document.getElementById('dmenud');cdobj.style.left="-210px";cdobj2=document.getElementById('dmenud2');cdobj2.style.left="0px";zscrollf();}
}setTimeout('strt()',1000);
