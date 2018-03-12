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
  .button{

  }
  #content {padding-bottom:200px};
  </style>

</head>

<body>
  <script>
  $(document).ready(function(){
      $("input").click(function(){
        if (this.id=="addCheckbox") {
          $('#addChecked').toggle();
          $('#removeChecked').hide();
        }else if (this.id=="removeCheckbox") {
          $('#removeChecked').toggle();
          $('#addChecked').hide();
        } else if (this.id == 'loadDB'){
        	alert ('Data base has been load')
        }
      });
  });


  </script>

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

<div id='content'>
    <div class='container'>
      <div class="row">
      <!-- FALTA O add só uma caixa-->
         <div class="col-xs-2 col-md-6">
           <div class="checkbox">
                <label> <input type="button" class="btn btn-secondary" name="test" id="addCheckbox" style="background-color:#283A06; color: white; font-size: 120%" value="Add/Update"></label>  
              <label> <input type="button" class="btn btn-secondary" name="test" id="removeCheckbox" style="background-color:#283A06; color: white; font-size: 120%" value="Remove"></label>
              <label><a href="http://appserver.di.fc.ul.pt/~aw006/ResearchBond/scraperAlldb.php"> <button class="btn btn-secondary" name="test" id="loadDB" style="background-color:#283A06; color: white; font-size: 120%" value="Load Data Base" >Load Data Base</a></label>
            </div>
           </div>
           <div class='addcheck' id='addChecked'>
          <div class="col-xs-4 col-md-8">
          <p style="color: #446600; font-size: 115%"><strong>Add/Update Institution data:</strong></p>
          <form action='../scraperInstitution.php' method='post'>
              <p style="color: #446600"> Enter an Institution/University name: <input type='text' name='name'/></p>
              <p><input  type="submit" class="btn btn-secondary" style="background-color:#283A06; color: white" value="Add/Update Institution" /></p>
              </form>

          <br>
          <br>  
          <p style="color: #446600; font-size: 115%"><strong> Add/Update a specific Author (fill-in both fields): </strong></p>
          <form action='../scraperAuthor.php' method='post'>
              <p style="color: #446600"> Name: <input type='text' name='name' autocomplete="off" /></p>
              <p style="color: #446600"> Institution: <input type='text' name='institution' autocomplete="off" /></p>
              <p><input type="checkbox" name="checkFlag" value='true'/>
            <label style="color: #446600">Search full publication data</label></p>
              <p><input type="submit" class="btn btn-secondary" style="background-color:#283A06; color: white" value="Add/Update Author"/></p>
              </form>
         </div>
         </div>
        <script type="text/javascript"> $('#addChecked').hide ()</script>

         <div class='removeCheck' id="removeChecked">
         <br>
         <br>
         <br>
         <br>
         <div class="col-xs-4 col-md-6">
            <form method='post' action="searchRemove.php" id='searchRemove-form'>
            	<script type="text/javascript">
            	$('#searchRemove-form').on('submit', function() {
				    var id = $('#searchInput').val();
				    var formAction = $('#searchRemove-form').attr('searchInput');
				    $('#searchRemove-form').attr('searchInput', formAction + id);
				});
            </script>
            	<div class="pull-right" style=" margin-right:-30%">
						<input type="submit" class="btn btn-secondary" style="background-color:#283A06; color: white" name="allAuthors" value="All Authors" /> 
				</div>
					<fieldset class="input-group-vertical">
						<div class="form-group">
							<label class="sr-only">by Author</label>
							<input type="text"  id="search-box" class="form-control" placeholder="by Author" name ="author" autocomplete=off />
							<div class="dropdown-content" id="suggestion-box"></div>
						</div>
					</fieldset>

					<div class="pull-right" style=" margin-right:-32%">
						<input type="submit" class="btn btn-secondary" style="background-color:#283A06; color: white" name="allInstitutions" value="All Institutions"/> 
					</div>

					<fieldset class="input-group-vertical">
						<div class="form-group">
							<label class="sr-only">by Affiliation</label>
							<input type="text" class="form-control" placeholder="by Institute" name = "institute" autocomplete="off"> 
						</div>

					</fieldset>
				<input type="submit" id = "searchInput" class="btn btn-secondary" style="background-color:#283A06; color: white" value="Search" /> 
              
            </form>
            
          </div>
         <script type="text/javascript"> $('#removeChecked').hide ()</script>
        </div>
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
