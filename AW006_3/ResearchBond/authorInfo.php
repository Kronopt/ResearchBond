<!DOCTYPE html>
<html lang="en">
<head>
	<title>aw006 project</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="https://d3js.org/d3.v3.min.js" charset="utf-8"></script>

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


<body vocab="http://xmlns.com/foaf/spec/" typeof="foaf:Agent">
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

	<div class="container">

	<script>
			var idAuthor = <?php echo $authorId ?>

			var authorXML = 'http://appserver.di.fc.ul.pt/~aw006/webservice/author/'+idAuthor
			var svgWidth = 0.64 * window.innerWidth;
			var svgHeight = 1.2 * window.innerHeight;
			
			// Team_percentage E Team_citations para cada co-autor
			<?php
				
				$result = select_author_coauthors($authorId, ["team_percentage", "team_citations"]);

				$teamPercentage = array();
				$teamCitations = array();

				while ($line = mysqli_fetch_array($result, MYSQL_ASSOC)) {
					$teamPercentage[] = $line["team_percentage"];
					$teamCitations[] = $line["team_citations"];
				}

				$teamPercentage = json_encode($teamPercentage);
				$teamCitations = json_encode($teamCitations);

				echo "var teamPercentage = " . $teamPercentage . ";\n";
				echo "			var teamCitations = " . $teamCitations . ";\n";
			?>
			
			// Force layout initialization
			var force = d3.layout.force()
				.charge(-1000)
				.linkDistance(function() {
					// random distance inside the predetermined space available
					return Math.floor(Math.random() * ((Math.min(svgWidth / 2, svgHeight / 2) - 150)
					- 120 + 1)) + 120;
				})
				.gravity(0)
				.size([svgWidth, svgHeight]);
			
			// Empty SVG
			var svg = d3.select("body")
				.append("svg")
				.attr("width", svgWidth)
				.attr("height", svgHeight)
				.style("float","right")
				.append("g");
			
			// XML parser
			d3.xml(authorXML, "application/xml", function(error, xml) {
				if (error) throw error;
				
				// Main author and co-authors' selection
				var nodes = d3.select(xml).selectAll("author, co-author")[0];
				var root = nodes[0];
				var links = nodes.slice(1).map(function(d) { return {source: d, target: root}; });
				
				// Main author's location on the SVG
				root.x = svgWidth / 2;
				root.y = svgHeight / 2;
				root.fixed = true; // Fixed main author

				force
					.nodes(nodes)
					.links(links)
					.start();
				
				// Links between co-authors and author
				var link = svg.selectAll(".link")
					.data(links)
					.enter()
					.append("line")
					.attr("stroke", "#cccccc")
					.attr("stroke-width", function(d, i) {
						var value = parseFloat(teamPercentage[i]);
						if (value < 0.1) { value = 0.1}
						value = value * 10
						var value = (((value - 1) * (20 - 1)) / (10 - 1)) + 1
						return (Math.ceil(value)).toString() + "px";
					})
					.attr("class", "link")
					.attr("property", "foaf:knows"); // Semantics
				
				// Empty nodes
				var node = svg.selectAll(".node")
					.data(nodes)
					.enter()
					.append("g")
					.attr("class", "node")
					.call(force.drag);
				
				//
				// Co-authors's attributes
				//
				
				// Make picture circular
				node.append("clipPath")
					.attr("id", "co-authorClip")
					.append("circle")
					// A definição do atributo 'r' só funciona como deve de ser no Chrome, por alguma razão...
					// O array teamCitations, para este caso, não tem índices nos demais browsers...
					// Mas para os restantes casos em que o array é usado já tem...
					.attr("r", function(d,i) {
						var value = parseInt(teamCitations[i - 1]);
						if (value) {
							if (value < 1) { value = 1 }
						} else { value = 1 }
						value = (((value - 1) * (90 - 40)) / (1000 - 1)) + 40
						return (Math.ceil(value) / 2) - 2;
					});
				
				// Circle around picture
				node.append("circle")
					.attr("id", "circleAround")
					.attr("fill", "#cccccc")
					.attr("stroke", "black")
					.attr("stroke-width", "4px")
					.attr("opacity", 0.8)
					.attr("filter", "alpha(opacity=40)") // IE8 and earlier
					.attr("r", function(d,i) { 
						var value = parseInt(teamCitations[i - 1]);
						if (value) {
							if (value < 1) { value = 1 }
						} else { value = 1 }
						var value = (((value - 1) * (90 - 40)) / (1000 - 1)) + 40
						return Math.ceil(value) / 2 ;
					});
				
				// Picture
				node.append("image")
					.attr("id", "authorPhoto")
					.attr("xlink:href", function(d) {
						var authorPhoto = d.getElementsByTagName("photo")[0].textContent.substring(1);
						if (authorPhoto) { authorPhoto = "/~aw006".concat(authorPhoto); }
						else { authorPhoto = "/~aw006/fotos/random.jpg"; }
						return authorPhoto;
					})
					.attr("x", function(d,i) { 
						var value = parseInt(teamCitations[i - 1]);
						if (value) {
							if (value < 1) { value = 1 }
						} else { value = 1 }
						var value = (((value - 1) * (90 - 40)) / (1000 - 1)) + 40
						return - Math.ceil(value) / 2;
					})
					.attr("y", function(d,i) { 
						var value = parseInt(teamCitations[i - 1]);
						if (value) {
							if (value < 1) { value = 1 }
						} else { value = 1 }
						var value = (((value - 1) * (90 - 40)) / (1000 - 1)) + 40
						return - Math.ceil(value) / 2;
					})
					.attr("width", function(d,i) {
						var value = parseInt(teamCitations[i - 1]);
						if (value) {
							if (value < 1) { value = 1 }
						} else { value = 1 }
						var value = (((value - 1) * (90 - 40)) / (1000 - 1)) + 40
						return Math.ceil(value);
					})
					.attr("height", function(d,i) { 
						var value = parseInt(teamCitations[i - 1]);
						if (value) {
							if (value < 1) { value = 1 }
						} else { value = 1 }
						var value = (((value - 1) * (90 - 40)) / (1000 - 1)) + 40
						return Math.ceil(value);
					})
					.attr("clip-path", function(d,i) { return "url(#co-authorClip)"; })
					.attr("typeof", "foaf:Person") // Semantics
					.attr("property", "foaf:img");
				
				// Name background
				node.append("rect")
					.attr("id", "nameBackground")
					.attr("rx", 6)
					.attr("ry", 6)
					.attr("x", function(d) {
						return 0 - (d.getElementsByTagName("name")[0].textContent.length * 8) / 2;
					})
					.attr("y", function(d,i) { 
						var value = parseInt(teamCitations[i - 1]);
						if (value) {
							if (value < 1) { value = 1 }
						} else { value = 1 }
						var value = (((value - 1) * (90 - 40)) / (1000 - 1)) + 40
						return (Math.ceil(value) / 2) + 4;
					})
					.attr("width", function(d) {
						return d.getElementsByTagName("name")[0].textContent.length * 8;
					})
					.attr("height", 18)
					.attr("opacity", 0.4)
					.attr("filter", "alpha(opacity=40)"); // IE8 and earlier
				
				// Name
				node.append("text")
					.attr("id", "authorName")
					.attr("font-size", "0.7em")
					.attr("font-family", '"Verdana", "Geneva", "sans-serif"')
					.attr("fill", "white")
					.attr("dy", function(d,i) { 
						var value = parseInt(teamCitations[i - 1]);
						if (value) {
							if (value < 1) { value = 1 }
						} else { value = 1 }
						var value = (((value - 1) * (90 - 40)) / (1000 - 1)) + 40
						return (Math.ceil(value) / 2) + 17;
					})
					.attr("text-anchor", "middle")
					.text(function(d) {
						return d.getElementsByTagName("name")[0].textContent;
					})
					.attr("typeof", "foaf:Person") // Semantics
					.attr("property", "foaf:name");
				
				//
				// Main author's attributes
				//
				
				var authorPictureSize = function(){
					var authorHindex = root.getElementsByTagName("hindex")[0].textContent;
					if (authorHindex) {
						authorHindex = parseInt(authorHindex);
						if (authorHindex < 1) { authorHindex = 1; }
					} else { authorHindex = 1; }
					authorHindex = (((authorHindex - 1) * (150 - 100)) / (140 - 1)) + 100;
					return authorHindex;
				};
				
				// Make picture circular
				node.append("clipPath")
					.attr("id", "mainAuthorClip")
					.append("circle")
					.attr("r", (authorPictureSize() / 2) - 7);
				
				// Circle around picture
				svg.select("#circleAround")
					.attr("stroke-width", "7px")
					.attr("r", (authorPictureSize() / 2) - 7);
				
				// Picture
				svg.select("#authorPhoto")
					.attr("x", - authorPictureSize() / 2)
					.attr("y", - authorPictureSize() / 2)
					.attr("width", authorPictureSize())
					.attr("height", authorPictureSize())
					.attr("clip-path", function(d,i) { return "url(#mainAuthorClip)"; });
				
				// Name
				svg.select("#authorName")
					.attr("dy", authorPictureSize() / 2 + 13);
				
				svg.select("#nameBackground")
					.attr("y", authorPictureSize() / 2);
				
				force.on("tick", function() {
					link.attr("x1", function(d) { return d.source.x; })
					.attr("y1", function(d) { return d.source.y; })
					.attr("x2", function(d) { return d.target.x; })
					.attr("y2", function(d) { return d.target.y; });

				node.attr("cx", function(d) { return d.x; })
					.attr("cy", function(d) { return d.y; })
					.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
				});
			});
		</script>
		
	</div>
	

	
	


	<div style="margin-left:2.5%">
	<h2 style="color: #446600;"> <strong><?php echo $authorName ?></strong></h2>
	<div class="col-md-4">
	    
	    <ul class="nav navbar-left"  >
			
			<li><h3 style="color: #446600;"> <strong> Author Information: </strong></h3></li>
			<li> </li>
			<li style= "color: #446600"><strong>Institution:</strong></li>
			<li> </li>
			<?php
				$x=0; 
				$institutions= select_author_institutions($authorId);
				while (($inst = $institutions -> fetch_array ()[1]) and ($x<5)) {
				 	echo '<li style= "color: #5d5d5d; margin-left:7px; list-style-position:initial; padding-left:4px;text-indent: -20px;" typeof="Organization" property="foaf:name">'.$inst.'</li>';
				 	$x ++;
				 };


			?>
			<li> </li>
			<li style= "color: #446600"><strong>Coauthors: </strong></li>
			<?php 
				$coauthors= select_author_coauthors($authorId);
				$x=0;
				while (($coau = $coauthors -> fetch_array ())and ($x<5)) {
				 	echo '<li><a href= "http://appserver.di.fc.ul.pt/~aw006/ResearchBond/authorInfo.php?author='.$coau[1].'&id='.$coau[0].'" style= "color: #5d5d5d;text-indent: -20px;">'.$coau[1].'</a></li>';
				 	$x ++;
				 };

			?>
			<li> </li>
			<li style= "color: #446600"><strong>Publications: </strong></li>
			<li> </li>
			<?php 
				$x=0;
				$publications= select_author_publications($authorId);
				while (($pub = $publications -> fetch_array ()[1]) and ($x<5)) {
				 	echo '<li style= "color: #5d5d5d;margin-left:7px; list-style-position:initial; padding-left:7px;text-indent: -20px;" typeof="foaf:Person" property="foaf:publication">'.$pub.'</li> <li></li>';
				 	$x ++;
				 };
				 echo "<li></li>";

			?>
		
			<li> </li>
			
		</ul>	

	
	<br>
	</div>
	</div>


	<div class='container'>
	<br>
	
	<?php 

	echo '<a href="fullInfo.php?author='.$authorName.'&id='.$authorId.'"><button type="submit" class="btn btn-secondary" style="background-color:#283A06; color: white"> See Full Information</button></a>
		<br>
		<br>
		
		<a href="clientFeedback.php?id='.$authorId.'"><button type="submit" class="btn btn-secondary" style="background-color:#283A06; color: white">Click here if the information was incorrect</button></a>';



		
		//<a href="clientFeedback.php?author='.$authorName.'&id='.$authorId.'">
	?>

		
	<br>
	<br>
	<br>
	<br>
	
		

			
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