/*  echo "origen " . $origenproc . "proc" . $proc ;
  if ($fechadoc <> '')
   { 
   $fechadoc =substr($fechadoc,0,2).'/'.substr($fechadoc,3,2) .'/' . substr($fechadoc,6,4);
   $query="Select a.materia,a.id_documento from documento  a, tramite b  where a.id_tipo_documento =" . $tipodoc . " and a.num_externo=" .$externo . " and a.fecha_documento=". "'" . $fechadoc."'" . " and a.id_documento=b.id_documento  and b.tipo_procedencia='E' and id_seguimiento in (select min(id_seguimiento) from tramite  where id_documento=a.id_documento)" ;
   }
   else 
   {
     $query="Select a.materia,a.id_documento  from documento  a, tramite b  where a.id_tipo_documento =" . $tipodoc . " and a.num_externo=" .$externo .  " and a.id_documento=b.id_documento  and b.tipo_procedencia='E'  and id_seguimiento in (select min(id_seguimiento) from tramite  where id_documento=a.id_documento)" ;
   }
  echo "consulta" . $query; 
  $rs_query = mssql_query($query, $cn);
  echo "<script>\n";
  if (mssql_num_rows($rs_query)==0)
  {   echo "alert('No existe documento origen  ');";     } 
  else 
  { $rsdoc = mssql_fetch_array($rs_query);
    $mat=$rsdoc["materia"];
	$iddoc=$rsdoc["id_documento"];
   //echo " parent.mainFrame.document.form2.materia2.value='" . $mat ."';\n";
    echo " parent.mainFrame.document.form2.iddoc2.value='" . $iddoc ."';\n";
  }
  echo "</script>\n";
 */