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
						<li role="presentation" class="active"><a href="aboutUs.html" style="color: #446600; font-size: 150%"><strong> About Us </strong></a></li>
						<li role="presentation" class="active"><a href="admin.html" style="color: #446600; font-size: 150%"><strong> Admin </strong></a></li>
					</ul>
				</div>
		    </div>	
	    </div>	
	</div>
	


	<?php

	$authorId = $_GET['id'];

	require '/home/aw006/database/bd_functions.php';
	//$listIDs = select_author($select = ['id']);
	//while($ID = $listIDs -> fetch_array()) {

	$query = "SELECT feedPercent FROM feedBack WHERE entry_id =".$authorId;
	$value = select_generic_query($query);
	$percent= $value -> fetch_array();
	$per = $percent[0]-0.02;
	
	
	
	$query = "UPDATE feedBack SET feedPercent=".$per." WHERE entry_id=".$authorId; 
	select_generic_query($query);

	?>


	<script type="text/javascript"> alert ("Thank you for the feedback!! Author's information confidence is <?php echo $per ?> ")
		window.history.back();

	</script>


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


