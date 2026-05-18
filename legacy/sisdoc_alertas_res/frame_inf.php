<?php
global $cn;
	$cn = mssql_connect("bd-minsal", "sa", "sqlminsal") or die("$svr won't talk to me!");
	mssql_select_db("sisdoc");


$rs_dependencia = mssql_query("SELECT * FROM dependencia ", $cn);

?> 

<SCRIPT  language="JavaScript">
<!--
function cambio()
{
var selindice, nuevalsel;

selindice = document.form1.cbo_dependencia.selectedIndex;
nuevasel = document.form1.cbo_dependencia.options[selindice].value;
parent.frames[0].location.href="frame_sup.php?cod_dep="+ nuevasel;

}
//-->
</script>

<html>
<head>
<title>Frame Inferior</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#0000FF" text="#000000">
<p>inferior</p>
<form name="form1" method="post" action="">
  <table width="75%" border="1">
    <tr> 
      <td width="33%">&nbsp;</td>
      <td width="15%"> 
        <p> Dependencia </p>
        <p> funcionario </p>
      </td>
      <td width="52%"> 
        <p> 
          <select name="cbo_dependencia" onChange="javascript:cambio()">
            <option value=0> </option>
            <?php 
		  while($reg_dependencia = mssql_fetch_array($rs_dependencia)) { ?>
            <option value=<?php echo $reg_dependencia[id_dependencia];?>> 
            <?php echo $reg_dependencia["desc_dependencia"] ?>
            </option>
            <?php } ?>
          </select>
        </p>
        <p> 
          <select name="cbo_funcionario">
           
          </select>
        </p>
      </td>
    </tr>
    <tr> 
      <td width="33%">&nbsp;</td>
      <td width="15%">dato 1</td>
      <td width="52%"> 
        <input type="text" name="campo1">
      </td>
    </tr>
    <tr> 
      <td width="33%">&nbsp;</td>
      <td width="15%">dato 2</td>
      <td width="52%"> 
        <textarea name="camp2"></textarea>
      </td>
    </tr>
    <tr>
      <td width="33%">&nbsp;</td>
      <td width="15%">dato 3</td>
      <td width="52%">
        <select name="combo_cualquiera">
          <option value="a">aaaaaaaaaaaa</option>
          <option value="b">bbbbbbbbbbbbb</option>
          <option value="c">cccccccccc</option>
          <option value="d">dddddddddddd</option>
          <option value="e">eeeeeee</option>
          <option value="f">ffffffffff</option>
        </select>
      </td>
    </tr>
  </table>
  </form>
</body>
</html>
