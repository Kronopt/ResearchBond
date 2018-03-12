<?php
require '/home/aw006/database/bd_functions.php';
function test_input($data) {
			   $data = trim($data);
			   $data = stripslashes($data);
			   $data = htmlspecialchars($data);
			   return $data;
			}	
		$authorId = test_input($_GET['id']);
		delete_author ($authorId);

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