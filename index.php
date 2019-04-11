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
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<link type="text/css" rel="stylesheet" href="CSS/EtoileCSS.css">
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
             	minZoom: 12,
                  layers: [
					Ecoles
                  ]
            }).addTo(carte);
			
			/* Affichage des marqueurs en fonction de données */
            var dataEcoles = <?php echo JSON_encode($ecoles); ?>;
            dataEcoles = JSON.parse(dataEcoles);
			
            // lien galerie d'images : https://postimg.cc/gallery/3891zxw0g/
            /* Icone pour les écoles */
            var LeafIcon = L.Icon.extend({
                options: {
                   iconSize:     [20, 20]
                   /*,
                   shadowSize:   [50, 64],
                   iconAnchor:   [22, 94],
                   shadowAnchor: [4, 62],
                   popupAnchor:  [-3, -76]*/
                }
            });

            var iconEcole = new LeafIcon({
                iconUrl: 'https://i.postimg.cc/50XtqXKN/Icon-Ecole.png'
            });

            var parcVert = new LeafIcon({
                iconUrl: 'https://i.postimg.cc/ZYf404TK/ParcV.png'
            });
            
            var parcOrange = new LeafIcon({
                iconUrl: 'https://i.postimg.cc/8cCGwnbS/ParcO.png'
            });

            var parcRouge = new LeafIcon({
                iconUrl: 'https://i.postimg.cc/gjBW7qM6/ParcR.png'
            });


            /* Données des écoles */
			var Ecoles = L.layerGroup();
			
            for (var i = 0; i < dataEcoles.length; i++) {
                L.marker([dataEcoles[i].longitude, dataEcoles[i].latitude], {icon: iconEcole})
                 .bindPopup(dataEcoles[i].ecole)
                 .addTo(Ecoles);
            }

			/* Affichage des marqueurs en fonction de données */
            var dataJeux = <?php echo JSON_encode($jeux); ?>;
            dataJeux = JSON.parse(dataJeux);
			
            /* Catégories des parcs (petit, moyen, grand) en fonction de la superficie (en m²) 
             */
             /* TODO => Accéder aux données facilement/rapidement afin d'affecter les icones en fonction de la superficie */
             /* TODO placer les repères / polygones => Les rendre cliquables et identifier le parc en fonction du clic */

            /* Données pour les jeux */
			var Jeux = L.layerGroup();

            for (var i = 0; i < dataJeux.length; i++) {
            	var icone;
            	// Catégorie de parcs en fonction de la SUPERFICIE / nbJeux (différents icônes)
            	if ( dataJeux[i].superficie >= 0 && dataJeux[i].superficie < 15) {
            		icone = parcRouge;
            	} else if (dataJeux[i].superficie > 15 && dataJeux[i].superficie <= 30) {
            		icone = parcOrange;
            	} else {
            		icone = parcVert;
            	}
				
				/* Note des parcs avec etoiles*/
				var star ="<span class='rating'>"
					  +"<input id='rating5' type='radio' name='rating' value='5'>"
					  +"<label for='rating5'>5</label>"
					  +"<input id='rating4' type='radio' name='rating' value='4'>"
					  +"<label for='rating4'>4</label>"
					  +"<input id='rating3' type='radio' name='rating' value='3'>"
					  +"<label for='rating3'>3</label>"
					  +"<input id='rating2' type='radio' name='rating' value='2' checked>"
					  +"<label for='rating2'>2</label>"
					  +"<input id='rating1' type='radio' name='rating' value='1'>"
					  +"<label for='rating1'>1</label>"
					  +"</span> ";
					  
				/* Affichage des données des parcs dans les popups */
				// TODO Pouvoir modif les CHAMPS + notes avec les étoiles 				
				var content = function() {
					$("h3").append(dataJeux[i].nom);
					var para = $("p").append("<p>Test</p>");/*
					var node = document.createTextNode("<p><b> Superficie : " + dataJeux[i].superficie + ' m²</b><br>');
					var node2 = document.createTextNode('<b> Note : ' + dataJeux[i].note + '/5</b><br>');
					

	
					var titre = document.createElement("h3");
					titre.innerHTML = dataJeux[i].nom;*/


					// Ecouteur pour clic
					para.addEventListener('click', function() { console.log('clicked'); });
					
					return para;
				}
					  
                /* popup (onClick) qui affiche toutes les informations de chaque parc */
                var popup = L.popup({className: 'popup'})
                    .setContent(content()
                );
				
				
				
				/* Ajout d'ecoute sur les etoiles liés aux parcs */
				/* var star5 = document.getElementById("rating5");
				star5.addEventListener("click", test);
				
				var star4 = document.getElementById("rating4");
				star4.addEventListener("click", test);

				var star3 = document.getElementById("rating3");
				star3.addEventListener("click", test);

				var star2 = document.getElementById("rating2");
				star2.addEventListener("click", test);

				var star1 = document.getElementById("rating1");
				star1.addEventListener("click", test);	 */

                /* Ajout des infos sur la carte */
                L.marker([dataJeux[i].longitude, dataJeux[i].latitude], {icon : icone})
                 .bindPopup(popup)
                 .addTo(Jeux);
            }

            /* Choix de l'affichage des données */
			var overlays = {
				"Ecoles": Ecoles,
				"Jeux": Jeux
			}
            /* Ajout du formulaire sur la carte */
            L.control.layers({},overlays).addTo(carte);
			
			function test(e){
				console.log(e.originalTarget.defaultValue);
			}
        </script>

    </body>
</html>
