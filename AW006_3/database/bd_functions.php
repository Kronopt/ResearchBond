<?php
/*
----------------------------------------------------------------------------
----------------------------------------------------------------------------

Ficheiro com comandos de ligaчуo р base de dados.

Todo o tipo de interacчуo com a base de dados passa por aqui, nomeadamente
comandos de ligaчуo, criaчуo, alteraчуo, etc de valores das tabelas existentes.


#####################
#   Como utilizar:  #
#####################

- Importar o ficheiro nas primeiras linhas do script, assim:
	require '/home/aw006/database/bd_functions.php';
- As funчѕes definidas aqui estуo agora prontas a ser usadas no script.


##########################
#  Funчѕes disponэveis:  #
##########################

Listagem e descriчуo de todas as funчѕes disponэvel no ficheiro:
	db_functions_doc.txt

----------------------------------------------------------------------------
----------------------------------------------------------------------------
*/


// server_info
//
// Funчуo que define o nome do servidor, nome de utilizador e password.
// Alteraчѕes ao servidor, nome de utilizador e password fazem-se aqui.
//
// PARТMETROS:
// $what
//	str, Valor pretendido ('server', 'user' ou 'pass')
//
// RETURN:
//	str, Informaчуo pretendida
//
function server_info($what){
	if ($what == 'server') {
		return 'appserver.di.fc.ul.pt';
	} else if ($what == 'user') {
		return 'aw006';
	} else if ($what == 'pass') {
		return 'passwordchata';
	}
}


// initiate_connection
//
// Funчуo de ligaчуo р base de dados.
// Usada internamente por todas as funчѕes.
// Nуo щ necessсrio chamar esta funчуo para usar as demais funчѕes aqui disponэveis
// Caso esta funчуo seja utilizada, a ligaчуo р base de dados pode ser fechada com a funчуo:
// close_connection
//
// PARТMETROS:
// $server_sql
//	str, URL do servidor sql
// $user_sql
//	str, Nome de utilizador
// $pass_sql
//	str, Password
//
// RETURN:
//	mysqli, Ligaчуo р base de dados
//
function initiate_connection($server_sql, $user_sql, $pass_sql) {
	$database_connection = mysqli_connect($server_sql, $user_sql, $pass_sql) or die('ERROR 
		connecting to database: ' . mysqli_connect_error());
	mysqli_select_db($database_connection, $user_sql) or die("ERROR Could not select database \"$user_sql\"");
	mysqli_set_charset($database_connection, 'utf8') or die('ERROR Could not set charset');
	return $database_connection;
}


// close_connection
//
// Funчуo de fecho de ligaчуo р base de dados
// Usada internamente por todas as funчѕes.
// Usar em caso de chamamento explэcito da funчуo initiate_connection
//
// PARТMETROS:
// $database_connection
//	mysqli, Objecto de ligaчуo р base de dados
//
function close_connection($database_connection) {
	mysqli_close($database_connection) or die('ERROR closing mysql connection');
}


// drop_tables
//
// Apaga as tabelas especificadas
//
// PARТMETROS:
// $columns
//	list[str], tabelas
//
function drop_tables($tables) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	$query = 'DROP TABLE IF EXISTS ';
	foreach ($tables as $table) {
		$table = mysqli_real_escape_string($connection, $table);
		$query .= "'$table', ";
	}
	
	if ($query != 'DROP TABLE IF EXISTS ') {
		$query = substr($query, 0, -2);
		$result = mysqli_query($connection, $query) or die('ERROR DROP_TABLES query failed: ' . mysqli_error($connection));
	}
	
	close_connection($connection);
}


// create_tables
//
// Cria tabelas prщ definidas (author, publication, institution, publishes, work, team)
//
function create_tables() {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	$createTables = "
	CREATE TABLE author(
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	name TEXT NOT NULL, 
	citations NUMERIC(9,0), 
	hindex NUMERIC(9,0), 
	link VARCHAR(255), 
	photo VARCHAR(255)
    );
    CREATE TABLE institution(
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	name TEXT NOT NULL, 
	link VARCHAR(255)
    );
	CREATE TABLE publication(
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	title TEXT NOT NULL, 
	abstract TEXT, 
	citations NUMERIC(9,0), 
    journal TEXT, 
    year NUMERIC(9,0), 
	doi VARCHAR(30), 
	doi_percentage NUMERIC(2,2), 
    link VARCHAR(255)
    );
    CREATE TABLE team(
    author_id int, 
    coAuthor_id int, 
	team_percentage numeric(2,2), 
	team_citations numeric(9,0), 
    PRIMARY KEY (author_id, coAuthor_id), 
    FOREIGN KEY (author_id) REFERENCES author(id), 
    FOREIGN KEY (coAuthor_id) REFERENCES author(id)
    );
    CREATE TABLE work(
    author_id int, 
    institution_id int, 
    PRIMARY KEY (author_id, institution_id), 
    FOREIGN KEY (author_id) REFERENCES author(id), 
    FOREIGN KEY (institution_id) REFERENCES institution(id)
    );
    CREATE TABLE publishes(
    author_id int, 
    publication_id int, 
    PRIMARY KEY (author_id, publication_id), 
    FOREIGN KEY (author_id) REFERENCES author(id), 
    FOREIGN KEY (publication_id) REFERENCES publication(id)
    );
	CREATE TABLE feedBack(
    entry_id int, 
    feddPercent numeric(2,2), 
    PRIMARY KEY (entry_id), 
    FOREIGN KEY (entry_id) REFERENCES author(id)
	ON DELETE CASCADE
    );";
	
	$result = mysqli_multi_query($connection, $createTables) or die('ERROR CREATE_TABLES query failed ' . mysqli_error($connection));
	mysqli_free_result($result);
    close_connection($connection);
}


############
#  AUTHOR  #
############


// add_author
//
// Adiciona 1 autor р base de dados.
// Sѓ o nome щ obrigatѓrio, todos os outros parтmetros podem ser omitidos.
// Para omitir:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar 'NULL' (citations, hindex, link e photo) ou [] (nas listas)
// O id do autor щ incrementado automaticamente (definido na prѓpria base de dados).
//
// PARТMETROS:
// $name:
//	str, Nome do autor
// $citations:
//	int, Nњmero de citaчѕes do autor
// $hindex:
//	int, Valor do h-index do autor
// $link:
//	str, Link do autor (Google Scholar)
// $photo:
//	str, Link para a fotografia do autor
// $publishes_list:
//	list[int], IDs das publicaчѕes do autor
// $team_list:
//	list[int], IDs dos co-autores do autor
// $work_list:
//	list[int], IDs das instituiчѕes a que o autor pertence
//
// RETURN:
//	int, ID do autor adicionado
//
function add_author($name, $citations = 'NULL', $hindex = 'NULL', $link = 'NULL', $photo = 'NULL', $publishes_list = [], $team_list = [], $work_list = []) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	//Add quotation marks to not null parameters and escape possible conflicting characters
	$name = mysqli_real_escape_string($connection, $name);
	if ($citations != 'NULL') {$citations = "'$citations'";}
	if ($hindex != 'NULL') { $hindex = "'$hindex'";}
	if ($link != 'NULL') {
		$link = mysqli_real_escape_string($connection, $link);
		$link = "'$link'";
	}
	if ($photo != 'NULL') {
		$photo = mysqli_real_escape_string($connection, $photo);
		$photo = "'$photo'";
	}
	
	// Insert into "author" table
	$query = "INSERT INTO author (name, citations, hindex, link, photo) VALUES ('$name', $citations, $hindex, $link, $photo);";
	$result = mysqli_query($connection, $query) or die('ERROR ADD_AUTHOR query failed: ' . mysqli_error($connection));
	$author_id = mysqli_insert_id($connection);
	close_connection($connection);
	
	// Insert into "publishes" table
	insert_author_publications($author_id, $publishes_list, 'ADD_AUTHOR');
	
	// Insert into "team" table
	insert_author_coauthors($author_id, $team_list, 'ADD_AUTHOR');
	
	// Insert into "work" table
	insert_author_institutions($author_id, $work_list, 'ADD_AUTHOR');

	return $author_id;
}


// insert_author_publications
//
// Insere valores na tabela publishes para um determinado autor.
// Usado pelo add_author e update_author
//
// PARТMETROS:
// $author_id:
// 	int/str numщrico, ID do autor
// $publication_id_list:
// 	list[int/str numщrico], IDs de publicaчѕes do autor
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function insert_author_publications($author_id, $publication_id_list, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	foreach ($publication_id_list as $publication_id) {
		$query = "INSERT INTO publishes (author_id, publication_id) VALUES ('$author_id', '$publication_id');";
		$result = mysqli_query($connection, $query) or die("ERROR $error insert into publishes query failed: " . mysqli_error($connection));
	}
	
	close_connection($connection);
}


// insert_author_coauthors
//
// Insere valores na tabela team para um determinado autor.
// Usado pelo add_author e update_author
//
// PARТMETROS:
// $author_id:
// 	int/str numщrico, ID do autor
// $coAuthor_id_list:
// 	list[int/str numщrico], IDs de co-autores do autor
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function insert_author_coauthors($author_id, $coAuthor_id_list, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	foreach ($coAuthor_id_list as $coAuthor_id) {
		$query = "INSERT INTO team (author_id, coAuthor_id) VALUES ('$author_id', '$coAuthor_id');";
		$result = mysqli_query($connection, $query) or die("ERROR $error insert into team query failed: " . mysqli_error($connection));
	}
	
	close_connection($connection);
}


// insert_author_institutions
//
// Insere valores na tabela work para um determinado autor.
// Usado pelo add_author e update_author
//
// PARТMETROS:
// $author_id:
// 	int/str numщrico, ID do autor
// $institution_id_list:
// 	list[int/str numщrico], IDs de instituiчѕes do autor
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function insert_author_institutions($author_id, $institution_id_list, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	foreach ($institution_id_list as $institution_id) {
		$query = "INSERT INTO work (author_id, institution_id) VALUES ('$author_id', '$institution_id');";
		$result = mysqli_query($connection, $query) or die("ERROR $error insert into work query failed: " . mysqli_error($connection));
	}

	close_connection($connection);
}


// update_author
//
// Actualiza 1 autor jс existente na base de dados.
// Dando o id, щ possэvel actualizar qualquer informaчуo desse autor.
// Tanto se pode actualizar toda a informaчуo como somente parte dela.
// O њnico parтmetro obrigatѓrio щ o id (no caso de ser dado somente o id, nada acontece)
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (name, citations, hindex e link) ou [] (nas listas)
//
// PARТMETROS:
// $author_id:
// 	int/str numщrico, ID do autor
// $name:
//	str, Nome do autor
// $citations:
//	int, Nњmero de citaчѕes do autor
// $hindex:
//	int, Valor do h-index do autor
// $link:
//	str, Link do autor (Google Scholar)
// $photo:
//	str, Link para a fotografia do autor
// $publishes_list:
//	list[int], IDs das publicaчѕes do autor
// $team_list:
//	list[int], IDs dos co-autores do autor
// $work_list:
//	list[int], IDs das instituiчѕes a que o autor pertence
//
function update_author($author_id, $name = false, $citations = false, $hindex = false, $link = false, $photo = false, $publishes_list = [], $team_list = [], $work_list = []) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	// Update "author" table
	$query = 'UPDATE author SET ';
	if (is_string($name)) {
		$name = mysqli_real_escape_string($connection, $name);
		$query .= "name = '$name', ";
	}
	if (is_int($citations)) {
		$query .= "citations = '$citations', ";
	}
	if (is_int($hindex)) {
		$query .= "hindex = '$hindex', ";
	}
	if (is_string($link)) {
		$link = mysqli_real_escape_string($connection, $link);
		$query .= "link = '$link', ";
	}
	if (is_string($photo)) {
		$photo = mysqli_real_escape_string($connection, $photo);
		$query .= "photo = '$photo', ";
	}

	if ($query != 'UPDATE author SET ') {
		$query = substr($query, 0, -2);
		$query .= " WHERE author.id = '$author_id'";
		$result = mysqli_query($connection, $query) or die('ERROR UPDATE_AUTHOR query failed: ' . mysqli_error($connection));
	}

	close_connection($connection);
	
	// Update "publishes" table
	insert_author_publications($author_id, $publishes_list, 'UPDATE_AUTHOR');
	
	// Update "team" table
	insert_author_coauthors($author_id, $team_list, 'UPDATE_AUTHOR');
	
	// Update "work" table
	insert_author_institutions($author_id, $work_list, 'UPDATE_AUTHOR');
}


// delete_author
//
// Apaga 1 autor da base de dados e toda a sua informaчуo das tabelas publishes, team e work.
//
// PARТMETROS:
// $author_id:
//	int/str numщrico, ID do autor
//
function delete_author($author_id) {
	// Delete "publishes" associated data
	delete_author_publications($author_id, 'DELETE_AUTHOR');
	
	// Delete "team" associated data
	delete_author_coauthors($author_id, 'DELETE_AUTHOR');

	// Delete "work" associated data
	delete_author_institutions($author_id, 'DELETE_AUTHOR');
	
	// Delete from "author" table
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	$query = "DELETE from author WHERE author.id = '$author_id'";
	$result = mysqli_query($connection, $query) or die('ERROR DELETE_AUTHOR query failed: ' . mysqli_error($connection));
	close_connection($connection);
}


// delete_author_publications
//
// Apaga valores da tabela publishes associados a um determinado autor.
// Usado pelo delete_author
//
// PARТMETROS:
// $author_id:
// 	int/str numщrico, ID do autor
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function delete_author_publications($author_id, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	$query = "DELETE FROM publishes WHERE publishes.author_id = '$author_id';";
	$result = mysqli_query($connection, $query) or die("ERROR $error delete from publishes query failed: " . mysqli_error($connection));
	close_connection($connection);
}


// delete_author_coauthors
//
// Apaga valores da tabela team associados a um determinado autor.
// Usado pelo delete_author
//
// PARТMETROS:
// $author_id:
// 	int/str numщrico, ID do autor
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function delete_author_coauthors($author_id, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	$query = "DELETE FROM team WHERE team.author_id = '$author_id' OR team.CoAuthor_id = '$author_id';";
	$result = mysqli_query($connection, $query) or die("ERROR $error delete from team query failed: " . mysqli_error($connection));
	close_connection($connection);
}


// delete_author_institutions
//
// Apaga valores da tabela work associados a um determinado autor.
// Usado pelo delete_author
//
// PARТMETROS:
// $author_id:
// 	int/str numщrico, ID do autor
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function delete_author_institutions($author_id, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	$query = "DELETE FROM work WHERE work.author_id = '$author_id';";
	$result = mysqli_query($connection, $query) or die("ERROR $error delete from work query failed: " . mysqli_error($connection));
	close_connection($connection);
}


#################
#  INSTITUTION  #
#################


// add_institution
//
// Adiciona 1 instituiчуo р base de dados.
// Sѓ o nome щ obrigatѓrio, todos os outros parтmetros podem ser omitidos.
// O id da institutiчуo щ incrementado automaticamente (definido na prѓpria base de dados).
// Para omitir:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar 'NULL' (link)
//
// PARТMETROS:
// $name:
//	str, Nome da instituiчуo
// $link:
//	str, Link da instituiчуo (Google Scholar)
// $work_list:
//	list[int], IDs dos autores pertences р instituiчуo
//
// RETURN:
//	int, ID da instituiчуo adicionada
//
function add_institution($name, $link = 'NULL', $work_list = []) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	// Add quotation marks to not null parameters and escape possible conflicting characters
	$name = mysqli_real_escape_string($connection, $name);
	if ($link != 'NULL') {
		$link = mysqli_real_escape_string($connection, $link);
		$link = "'$link'";
	}
	
	// Insert into "institution" table
	$query = "INSERT INTO institution (name, link) VALUES ('$name', $link);";
	$result = mysqli_query($connection, $query) or die('ERROR ADD_INSTITUTION query failed: ' . mysqli_error($connection));
	$institution_id = mysqli_insert_id($connection);
	close_connection($connection);
	
	// Insert into "work" table
	insert_institution_investigators($institution_id, $work_list, 'ADD_INSTITUTION');
	
	return $institution_id;
}


// insert_institution_investigators
//
// Insere valores na tabela work para uma determinada instituiчуo.
// Usado pelo add_institution e update_institution
//
// PARТMETROS:
// $institution_id:
// 	int/str numщrico, ID da instituiчуo
// $author_id_list:
// 	list[int/str numщrico], IDs de autores pertencentes р instituiчуo
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function insert_institution_investigators($institution_id, $author_id_list, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	foreach ($author_id_list as $author_id) {
		$query = "INSERT INTO work (author_id, institution_id) VALUES ('$author_id', '$institution_id');";
		$result = mysqli_query($connection, $query) or die("ERROR $error insert into work query failed: " . mysqli_error($connection));
	}

	close_connection($connection);
}


// update_institution
//
// Actualiza 1 instituiчуo jс existente na base de dados.
// Dando o id, щ possэvel actualizar qualquer informaчуo dessa instituiчуo.
// Tanto se pode actualizar toda a informaчуo como somente parte dela.
// O њnico parтmetro obrigatѓrio щ o id (no caso de ser dado somente o id, nada acontece)
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (name e link)
//
// PARТMETROS:
// $institution_id:
// 	int/str numщrico, ID da instituiчуo
// $name:
//	str, Nome da instituiчуo
// $link:
//	str, Link da instituiчуo (Google Scholar)
// $work_list:
//	list[int], IDs dos autores pertences р instituiчуo
//
function update_institution($institution_id, $name = false, $link = false, $work_list = []) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	// Update "institution" table
	$query = 'UPDATE institution SET ';
	if (is_string($name)) {
		$name = mysqli_real_escape_string($connection, $name);
		$query .= "name = '$name', ";
	}
	if (is_string($link)) {
		$link = mysqli_real_escape_string($connection, $link);
		$query .= "link = '$link', ";
	}

	if ($query != 'UPDATE institution SET ') {
		$query = substr($query, 0, -2);
		$query .= " WHERE institution.id = '$institution_id'";
		$result = mysqli_query($connection, $query) or die('ERROR UPDATE_INSTITUTION query failed: ' . mysqli_error($connection));
	}

	close_connection($connection);
	
	// Update "work" table
	insert_institution_investigators($institution_id, $work_list, 'UPDATE_INSTITUTION');
}


// delete_institution
//
// Apaga 1 instituiчуo da base de dados e toda a sua informaчуo da tabela work.
//
// PARТMETROS:
// $institution_id:
//	int/str numщrico, ID da instituiчуo
//
function delete_institution($institution_id) {
	// Delete "work" associated data
	delete_institution_investigators($institution_id, 'DELETE_INSTITUION');
	
	// Delete from "institution" table
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	$query = "DELETE from institution WHERE institution.id = '$institution_id'";
	$result = mysqli_query($connection, $query) or die('ERROR DELETE_INSTITUTION query failed: ' . mysqli_error($connection));
	close_connection($connection);
}


// delete_institution_investigators
//
// Apaga valores da tabela work associados a uma determinada instituiчуo.
// Usado por delete_institution
//
// PARТMETROS:
// $institution_id:
// 	int/str numщrico, ID da instituiчуo
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function delete_institution_investigators($institution_id, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	$query = "DELETE FROM work WHERE work.institution_id = '$institution_id';";
	$result = mysqli_query($connection, $query) or die("ERROR $error delete from work query failed: " . mysqli_error($connection));
	
	close_connection($connection);
}


#################
#  PUBLICATION  #
#################


// add_publication
//
// Adiciona 1 publicaчуo р base de dados.
// Sѓ o tэtulo щ obrigatѓrio, todos os outros parтmetros podem ser omitidos.
// O id da publicaчуo щ incrementado automaticamente (definido na prѓpria base de dados).
// Para omitir:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar 'NULL' (abstract, citations, journal, year, doi, link e doi_percentage) ou [] (nas listas).
//
// PARТMETROS:
// $title:
//	str, Tэtulo da publicaчуo
// $abstract:
//	str, Abstract da publicaчуo
// $citations:
//	int, Nњmero de citaчѕes da publicaчуo
// $journal:
//	str, Jornal no qual a publicaчуo foi feita
// $year:
//	int, Ano da publicaчуo
// $doi:
//	str, DOI da publicaчуo
// $link:
//	str, Link da publicaчуo (Google Scholar)
// $doi_percentage:
//	float, Valor de certeza do DOI
// $publishes_list:
//	list[int], IDs dos autores da publicaчуo
//
// RETURN:
//	int, ID da publicaчуo adicionada
//
function add_publication($title, $abstract = 'NULL', $citations = 'NULL', $journal = 'NULL', $year = 'NULL', $doi = 'NULL', $link = 'NULL', $doi_percentage = 'NULL', $publishes_list = []) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	// Add quotation marks to not null parameters and escape possible conflicting characters
	$title = mysqli_real_escape_string($connection, $title);
	if ($abstract != 'NULL') {
		$abstract = mysqli_real_escape_string($connection, $abstract);
		$abstract = "'$abstract'";
	}
	if ($citations != 'NULL') { $citations = "'$citations'";}
	if ($journal != 'NULL') {
		$journal = mysqli_real_escape_string($connection, $journal);
		$journal = "'$journal'";
	}
	if ($year != 'NULL') { $year = "'$year'";}
	if ($doi != 'NULL') {
		$doi = mysqli_real_escape_string($connection, $doi);
		$doi = "'$doi'";
	}
	if ($link != 'NULL') {
		$link = mysqli_real_escape_string($connection, $link);
		$link = "'$link'";
	}
	if ($doi_percentage != 'NULL') { $doi_percentage = "'$doi_percentage'";}
	
	// Insert into "publication" table
	$query = "INSERT INTO publication (title, abstract, citations, journal, year, doi, doi_percentage, link) VALUES ('$title', $abstract, $citations, $journal, $year, $doi, $doi_percentage, $link);";
	$result = mysqli_query($connection, $query) or die('ERROR ADD_PUBLICATION query failed: ' . mysqli_error($connection));
	$publication_id = mysqli_insert_id($connection);
	close_connection($connection);
	
	// Insert into "publishes" table
	insert_publication_authors($publication_id, $publishes_list, 'ADD_PUBLICATION');
	
	return $publication_id;
}


// insert_publication_authors
//
// Insere valores na tabela publishes para uma determinada publicaчуo.
// Usado por add_publication e update_publication
//
// PARТMETROS:
// $publication_id:
// 	int/str numщrico, ID da publicaчуo
// $author_id_list:
// 	list[int/str numщrico], IDs dos autores da publicaчуo
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function insert_publication_authors($publication_id, $author_id_list, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	foreach ($author_id_list as $author_id) {
		$query = "INSERT INTO publishes (author_id, publication_id) VALUES ('$author_id', '$publication_id');";
		$result = mysqli_query($connection, $query) or die("ERROR $error insert into publishes query failed: " . mysqli_error($connection));
	}

	close_connection($connection);
}


// update_publication
//
// Actualiza 1 publicaчуo jс existente na base de dados.
// Dando o id, щ possэvel actualizar qualquer informaчуo dessa publicaчуo.
// Tanto se pode actualizar toda a informaчуo como somente parte dela.
// O њnico parтmetro obrigatѓrio щ o id (no caso de ser dado somente o id, nada acontece)
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (title, abstract, citations, journal, year, doi, link e doi_percentage) ou [] (nas listas).
//
// PARТMETROS:
// $publication_id:
// 	int/str numщrico, ID da publicaчуo
// $title:
//	str, Tэtulo da publicaчуo
// $abstract:
//	str, Abstract da publicaчуo
// $citations:
//	int, Nњmero de citaчѕes da publicaчуo
// $journal:
//	str, Revista na qual a publicaчуo foi feita
// $year:
//	int, Ano da publicaчуo
// $doi:
//	str, DOI da publicaчуo
// $link:
//	str, Link da publicaчуo (Google Scholar)
// $doi_percentage:
//	float, Valor de certeza do DOI
// $publishes_list:
//	list[int], IDs dos autores da publicaчуo
//
function update_publication($publication_id, $title = false, $abstract = false, $citations = false, $journal = false, $year = false, $doi = false, $link = false, $doi_percentage = false, $publishes_list = []) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	// Update "publication" table
	$query = 'UPDATE publication SET ';
	if (is_string($title)) {
		$title = mysqli_real_escape_string($connection, $title);
		$query .= "title = '$title', ";
	}
	if (is_string($abstract)) {
		$abstract = mysqli_real_escape_string($connection, $abstract);
		$query .= "abstract = '$abstract', ";
	}
	if (is_int($citations)) {
		$query .= "citations = '$citations', ";
	}
	if (is_string($journal)) {
		$journal = mysqli_real_escape_string($connection, $journal);
		$query .= "journal = '$journal', ";
	}
	if (is_int($year)) {
		$query .= "year = '$year', ";
	}
	if (is_string($doi)) {
		$doi = mysqli_real_escape_string($connection, $doi);
		$query .= "doi = '$doi', ";
	}
	if (is_string($link)) {
		$link = mysqli_real_escape_string($connection, $link);
		$query .= "link = '$link', ";
	}
	if (is_float($doi_percentage)) {
		$query .= "doi_percentage = '$doi_percentage', ";
	}

	if ($query != 'UPDATE publication SET ') {
		$query = substr($query, 0, -2);
		$query .= " WHERE publication.id = '$publication_id'";
		$result = mysqli_query($connection, $query) or die('ERROR UPDATE_PUBLICATION query failed: ' . mysqli_error($connection));
	}

	close_connection($connection);
	
	// Update "publishes" table
	insert_publication_authors($publication_id, $publishes_list, 'UPDATE_PUBLICATION');
}


// delete_publication
//
// Apaga 1 publicaчуo da base de dados e toda a sua informaчуo da tabela publishes.
//
// PARТMETROS:
// $publication_id:
//	int/str numщrico, ID da publicaчуo
//
function delete_publication($publication_id) {
	// Delete "publishes" associated data
	delete_publication_authors($publication_id, 'DELETE_PUBLICATION');
	
	// Delete from "publication" table
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	$query = "DELETE from publication WHERE publication.id = '$publication_id'";
	$result = mysqli_query($connection, $query) or die('ERROR DELETE_PUBLICATION query failed: ' . mysqli_error($connection));
	close_connection($connection);
}


// delete_publication_authors
//
// Apaga valores da tabela publishes associados a uma determinada publicaчao.
// Usado por delete_publication
//
// PARТMETROS:
// $publication_id:
// 	int/str numщrico, ID da publicaчуo
// $error:
// 	str, Nome da funчуo que chama esta funчуo (para melhor detecчуo da origem de um erro)
//	Pode ser omitido
//
function delete_publication_authors($publication_id, $error = '') {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));

	$query = "DELETE FROM publishes WHERE publishes.publication_id = '$publication_id';";
	$result = mysqli_query($connection, $query) or die("ERROR $error delete from publishes query failed: " . mysqli_error($connection));
	
	close_connection($connection);
}


#############
#  SELECTS  #
#############


// select_author
//
// Select linhas inteiras de todos os autores (nуo passando nenhum parтmetro) ou
// especificando as colunas que se quer igualar (id, name, citations, hindex, link e/ou photo)
// Tambщm permite especificar que colunas se quer, em vez de devolver as linhas completas
// Щ possэvel limitar o nњmero de linhas devolvidas
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (author_id, name, citations, hindex, link e/ou photo) ou [] (select).
//
// PARТMETROS:
// $select:
//	list[str], elementos do autor que se quer especificamente (id, name, citations, hindex e/ou link)
// $author_id:
// 	int, ID do autor
// $name:
//	str, Nome do autor
// $citations:
//	int, Nњmero de citaчѕes do autor
// $hindex:
//	int, Valor do h-index do autor
// $link:
//	str, Link do autor (Google Scholar)
// $photo:
//	str, Link para a fotografia do autor
// $top:
//  int, Nњmero de linhas a devolver
// $offset:
//  int, Nњmero de linhas a ignorar no inэcio antes de devolver o top de linhas
//
// RETURN:
//	mysqli_result, linhas com os valores pretendidos (iterсvel, uma espщcie de tabela)
//	(mais info: http://php.net/manual/en/class.mysqli-result.php)
//
function select_author($select = [], $author_id = false, $name = false, $citations = false, $hindex = false, $link = false, $photo = false, $top = false, $offset = false) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	if (count($select) > 0) {
		$select_what = '';
		foreach ($select as $select_value) {
			$select_what .= "$select_value, ";
		}
		$select_what = substr($select_what, 0, -2);
	} else if ($select == []) {
		$select_what = '*';
	} else {
		$select_what = '*';
	}
	
	if (is_int($top)) {
		$top = "LIMIT $top";
		if (is_int($offset)) {
			$top .= " OFFSET $offset";
		}
	} else {
		$top = "";
	}
	
	if ($author_id == false && $name == false && $citations == false && $hindex == false && $link == false && $photo == false) {
		$query = "SELECT $select_what FROM author $top";
		$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM author query failed: ' . mysqli_error($connection));
		close_connection($connection);
	} else {
		$query = "SELECT $select_what FROM author WHERE ";
		if (is_int($author_id)) {
			$query .= "author.id = '$author_id' AND";
		}
		if (is_string($name)) {
			$name = mysqli_real_escape_string($connection, $name);
			$query .= "author.name = '$name' AND";
		}
		if (is_int($citations)) {
			$query .= "author.citations = '$citations' AND";
		}
		if (is_int($hindex)) {
			$query .= "author.hindex = '$hindex' AND";
		}
		if (is_string($link)) {
			$link = mysqli_real_escape_string($connection, $link);
			$query .= "author.link = '$link' AND";
		}
		if (is_string($photo)) {
			$photo = mysqli_real_escape_string($connection, $photo);
			$query .= "author.photo = '$photo' AND";
		}

		if ($query != "SELECT $select_what FROM author WHERE ") {
			$query = substr($query, 0, -4);
			$query .= $top;
			$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM author query failed: ' . mysqli_error($connection));
			close_connection($connection);
		}
	}
	return $result;
}


// select_author_coauthors
//
// Select linhas inteiras de todos os co-autores de um autor (nуo passando nenhum parтmetro alщm do id do autor) ou
// especificando as colunas que se quer igualar (id, name, citations, hindex, link e/ou photo)
// Tambщm permite especificar que colunas se quer, em vez de devolver as linhas completas
// Щ possэvel limitar o nњmero de linhas devolvidas
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (coauthor_id, name, citations, hindex, link e/ou photo) ou [] (select).
//
// PARТMETROS:
// $author_id
//	int, ID do autor dos quais se quer os co-autores
// $select:
//	list[str], elementos dos co-autores que se quer especificamente (id, name, citations, hindex e/ou link)
// $coauthor_id:
// 	int, ID do co-autor
// $name:
//	str, Nome do/s co-autor/es
// $citations:
//	int, Nњmero de citaчѕes do/s co-autor/es
// $hindex:
//	int, Valor do h-index do/s co-autor/es
// $link:
//	str, Link do co-autor (Google Scholar)
// $photo:
//	str, Link para a fotografia do autor
// $top:
//  int, Nњmero de linhas a devolver
// $offset:
//  int, Nњmero de linhas a ignorar no inэcio antes de devolver o top de linhas
//
// RETURN:
//	mysqli_result, linhas com os valores pretendidos (iterсvel, uma espщcie de tabela)
//	(mais info: http://php.net/manual/en/class.mysqli-result.php)
//
function select_author_coauthors($author_id, $select = [], $coauthor_id = false, $name = false, $citations = false, $hindex = false, $link = false, $photo = false, $top = false, $offset = false) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	if (count($select) > 0) {
		$select_what = '';
		foreach ($select as $select_value) {
			$select_what .= "$select_value, ";
		}
		$select_what = substr($select_what, 0, -2);
	} else if ($select == []) {
		$select_what = 'id, name, citations, hindex, link, photo';
	} else {
		$select_what = 'id, name, citations, hindex, link, photo';
	}
	
	if (is_int($top)) {
		$top = "LIMIT $top";
		if (is_int($offset)) {
			$top .= " OFFSET $offset";
		}
	} else {
		$top = "";
	}
	
	if ($coauthor_id == false && $name == false && $citations == false && $hindex == false && $link == false  && $photo == false) {
		$query = "SELECT $select_what FROM author, team WHERE team.author_id = '$author_id' AND team.coAuthor_id = author.id $top";
		$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM author_team query failed: ' . mysqli_error($connection));
		close_connection($connection);
	} else {
		$query = "SELECT $select_what FROM author, team WHERE team.author_id = '$author_id' AND team.coAuthor_id = author.id AND ";
		if (is_int($coauthor_id)) {
			$query .= "author.id = '$coauthor_id' AND";
		}
		if (is_string($name)) {
			$name = mysqli_real_escape_string($connection, $name);
			$query .= "author.name = '$name' AND";
		}
		if (is_int($citations)) {
			$query .= "author.citations = '$citations' AND";
		}
		if (is_int($hindex)) {
			$query .= "author.hindex = '$hindex' AND";
		}
		if (is_string($link)) {
			$link = mysqli_real_escape_string($connection, $link);
			$query .= "author.link = '$link' AND";
		}
		if (is_string($photo)) {
			$photo = mysqli_real_escape_string($connection, $photo);
			$query .= "author.photo = '$photo' AND";
		}

		if ($query != "SELECT $select_what FROM author, team WHERE team.author_id = '$author_id' AND team.coAuthor_id = author.id AND ") {
			$query = substr($query, 0, -4);
			$query .= $top;
			$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM author_team query failed: ' . mysqli_error($connection));
			close_connection($connection);
		}
	}
	return $result;
}


// select_author_institutions
//
// Select linhas inteiras de todas as instituiчѕes de um autor (nуo passando nenhum parтmetro alщm do id do autor) ou
// especificando as colunas que se quer igualar (id, name e/ou link)
// Tambщm permite especificar que colunas se quer, em vez de devolver as linhas completas
// Щ possэvel limitar o nњmero de linhas devolvidas
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (institution_id, name e/ou link) ou [] (select).
//
// PARТMETROS:
// $author_id
//	int, ID do autor dos quais se quer as instituiчѕes
// $select:
//	list[str], elementos das instituiчѕes que se quer especificamente (id, name e/ou link)
// $institution_id:
// 	int, ID da/s instituiчуo/ѕes
// $name:
//	str, Nome da/s instituiчуo/ѕes
// $link:
//	str, Link da instituiчуo (Google Scholar)
// $top:
//  int, Nњmero de linhas a devolver
// $offset:
//  int, Nњmero de linhas a ignorar no inэcio antes de devolver o top de linhas
//
// RETURN:
//	mysqli_result, linhas com os valores pretendidos (iterсvel, uma espщcie de tabela)
//	(mais info: http://php.net/manual/en/class.mysqli-result.php)
//
function select_author_institutions($author_id, $select = [], $institution_id = false, $name = false, $link = false, $top = false, $offset = false) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	if (count($select) > 0) {
		$select_what = '';
		foreach ($select as $select_value) {
			$select_what .= "$select_value, ";
		}
		$select_what = substr($select_what, 0, -2);
	} else if ($select == []) {
		$select_what = 'id, name, link';
	} else {
		$select_what = 'id, name, link';
	}
	
	if (is_int($top)) {
		$top = "LIMIT $top";
		if (is_int($offset)) {
			$top .= " OFFSET $offset";
		}
	} else {
		$top = "";
	}
	
	if ($institution_id == false && $name == false && $link == false) {
		$query = "SELECT $select_what FROM institution, work WHERE work.author_id = '$author_id' AND work.institution_id = institution.id $top";
		$result = mysqli_query($connection, $query) or die('Select from author_team query failed: ' . mysqli_error($connection));
		close_connection($connection);
	} else {
		$query = "SELECT $select_what FROM institution, work WHERE work.author_id = '$author_id' AND work.institution_id = institution.id AND ";
		if (is_int($institution_id)) {
			$query .= "institution.id = '$institution_id' AND";
		}
		if (is_string($name)) {
			$name = mysqli_real_escape_string($connection, $name);
			$query .= "institution.name = '$name' AND";
		}
		if (is_string($link)) {
			$link = mysqli_real_escape_string($connection, $link);
			$query .= "institution.link = '$link' AND";
		}

		if ($query != "SELECT $select_what FROM institution, work WHERE work.author_id = '$author_id' AND work.institution_id = institution.id AND ") {
			$query = substr($query, 0, -4);
			$query .= $top;
			$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM author_work query failed: ' . mysqli_error($connection));
			close_connection($connection);
		}
	}
	return $result;
}


// select_author_publications
//
// Select linhas inteiras de todas as publicaчѕes de um autor (nуo passando nenhum parтmetro alщm do id do autor) ou
// especificando as colunas que se quer igualar (id, title, abstract, citations, journal, year, doi, link e/ou doi_percentage)
// Tambщm permite especificar que colunas se quer, em vez de devolver as linhas completas
// Щ possэvel limitar o nњmero de linhas devolvidas
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (publication_id, title, abstract, citations, journal, year, doi, link e/ou doi_percentage) ou [] (select).
//
// PARТMETROS:
// $author_id
//	int, ID do autor dos quais se quer as instituiчѕes
// $select:
//	list[str], elementos das publicaчѕes que se quer especificamente (id, title, abstract, citations, journal, year, doi e/ou link)
// $publication_id:
// 	int, ID da/s publicaчуo/ѕes
// $title:
//	str, Nome da/s publicaчуo/ѕes
// $abstract:
//	str, Abstract da/s publicaчуo/ѕes
// $citations:
//	int, Nњmero de citaчѕes da/s publicaчуo/ѕes
// $journal:
//	str, Revista da/s publicaчуo/ѕes
// $year:
//	int, Ano da/s publicaчуo/ѕes
// $doi:
//	str, DOI da publicaчуo
// $link:
//	str, Link da publicaчуo (Google Scholar)
// $doi_percentage:
//	float, Valor de certeza do DOI
// $top:
//  int, Nњmero de linhas a devolver
// $offset:
//  int, Nњmero de linhas a ignorar no inэcio antes de devolver o top de linhas
//
// RETURN:
//	mysqli_result, linhas com os valores pretendidos (iterсvel, uma espщcie de tabela)
//	(mais info: http://php.net/manual/en/class.mysqli-result.php)
//
function select_author_publications($author_id, $select = [], $publication_id = false, $title = false, $abstract = false, $citations = false, $journal = false, $year = false, $doi = false, $link = false, $doi_percentage = false, $top = false, $offset = false) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	if (count($select) > 0) {
		$select_what = '';
		foreach ($select as $select_value) {
			$select_what .= "$select_value, ";
		}
		$select_what = substr($select_what, 0, -2);
	} else if ($select == []) {
		$select_what = 'id, title, abstract, citations, journal, year, doi, link, doi_percentage';
	} else {
		$select_what = 'id, title, abstract, citations, journal, year, doi, link, doi_percentage';
	}
	
	if (is_int($top)) {
		$top = "LIMIT $top";
		if (is_int($offset)) {
			$top .= " OFFSET $offset";
		}
	} else {
		$top = "";
	}
	
	if ($publication_id == false && $title == false && $abstract == false && $citations == false && $journal == false && $year == false && $doi == false && $link == false && $doi_percentage == false) {
		$query = "SELECT $select_what FROM publication, publishes WHERE publishes.author_id = '$author_id' AND publishes.publication_id = publication.id $top";
		$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM author_team query failed: ' . mysqli_error($connection));
		close_connection($connection);
	} else {
		$query = "SELECT $select_what FROM publication, publishes WHERE publishes.author_id = '$author_id' AND publishes.publication_id = publication.id AND ";
		if (is_int($publication_id)) {
			$query .= "publication.id = '$publication_id' AND";
		}
		if (is_string($title)) {
			$title = mysqli_real_escape_string($connection, $title);
			$query .= "publication.title = '$title' AND";
		}
		if (is_string($abstract)) {
			$abstract = mysqli_real_escape_string($connection, $abstract);
			$query .= "publication.abstract = '$abstract' AND";
		}
		if (is_int($citations)) {
			$query .= "publication.citations = '$citations' AND";
		}
		if (is_string($journal)) {
			$journal = mysqli_real_escape_string($connection, $journal);
			$query .= "publication.journal = '$journal' AND";
		}
		if (is_int($year)) {
			$query .= "publication.year = '$year' AND";
		}
		if (is_string($doi)) {
			$doi = mysqli_real_escape_string($connection, $doi);
			$query .= "publication.doi = '$doi' AND";
		}
		if (is_string($link)) {
			$link = mysqli_real_escape_string($connection, $link);
			$query .= "publication.link = '$link' AND";
		}
		if (is_float($doi_percentage)) {
			$query .= "publication.doi_percentage = '$doi_percentage' AND";
		}

		if ($query != "SELECT $select_what FROM publication, publishes WHERE publishes.author_id = '$author_id' AND publishes.publication_id = publication.id AND ") {
			$query = substr($query, 0, -4);
			$query .= $top;
			$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM author_publishes query failed: ' . mysqli_error($connection));
			close_connection($connection);
		}
	}
	return $result;
}


// select_institution
//
// Select linhas inteiras de todas as instituiчѕes (nуo passando nenhum parтmetro) ou
// especificando as colunas que se quer igualar (id, name e/ou link)
// Tambщm permite especificar que colunas se quer, em vez de devolver as linhas completas
// Щ possэvel limitar o nњmero de linhas devolvidas
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (institution_id, name e/ou link) ou [] (select).
//
// PARТMETROS:
// $select:
//	list[str], elementos da instituiчуo que se quer especificamente (id, name e/ou link)
// $institution_id:
// 	int, ID da instituiчуo
// $name:
//	str, Nome da/s instituiчуo/ѕes
// $link:
//	str, Link da instituiчуo (Google Scholar)
// $top:
//  int, Nњmero de linhas a devolver
// $offset:
//  int, Nњmero de linhas a ignorar no inэcio antes de devolver o top de linhas
//
// RETURN:
//	mysqli_result, linhas com os valores pretendidos (iterсvel, uma espщcie de tabela)
//	(mais info: http://php.net/manual/en/class.mysqli-result.php)
//
function select_institution($select = [], $institution_id = false, $name = false, $link = false, $top = false, $offset = false) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	if (count($select) > 0) {
		$select_what = '';
		foreach ($select as $select_value) {
			$select_what .= "$select_value, ";
		}
		$select_what = substr($select_what, 0, -2);
	} else if ($select == []) {
		$select_what = '*';
	} else {
		$select_what = '*';
	}
	
	if (is_int($top)) {
		$top = "LIMIT $top";
		if (is_int($offset)) {
			$top .= " OFFSET $offset";
		}
	} else {
		$top = "";
	}
	
	if ($institution_id == false && $name == false && $link == false) {
		$query = "SELECT $select_what FROM institution $top";
		$result = mysqli_query($connection, $query) or die('SERROR SELECT FROM institution query failed: ' . mysqli_error($connection));
		close_connection($connection);
	} else {
		$query = "SELECT $select_what FROM institution WHERE ";
		if (is_int($institution_id)) {
			$query .= "institution.id = '$institution_id' AND";
		}
		if (is_string($name)) {
			$name = mysqli_real_escape_string($connection, $name);
			$query .= "institution.name = '$name' AND";
		}
		if (is_string($link)) {
			$link = mysqli_real_escape_string($connection, $link);
			$query .= "institution.link = '$link' AND";
		}

		if ($query != "SELECT $select_what FROM institution WHERE ") {
			$query = substr($query, 0, -4);
			$query .= $top;
			$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM institution query failed: ' . mysqli_error($connection));
			close_connection($connection);
		}
	}
	return $result;
}


// select_institution_investigators
//
// Select linhas inteiras de todos os investigadores de uma instituiчуo (nуo passando nenhum parтmetro alщm do id da instituiчуo) ou
// especificando as colunas que se quer igualar (id, name, citations, hindex, link e/ou photo)
// Tambщm permite especificar que colunas se quer, em vez de devolver as linhas completas
// Щ possэvel limitar o nњmero de linhas devolvidas
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (author_id, name, citations, hindex, link e/ou photo) ou [] (select).
//
// PARТMETROS:
// $institution_id
//	int, ID da instituiчуo da qual se quer os investigadores
// $select:
//	list[str], elementos dos investigadores que se quer especificamente (id, name, citations, hindex e/ou link)
// $author_id:
// 	int, ID do autor
// $name:
//	str, Nome do investigador
// $citations:
//	int, Nњmero de citaчѕes do/s investigador/es
// $hindex:
//	int, Valor do h-index do/s investigador/es
// $link:
//	str, Link do investigador (Google Scholar)
// $photo:
//	str, Link para a fotografia do autor
// $top:
//  int, Nњmero de linhas a devolver
// $offset:
//  int, Nњmero de linhas a ignorar no inэcio antes de devolver o top de linhas
//
// RETURN:
//	mysqli_result, linhas com os valores pretendidos (iterсvel, uma espщcie de tabela)
//	(mais info: http://php.net/manual/en/class.mysqli-result.php)
//
function select_institution_investigators($institution_id, $select = [], $author_id = false, $name = false, $citations = false, $hindex = false, $link = false, $photo = false, $top = false, $offset = false) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	if (count($select) > 0) {
		$select_what = '';
		foreach ($select as $select_value) {
			$select_what .= "$select_value, ";
		}
		$select_what = substr($select_what, 0, -2);
	} else if ($select == []) {
		$select_what = 'id, name, citations, hindex, link, photo';
	} else {
		$select_what = 'id, name, citations, hindex, link, photo';
	}
	
	if (is_int($top)) {
		$top = "LIMIT $top";
		if (is_int($offset)) {
			$top .= " OFFSET $offset";
		}
	} else {
		$top = "";
	}
	
	if ($author_id == false && $name == false && $citations == false && $hindex == false && $link == false && $photo == false) {
		$query = "SELECT $select_what FROM author, work WHERE work.institution_id = '$institution_id' AND work.author_id = author.id $top";
		$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM author_team query failed: ' . mysqli_error($connection));
		close_connection($connection);
	} else {
		$query = "SELECT $select_what FROM author, work WHERE work.institution_id = '$institution_id' AND work.author_id = author.id AND ";
		if (is_int($author_id)) {
			$query .= "author.id = '$author_id' AND";
		}
		if (is_string($name)) {
			$name = mysqli_real_escape_string($connection, $name);
			$query .= "author.name = '$name' AND";
		}
		if (is_int($citations)) {
			$query .= "author.citations = '$citations' AND";
		}
		if (is_int($hindex)) {
			$query .= "author.hindex = '$hindex' AND";
		}
		if (is_string($link)) {
			$link = mysqli_real_escape_string($connection, $link);
			$query .= "author.link = '$link' AND";
		}
		if (is_string($photo)) {
			$photo = mysqli_real_escape_string($connection, $photo);
			$query .= "author.photo = '$photo' AND";
		}

		if ($query != "SELECT $select_what FROM author, work WHERE work.institution_id = '$institution_id' AND work.author_id = author.id AND ") {
			$query = substr($query, 0, -4);
			$query .= $top;
			$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM institution_work query failed: ' . mysqli_error($connection));
			close_connection($connection);
		}
	}
	return $result;
}


// select_publication
//
// Select linhas inteiras de todas as publicaчѕes (nуo passando nenhum parтmetro) ou
// especificando as colunas que se quer igualar (id, title, abstract, citations, journal, year, doi, link e/ou doi_percentage)
// Tambщm permite especificar que colunas se quer, em vez de devolver as linhas completas
// Щ possэvel limitar o nњmero de linhas devolvidas
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (publication_id, title, abstract, citations, journal, year, doi, link e/ou doi_percentage) ou [] (select).
//
// PARТMETROS:
// $select:
//	list[str], elementos das publicaчѕes que se quer especificamente (id, title, abstract, citations, journal, year, doi e/ou link)
// $publication_id:
// 	int, ID da/s publicaчуo/ѕes
// $title:
//	str, Nome da/s publicaчуo/ѕes
// $abstract:
//	str, Abstract da/s publicaчуo/ѕes
// $citations:
//	int, Nњmero de citaчѕes da/s publicaчуo/ѕes
// $journal:
//	str, Revista da/s publicaчуo/ѕes
// $year:
//	int, Ano da/s publicaчуo/ѕes
// $doi:
//	str, DOI da publicaчуo
// $link:
//	str, Link da publicaчуo (Google Scholar)
// $doi_percentage:
//	float, Valor de certeza do DOI
// $top:
//  int, Nњmero de linhas a devolver
// $offset:
//  int, Nњmero de linhas a ignorar no inэcio antes de devolver o top de linhas
//
// RETURN:
//	mysqli_result, linhas com os valores pretendidos (iterсvel, uma espщcie de tabela)
//	(mais info: http://php.net/manual/en/class.mysqli-result.php)
//
function select_publication($select = [], $publication_id = false, $title = false, $abstract = false, $citations = false, $journal = false, $year = false, $doi = false, $link = false, $doi_percentage = false, $top = false, $offset = false) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	if (count($select) > 0) {
		$select_what = '';
		foreach ($select as $select_value) {
			$select_what .= "$select_value, ";
		}
		$select_what = substr($select_what, 0, -2);
	} else if ($select == []) {
		$select_what = '*';
	} else {
		$select_what = '*';
	}
	
	if (is_int($top)) {
		$top = "LIMIT $top";
		if (is_int($offset)) {
			$top .= " OFFSET $offset";
		}
	} else {
		$top = "";
	}
	
	if ($publication_id == false && $title == false && $abstract == false && $citations == false && $journal == false && $year == false && $doi == false && $link == false && $doi_percentage == false) {
		$query = "SELECT $select_what FROM publication $top";
		$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM publication query failed: ' . mysqli_error($connection));
		close_connection($connection);
	} else {
		$query = "SELECT $select_what FROM publication WHERE ";
		if (is_int($publication_id)) {
			$query .= "publication.id = '$publication_id' AND";
		}
		if (is_string($title)) {
			$title = mysqli_real_escape_string($connection, $title);
			$query .= "publication.title = '$title' AND";
		}
		if (is_string($abstract)) {
			$abstract = mysqli_real_escape_string($connection, $abstract);
			$query .= "publication.abstract = '$abstract' AND";
		}
		if (is_int($citations)) {
			$query .= "publication.citations = '$citations' AND";
		}
		if (is_string($journal)) {
			$journal = mysqli_real_escape_string($connection, $journal);
			$query .= "publication.journal = '$journal' AND";
		}
		if (is_int($year)) {
			$query .= "publication.year = '$year' AND";
		}
		if (is_string($doi)) {
			$doi = mysqli_real_escape_string($connection, $doi);
			$query .= "publication.doi = '$doi' AND";
		}
		if (is_string($link)) {
			$link = mysqli_real_escape_string($connection, $link);
			$query .= "publication.link = '$link' AND";
		}
		if (is_float($doi_percentage)) {
			$query .= "publication.doi_percentage = '$doi_percentage' AND";
		}

		if ($query != "SELECT $select_what FROM publication WHERE ") {
			$query = substr($query, 0, -4);
			$query .= $top;
			$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM publication query failed: ' . mysqli_error($connection));
			close_connection($connection);
		}
	}
	return $result;
}


// select_publication_authors
//
// Select linhas inteiras de todos os autores de uma publicaчуo (nуo passando nenhum parтmetro alщm do id da autores) ou
// especificando as colunas que se quer igualar (id, name, citations, hindex, link e/ou photo)
// Tambщm permite especificar que colunas se quer, em vez de devolver as linhas completas
// Щ possэvel limitar o nњmero de linhas devolvidas
// Para omitir parтmetros:
//	- Caso o parтmetro esteja no final, simplesmente ignorar
//	- Caso o parтmetro esteja entre outros parтmetros, usar o valor booleano false (author_id, name, citations, hindex, link e/ou photo) ou [] (select).
//
// PARТMETROS:
// $publication_id
//	int, ID da publicaчуo da qual se quer os autor
// $select:
//	list[str], elementos dos autores que se quer especificamente (id, name, citations, hindex e/ou link)
// $author_id:
// 	int, ID do autor
// $name:
//	str, Nome do/s autor/es
// $citations:
//	int, Nњmero de citaчѕes do/s autor/es
// $hindex:
//	int, Valor do h-index do/s autor/es
// $link:
//	str, Link do autor (Google Scholar)
// $photo:
//	str, Link para a fotografia do autor
// $top:
//  int, Nњmero de linhas a devolver
// $offset:
//  int, Nњmero de linhas a ignorar no inэcio antes de devolver o top de linhas
//
// RETURN:
//	mysqli_result, linhas com os valores pretendidos (iterсvel, uma espщcie de tabela)
//	(mais info: http://php.net/manual/en/class.mysqli-result.php)
//
function select_publication_authors($publication_id, $select = [], $author_id = false, $name = false, $citations = false, $hindex = false, $link = false, $photo = false, $top = false, $offset = false) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	
	if (count($select) > 0) {
		$select_what = '';
		foreach ($select as $select_value) {
			$select_what .= "$select_value, ";
		}
		$select_what = substr($select_what, 0, -2);
	} else if ($select == []) {
		$select_what = 'id, name, citations, hindex, link, photo';
	} else {
		$select_what = 'id, name, citations, hindex, link, photo';
	}
	
	if (is_int($top)) {
		$top = "LIMIT $top";
		if (is_int($offset)) {
			$top .= " OFFSET $offset";
		}
	} else {
		$top = "";
	}
	
	if ($author_id == false && $name == false && $citations == false && $hindex == false && $link == false && $photo == false) {
		$query = "SELECT $select_what FROM author, publishes WHERE publishes.publication_id = '$publication_id' AND publishes.author_id = author.id $top";
		$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM publication_publishes query failed: ' . mysqli_error($connection));
		close_connection($connection);
	} else {
		$query = "SELECT $select_what FROM author, publishes WHERE publishes.publication_id = '$publication_id' AND publishes.author_id = author.id AND ";
		if (is_int($author_id)) {
			$query .= "author.id = '$author_id' AND";
		}
		if (is_string($name)) {
			$name = mysqli_real_escape_string($connection, $name);
			$query .= "author.name = '$name' AND";
		}
		if (is_int($citations)) {
			$query .= "author.citations = '$citations' AND";
		}
		if (is_int($hindex)) {
			$query .= "author.hindex = '$hindex' AND";
		}
		if (is_string($link)) {
			$link = mysqli_real_escape_string($connection, $link);
			$query .= "author.link = '$link' AND";
		}
		if (is_string($photo)) {
			$photo = mysqli_real_escape_string($connection, $photo);
			$query .= "author.photo = '$photo' AND";
		}

		if ($query != "SELECT $select_what FROM author, publishes WHERE publishes.publication_id = '$publication_id' AND publishes.author_id = author.id AND ") {
			$query = substr($query, 0, -4);
			$query .= $top;
			$result = mysqli_query($connection, $query) or die('ERROR SELECT FROM publication_publishes query failed: ' . mysqli_error($connection));
			close_connection($connection);
		}
	}
	return $result;
}

// select_generic_query
//
// Query genщrica
//
// PARТMETROS:
// $query
//	str, query SQL
//
// RETURN:
//	mysqli_result, linhas com os valores pretendidos (iterсvel, uma espщcie de tabela)
//	(mais info: http://php.net/manual/en/class.mysqli-result.php)
//
function select_generic_query($query) {
	$connection = initiate_connection(server_info('server'), server_info('user'), server_info('pass'));
	$result = mysqli_query($connection, $query) or die('ERROR GENERIC QUERY failed: ' . mysqli_error($connection));
	close_connection($connection);
	return $result;
}
?>