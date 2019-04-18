<?php
	// Connexion BD
	include "connexionBD.inc.php";

	/* Récupération des données JSON */
    if( isset($_GET['id']) && isset($_GET['superficie']) && isset($_GET['note'])) {  // si les données sont bien définies
		$id = $_GET['id'];
		$superficie = $_GET['superficie'];
		$note = $_GET['note'];

		/* Requête UPDATE */
		$sql = "UPDATE jeux SET superficie=?, note=? WHERE id=?";
		$stmt= $dpo->prepare($sql);
		$stmt->execute([$superficie, $note, $id]);

    } else {  // ERR

    }
?>