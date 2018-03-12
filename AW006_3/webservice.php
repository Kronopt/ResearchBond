<?php
#######################################################################
#                              Functions                              #
#######################################################################

// Database connection functions
require '/home/aw006/database/bd_functions.php';

// Print XML file, GETs
function print_xml($result, $other_result_list, $element) {	
	if (mysqli_num_rows($result) > 0) {
		$xml_output = '<?xml version="1.0"?>';
		$xml_output .= "<$element" . "s>";
		while ($line = mysqli_fetch_array($result, MYSQL_ASSOC)) {
			$xml_output .= "<$element>";
			foreach ($line as $key => $col_value) {
				if (is_string($col_value)) {$col_value = htmlspecialchars($col_value);}
				if ($key == 'link') {
					$col_value = 'https://scholar.google.pt' . $col_value;
				}
				if ($key == 'id') {
					$instance_id = intval($col_value); // used in the next section
				}
				$xml_output .= "<$key>$col_value</$key>";
			}
			
			// $other_result_list will have one of these values: 'institution', 'publication', 'co-author', 'investigator', 'author'
			foreach ($other_result_list as $other_result_str) {
				$other_result = other_function($other_result_str, $instance_id);
				$xml_output .=  "<$other_result_str" . "s>";
				if (mysqli_num_rows($other_result) > 0) {
					while ($other_line = mysqli_fetch_array($other_result, MYSQL_ASSOC)) {
						$xml_output .= "<$other_result_str>";
						foreach ($other_line as $key => $col_value) {
							if (is_string($col_value)) {$col_value = htmlspecialchars($col_value);}
							if ($key == 'link') {
								$col_value = 'https://scholar.google.pt' . $col_value;
							}
							$xml_output .= "<$key>$col_value</$key>";
						}
						$xml_output .= "</$other_result_str>";
					}
					mysqli_free_result($other_result);
				}
				$xml_output .= "</$other_result_str" . "s>";
			}
			
			$xml_output .= "</$element>";
		}
		$xml_output .= "</$element" . "s>";
		
		
		// Type of output
		$http_accept = $_SERVER['HTTP_ACCEPT'];
		setHttpHeaders($http_accept, 200);

		if (strpos($http_accept, 'application/json') !== false) {
			$xml = simplexml_load_string($xml_output);
			$json = json_encode($xml);
			echo $json;
		}
		else if (strpos($http_accept, 'text/xml') !== false) {
			echo $xml_output;
		}
		else {
			//$xml = simplexml_load_string($xml_output);
			//echo json_encode($xml);
			
			echo $xml_output;
		}
	}
	mysqli_free_result($result);
}

// Required function for print_xml
function other_function($name_of_function, $instance_id) {
	$function_to_return = '';
	
	if($name_of_function == 'institution') {
		$function_to_return = select_author_institutions($instance_id);
	}
	else if($name_of_function == 'publication') {
		$function_to_return = select_author_publications($instance_id);
	}
	else if($name_of_function == 'co-author') {
		$function_to_return = select_author_coauthors($instance_id);
	}
	else if($name_of_function == 'investigator') {
		$function_to_return = select_institution_investigators($instance_id);
	}
	else if($name_of_function == 'author') {
		$function_to_return = select_publication_authors($instance_id);
	}
	return $function_to_return;
}

// HTTP status and messages
function setHttpHeaders($contentType, $statusCode){
	
	$httpStatus = array(
		100 => 'Continue',  
		101 => 'Switching Protocols',  
		200 => 'OK',
		201 => 'Created',  
		202 => 'Accepted',  
		203 => 'Non-Authoritative Information',  
		204 => 'No Content',  
		205 => 'Reset Content',  
		206 => 'Partial Content',  
		300 => 'Multiple Choices',  
		301 => 'Moved Permanently',  
		302 => 'Found',  
		303 => 'See Other',  
		304 => 'Not Modified',  
		305 => 'Use Proxy',  
		306 => '(Unused)',  
		307 => 'Temporary Redirect',  
		400 => 'Bad Request',  
		401 => 'Unauthorized',  
		402 => 'Payment Required',  
		403 => 'Forbidden',  
		404 => 'Not Found',  
		405 => 'Method Not Allowed',  
		406 => 'Not Acceptable',  
		407 => 'Proxy Authentication Required',  
		408 => 'Request Timeout',  
		409 => 'Conflict',  
		410 => 'Gone',  
		411 => 'Length Required',  
		412 => 'Precondition Failed',  
		413 => 'Request Entity Too Large',  
		414 => 'Request-URI Too Long',  
		415 => 'Unsupported Media Type',  
		416 => 'Requested Range Not Satisfiable',  
		417 => 'Expectation Failed',  
		500 => 'Internal Server Error',  
		501 => 'Not Implemented',  
		502 => 'Bad Gateway',  
		503 => 'Service Unavailable',  
		504 => 'Gateway Timeout',  
		505 => 'HTTP Version Not Supported'
	);
		
	$statusMessage = ($httpStatus[$statusCode]) ? $httpStatus[$statusCode] : $status[500];
	
	header($_SERVER["SERVER_PROTOCOL"] . " " . $statusCode ." ". $statusMessage);		
	header("Content-Type:". $contentType);
}


######################################################################
#                                Code                                #
######################################################################

// PATH_INFO
$path = $_SERVER['PATH_INFO'];
if ($path != null) {
	$path_params = split("/", $path);
}

if ($path_params[1] != null) {
	// AUTHOR WEBSERVICE
	if (substr($path_params[1], 0, 7) == 'author=') { // "author=[offset]-[number of results]"
		if (count($path_params) == 2) {
			// /author=#-# GET
			$parameters = explode("-", explode("=", $path_params[1])[1]);
			if (count($parameters) == 2) {
				if (is_numeric($parameters[0]) && is_numeric($parameters[1])) {
					if ($_SERVER['REQUEST_METHOD'] == 'GET') {
						$result = select_author([], false, false, false, false, false, false, intval($parameters[1]), intval($parameters[0]));
						print_xml($result, ['publication', 'institution', 'co-author'], 'author');
					}
				}
			}
		}
	}
	else if ($path_params[1] == 'author') {
		if (count($path_params) == 2) {
			// /author GET
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				// defaults to the 10 first authors
				$result = select_author([], false, false, false, false, false, false, 10, 0);
				print_xml($result, ['publication', 'institution', 'co-author'], 'author');
			}
		}
		else if ($path_params[2] != null) {
			if (is_numeric($path_params[2])) {
				settype($path_params[2], 'integer'); // avoids SQL injection
				if (count($path_params) == 3) {
					// /author/{A_ID} GET
					if ($_SERVER['REQUEST_METHOD'] == 'GET') {
						$result = select_author([], intval($path_params[2]));
						print_xml($result, ['publication', 'institution', 'co-author'], 'author');
					}
				}
				else if ($path_params[3] != null) {
					if (count($path_params) == 4) {
						if ($path_params[3] == 'name') {
							// /author/{A_ID}/name GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_author(['name'], intval($path_params[2]));
								print_xml($result, [], 'author');
							}
						}
						else if ($path_params[3] == 'citations') {
							// /author/{A_ID}/citations GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_author(['citations'], intval($path_params[2]));
								print_xml($result, [], 'author');
							}							
						}
						else if ($path_params[3] == 'hindex') {
							// /author/{A_ID}/hindex GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_author(['hindex'], intval($path_params[2]));
								print_xml($result, [], 'author');
							}	
						}
						else if ($path_params[3] == 'link') {
							// /author/{A_ID}/link GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_author(['link'], intval($path_params[2]));
								print_xml($result, [], 'author');
							}	
						}
						else if ($path_params[3] == 'institution') {
							// /author/{A_ID}/institution GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_author_institutions(intval($path_params[2]));
								print_xml($result, [], 'institution');
							}
						}
						else if ($path_params[3] == 'publication') {
							// /author/{A_ID}/publication GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_author_publications(intval($path_params[2]));
								print_xml($result, [], 'publication');
							}
						}
						else if ($path_params[3] == 'co_author') {
							// /author/{A_ID}/co_author GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_author_coauthors(intval($path_params[2]));
								print_xml($result, [], 'co_author');
							}
						}
					}
					else if ($path_params[4] != null) {
						if (is_numeric($path_params[4])) {
							settype($path_params[4], 'integer'); // avoids SQL injection
							if (count($path_params) == 5) {
								if ($path_params[3] == 'institution') {
									// /author/{A_ID}/institution/{P_ID} GET
									if ($_SERVER['REQUEST_METHOD'] == 'GET') {
										$result = select_author_institutions(intval($path_params[2]), [], intval($path_params[4]));
										print_xml($result, [], 'author');
									}
								}
								else if ($path_params[3] == 'publication') {
									// /author/{A_ID}/publication/{P_ID} GET
									if ($_SERVER['REQUEST_METHOD'] == 'GET') {
										$result = select_author_publications(intval($path_params[2]), [], intval($path_params[4]));
										print_xml($result, [], 'author');
									}
								}
								else if ($path_params[3] == 'co_author') {
									// /author/{A_ID}/co_author/{A_ID} GET
									if ($_SERVER['REQUEST_METHOD'] == 'GET') {
										$result = select_author_coauthors(intval($path_params[2]), [], intval($path_params[4]));
										print_xml($result, [], 'author');
									}
								}
							}
						}
					}
				}
			}
		}
	}
	// INSTITUTION WEBSERVICE
	else if (substr($path_params[1], 0, 12) == 'institution=') { // "institution=[offset]-[number of results]"
		if (count($path_params) == 2) {
			// /institution=#-# GET
			$parameters = explode("-", explode("=", $path_params[1])[1]);
			if (count($parameters) == 2) {
				if (is_numeric($parameters[0]) && is_numeric($parameters[1])) {
					if ($_SERVER['REQUEST_METHOD'] == 'GET') {
						$result = select_institution([], false, false, false, intval($parameters[1]), intval($parameters[0]));
						print_xml($result, ['investigator'], 'institution');
					}
				}
			}
		}
	}
	else if ($path_params[1] == 'institution') {
		if (count($path_params) == 2) {
			// /institution GET
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$result = select_institution([], false, false, false, 10, 0);
				print_xml($result, ['investigator'], 'institution');
			}
		}
		else if ($path_params[2] != null) {
			if (is_numeric($path_params[2])) {
				settype($path_params[2], 'integer'); // avoids SQL injection
				if (count($path_params) == 3) {
					// /institution/{I_ID} GET
					if ($_SERVER['REQUEST_METHOD'] == 'GET') {
						$result = select_institution([], intval($path_params[2]));
						print_xml($result, ['investigator'], 'institution');
					}
				}
				else if ($path_params[3] != null) {
					if (count($path_params) == 4) {
						if ($path_params[3] == 'name') {
							// /institution/{I_ID}/name GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_institution(['name'], intval($path_params[2]));
								print_xml($result, [], 'institution');
							}
						}
						else if ($path_params[3] == 'link') {
							// /institution/{I_ID}/link GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_institution(['link'], intval($path_params[2]));
								print_xml($result, [], 'institution');
							}
						}
						else if ($path_params[3] == 'investigator') {
							// /institution/{I_ID}/investigator GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_institution_investigators(intval($path_params[2]));
								print_xml($result, [], 'investigator');
							}
						}
					}
					else if ($path_params[4] != null) {
						if (is_numeric($path_params[4])) {
							settype($path_params[4], 'integer'); // avoids SQL injection
							if (count($path_params) == 5 && $path_params[3] == 'investigator') {
								// /institution/{I_ID}/investigator/{A_ID} GET
								if ($_SERVER['REQUEST_METHOD'] == 'GET') {
									$result = select_institution_investigators(intval($path_params[2]), [], intval($path_params[4]));
									print_xml($result, [], 'investigator');
								}
							}
						}
					}
				}
			}
		}
	}
	// PUBLICATION WEBSERVICE
	else if (substr($path_params[1], 0, 12) == 'publication=') { // "publication=[offset]-[number of results]"
		if (count($path_params) == 2) {
			// /publication=#-# GET
			$parameters = explode("-", explode("=", $path_params[1])[1]);
			if (count($parameters) == 2) {
				if (is_numeric($parameters[0]) && is_numeric($parameters[1])) {
					if ($_SERVER['REQUEST_METHOD'] == 'GET') {
						$result = select_publication([], false, false, false, false, false, false, false, false, false, intval($parameters[1]), intval($parameters[0]));
						print_xml($result, ['author'], 'publication');
					}
				}
			}
		}
	}
	else if ($path_params[1] == 'publication') {
		if (count($path_params) == 2) {
			// /publication GET
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				$result = select_publication([], false, false, false, false, false, false, false, false, false, 10, 0);
				print_xml($result, ['author'], 'publication');
			}
		}
		else if ($path_params[2] != null) {
			if (is_numeric($path_params[2])) {
				settype($path_params[2], 'integer'); // avoids SQL injection
				if (count($path_params) == 3) {
					// /publication/{P_ID} GET
					if ($_SERVER['REQUEST_METHOD'] == 'GET') {
						$result = select_publication([], intval($path_params[2]));
						print_xml($result, ['author'], 'publication');
					}
				}
				else if ($path_params[3] != null) {
					if (count($path_params) == 4) {
						if ($path_params[3] == 'title') {
							// /publication/{P_ID}/title GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_publication(['title'], intval($path_params[2]));
								print_xml($result, [], 'publication');
							}
						}
						else if ($path_params[3] == 'abstract') {
							// /publication/{P_ID}/abstract GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_publication(['abstract'], intval($path_params[2]));
								print_xml($result, [], 'publication');
							}
						}
						else if ($path_params[3] == 'citations') {
							// /publication/{P_ID}/citations GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_publication(['citations'], intval($path_params[2]));
								print_xml($result, [], 'publication');
							}
						}else if ($path_params[3] == 'journal') {
							// /publication/{P_ID}/journal GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_publication(['journal'], intval($path_params[2]));
								print_xml($result, [], 'publication');
							}
						}
						else if ($path_params[3] == 'year') {
							// /publication/{P_ID}/year GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_publication(['year'], intval($path_params[2]));
								print_xml($result, [], 'publication');
							}
						}
						else if ($path_params[3] == 'doi') {
							// /publication/{P_ID}/doi GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_publication(['doi'], intval($path_params[2]));
								print_xml($result, [], 'publication');
							}
						}
						else if ($path_params[3] == 'link') {
							// /publication/{P_ID}/link GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_publication(['link'], intval($path_params[2]));
								print_xml($result, [], 'publication');
							}
						}
						else if ($path_params[3] == 'author') {
							// /publication/{P_ID}/author GET
							if ($_SERVER['REQUEST_METHOD'] == 'GET') {
								$result = select_publication_authors(intval($path_params[2]));
								print_xml($result, [], 'author');
							}
						}
					}
					else if ($path_params[4] != null) {
						if (is_numeric($path_params[4])) {
							settype($path_params[4], 'integer'); // avoids SQL injection
							if (count($path_params) == 5 && $path_params[3] == 'author') {
								// /publication/{P_ID}/author/{A_ID} GET
								if ($_SERVER['REQUEST_METHOD'] == 'GET') {
									$result = select_publication_authors(intval($path_params[2]), [], intval($path_params[4]));
									print_xml($result, [], 'publication');
								}
							}
						}
					}
				}
			}
		}
	}
	// DOCUMENTATION
	else if ($path_params[1] == 'documentation') {
		header( 'Location: http://appserver.di.fc.ul.pt/~aw006/Webservice/documentation/');
	}
}
?>