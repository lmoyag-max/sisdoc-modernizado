<?php 

$re_query="Select txt_comentario from comentario where id_documento =$id_doc ";
$rs_re  = mssql_query($ref_query);
$rs_comen = mssql_fetch_array($rs_re);
$comentario=$rs_comen[txt_comentario];
echo "comentario " . $comentario;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Documento sin t&iacute;tulo</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>

</body>
</html>
