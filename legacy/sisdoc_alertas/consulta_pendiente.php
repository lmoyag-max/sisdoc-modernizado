$consulta="select a.id_documento,a.num_interno,a.num_oficial,a.num_externo,  
        f.desc_tipo_documento,a.materia,a.fecha_documento,b.fecha_despacho,          
          b.id_seguimiento,b.id_procedencia,b.id_destino,    
       procedencia=             
       case  b.tipo_procedencia             
          when 'I' then             
               (select desc_dependencia from dependencia where  b.id_procedencia=id_dependencia)            
          else            
               (select desc_dependencia_externa  from dependencia_externa where  b.id_procedencia=id_dependencia_externa)            
          end,
       funcprocedencia=
          case when b.rut_procedencia =' ' or b.rut_procedencia='0' then     ''    
             else 
		(select rtrim((funcionario.nombres)+ ' ' + (funcionario.apellidos))
		from funcionario where 
		b.rut_procedencia =funcionario.rut )

          End             
        
          from documento  a,  tramite b,  tipo_documento f         
          where  a.id_documento= b.id_documento   
                     
                and  f.id_tipo_documento= a.id_tipo_documento            
                and  b.id_estado_tramite = 2
                and  b.fecha_despacho >=  $fechaini          
                and b.fecha_despacho <= $fechafin 
				order by b.fecha_despacho,f.desc_tipo_documento   ";
				

if ($Cbo_Procedencia==0)
{ $Cbo_proc="";   }
else{
   $Cbo_proc=" and b.id_procedencia=" . $Cbo_Procedencia;
   $consulta =$consulta . $Cbo_proc;}
   		
if ($Cbo_Func_Procedencia==0)
{ $Cbo_fproc="";   }
else{
   $Cbo_fproc=" and b.rut_procedencia=" . $Cbo_Func_Procedencia;
   $consulta =$consulta . $Cbo_fproc;}


if ($Cbo_Destinatario==0)
{ $Cbo_dest="";   }
else{
   $Cbo_dest=" and b.id_destinatario=" . $Cbo_Destinatario;
   $consulta =$consulta . $Cbo_dest;}
   		
if ($Cbo_Func_Destino==0)
{ $Cbo_fdest="";   }
else{
   $Cbo_fdest=" and b.rut_destino=" . $Cbo_Func_Destino;
   $consulta =$consulta . $Cbo_fdest;}
   
$orden =" order by b.fecha_despacho,f.desc_tipo_documento   ";

$consulta = $consulta .  $orden;   
