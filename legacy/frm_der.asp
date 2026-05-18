
<%@ Language="VBScript" %>
<!-- #include file ='conexion/data_on.asp'-->
<html>
<%

Set Base = Server.CreateObject("ADODB.Connection")
Base.open "g3", "sa", "sqlminsal"

'Base.Open ("DSN=DB-MINSAL;UID=sa;pwd=sqlminsal")

set RS = Server.CreateObject("adodb.recordset")
set RS_news = Server.CreateObject("adodb.recordset")

sql = "select max(i.id) id_news from info i, clas_info c where i.id = c.id and c.id_clas = 56 " 
RS.open sql,Base

id_noticia = rs.fields("id_news")

RS.close
sql = "select fec_publ, titulo, cuerpo from info where id = " & id_noticia 
RS_news.open sql,Base

%>
<head>
<title>Ministerio de Salud - CHILE</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script src="js/coolmenus_frame.js" type="text/javascript"></script>
<script language="JavaScript">
<!--
// de noticia links encuentra.asp?cbc=56&id_bot=67
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
function popup(url,ancho,alto,scroll){
newWin=window.open(url,"popup","resize=0,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars="+scroll+",resizable=0,width="+ancho+",height="+alto+",top=0,left=100");
   return;
newWin.focus();
}

//-->
</script>
</head>
<STYLE type=text/css>

A { COLOR: blue; FONT-FAMILY: verdana,arial,helvetica,sans-serif; FONT-SIZE: 11px; TEXT-DECORATION: none
}

A:hover { COLOR: red; TEXT-DECORATION: none
}
</style>
<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" onLoad="MM_preloadImages('images/botones/bd_02_over.gif')">
<table border="0" cellpadding="0" cellspacing="1">
  <tr> 
    <td><img src="images/noticia_over.gif" width="122" height="22"></td>
  </tr>
</table>
<table width="130" border="0" cellpadding="0" cellspacing="1" bordercolor="#006699">
  <tr>
    <td height="38"> 
      <div align="left">
        <p><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><a href="info.asp?cbc=56&id=<%=id_noticia%>" target="mainFrame"><%=RS_news.fields("titulo")%></a> 
          </font> </p>
        <p align="right"><a href="encuentra.asp?cbc=56&leer=1" target="mainFrame"><img src="images/masnew_over.gif" width="110" height="20" border="0"></a></p>
      </div>
      </td>
  </tr>
</table>

<table width="100%" border="0" cellpadding="0">
  <tr> 
    <td> 
      <div align="left"><a href="http://epi.minsal.cl/epi/html/vigilan/sars/sars.htm" target="_blank"><img src="images/natipica.jpg" width="120" height="35" border="0"></a></div>
    </td>
  </tr>
  <tr> 
    <td><a href="encuentra.asp?cbc=135&id_bot=52" target="mainFrame"><img src="images/utilidad.jpg" width="121" height="44" border="0"></a></td>
  </tr>
  <tr> 
    <td><img src="images/congreso.jpg" width="120" height="50" border="0"></td>
  </tr>
  <tr> 
    <td><a href="info.asp?cbc=198&id=363" target="mainFrame"><img src="images/oferta_ani.gif" width="121" height="50" border="0"></a></td>
  </tr>
  <tr> 
    <td><a href="cuenta_publica.htm" target="mainFrame"><img src="images/cpublica.jpg" width="121" height="50" border="0"></a></td>
  </tr>
  <tr> 
    <td><a href="info.asp?id=357" target="mainFrame"><img src="images/prevencion.jpg" width="120" height="90" border="0"></a></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>
