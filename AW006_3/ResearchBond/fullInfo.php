<!DOCTYPE html>
<html lang="en">
<?php
$curl = curl_init();

$curl='http://api.altmetric.com/v1/id/241939?key=1db00bd164b9fd6c33825cb35b1d9242';

curl_exec ($curl);

curl_close ($curl);
?>
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
<?php

require '/home/aw006/database/bd_functions.php';
		$authorName = $_GET['author'];
		$authorId = $_GET['id'];
?>
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
	<div class="container">
		<h1 style="color: #446600;"> <strong><?php echo $authorName ?></strong></h1>
			<div class="pull-left " style="margin-left:-1%">
				    <ul class="nav navbar-left" >
			
			<li><h2 style="color: #446600;"> <strong> Author Information: </strong></h2></li>
			<li ><h4 style="color: #446600;"> <strong> Institution: </strong></h4></li>
			<?php
				$x=0; 
				$institutions= select_author_institutions($authorId);
				while (($inst = $institutions -> fetch_array ()[1])) {
				 	echo '<li style= "color: #5d5d5d; margin-left:7px; list-style-position:initial; padding-left:4px;text-indent: -6px;">'.$inst.'</li>';
				 	$x ++;
				 };


			?>
			<li ><h4 style="color: #446600;"> <strong> Coauthors: </strong></h4></li>
			<?php 
				$coauthors= select_author_coauthors($authorId);
				$x=0;
				while (($coau = $coauthors -> fetch_array ()[1])) {
				 	echo '<li style= "color: #5d5d5d; margin-left:7px; list-style-position:initial; padding-left:4px;text-indent: -6px;">'.$coau.'</li>';
				 	$x ++;
				 };

			?>
			<li ><h4 style="color: #446600;"> <strong> Publications: </strong></h4></li>
			<?php 
				$x=0;
				$publications= select_author_publications($authorId);
				while (($pub = $publications -> fetch_array ())) {
				 	echo "<li list-style-type: 'circle'><div style= 'color: #5d5d5d;margin-left:7px; list-style-position:initial; padding-left:5px;text-indent: -6px;''></div>".$pub[1]."
				 	<script type='text/javascript' src='https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js'></script>";

					if ($pub[6] != ''){
						echo "<div data-badge-details='right' data-badge-type='medium-donut' data-doi=".$pub[6]." data-hide-no-mentions='true' class='altmetric-embed' style='padding-left:100px'></div>";
					};

				 	echo "</li>";
				 	$x ++;
				 };

			?>
		
			
		</ul>	

		</div>

	
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