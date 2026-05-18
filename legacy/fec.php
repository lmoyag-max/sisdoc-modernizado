<?PHP
  $i = '12345678' ;
  $mnd = substr($i,3,2);
  $day = substr($i,0,2);
  $year = substr($i,6,4);
echo $day  .'  ' .$mnd . '  ' . $year;

   if(checkdate ($mnd,$day,$year)){
   	echo "es fecha valida " ;
}
else { echo " fechA no valida";}
?>