<?php
    /* **** Récupération des données **** */
    // Connexion BD
    include "PHP/connexionBD.inc.php";

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

    // test
    function coordsEcole($data) {
        /* Affichage des données */
        foreach($data as $row)
        {
            echo $row["ecole"], " : ", $row["longitude"], $row["latitude"], "<br>";
        }
    }

    /* ***************************************************************** */
    /* test() */
    //coordsEcole($ecoles);
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css" />
        <script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>

        <title>Projet Web3</title>
    </head>
    <body>
        <!-- Le conteneur de notre carte (avec une contrainte CSS pour la taille) -->
        <div id="macarte" style="width: 80%; height: 800px;"></div>

        <!-- Affichage de la carte -->
        <script type="text/javascript">
        	var carte = L.map('macarte').setView([43.6043 , 1.4437], 12);  // zoom sur Toulouse

        	/* Vue de la carte */
        	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            	attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
             	maxZoom: 17,
             	minZoom: 12
            }).addTo(carte);

            /* Affichage des marqueurs en fonction de données */
            var data = <?php echo JSON_encode($ecoles); ?>;
            data = JSON.parse(data);

            /* Marqueurs sur la map */
            for (var i = 0; i < data.length; i++) {
                L.marker([data[i].longitude, data[i].latitude])
                 .bindPopup(data[i].ecole)
                 .addTo(carte);
            }

            /* TODO Catégorie de parcs en fonction de la SUPERFICIE : Logos rouge > orange > vert */
            // + placer les repères / polygones => Les rendre cliquables et identifier le parc en fonction du clic

        </script>
    </body>
</html>
