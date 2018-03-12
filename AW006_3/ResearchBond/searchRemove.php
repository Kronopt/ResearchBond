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
<script>

</script>
	<div class="container">
		<div class="pull-left ">
		   	<a href="../index.html"><img src="researchBond.png" alt="Research Bond" class="img-responsive" width="484"></a>
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
			   $allAuthors = test_input($_POST["allAuthors"]);
			   $allInstitutions = test_input($_POST["allInstitutions"]);
			}  

			function test_input($data) {
			   $data = trim($data);
			   $data = stripslashes($data);
			   $data = htmlspecialchars($data);
			   return $data;
			}
		
			if($allAuthors == "All Authors"){
				
				$query = "SELECT * FROM author";
				
				$authorQuery = select_generic_query ($query);

				echo '<table class="table table-striped">
						<tr style= "font-size: 135%">
						<th style= "color: #5d5d5d">Remove</th>
					    <th style= "color: #5d5d5d">Author Name</th>
					    <th style= "color: #5d5d5d;width:40%">Institution</th>		
						    <th style= "color: #5d5d5d" >h-index</th>
					    <th style= "color: #5d5d5d">Citations</th>
						</tr>';
				while ($authorInfo = $authorQuery -> fetch_array ()) {
					$institution = select_author_institutions ($authorInfo [0], []);

					echo '<tr>';

				 	echo '<td><a href = "removeAuthor.php?id='.$authorInfo[0].'"><input type = "button" class="btn btn-secondary" name="author" id="removeButton" style="background-color:#283A06; color: white" value = "Remove"><input type = "hidden" value = "'.$authorInfo [1].'" /><input type = "hidden" value = "'.$authorInfo [1].'" /> </a></td>
				 	<td style= "font-size: 110%; color: #7d7d7d"><strong>'.$authorInfo [1].'</strong></td>
					<td >'.$institution->fetch_array ()[1].'</td>
					<td>'.$authorInfo [3].'</td>
					<td>'.$authorInfo [2].'</td>';
					echo '</tr>';
				}
				echo '</table>';
			}

			if($author !=""){
				
				$query = "SELECT * FROM author WHERE UPPER(name) LIKE UPPER ('%$author%')";
				
				$authorQuery = select_generic_query ($query);

				echo '<table class="table table-striped">
						<tr style= "font-size: 135%">
						<th style= "color: #5d5d5d">Remove</th>
					    <th style= "color: #5d5d5d">Author Name</th>
					    <th style= "color: #5d5d5d;width:40%">Institution</th>		
						    <th style= "color: #5d5d5d" >h-index</th>
					    <th style= "color: #5d5d5d">Citations</th>
						</tr>';
				while ($authorInfo = $authorQuery -> fetch_array ()) {
					$institution = select_author_institutions ($authorInfo [0], []);

					echo '<tr>';

				 	echo '<td><a href = "removeAuthor.php?id='.$authorInfo[0].'"><input type = "button" class="btn btn-secondary" name="author" id="removeButton" style="background-color:#283A06; color: white" value = "Remove"><input type = "hidden" value = "'.$authorInfo [1].'" /><input type = "hidden" value = "'.$authorInfo [1].'" /> </a></td>
				 	<td style= "font-size: 110%; color: #7d7d7d"><strong>'.$authorInfo [1].'</strong></td>
					<td >'.$institution->fetch_array ()[1].'</td>
					<td>'.$authorInfo [3].'</td>
					<td>'.$authorInfo [2].'</td>';
					echo '</tr>';
				}
				echo '</table>';
			}

			if($allInstitutions == "All Institutions"){

				$query = "SELECT * FROM institution";

				$institutionQuery = select_generic_query ($query);

				echo '<table class="table table-striped">
						<tr style= "font-size: 135%">
						<th style= "color: #5d5d5d">Remove Institution</th>
					    <th style= "color: #5d5d5d;width:40%">Institution</th>
					    <th style= "color: #5d5d5d">Remove Investigator</th>
					    <th style= "color: #5d5d5d">Investigators</th>		
						</tr>';


				while ($institutionInfo = $institutionQuery -> fetch_array()){
					$investigators = select_institution_investigators ($institutionInfo [0], []);

					while ($investigator = $investigators->fetch_array()){

						echo '<tr>';

						echo '<td><a href = "removeInstitution.php?id='.$institutionInfo[0].'"><input type = "button" class="btn btn-secondary" name="author" id="removeButton" style="background-color:#283A06; color: white" value = "Remove"><input type = "hidden" value = "'.$institutionInfo [1].'" /><input type = "hidden" value = "'.$institutionInfo [1].'" /></a></td>

						<td>'.$institutionInfo [1].'</td>';
						
						echo '<td><a href = "removeInstitution.php?id='.$investigator[0].'"><input type = "button" class="btn btn-secondary" name="author" id="removeButton" style="background-color:#283A06; color: white" value = "Remove"><input type = "hidden" value = "'.$investigator [1].'" /><input type = "hidden" value = "'.$investigator [1].'" /></a></td>

						<td ><a href = "authorInfo.php?author='.$investigator[1].'&id='.$investigator[0].'" style = "text-decoration-color: none; color:#808080; font-size: 110% ">'.$investigator[1].'</a></td>';

					}
					
				}
				echo '</tr>';
				echo '</table>';	
			}

			if($institute !=""){

				$query = "SELECT * FROM institution WHERE UPPER(name) LIKE UPPER ('%$institute%')";

				$institutionQuery = select_generic_query ($query);

				echo '<table class="table table-striped">
						<tr style= "font-size: 135%">
						<th style= "color: #5d5d5d">Remove Institution</th>
					    <th style= "color: #5d5d5d;width:40%" >Institution</th>
					    <th style= "color: #5d5d5d">Remove Investigator</th>
					    <th style= "color: #5d5d5d">Investigators</th>		
						</tr>';


				while ($institutionInfo = $institutionQuery -> fetch_array()){
					$investigators = select_institution_investigators ($institutionInfo [0], []);

					while ($investigator = $investigators->fetch_array()){

						echo '<tr>';

						echo '<td><a href = "removeInstitution.php?id='.$institutionInfo[0].'"><input type = "button" class="btn btn-secondary" name="author" id="removeButton" style="background-color:#283A06; color: white" value = "Remove"><input type = "hidden" value = "'.$institutionInfo [1].'" /><input type = "hidden" value = "'.$institutionInfo [1].'" /></a></td>

						<td>'.$institutionInfo [1].'</td>';
						
						echo '<td><a href = "removeInstitution.php?id='.$investigator[0].'"><input type = "button" class="btn btn-secondary" name="author" id="removeButton" style="background-color:#283A06; color: white" value = "Remove"><input type = "hidden" value = "'.$investigator [1].'" /><input type = "hidden" value = "'.$investigator [1].'" /></a></td>

						<td ><a href = "authorInfo.php?author='.$investigator[1].'&id='.$investigator[0].'" style = "text-decoration-color: none; color:#808080; font-size: 110% ">'.$investigator[1].'</a></td>';

					}
					
				}
				echo '</tr>';
				echo '</table>';	
			}

			
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