<?php
	include("conexion_bd.php");
	

	$rs ="SELECT     (SELECT     dias_compromiso
                       FROM          tramite
                       WHERE      (id_documento = '".$iddoc."')
AND id_seguimiento IN (
SELECT MIN(id_seguimiento) FROM tramite WHERE (id_documento = '".$iddoc."') 
)					   
					   ) -
                          (SELECT     COUNT(tipo_dia) AS cantidad
                            FROM          calendario c
                            WHERE      (fecha BETWEEN
                                                       (SELECT     fecha_sistema
                                                         FROM          tramite
                                                         WHERE      id_documento = '".$iddoc."'
AND id_seguimiento IN (
SELECT MIN(id_seguimiento) FROM tramite WHERE (id_documento = '".$iddoc."') 
)
														 ) AND GETDATE()) AND (tipo_dia = 'H')
                            GROUP BY tipo_dia) AS Expr1";

	$rs0 =mssql_query($rs);
	$rs1=mssql_fetch_row($rs0);
	$dias_compromiso=$rs1[0];
/* 	echo "     dias:".$dias_compromiso."<br>";
	echo "     f_hoy:".$fecha_x."<br>";
	echo "     rs0:".$rs."<br>";
 */ ?>
 