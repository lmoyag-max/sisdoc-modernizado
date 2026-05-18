<?php

 		session_start();
		
?>
<html>
<head>
  <title>SISDOC</title>
</head>
<script>
<?php
		session_destroy();
		echo 'location.href="..\index.php";';
		//@header("Location: index.php");
?>
</script>
</html>
