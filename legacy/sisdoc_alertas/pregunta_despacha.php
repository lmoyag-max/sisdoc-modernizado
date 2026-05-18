<?php
include("variables.php");
include("conexion_bd.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>

<script language="JavaScript" type="text/javascript">
function despacha_todos() 
	
{
 //	document.form1.submit();
	location.href="nomina.php"  ;
 }
 
 function despacha_algunos() 
	
{
//document.form1.submit();
	location.href="despachar.php"  ;
 }

</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="">
  <table width="500" border="2" cellspacing="2" cellpadding="2">
    <tr>
      <td height="121">
<table width="419" height="47" border="0" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td height="43"> <div align="center"><strong>&iquest;Despacha todos 
                los documentos?</strong></div></td>
          </tr>
        </table>
        <table width="419" border="0" align="center" cellpadding="2" cellspacing="2">
          <tr> 
            <td width="193"><div align="center"> 
                <input type="button" name="cmd_si" value="Si" onclick="despacha_todos();">
              </div></td>
            <td width="212"><div align="center"> 
                <input type="button" name="cmd_no2" value="No" onClick="despacha_algunos();">
              </div></td>
          </tr>
        </table>
        <p>&nbsp;</p></td>
    </tr>
  </table>
  </form>
</body>
</html>
