<?php
require '/home/aw006/database/bd_functions.php';
function test_input($data) {
			   $data = trim($data);
			   $data = stripslashes($data);
			   $data = htmlspecialchars($data);
			   return $data;
			}	
		$institutionId = test_input($_GET['id']);
		delete_institution ($institutionId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
</head>
<body>

		<script>
			window.location.href = "http://appserver.di.fc.ul.pt/~aw006/ResearchBond/adminOptions.php";
		</script>

</body>