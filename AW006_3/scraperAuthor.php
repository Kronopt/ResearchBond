<?php
require '/home/aw006/database/bd_functions.php';
require '/home/aw006/public_html/scrapper.php';
require '/home/aw006/public_html/getDOI.php';

//require 'bd_functions.php';

// This script only does scraping in author and co-author list and publications list web pages 
// It does not access individual publications unless $checkFlag = true

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// this function adds a new publication to the database
// requires: $ID is int for author id in database, $URL is str with link from author
function addPublications($ID, $URL) {
    $more = 0;
    while ($more >= 0){
        $AuthorURL = $URL.'&cstart='.$more.'&pagesize=100';
        $xpath = scrapper('scholar.google.pt'.$AuthorURL);
        $stopFlag = $xpath -> query('//td[@class="gsc_a_e"]');
        $flag = $stopFlag -> length;
        if ($flag != 0) {
            $more = -1;
        } else {
            $pubLinks = $xpath -> query('//a[@class="gsc_a_at"]/@href');
            $publications = $xpath -> query('//a[@class="gsc_a_at"]');
            $citations = $xpath -> query('//td[@class="gsc_a_c"]');
            $year = $xpath -> query('//span[@class="gsc_a_h"]');
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
                    add_publication ($title, 'NULL', $citations = $values[1], 'NULL', $year = $values[2], 'NULL', $link = $values[0], 'NULL');
                    $publicationID = select_publication($select = ["id"], false, $title) ->fetch_array()[0];
                    $publicationID = intval($publicationID);
                    $checkAuthPub = select_author_publications($ID, $select = [], $publicationID); 
                    if ($checkAuthPub -> num_rows == 0) {
                        insert_author_publications($author_id = $ID, $publication_id_list= [$publicationID]);
                    }
                } 
                elseif ($checkPub -> fetch_array()[7] == NULL){
                	$publicationID = select_publication($select = ["id"], false, $title) ->fetch_array()[0];
                    $publicationID = intval($publicationID);
                    getDOI($title, $publicationID);
                    $checkAuthPub = select_author_publications($ID, $select = [], $publicationID); 
                    if ($checkAuthPub -> num_rows == 0) {
                        insert_author_publications($ID, [$publicationID]);
                    }
                }
                else {
                    echo "<p>[PUBLICATION] '$title' already in the Database </p>";
                    $publicationID = select_publication($select = ["id"], false, $title) ->fetch_array()[0];
                    $publicationID = intval($publicationID);
                    $checkAuthPub = select_author_publications($ID, $select = [], $publicationID); 
                    if ($checkAuthPub -> num_rows == 0) {
                        insert_author_publications($ID, [$publicationID]);
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
}


// this function adds co-authors to the database for a given author
// requires: $ID is int for author id in database, $XPATH is object of class DOMXPATH with with
//      html containing co-authors given using function scrapper() 
function addCoAuthors($xpath, $ID) {
    $coAuthors = $xpath->query('//a[@class="gsc_rsb_lc"]/@href');
    if ($coAuthors -> item(0)) {
        $gscaURL = $coAuthors-> item(0) ->nodeValue;
        $coAuthors = scrapper("scholar.google.pt".$gscaURL);
        $authors = $coAuthors -> query('//h3[@class="gsc_1usr_name"]');
        $links = $coAuthors -> query('//h3[@class="gsc_1usr_name"]/a/@href');
        $coAuthorsList = createArray($authors);
        $coAuthorLinkList = createArray($links);
        $coAuthorCol = array();
        for($i = 0; $i < count($coAuthorsList); ++$i){
            $coAuthorCol["$coAuthorsList[$i]"] = $coAuthorLinkList[$i];
        };
        foreach($coAuthorCol as $name => $URL) {
            $check = select_author($select = [], false, false, false, false, $link = $URL);
            if ($check -> num_rows == 0) {
            $coAuthorID = add_author($name, 'NULL', 'NULL', $link = $URL, 'NULL');
            $coAuthorID = intval($coAuthorID);
            } else {
            echo "<p>[CO-AUTHOR] ".$name." already in the database.</p>";
            $coAuthorID = select_author($select = ["id"], false, false, false,  false, $link = $URL) ->fetch_array()[0];
            $coAuthorID = intval($coAuthorID);
            }
            $checkAuthCo = select_author_coauthors($ID, $select = [], $coAuthorID);
            if ($checkAuthCo -> num_rows == 0) {
                insert_author_coauthors($ID, [$coAuthorID]);
            }
        }
    }
}

if(isset($_POST['checkFlag']) && 
   $_POST['checkFlag'] == 'true') {
    $checkFlag = true; 
} else {
    $checkFlag = false;
}    


$query_author = htmlspecialchars($_POST['name']);
$query_institution = htmlspecialchars($_POST['institution']);

$query = urlencode($query_author.' '.$query_institution);
$url = "scholar.google.pt/citations?hl=pt-PT&view_op=search_authors&mauthors=".$query;
$xpath = scrapper($url);
$newURL = $xpath -> query('//h3[@class="gsc_1usr_name"]/a/@href');

////////////////////////////////////////////////////////  
//     searching for an author based on  query        //
//                                                    //
//   - selects the first suggestion from Scholar      //
////////////////////////////////////////////////////////

if (empty($newURL->item(0))){
    echo '<p>'.$query_author." not found in GoogleScholar </p>";
} 
else {
    $gsaURL = $newURL->item(0) ->nodeValue;
    $xpath = scrapper("scholar.google.pt".$gsaURL);
    $lateralMenu = $xpath->query('//td[@class="gsc_rsb_std"]');
    $totalCitations = $lateralMenu ->item(0) -> nodeValue; 
    $hindex = $lateralMenu->item(2)-> nodeValue;
    $authorName = $xpath->query('//div[@id="gsc_prf_in"]');
    $name = $authorName->item(0) ->nodeValue;
    //    Adds author's Institution.    //
    $photo = $xpath->query('//img[@id="gsc_prf_pup"]/@src');

    $institutionData = $xpath -> query('//div[@class="gsc_prf_il"]');
    $institutionLink = $xpath -> query('//div[@class="gsc_prf_il"]/a/@href');

    //  Complete Institution data is provided in author's profile  //

    if ($institutionData->item(0)->childNodes-> item(1)) {
        $institutionName = $institutionData->item(0)->childNodes-> item(1) ->nodeValue;
        $gsiURL = $institutionLink ->item(0) ->nodeValue;
        $check = select_institution($select = [], false, false, $gsiURL);
        if ($check -> num_rows == 0) {
            add_institution($institutionName, $gsiURL);
        } else {
            echo '<p>'.$institutionName." already in the Database </p>";
        }
        $institutionID = select_institution($select = ["id"], false, false, $gsiURL) ->fetch_array()[0];
        $institutionID = intval($institutionID);
        }
        //  Incomplete Institution data is provided in author's profile  //
    else {
        $institutionName = $institutionData ->item(0)->nodeValue;
        $gsiURL = "NULL";
        $check = select_institution($select = [], false, $institutionName);
        if ($check -> num_rows == 0) {
            add_institution($institutionName);
        } 
        else {
            echo '<p>'.$institutionName." already in the Database </p>";
        };
        $institutionID = select_institution($select = ["id"], false, $institutionName) ->fetch_array()[0];
        $institutionID = intval($institutionID);
    }
    
    $check = select_author($select = [], false, false, false,  false, $link = $gsaURL);
    if ($check -> num_rows == 0) {
        $authorID = add_author($name, $totalCitations, $hindex, $gsaURL, $photoURL);
        $authorID = intval($authorID);
        $check = select_author_institutions($authorID, $select = [], $institutionID, false, false);
        if ($check -> num_rows == 0) {
            insert_author_institutions($authorID, [$institutionID]);
        }
        $query2 = "INSERT INTO feedBack (entry_id) VALUES ($authorID)";
        select_generic_query($query2);
    }

        //    If author is already in database (because it is co-author)  // 
        //  the totalCitations and hindex are uptaded since these fields  //
        //  are NULL in the beginning for most co-authors.                 //

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
    }
    else {
        echo '<p>[AUTHOR] '.$name." already in the Database </p>";
        $authorID = select_author($select = ["id"], false, false, false,  false, $link = $gsaURL) ->fetch_array()[0];
        $authorID = intval($authorID);
        $check = select_author_institutions($authorID, $select = [], $institutionID, false, false);
        if ($check -> num_rows == 0) {
            insert_author_institutions($authorID, [$institutionID]);
        };
    }
    $gs_photo = $photo ->item(0)->nodeValue;
    $photoURL = "./fotos/".$authorID.".jpg";
    $newfile = fopen($photoURL, "w")  or die("Unable to open file!");
    $file = file_get_contents("http://scholar.google.pt.".$gs_photo);
    file_put_contents($photoURL, $file);
    fclose($newfile);
    $query = "UPDATE author SET photo = '$photoURL' WHERE link ='$gsaURL';"; 
    select_generic_query($query);
    addPublications($authorID, $gsaURL);
    addCoAuthors($xpath, $authorID);
}           


if ($checkFlag) {
    $pubLinks = select_author_publications($authorID, $select = ["link"]) ;
            
    while($link = $pubLinks -> fetch_array()){
         
        $checkPubAbs = select_publication($select = ["abstract"], false, false, false, false, false, false, false, $link[0]);
        $checkPubID = select_publication($select = ["id"], false, false, false, false, false, false, false, $link[0]);
        $publicationID = intval($checkPubID -> fetch_array()[0]);
        $checkPubDOI = select_publication($select = ["doi"], false, false, false, false, false, false, false, $link[0]);
        if ($checkPubAbs -> fetch_array()[0] == NULL) {
            sleep( rand ( 10, 30));
            $xpub = scrapper('scholar.google.pt'.$link[0]);
            $publication = $xpub->query('//a[@class="gsc_title_link"]');
            $values = $xpub->query('//div[@class="gsc_value"]');
            $abstract = $xpub ->query('//div[@id="gsc_descr"]');
            $values2 = $xpub->query('//div[@class="gs_scl"]'); 
            $test = true;
            
            foreach($values2 as $val) {
                if($test) {
                    $node = $val -> nodeValue;
                    if (substr($node,0,21) == "Revista especializada"){
                        $journal = substr($node, 21, strlen($node)-1);
                        $test = false;
                    }
                    else {$journal = NULL;
                    }
                }
            }
            //$authors = $values[0]-> nodeValue;
            //$authorsList = explode(", ", $authors);
            if ($abstract->item(0)){
                $abstract = $abstract -> item(0) -> nodeValue;
                update_publication($publicationID, false, $abstract, false, $journal);
            } 
            else {
                update_publication($publicationID, false, false, false, $journal);
            }
        }
                // if($checkPubDOI -> fetch_array()[0] == NULL) {
                //     $checkPubTitle = select_publication($select = ["title"], false, false, false, false, false, false, false, $link[0]);
                //     $dbTitle = $checkPubTitle -> fetch_array()[0];
                //     //$crossRefTitle=urlencode($dbTitle);
                //     $checkPubJournal= select_publication($select = ["journal"], false, false, false, false, false, false, false, $link[0]);
                //     $dbJournal = $checkPubJournal -> fetch_array()[0];
                //     getDOI($dbTitle, $publicationID, $dbJournal);
                // }             
    }
} 
else{
    exit;
}

?>