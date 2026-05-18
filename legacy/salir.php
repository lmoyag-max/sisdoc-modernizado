<?php

 		session_start();
		
		//@header("Location: index.php");
?>
<html>
<head>
  <title>SISDOC</title>
</head>
    <body>
<script>
<?php
		session_destroy();
		//echo 'location.href="index.php";';
?>
	function breakOut(){
		if (self.parent.frames.length != 0)
			self.parent.location=document.location.href;
	} 

	breakOut();
	location.href="index.php";
</script>
    </body>
    </html>
