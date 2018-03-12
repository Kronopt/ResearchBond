<?php
//require '/home/aw006/database/bd_functions.php';

// Use crossref API to obtain a DOI from a publication
// requires: $title is str with title of publication, 
//      $ID is int for publication id in database,
//      $journal is str for name of the journal (defaul NULL)

function getDOI($title, $ID, $journal = NULL)  {
    $crossRefTitle=urlencode($title);
    $q=($crossRefTitle)."&rows=".(1);
    $url = "http://api.crossref.org/works?query=".$q;
    $cURL = curl_init($url);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
    $result1 = curl_exec($cURL);
    curl_close($cURL);
    $start = "DOI";
    $end = ",";
    $output=getBetween($result1,$start,$end);
    $doi = stripslashes(substr($output, 3, -1));
    $startT = "title";
    $endT = "]";
    $output2 = getBetween($result1,$startT,$endT);
    $crossTitle = trim(substr($output2, 4, -1));
    $startP="container-title";
    $endP=",";
    $output3 = getBetween($result1,$startP,$endP);
    $crossJournal = substr($output3, 4, strlen($output3)-6);
    similar_text(strtolower($title), strtolower($crossTitle), $percent);

    if ($percent > 80 and $journal == NULL){
        update_publication($ID , false, false, false, $crossJournal , false, $doi, false, $percent);
    } 
    else if ($percent > 80 and $journal != NULL) {
        update_publication($ID, false, false, false , false, false, $doi, false, $percent);
    }
    //sleep ( rand ( 2, 5));
}


function getBetween($content, $start, $end){
    $r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}

?>