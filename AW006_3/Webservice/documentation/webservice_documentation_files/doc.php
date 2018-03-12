<?php
# /author
/**
 * @api {get} author[={OFFSET}-{TOTAL}] Everything
 * @apiVersion 0.1.1
 * @apiName author
 * @apiGroup Author
 *
 * @apiDescription Returns all the information stored about every author in the database
 *
 * @apiParam {Number} [OFFSET=0] Skips the specified number of authors, used in conjunction with TOTAL
 * @apiParam {Number} [TOTAL=10] Number of authors to return, used in conjunction with OFFSET
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number}   id           Authors' id
 * @apiSuccess {String}   name         Authors' name
 * @apiSuccess {Number}   citations    Authors' number of citations
 * @apiSuccess {Number}   h-index      Authors' h-index value
 * @apiSuccess {String}   link         Authors' Google Scholar url
 * @apiSuccess {String[]} institutions Authors' institutions
 * @apiSuccess {String[]} publications Authors' publications
 * @apiSuccess {String[]} co-authors   Authors' co-authors
*/

# /author/{A_ID}
/**
 * @api {get} author/{A_ID} Author
 * @apiVersion 0.1.1
 * @apiName author_id
 * @apiGroup Author
 *
 * @apiDescription Returns all the information stored about one specific author in the database
 *
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number}   id           Author's id
 * @apiSuccess {String}   name         Author's name
 * @apiSuccess {Number}   citations    Author's number of citations
 * @apiSuccess {Number}   h-index      Author's h-index value
 * @apiSuccess {String}   link         Author's Google Scholar url
 * @apiSuccess {String[]} institutions Author's institutions
 * @apiSuccess {String[]} publications Author's publications
 * @apiSuccess {String[]} co-authors   Author's co-authors
*/

# /author/{A_ID}/name
/**
 * @api {get} author/{A_ID}/name Name
 * @apiVersion 0.1.1
 * @apiName author_id_name
 * @apiGroup Author
 *
 * @apiDescription Returns the name of an Author
 *
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String} name Author's name
*/

# /author/{A_ID}/citations
/**
 * @api {get} author/{A_ID}/citations Citations
 * @apiVersion 0.1.1
 * @apiName author_id_citations
 * @apiGroup Author
 *
 * @apiDescription Returns the citations of an Author
 *
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number} citations Author's number of citations
*/

# /author/{A_ID}/h-index
/**
 * @api {get} author/{A_ID}/h-index H-index
 * @apiVersion 0.1.1
 * @apiName author_id_h_index
 * @apiGroup Author
 *
 * @apiDescription Returns the h-index of an Author
 *
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number} h-index Author's h-index value
*/

# /author/{A_ID}/link
/**
 * @api {get} author/{A_ID}/link Link
 * @apiVersion 0.1.1
 * @apiName author_id_link
 * @apiGroup Author
 *
 * @apiDescription Returns the Google Scholar url of an Author
 *
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String} link Author's Google Scholar url
*/

# /author/{A_ID}/institution
/**
 * @api {get} author/{A_ID}/institution Institutions
 * @apiVersion 0.1.1
 * @apiName author_id_institution
 * @apiGroup Author
 *
 * @apiDescription Returns all the institutions the author is affiliated with
 *
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]}  institutions Author's institutions
*/

# /author/{A_ID}/institution/{I_ID}
/**
 * @api {get} author/{A_ID}/institution/{I_ID} Institution
 * @apiVersion 0.1.1
 * @apiName author_id_institution_id
 * @apiGroup Author
 *
 * @apiDescription Returns a specific institution the author is affiliated with
 *
 * @apiParam {Number} A_ID Author id
 * @apiParam {Number} I_ID Institution id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]}  institution Author's institution
*/

# /author/{A_ID}/publication
/**
 * @api {get} author/{A_ID}/publication Publications
 * @apiVersion 0.1.1
 * @apiName author_id_publication
 * @apiGroup Author
 *
 * @apiDescription Returns all the publications of the author
 *
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]}  publications Author's publications
*/

# /author/{A_ID}/publication/{P_ID}
/**
 * @api {get} author/{A_ID}/publication/{P_ID} Publication
 * @apiVersion 0.1.1
 * @apiName author_id_publication_id
 * @apiGroup Author
 *
 * @apiDescription Returns a specific publication of the author
 *
 * @apiParam {Number} A_ID Author id
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]}  publication Author's publication
*/

# /author/{A_ID}/co_author
/**
 * @api {get} author/{A_ID}/co_author Co-authors
 * @apiVersion 0.1.1
 * @apiName author_id_co_author
 * @apiGroup Author
 *
 * @apiDescription Returns all the co-authors of the author
 *
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]}  co_authors Author's co-authors
*/

# /author/{A_ID}/co_author/{C_ID}
/**
 * @api {get} author/{A_ID}/co_author/{C_ID} Co-author
 * @apiVersion 0.1.1
 * @apiName author_id_co_author_id
 * @apiGroup Author
 *
 * @apiDescription Returns a specific co-author of the author
 *
 * @apiParam {Number} A_ID Author id
 * @apiParam {Number} C_ID co-author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]}  co_author Author's co_author
*/

# /institution
/**
 * @api {get} institution[={OFFSET}-{TOTAL}] Everything
 * @apiVersion 0.1.1
 * @apiName institution
 * @apiGroup Institution
 *
 * @apiDescription Returns all the information stored about every institution in the database
 *
 * @apiParam {Number} [OFFSET=0] Skips the specified number of institutions, used in conjunction with TOTAL
 * @apiParam {Number} [TOTAL=10] Number of institutions to return, used in conjunction with OFFSET
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number}   id           Institutions' id
 * @apiSuccess {String}   name         Institutions' name
 * @apiSuccess {String}   link         Institutions' Google Scholar url
 * @apiSuccess {String[]} investigator Institutions' investigators
*/

# /institution/{I_ID}
/**
 * @api {get} institution/{I_ID} Institution
 * @apiVersion 0.1.1
 * @apiName institution_id
 * @apiGroup Institution
 *
 * @apiDescription Returns all the information stored about eone specific institution in the database
 *
 * @apiParam {Number} I_ID Institution id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number}   id           Institution's id
 * @apiSuccess {String}   name         Institution's name
 * @apiSuccess {String}   link         Institution's Google Scholar url
 * @apiSuccess {String[]} investigator Institution's investigators
*/

# /institution/{I_ID}/name
/**
 * @api {get} institution/{I_ID}/name Name
 * @apiVersion 0.1.1
 * @apiName institution_id_name
 * @apiGroup Institution
 *
 * @apiDescription Returns the name of an Institution
 *
 * @apiParam {Number} I_ID Institution id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String} name Institution's name
*/

# /institution/{I_ID}/link
/**
 * @api {get} institution/{I_ID}/link Link
 * @apiVersion 0.1.1
 * @apiName institution_id_link
 * @apiGroup Institution
 *
 * @apiDescription Returns the Google Scholar url of an Institution
 *
 * @apiParam {Number} I_ID Institution id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String} link Institution's Google Scholar url
*/

# /institution/{I_ID}/investigator
/**
 * @api {get} institution/{I_ID}/investigator Investigators
 * @apiVersion 0.1.1
 * @apiName institution_id_investigator
 * @apiGroup Institution
 *
 * @apiDescription Returns all the investigators affiliated with an Institution
 *
 * @apiParam {Number} I_ID Institution id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]} investigators Institution's investigators
*/

# /institution/{I_ID}/investigator/{A_ID}
/**
 * @api {get} institution/{I_ID}/investigator/{A_ID} Investigator
 * @apiVersion 0.1.1
 * @apiName institution_id_investigator_id
 * @apiGroup Institution
 *
 * @apiDescription Returns a specific investigator affiliated with an Institution
 *
 * @apiParam {Number} I_ID Institution id
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]}  investigator Institution's investigator
*/

# /publication
/**
 * @api {get} publication[={OFFSET}-{TOTAL}] Everything
 * @apiVersion 0.1.1
 * @apiName publication
 * @apiGroup Publication
 *
 * @apiDescription Returns all the information stored about every publication in the database
 *
 * @apiParam {Number} [OFFSET=0] Skips the specified number of publications, used in conjunction with TOTAL
 * @apiParam {Number} [TOTAL=10] Number of publications to return, used in conjunction with OFFSET
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number}   id           Publications' id
 * @apiSuccess {String}   title        Publications' title
 * @apiSuccess {String}   abstract     Publications' abstract
 * @apiSuccess {Number}   citations    Publications' number of citations
 * @apiSuccess {String}   journal      Publications' journal
 * @apiSuccess {Number}   year         Publications' year
 * @apiSuccess {String}   doi          Publications' doi
 * @apiSuccess {String}   link         Publications' Google Scholar url
 * @apiSuccess {String[]} authors      Publications' authors
*/

# /publication/{P_ID}
/**
 * @api {get} publication/{P_ID} Publication
 * @apiVersion 0.1.1
 * @apiName publication_id
 * @apiGroup Publication
 *
 * @apiDescription Returns all the information stored about one specific publication in the database
 *
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number}   id           Publication's id
 * @apiSuccess {String}   title        Publication's title
 * @apiSuccess {String}   abstract     Publication's abstract
 * @apiSuccess {Number}   citations    Publication's number of citations
 * @apiSuccess {String}   journal      Publication's journal
 * @apiSuccess {Number}   year         Publication's year
 * @apiSuccess {String}   doi          Publication's doi
 * @apiSuccess {String}   link         Publication's Google Scholar url
 * @apiSuccess {String[]} authors      Publication's authors
*/

# /publication/{P_ID}/title
/**
 * @api {get} publication/{P_ID}/title Title
 * @apiVersion 0.1.1
 * @apiName publication_id_title
 * @apiGroup Publication
 *
 * @apiDescription Returns the name of a Publication
 *
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String} title Publication's title
*/

# /publication/{P_ID}/abstract
/**
 * @api {get} publication/{P_ID}/abstract Abstract
 * @apiVersion 0.1.1
 * @apiName publication_id_abstract
 * @apiGroup Publication
 *
 * @apiDescription Returns the abstract of a Publication
 *
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String} abstract Publication's abstract
*/

# /publication/{P_ID}/citations
/**
 * @api {get} publication/{P_ID}/citations Citations
 * @apiVersion 0.1.1
 * @apiName publication_id_citations
 * @apiGroup Publication
 *
 * @apiDescription Returns the number of citations of a Publication
 *
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number} citations Publication's number of citations
*/

# /publication/{P_ID}/journal
/**
 * @api {get} publication/{P_ID}/journal Journal
 * @apiVersion 0.1.1
 * @apiName publication_id_journal
 * @apiGroup Publication
 *
 * @apiDescription Returns the journal of a Publication
 *
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String} journal Publication's journal
*/

# /publication/{P_ID}/year
/**
 * @api {get} publication/{P_ID}/year Year
 * @apiVersion 0.1.1
 * @apiName publication_id_year
 * @apiGroup Publication
 *
 * @apiDescription Returns linkthe year of a Publication
 *
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {Number} year Publication's year
*/

# /publication/{P_ID}/doi
/**
 * @api {get} publication/{P_ID}/doi Doi
 * @apiVersion 0.1.1
 * @apiName publication_id_doi
 * @apiGroup Publication
 *
 * @apiDescription Returns the doi of a Publication
 *
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String} doi Publication's doi
*/

# /publication/{P_ID}/link
/**
 * @api {get} publication/{P_ID}/link Link
 * @apiVersion 0.1.1
 * @apiName publication_id_link
 * @apiGroup Publication
 *
 * @apiDescription Returns the Google Scholar url of a Publication
 *
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String} link Publication's Google Scholar url
*/

# /publication/{P_ID}/author
/**
 * @api {get} publication/{P_ID}/author Authors
 * @apiVersion 0.1.1
 * @apiName publication_id_author
 * @apiGroup Publication
 *
 * @apiDescription Returns all the authors aof a Publication
 *
 * @apiParam {Number} P_ID Publication id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]} authors Publication's authors
*/

# /publication/{P_ID}/author/{A_ID}
/**
 * @api {get} publication/{P_ID}/author/{A_ID} Author
 * @apiVersion 0.1.1
 * @apiName publication_id_author_id
 * @apiGroup Publication
 *
 * @apiDescription Returns a specific author of a Publication
 *
 * @apiParam {Number} P_ID Publication id
 * @apiParam {Number} A_ID Author id
 *
 * @apiHeader Accept "text/html text/xml" or "text/html application/json"
 *
 * @apiSuccess {String[]} author Publication's author
*/
?>
