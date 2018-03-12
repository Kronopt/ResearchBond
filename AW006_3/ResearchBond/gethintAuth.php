<?php
require '/home/aw006/database/bd_functions.php';
//require 'bd_functions.php';

$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
//$str = strtr( $str, $unwanted_array );


// get the q parameter from URL
$authorSuggest = $_REQUEST["authorSuggest"];
//$q = strtr( $q, $unwanted_array);


$hint = "";

// lookup all hints from array if $q is different from "" 
if ($authorSuggest !== "") {
    $query = "SELECT * FROM author WHERE name like '%".$authorSuggest."%'  ORDER BY name";
    $authorsQuery = select_generic_query($query);
    $authorSuggest = strtolower($authorSuggest);
    $len=strlen($authorSuggest);
    $counter=0;
    $max=10;
    //colocar aqui as indicaçoes do sql
    $hint.='<ul style="list-style-type:none">';

    while(($authors = $authorsQuery -> fetch_array()) and ($counter<$max)){
    //foreach($a as $name) {
        $name = $authors [1];
        $id = $authors [0];
        if (stristr($authorSuggest, substr($authors[1], 0, $len))) {
                $hint.='<li><a href = "ResearchBond/authorInfo.php?author='.$name.'&id='.$id.'" style = "text-decoration-color: none; color:#808080; font-size: 110% ">'.$name.'</a></li>';

	           $counter ++;
        }
    }
    $hint.="</ul>";
}

// Output "no suggestion" if no hint was found or output correct values 
echo $hint === "" ? "no suggestion" : $hint;
?>
