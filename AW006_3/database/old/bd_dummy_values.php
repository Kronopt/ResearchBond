<?php

// Settings
$dbhost = "appserver.di.fc.ul.pt";
$dbuser = "aw006";
$dbpass = "passwordchata";

// Connection
$connection = mysqli_connect($dbhost, $dbuser, $dbpass) or die ("Error connecting to mysql: " 
. mysqli_connect_error());
mysqli_select_db($connection , $dbuser);

// Insert dummie values into the database
$insert = "INSERT INTO Author VALUES 
(NULL, 'Joao', '20', '1', 'UL;FC', 'paper1;paper2', 'Andre;Andreia;Maria'),
(NULL, 'Maria', 20, 2, 'UL;FC', 'paper2;paper3', 'Andreia;Joao'),
(NULL, 'Joaquim', 20, 5, 'Tecnico', 'paper2;paper3;paper4', 'Andreia;Maria'),
(NULL, 'Alberto', 20, 40, 'UL;FC', 'paper2;paper3;paper4;paper5', 'Tiburcio'),
(NULL, 'Tiburcio', 20, 60, 'Tecnico', 'paper1', 'Alberto'),
(NULL, 'Ana', 20, 60, 'Tecnico', 'paper1;paper4;paper5', 'Alberto;Antonio'),
(NULL, 'André', 20, 60, 'Tecnico', 'paper1;paper3', 'Alberto'),
(NULL, 'Antonieta', 1, 5, 'Tecnico;UL', 'paper4;paper5', 'Alberto;Tiburcio');
INSERT INTO Institution VALUES 
(NULL, 'A', 'Andre;Andreia;Maria'),
(NULL, 'B', 'Antonio;Andreia;Maria'),
(NULL, 'C', 'Joaquim;Andreia;Maria'),
(NULL, 'D', 'Jose'),
(NULL, 'E', 'Andreia'),
(NULL, 'F', 'Antonio;Maria;Jose'),
(NULL, 'G', 'Andreia;Carlos;Jose'),
(NULL, 'H', 'Jose;Maria');
INSERT INTO Publication VALUES 
(NULL, 'paper1', 'paper1 is about paper5', '5', 'Joaquim;Andreia;Maria'),
(NULL, 'paper2', 'paper2 is about paper1', '10', 'Joaquim;Andreia;Maria;Antonio'),
(NULL, 'paper3', 'paper3 is about paper2', '20', 'Maria'),
(NULL, 'paper4', 'paper4 is about paper3', '100', 'Jose;Joaquim;Andreia;Maria'),
(NULL, 'paper5', 'paper5 is about paper3', '200', 'Jose;Maria'),
(NULL, 'paper6', 'paper6 is about paper3', '300', 'Joaquim;Andreia;Maria'),
(NULL, 'paper7', 'paper7 is about paper2', '400', 'Jose;Andreia;Maria'),
(NULL, 'paper8', 'paper8 is about paper4', '1000', 'Andreia;Maria;Jose')";

mysqli_multi_query($connection, $insert) or die("Error, query failed: " 
. mysqli_error($connection));

mysqli_close($connection);
?>
