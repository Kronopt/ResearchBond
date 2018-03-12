<?php

require '/home/aw006/database/bd_functions.php';

function fillData($author_id) {
	$totalPubs = select_author_publications($author_id, $select = ["id"]) -> num_rows;

	$coAuthor_ids = select_author_coauthors($author_id, ['coAuthor_id']);
	while($id = $coAuthor_ids  -> fetch_array()) {
		$coAuthor_id = $id[0];
		$query = "SELECT DISTINCT p.id, p.title, p.citations FROM publication p, team t, publishes pub1, publishes pub2 WHERE t.author_id = $author_id AND t.coAuthor_id= $coAuthor_id AND pub1.author_id = t.author_id AND pub2.author_id = t.coAuthor_id AND p.id = pub1.publication_id AND p.id = pub2.publication_id;";
		$pubs= select_generic_query($query);
		$totalCits = 0;
		while($pub = $pubs  -> fetch_array()){
			$totalCits += $pub[2];
		}
		$pubs_coAuthor = $pubs -> num_rows;
		$percentage = round($pubs_coAuthor / $totalPubs, 2);
		$query1 = "UPDATE team SET team_percentage = $percentage, team_citations= $totalCits  WHERE author_id = $author_id AND coAuthor_id = $coAuthor_id;"; 
		select_generic_query($query1);
	}
}

$author_id = 1;
echo "string";


fillData($author_id);
?>