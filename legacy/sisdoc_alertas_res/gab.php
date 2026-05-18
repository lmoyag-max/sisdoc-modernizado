<?php 
include("conexion_bd.php");
$Txt_fecha_ini = '01/01/2005';
$Txt_fecha_fin='31/12/2005';

$dia = substr($Txt_fecha_ini,0,2);
$mes = substr($Txt_fecha_ini,3,2);
$año = substr($Txt_fecha_ini,6,4);
$Fechaini = date("d/m/Y H:i", mktime(0, 0,0, $mes, $dia, $año));

$dia = substr($Txt_fecha_fin,0,2);
$mes = substr($Txt_fecha_fin,3,2);
$año = substr($Txt_fecha_fin,6,4);
$Fechafin = date("d/m/Y H:i", mktime(23, 59,59, $mes, $dia, $año));


$query="select a.id_documento,a.id_tipo_documento,a.num_interno,a.num_oficial,a.num_externo,a.materia ,a.fecha_documento,
b.id_procedencia,b.id_destino,b.fecha_despacho,b.fecha_recepcion,b.observaciones
 from  documento a , tramite b 
where a.id_documento = b.id_documento 
and (b.id_procedencia = 5 or b.id_procedencia = 4 or b.id_procedencia = 89 or b.id_procedencia = 6)
and  b.tipo_procedencia='E' 
and b.id_seguimiento in (select min(id_seguimiento) from tramite where a.id_documento=id_documento)
order by a.id_documento";
echo "rerer" . $query;
$reg=mssql_query($query,$cn);
while($cons=mssql_fetch_array($reg))
{
     $ps_busca_padre = "select * from historia5($cons[id_documento])";
     $rs_p = mssql_query($ps_busca_padre,$cn); 
     while ($rs_padre=mssql_fetch_array($rs_p))
     { 
	 if ($cons[id_documento] <> $rs_padre[id_documento])
	 {  ?>
	 <table width="738" border="0" cellpadding="1" cellspacing="0" bgcolor="#ECE9D8">
      	<tr> 
                <td width="736" height="263"  align="center" bgcolor="#9CCBED" > 
          		<table width="99%" height="148" border="1" >
            		<tr> 
                               <td width="633" height="142" bgcolor="#9CCBED"> 
                	<table width="99%" border="0" align="center">
                  		<tr> 
                    		<td bgcolor="#6699FF"><font color="#FFFFFF"><strong>INFORMACION 	DOCUMENTO DE REFERENCIA</strong></font>
			</td>
                  		</tr>
                	</table>
                	<table width="99%" border="0" align="center" cellpadding="2" cellspacing="2" bgcolor="#C3D6E6">
                  	<tr> 
                    		<td width="98"><strong>Tipo de Docto</strong></td>
                    		<td width="106"> <? echo $rs_padre[desc_tipo_documento]; ?> </td>
                    		<td width="86"><b>Fecha Docto<font face="Arial, Helvetica, sans-serif">&nbsp;</font></b></td>
                    		<td width="72"> 
                      		<?php
                      		 $fec_doc=strtotime($rs_padre[fecha_documento]);
		   	$fech_doc=date("d/m/Y",$fec_doc);
     		 	echo $fech_doc;?>
                    		</td>
                    		<td width="35"><b>Medio</b></td>
                    		<td width="50"> 
                      		<? If ($rs_padre["medio"]=="P")
                      			{    echo "Papel";}
			     else{	if ($rs_padre["medio"]=="C"){
				   echo "Copia";}
				else {   echo "Video";}}   
			?>
                    		</td>
                    		<td width="46"><b>Original</b></td>
                    		<td width="171"><? echo $rs_padre[original];?></td>
                  	</tr>
		</table>
          
                	<table width="99%" border="0" align="center"  cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
                  	<tr valign="middle"> 
                    		<td width="109"><b><i>N&uacute;meros : Interno<font size="4" face="Arial"> </font></i></b></td>
                    		<td width="110"> <? echo $rs_padre[num_interno];?> </td>
                    		<td width="110"><b><i>Oficial</i></b><font size="4" face="Arial">&nbsp; </font></td>
                    		<td width="103"> <? echo $rs_padre[num_oficial];?> </td>
                    		<td width="55"> <b>Fecha Oficial </b></td>
                    		<td width="66">
                    		<?php 
                    		$fec_doc=strtotime($rs_padre[fecha_num_oficial]);
		             	$fech_doc=date("d/m/Y",$fec_doc);
		             	if ($rs_padre[fecha_num_oficial] <>NULL)
     			{ echo $fech_doc;}
     			?>
                   		</td>
                    		<td width="65"><b><i>Externo<font size="4" face="Arial"> </font></i></b></td>
                    		<td width="189"> <? echo $rs_padre[num_externo]; ?> </td>
                  	</tr>
                	</table>
                	<table width="99%" border="1" align="center" cellpadding="1" cellspacing="0" bgcolor="#C3D6E6">
                  	<tr> 
                    	<td width="17%"><b>Materia</b></td>
                    	<td width="83%">    <? echo $rs_padre[materia];?> </td>
                  	</tr>
              		</table>
                	<p>&nbsp;</p></td>
            		</tr>
          		</table>
          		<table width="97%" height="19" border="0" align="center" cellpadding="1" cellspacing="1">
            		<tr> 
              		<td width="634" > 
                	<div align="center"><font color="#800000"><strong>TRAMITES        ASOCIADOS</strong></font></div></td>
            		</tr>
          		</table>		 
          		<table width="98%" border="1" align="center" cellpadding="1" cellspacing="0" bgcolor="#D1D7DC">
            		<tr> 
              		<td width="46"><font color="#02392D"><strong>N&oacute;mina</strong></font></td>
              		<td width="77"><font color="#02392D"><strong>Procedencia</strong></font></td>
              		<td width="71"><font color="#02392D"><strong>Funcionario</strong></font></td>
              		<td width="71"><font color="#02392D"><strong>Destino</strong></font></td>
              		<td width="71"><font color="#02392D"><strong>Funcionario</strong></font></td>
              		<td width="73"><font color="#02392D"><strong>Tipo Distribucion</strong></font></td>
              		<td width="51"><font color="#02392D"><strong>Fecha Registro</strong></font></td>
              		<td width="61"><font color="#02392D"><strong>Fecha Despacho</strong></font></td>
              		<td width="64"><font color="#02392D"><strong>Fecha Recepcion</strong></font></td>
					<td width="95"><font color="#02392D"><strong>Observaciones</strong></font></td>		 
            		</tr>
          		<? }?>
          		<tr> 
              		<td width="46" height="27"> <?php echo $rs_padre["id_nomina_despacho"];?></td>
              		<td width="77"> 
                	<?php  
					if ($rs_padre["procedencia"]=="") {
		   			 $rs_padre["procedencia"]="&nbsp";} 
		  			 echo $rs_padre["procedencia"]; ?>
              		</td>
              		<td width="71"> 
                	<?php 
		 			 if ($rs_padre["nombre_procedencia"]=="") {
					$rs_padre["nombre_procedencia"]="&nbsp";} 
		    		echo $rs_padre["nombre_procedencia"]; 
		   			?>
              		</td>
              		<td width="71"><?php echo $rs_padre["destino"]; ?> </td>
              		<td width="71"> 
                	<?php
		  			if ($rs_padre["nombre_destino"]=="") {
		    		$rs_padre["nombre_destino"]="&nbsp";}
		  			 echo $rs_padre["nombre_destino"]; 
					 ?>
              		</td>
              		<td width="73"><?php echo $rs_padre["desc_tipo_distribucion"]; ?> </td>
               		<td width="51">
		     	 <?php 
		       $fec_reg=strtotime($rs_padre["fecha_sistema"]);
		        $fec_reg=date("d/m/Y",$fec_reg);
		        echo $fec_reg; ?></td>
              		<td width="61"> 
                	<?php 
                	  if ($rs_padre["fecha_despacho"]==NULL)
		{		
		 $fec_tra="&nbsp";
		  echo $fec_tra;}
		else 
		 {
		 $fec_tra=strtotime($rs_padre["fecha_despacho"]);
		 $fec_tra=date("d/m/Y",$fec_tra);
		echo $fec_tra;}?>
              		</td>
		<td width="64"> <?php 
                  	if ($rs_padre["fecha_recepcion"]==NULL)
		{		
		   $fec_rec="&nbsp";
		   echo $fec_rec;}
		else 
		 {
		 $fec_rec=strtotime($rs_padre["fecha_recepcion"]);
		 $fec_rec=date("d/m/Y",$fec_rec);
		 echo $fec_rec; }?>
		</td>
             		<td width="95"> 
                	<?php
		  if ($rs_padre["observaciones"]=="") {
			    $rs_padre["observaciones"]="&nbsp";}
			   echo $rs_padre["observaciones"]; ?>
              		</td>
            		</tr>
                        	<? $cons[id_documento]= $rs_padre[id_documento];   ?>
		
</table>
		   <? }
	
		   
	 }?>


