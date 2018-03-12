<!DOCTYPE html>
<html lang="en">
<head>
	<title>aw006 project</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

	<style>
	.input-group-vertical {
	  margin-bottom: 10px;
	}
	.input-group-vertical .form-control {
	  border-radius: 0;
	}
	.input-group-vertical .form-group {
	  margin-bottom: 0;
	}
	.input-group-vertical .form-group:not(:last-child) .form-control:not(:focus) {
	  border-bottom-color: transparent;
	}
	.input-group-vertical .form-group:first-child .form-control {
	  border-top-left-radius: 3px;
	  border-top-right-radius: 3px;
	}
	.input-group-vertical .form-group:last-child .form-control {
	  border-bottom-right-radius: 3px;
	  border-bottom-left-radius: 3px;
	  top: -2px;
	  
	}
	.table-striped>tbody>tr:nth-child(odd)>td, 
	.table-striped>tbody>tr:nth-child(odd)>th {
	   background-color: #efefdc;
	 }

	#content {padding-bottom:200px};
	</style>

</head>



<body>
	<div class="container">
		<div class="pull-left ">
		   	<a href="../index.html" style="color: #446600; font-size: 150%"><img src="researchBond.png" alt="Research Bond" class="img-responsive" width="484"></a>
		</div>
		<div class="container" style="margin-top: 25px">
		    <div class="pull-right">
		   		<div class='page-header' style=""> 
					<ul class="nav navbar-nav navbar-right">
						<li role="presentation" class="active"><a href="../index.html" style="color: #446600; font-size: 150%"><strong> Home </strong></a></li>
						<li role="presentation" class="active"><a href="aboutUs.html" style="color: #446600; font-size: 150%"><strong> About </strong></a></li>
						<li role="presentation" class="active"><a href="admin.html" style="color: #446600; font-size: 150%"><strong> Admin </strong></a></li>
					</ul>
				</div>
		    </div>	
	    </div>	
	</div>
	
<br>
<br>
	<div id='content'>	  
		<div class='container'>

		 	<?php
			ini_set('display_erros', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);

			require '/home/aw006/database/bd_functions.php';
			// define variables and set to empty values
			$tablename = $attributelist = $valuelist= "";


			if ($_SERVER["REQUEST_METHOD"] == "POST") {
			   $author = test_input($_POST["author"]);
			   $institute = test_input($_POST["institute"]);
			   $other = test_input($_POST["other"]);
			}  

			function test_input($data) {
			   $data = trim($data);
			   $data = stripslashes($data);
			   $data = htmlspecialchars($data);
			   return $data;
			}	 

			if($author !=""){
				
				$query = "SELECT * FROM author WHERE UPPER(name) LIKE UPPER ('%$author%')";
				
				$authorQuery = select_generic_query ($query);

				echo '<table class="table table-striped">
						<tr style= "font-size: 150%">
					    <th style= "color: #5d5d5d">Author Name</th>
					    <th style= "color: #5d5d5d">Institution</th>		
						    <th style= "color: #5d5d5d" >h-index</th>
					    <th style= "color: #5d5d5d">Citations</th>
						</tr>';
				while ($authorInfo = $authorQuery -> fetch_array ()) {
					$institution = select_author_institutions ($authorInfo [0], []);

					echo '<tr>';

				 	echo '<td><a href = "authorInfo.php?author='.$authorInfo[1].'&id='.$authorInfo[0].'" style= "font-size: 110%; color: #7d7d7d"><strong>'.$authorInfo [1].'</strong></a></td>
					<td>'.$institution->fetch_array ()[1].'</td>
					<td>'.$authorInfo [3].'</td>
					<td>'.$authorInfo [2].'</td>';
					echo '</tr>';
				}
				echo '</table>';
			}

			if($institute !=""){

				$query = "SELECT * FROM institution WHERE UPPER(name) LIKE UPPER ('%$institute%') LIMIT 10";

				$institutionQuery = select_generic_query ($query);

				echo '<table class="table table-striped">
						<tr style= "font-size: 150%">
					    <th style= "color: #5d5d5d">Institution</th>
					    <th style= "color: #5d5d5d">Investigators</th>		
						</tr>';


				while ($institutionInfo = $institutionQuery -> fetch_array()){
					$investigators = select_institution_investigators ($institutionInfo [0], []);

					while ($investigator = $investigators->fetch_array()){

						echo '<tr>';

						echo '<td>'.$institutionInfo [1].'</td>';
						echo '<td><a href = "authorInfo.php?author='.$investigator[1].'&id='.$investigator[0].'" style= "font-size: 110%; color: #7d7d7d"><strong>'.$investigator[1].'</strong></a></td>';

					}
					
					
					// $idInstitute = $institutionInfo [0];
					// $instituteInvestigators [] = $investigators [0];
					// $strInvestigators = implode('</a>,<a>', $instituteInvestigators);
					// echo '<a>'.$strInvestigators.'</a>';
					
					
				}
				echo '</tr>';
				
				// while ($institutionInfo=$institutionQuery -> fetch_array()) {
				// 		echo '<tr>';
				// 		 	 echo '<td>'.$institutionQuery [1].'</td>';
				// 		 	 // foreach ($investigators as $investigator) {
				// 		 	 // 	'<td><a>'.$i->investigator.'</a></td>';
				// 		 	 // }
				// 		 	 // while ($investigator= $investigators -> fetch_array()) {
				// 		 	 // 	'<td>'.$i->investigator.'</td>';
				// 		 	 // }
							
				// 		echo '</tr>';
				// }
				echo '</table>';	
			}
			// if($other !="" && $author =="" && $institution ==""){
			// 	header( "Location: http://lmgtfy.com/?q=". implode('+', explode(' ',$other)));
				
			// }


			

			
			?>

		</div>
	</div>

	<footer>
		<div>
			<div class="navbar navbar-inverse navbar-fixed-bottom" role="navigation">
	       		<div class='container'>
					<div class="navbar-text">	   
						<p>Web Applications - Faculty of Sciences, University of Lisbon - 2016</p>
					</div>	
	       		</div>
	   		</div>
		</div>
	</footer>

</body>
</html>