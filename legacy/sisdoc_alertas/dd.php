<?php 
include ("conexion_bd.php");
 //sacando datos enviados a la subsecretaria de salud 
 
 $registros ="select a.id_documento,a.num_interno,a.num_oficial,a.num_externo,".  
"        f.desc_tipo_documento,a.materia,a.fecha_documento,b.fecha_despacho,fecha_recepcion,b.id_nomina_despacho,          ".
 "         b.id_seguimiento,b.id_procedencia,b.id_destino,    ".
  "     procedencia=             ".
   "    case  b.tipo_procedencia   ".          
    "      when 'I' then             ".
     "          (select desc_dependencia from dependencia where  b.id_procedencia=id_dependencia)            ".
      "    else            ".
       "        (select desc_dependencia_externa  from dependencia_externa where  b.id_procedencia=id_dependencia_externa)            ".
"          end,".
 "      funcprocedencia=".
  "        case when b.rut_procedencia =' ' or b.rut_procedencia='0' then     ''    ".
   "          else ".
	"	(select max(rtrim(funcionario.nombres)+ ' ' + rtrim(funcionario.apellidos))".
	"	from funcionario where ".
	"	b.rut_procedencia =funcionario.rut )".
"      End,".
"	   destino=           ".
 "      case  b.tipo_destinatario    ".
  "        when 'I' then             ".
  "            (select desc_dependencia from dependencia where  b.id_destino=id_dependencia)            ".
   "       else            ".
    "           (select desc_dependencia_externa  from dependencia_externa where  b.id_destino=id_dependencia_externa)            ".
    "      end,".
    "   funcdestino=".
    "      case when b.rut_destino =' ' or b.rut_destino='0' then     ''    ".
    "         else ".
"		(select max(rtrim(funcionario.nombres)+ ' ' + rtrim(funcionario.apellidos))".
"		from funcionario where ".
"		b.rut_destino =funcionario.rut )".
 "      End,b.observaciones,g.desc_estado_tramite            ".
       
  "        from documento  a,  tramite b,  tipo_documento f,estado_tramite g ".
   "       where  a.id_documento= b.id_documento   ".
    "             and b.id_estado_tramite=g.id_estado_tramite   ".
     "           and  f.id_tipo_documento= a.id_tipo_documento    ".        
      "          and  (b.id_estado_tramite = 3 or b.id_estado_tramite=6 )".
                
		"		 and (b.fecha_despacho between '01/01/2009' and '01/01/2010')".
 " and  id_destino in (2,95,173) and tipo_destinatario='I'    ".
  " and  (b.id_estado_tramite = 3 or b.id_estado_tramite=6 )".
                

   " order by b.fecha_despacho,f.desc_tipo_documento  ";
   
   $reg=mssql_query($registros);
   $r =mssql_num_rows($reg);
 // echo "total filas ". $r; 
   $doc='';
   while ($reg2=mssql_fetch_array($reg))
	  {   
	      $dest="select * from tramite  where id_seguimiento in (select min(id_seguimiento) from tramite where id_documento= ". $reg2[id_documento] . ") and  id_procedencia in(81,5,4,6,89) and tipo_procedencia ='E' ";
		  $reg_dest=mssql_query($dest);
		  $reg_destt=mssql_fetch_array($reg_dest);
		  if (mssql_num_rows($reg_dest) >0)
		     {
			  $doc =$doc . "@" . $reg_destt[id_seguimiento] ;
			 } 
   //buscando hijos
			  $relacion ="select  * from relacion_documento where id_documento= ". $reg2[id_documento] ;
			  $reg_relacion=mssql_query($relacion);
			   
			  if (mssql_num_rows($reg_relacion)>0)
			  {
			    
				while($datos1=mssql_fetch_array($reg_relacion))
				{
				$d1="select * from tramite where id_seguimiento in (select min(id_seguimiento) from tramite where id_documento= ". $datos1[id_documento_hijo] . ") and  id_procedencia in(81,5,4,6,89) and tipo_procedencia ='E' ";
			   $reg_d1=mssql_query($d1);
		       if (mssql_num_rows($reg_d1) >0)
		        {
				 while($datf=mssql_fetch_array($reg_d2))
				{ 
			      $doc =$doc . "@" . $datf[id_seguimiento] ; 
				} 
				}
				}
			  }	
   //buscando padres
			  $relacion2 ="select  * from relacion_documento where id_documento_hijo= ". $reg2[id_documento] ;
			  $reg_relacion2=mssql_query($relacion2);
			  if (mssql_num_rows($reg_relacion2)>0)
			  {
			   while($datos2=mssql_fetch_array($reg_relacion2))
				{ 
			   $d2="select * from tramite where id_seguimiento in (select min(id_seguimiento) from tramite where id_documento= ". $datos2[id_documento] . ") and  id_procedencia in(81,5,4,6,89) and tipo_procedencia ='E' ";
		       $reg_d2=mssql_query($d2);
		       if (mssql_num_rows($reg_d2) >0)
		        {
			   while($datr=mssql_fetch_array($reg_d2))
				{ 
			      $doc =$doc . "@" . $datr[id_seguimiento] ; 
				}
				}
				} 
			  }
			 
	  }
$arreglo = split ("@",$doc); 
 
$largo=25 ;
 for($x=1;$x <=$largo;$x++)
  { 
$final =" select a.id_documento,a.num_interno,a.num_oficial,a.num_externo,   ".
"        f.desc_tipo_documento,a.materia,a.fecha_documento,b.fecha_despacho,fecha_recepcion,b.id_nomina_despacho,          ".
 "         b.id_seguimiento,b.id_procedencia,b.id_destino,    ".
  "     procedencia=             ".
   "    case  b.tipo_procedencia             ".
    "      when 'I' then             ".
     "          (select desc_dependencia from dependencia where  b.id_procedencia=id_dependencia)            ".
     "    else            ".
      "         (select desc_dependencia_externa  from dependencia_externa where  b.id_procedencia=id_dependencia_externa)            ".
       "   end, ".
"       funcprocedencia=".
 "         case when b.rut_procedencia =' ' or b.rut_procedencia='0' then     ''    ".
  "           else ".
	"	(select max(rtrim(funcionario.nombres)+ ' ' + rtrim(funcionario.apellidos)) ".
	"	from funcionario where ".
	"	b.rut_procedencia =funcionario.rut )".
     "  End,".
	  " destino=           ".
"       case  b.tipo_destinatario    ".
    "      when 'I' then             ".
     "          (select desc_dependencia from dependencia where  b.id_destino=id_dependencia)            ".
  "        else            ".
   "            (select desc_dependencia_externa  from dependencia_externa where  b.id_destino=id_dependencia_externa)            ".
 "         end, ".
"       funcdestino=".
      "    case when b.rut_destino =' ' or b.rut_destino='0' then     ''    ".
     "        else ".
	"	(select max(rtrim(funcionario.nombres)+ ' ' + rtrim(funcionario.apellidos))".
	
	"	from funcionario where ".
	"	b.rut_destino =funcionario.rut )".
    "  End,b.observaciones,g.desc_estado_tramite            ".
       
   "       from documento  a,  tramite b,  tipo_documento f,estado_tramite g ".
  "        where  a.id_documento= b.id_documento   ".
"  and f.id_tipo_documento=a.id_tipo_documento ".
"         and g.id_estado_tramite =b.id_estado_tramite ".
 "                 and b.id_seguimiento = ". $arreglo[$x]  .
"  order by b.id_documento  ";
//" group by b.id_documento  ";
 
 echo ($final);
$rr=mssql_query($final);

while ($documentos=mssql_fetch_array($rr))
{    echo  $documentos[id_seguimiento]. "<br>" ;
//echo  $documentos[id_documento ] ."|" . $documentos[num_interno] ."|". $documentos[num_oficial] . "|" .  $documentos[num_externo] . "<br>";
//"|". $documentos[desc_tipo_documento] . "|" . $documentos[materia] ."|" . $documentos[fecha_documento]. "|". $documentos[fecha_despacho] ."|". $documentos[fecha_recepcion]."|" . $documentos[id_nomina_despacho]."|". $documentos[id_seguimiento] ."|" . $documentos[id_procedencia]."|" . $documentos[id_destino]."|" . $documentos[procedencia]."|". $documentos[func_proc].  "<br>";
}	

}


?>