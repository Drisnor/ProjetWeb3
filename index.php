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

            /* Ajout d'un layer pour switch l'affichage des "points" : ecoles / parcs */
			

			
				
            /* Vue de la carte */
        	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            	attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
             	maxZoom: 17,
             	minZoom: 12,
                  layers: [
					Ecoles
                  ]
            }).addTo(carte);
			
			/* Affichage des marqueurs en fonction de données */
            var dataEcoles = <?php echo JSON_encode($ecoles); ?>;
            dataEcoles = JSON.parse(dataEcoles);
			
			var Ecoles = L.layerGroup();
			
            for (var i = 0; i < dataEcoles.length; i++) {
                L.marker([dataEcoles[i].longitude, dataEcoles[i].latitude])
                 .bindPopup(dataEcoles[i].ecole)
                 .addTo(Ecoles);
            }

			/* Affichage des marqueurs en fonction de données */
            var dataJeux = <?php echo JSON_encode($jeux); ?>;
            dataJeux = JSON.parse(dataJeux);
			
			var Jeux = L.layerGroup();
			
            for (var i = 0; i < dataJeux.length; i++) {
                L.marker([dataJeux[i].longitude, dataJeux[i].latitude],{iconUrl: "images/ParcV.png"})
                 .bindPopup(dataJeux[i].nom)
                 .addTo(Jeux);
            }

			var overlays = {
				"Ecoles": Ecoles,
				"Jeux": Jeux
			}
            /* Ajout du formulaire sur la carte */
            L.control.layers({},overlays).addTo(carte);




            /* Marqueurs sur la map */

            /* TODO Catégorie de parcs en fonction de la SUPERFICIE : Logos rouge > orange > vert */
            // + placer les repères / polygones => Les rendre cliquables et identifier le parc en fonction du clic

        </script>
    </body>
</html>
