<?php

// Settings
$dbhost = "appserver.di.fc.ul.pt";
$dbuser = "aw006";
$dbpass = "passwordchata";

// Connection
$connection = mysqli_connect($dbhost, $dbuser, $dbpass) or die ("Error connecting to mysql: " 
. mysqli_connect_error());
mysqli_select_db($connection , $dbuser);

// Create tables
$create_tables = "CREATE TABLE 
	Author(
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	name TEXT NOT NULL, 
	citations NUMERIC(9,0), 
	hindex NUMERIC(9,0), 
	institution TEXT, 
	publication TEXT, 
	co_author TEXT
	);
	CREATE TABLE 
	Institution(
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	name TEXT NOT NULL, 
	investigator TEXT
	);
	CREATE TABLE 
	Publication(
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	title TEXT NOT NULL, 
	abstract TEXT,
	citations NUMERIC(9,0),
	author TEXT
	);";

mysqli_multi_query($connection, $create_tables) or die("Error, query failed: " 
. mysqli_error($connection));
mysqli_close($connection);
?>
