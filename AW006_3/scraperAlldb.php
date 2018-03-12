<?php

require '/home/aw006/database/bd_functions.php';
require '/home/aw006/public_html/scrapper.php';
require '/home/aw006/public_html/getDOI.php';


// searches for Portuguese Institutions on dbpedia using a SPARQL query
// adds new institutions to database  

require '/home/aw006/easyRDF/vendor/autoload.php';

// Prefixes for DBpedia
EasyRdf_Namespace::set('dbr', 'http://dbpedia.org/resource/');
EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
$sparql = new EasyRdf_Sparql_Client('http://dbpedia.org/sparql');


$result = $sparql->query(
	'SELECT ?label
	WHERE {
		{ ?institution rdf:type dbo:University }
		UNION { ?institution rdf:type dbo:College }. 
		?institution dbo:country dbr:Portugal . 
		?institution rdfs:label ?label . 
		FILTER ( lang(?label) = "pt" )
	} 
	ORDER BY ?label'
);
	
foreach ($result as $row) {
	$institution = $row->label;
	$query = urlencode($institution);
	$url = "scholar.google.pt/citations?hl=pt-PT&view_op=search_authors&mauthors=".$query;
	$xpath = scrapper($url);
	$newURL = $xpath -> query('//h3[@class="gsc_inst_res"]/a/@href');
	if (empty($newURL->item(0))){
		echo '<p>'.$institution." not found in GoogleScholar </p>";
	} 
	else {
		$gsiURL = $newURL->item(0) ->nodeValue;
		$gsiURLFixo = $gsiURL;
		$check = select_institution($select = [], false, false, $gsiURL);
		if ($check -> num_rows == 0) {
			add_institution($institution, $gsiURL);
		} 
		else {echo '<p>'.$institution." already in the Database </p>";};
	}
}



// scraping google Scholar starting from all the Institutions added after SPARQL query to dbpedia 

$listInst = select_institution($select = ['id','link']);
while($link = $listInst -> fetch_array()){
	if ($link[1]){
		$institutionID = intval($link[0]);
		$gsiURL = $link[1];
		$scroll = TRUE;
	    while ($scroll) {
	        $xinst = scrapper("scholar.google.pt".$gsiURL);
	        $authors = $xinst->query('//h3[@class="gsc_1usr_name"]');
	        $links = $xinst->query('//h3[@class="gsc_1usr_name"]/a/@href');
	        $authorList = createArray($authors);
	        $authorLinkList = createArray($links);
	        $authorsCol = array();
	        for($i = 0; $i < count($authorList); ++$i){
	            $authorsCol["$authorList[$i]"] = $authorLinkList[$i];
	        }
	        
	        #######################################################  
	        #     searching for all authors from institution      #
	        #######################################################
	        
	        foreach($authorsCol as $name => $gsaURL){
	            $xpath = scrapper("scholar.google.pt".$gsaURL);
	            $lateralMenu = $xpath->query('//td[@class="gsc_rsb_std"]');
	            $totalCitations = $lateralMenu ->item(0) -> nodeValue; 
	            $hindex = $lateralMenu->item(2)-> nodeValue;
	            $check = select_author($select = [], false, false, false,  false, $link = $gsaURL);
	            $photo = $xpath->query('//img[@id="gsc_prf_pup"]/@src');

	            if ($check -> num_rows == 0) {
	                $authorID = add_author($name, $totalCitations, $hindex, $gsaURL, 'NULL');
	                $authorID = intval($authorID);
	                $check = select_author_institutions($authorID, $select = [], $institutionID, false, false);
	                if ($check -> num_rows == 0) {
	                    insert_author_institutions($authorID, [$institutionID]);
	                }
	                $query2 = "INSERT INTO feedBack (entry_id) VALUES ($authorID)";
					select_generic_query($query2); 
	            }
	            // if author is already in database (because it is co-author with another one) but without totalCitations and hindex 
	            
	            elseif ($check->fetch_array()[3] == NULL or $check->fetch_array()[3] < $hindex) {
	                $query = "UPDATE author SET citations= $totalCitations, hindex= $hindex WHERE link ='$gsaURL';"; 
	                select_generic_query($query);
	                $authorID = select_author($select = ["id"], false, false, false,  false, $link = $gsaURL) ->fetch_array()[0];
	                $authorID = intval($authorID);
	                $check = select_author_institutions($authorID, $select = [], $institutionID, false, false);
	                if ($check -> num_rows == 0) {
	                    insert_author_institutions($authorID, [$institutionID]);
	                }
	                $query2 = "INSERT INTO feedBack (entry_id) VALUES ($authorID)";
					select_generic_query($query2);

	            } else {
	                echo '<p>[AUTHOR] '.$name." already in the Database </p>";
	                $authorID = select_author($select = ["id"], false, false, false,  false, $link = $gsaURL) ->fetch_array()[0];
	                $authorID = intval($authorID);
	            };
	            $gs_photo = $photo ->item(0)->nodeValue;
	            $photoURL = "./fotos/".$authorID.".jpg";
	            $newfile = fopen($photoURL, "w")  or die("Unable to open file!");
	            $file = file_get_contents("http://scholar.google.pt.".$gs_photo);
	            file_put_contents($photoURL, $file);
	            fclose($newfile);
	            $query = "UPDATE author SET photo = '$photoURL' WHERE link ='$gsaURL';"; 
	            select_generic_query($query);
	            
	            
	            
	            #######################################################  
	            #     searching for publications data in Scholar      #
	            #######################################################
	            $more = 0;
				while ($more >= 0){
	                $AuthorURL = $gsaURL.'&cstart='.$more.'&pagesize=100';
	                $xpathi = scrapper('scholar.google.pt'.$AuthorURL);
	                $stopFlag = $xpathi -> query('//td[@class="gsc_a_e"]');
	                $flag = $stopFlag -> length;
	                if ($flag != 0) {
	                    $more = -1;
	                } 
	                else {
	                    $pubLinks = $xpathi->query('//a[@class="gsc_a_at"]/@href');
	                    $publications = $xpathi->query('//a[@class="gsc_a_at"]');
	                    $citations = $xpathi->query('//td[@class="gsc_a_c"]');
	                    $year = $xpathi->query('//span[@class="gsc_a_h"]');
	                    $publicationList = createArray($publications);
	                    $pubLinkList = createArray($pubLinks);
	                    $citationsList = createArray($citations);
	                    $yearsList = createArray($year); 
	                    $publicationCol = array();
	                    for($i = 0; $i < count($publicationList); ++$i){
	                        $publicationCol["$publicationList[$i]"] = array($pubLinkList[$i], preg_replace('/[^\d]+/', '', $citationsList[$i]), $yearsList[$i+1]);
	                    }
	                    foreach($publicationCol as $title => $values){
	                        $checkPub = select_publication($select = [], false, $title);
	                        if ($checkPub -> num_rows == 0) {
	                            add_publication ($title, 'NULL', $citations = $values[1], 'NULL', $year = $values[2], 'NULL', $link = $values[0],'NULL');
	                            $publicationID = select_publication($select = ["id"], false, $title) ->fetch_array()[0];
	                            $publicationID = intval($publicationID);
	                            $checkAuthPub = select_author_publications($authorID, $select = [], $publicationID); 
	                            if ($checkAuthPub -> num_rows == 0) {
	                                insert_author_publications($author_id = $authorID, $publication_id_list= [$publicationID]);
	                            }
	                        } 
	                        else {
	                            echo "<p>[PUBLICATION] '$title' already in the Database </p>";
	                            $publicationID = select_publication($select = ["id"], false, $title) ->fetch_array()[0];
	                            $publicationID = intval($publicationID);
	                            $checkAuthPub = select_author_publications($authorID, $select = [], $publicationID); 
	                            if ($checkAuthPub -> num_rows == 0) {
	                                insert_author_publications($author_id = $authorID, $publication_id_list= [$publicationID]);
	                            }
	                        }
	                        $checkPubDOI = select_publication($select = ["doi"], false, $title);
	                        if ($checkPubDOI -> fetch_array()[0] == NULL) {
	                            getDOI($title, $publicationID);
	                        }
	                    }
	                    $more = $more + 100;
	                }
	                    
	            }
	            
	        	// scraps lateral menu
	            $coAuthors = $xpath->query('//a[@class="gsc_rsb_lc"]/@href');

	            #######################################################  
	            #     searching for co-Authors data in Scholar        #
	            ####################################################### 

	        	//Extract Co-author Data
	            if ($coAuthors -> item(0)) {
	                $gscaURL = $coAuthors-> item(0) ->nodeValue;
	                $coAuthors = scrapper("scholar.google.pt".$gscaURL);
	                $authors = $coAuthors -> query('//h3[@class="gsc_1usr_name"]');
	                $links = $coAuthors -> query('//h3[@class="gsc_1usr_name"]/a/@href');
	                $coAuthorsList = createArray($authors);
	                $coAuthorLinkList = createArray($links);
	            	
	            	//creates an array $authorCol with coAuthorName as key and link as value. 
	            	//other info may be added in values if necessary.
	                $coAuthorCol = array();
	                for($i = 0; $i < count($coAuthorsList); ++$i){
	                    $coAuthorCol["$coAuthorsList[$i]"] = $coAuthorLinkList[$i];
	                };
	                foreach($coAuthorCol as $name => $URL) {
	                    $check = select_author($select = [], false, false, false,  false, $link = $URL);
	                    if ($check -> num_rows == 0) {
	                        $coAuthorID = add_author($name, 'NULL', 'NULL', $link = $URL, 'NULL');
	                        $coAuthorID = intval($coAuthorID);
	                    } 
	                    else {
	                    	echo "<p>[CO-AUTHOR] ".$name." already in the database.</p>";
	                    	$coAuthorID = select_author($select = ["id"], false, false, false,  false, $link = $URL) ->fetch_array()[0];
	                        $coAuthorID = intval($coAuthorID);    
	                    }
	                    $checkAuthCo = select_author_coauthors($authorID, $select = [], $coAuthorID);
	                    if ($checkAuthCo -> num_rows == 0) {
	                        insert_author_coauthors($authorID, [$coAuthorID]);
	                    }
	                }
	            }
	        }
	        $urlNextPage = $xinst-> query('//span[@class="gsc_pgn"]/button/@onclick');
	        $buttons = $xinst -> query('//span[@class="gsc_pgn"]/button/@class');
	        $stopString = $buttons -> item(1) -> nodeValue;
	        if (preg_match('/gs_dis/',$stopString)){
	            $scroll = FALSE;
	        } else {
	            $urlNextPage = $urlNextPage -> item(1) -> nodeValue;
	            $urlNextPage = str_replace('\x3d', '=', $urlNextPage);
	            $urlNextPage = str_replace('\x26', '&', $urlNextPage);
	            $urlNextPage = substr(str_replace('window.location=', '', $urlNextPage), 1,-1);
	            $gsiURL = $urlNextPage;
	            sleep ( rand ( 5, 10));
	        };
	    }
	}
}
?>