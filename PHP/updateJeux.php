<?php
	// Connexion BD
	include "connexionBD.inc.php";

	/* Récupération des données JSON */
    if( isset($_GET['id']) && isset($_GET['superficie']) && isset($_GET['note'])) {  // si les données sont bien définies

		/* Requête UPDATE */
		$sql = "UPDATE jeux SET superficie=:sup, note=:note WHERE id=:id";
		$stmt = $cnx->prepare($sql);
		$stmt->bindParam(':id', $_GET['id'], PDO::PARAM_STR);
		$stmt->bindParam(':sup', $_GET['superficie'], PDO::PARAM_STR);
		$stmt->bindParam(':note', $_GET['note'], PDO::PARAM_STR);

		$stmt->execute();

    } else {  // ERR
    	echo 'ERREUR UPDATE';
    }
?>