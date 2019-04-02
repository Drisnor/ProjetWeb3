<?php				
	$DB_NAME = "projet";
	$DB_HOST = "localhost";
	$DB_USER = "root";
	$DB_PASS = "";
	
	try {
		// utf-8
		$options = array(
		  PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
		);

		$cnx = new PDO('mysql:host='.$DB_HOST.';dbname='.$DB_NAME, $DB_USER, $DB_PASS, $options);
		//echo "Connexion à la BDD réussie";
	} catch (PDOException $e) {
	    print "Echec de la connexion !: " . $e->getMessage() . "<br/>";
	    die();
	}
?>