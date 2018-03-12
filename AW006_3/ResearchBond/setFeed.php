<?php

require '/home/aw006/database/bd_functions.php';

$listIDs = select_author($select = ['id']);
while($ID = $listIDs -> fetch_array()) {
	$query = "INSERT INTO feedBack (entry_id) VALUES ($ID[0])";
	select_generic_query($query);
	echo "done";
}
?>