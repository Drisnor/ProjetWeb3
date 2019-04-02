<?php
	// Connexion BD
	include "connexionBD.inc.php";

	$ecoles = $cnx->prepare("SELECT * FROM ecoles");
	$jeux = $cnx->prepare("SELECT * FROM jeux");

	// FETCH:ASSOC : format des résultats => tableau associatif
	$ecoles->setFetchMode(PDO::FETCH_ASSOC);
	$jeux->setFetchMode(PDO::FETCH_ASSOC);

	// Récupération des données
	$ecoles->execute();
	$jeux->execute();


	function coordsEcole($data) {
		/* Traitement des données */
		foreach($data as $row)
		{
		    echo $row["ecole"], " : ", $row["longitude"], $row["latitude"], "<br>";
		}
	}

	/* ***************************************************************** */
	/* main() */
	coordsEcole($ecoles);
?>