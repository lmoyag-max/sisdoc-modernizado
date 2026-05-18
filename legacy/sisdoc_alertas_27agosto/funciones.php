<?
function cuenta_rows($registro) {

    $nRows = mssql_num_rows($registro);
	return $nRows;
}


?>