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

    // Conversions des données en tableau
    $ecoles = $ecoles->fetchAll();
    $jeux = $jeux->fetchAll();

    // Conversions des données en JSON
    $ecoles = JSON_encode($ecoles);
    $jeux = JSON_encode($jeux);
?>