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
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css" />
        <script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <link type="text/css" rel="stylesheet" href="CSS/EtoileCSS.css">
        <title>Projet Web3</title>

        <style>
            .leaflet-popup-content {
                width: 200px;
                height: 150px;
                /*overflow-y: scroll; (scrollbar) */
            }
        </style>
    </head>

    <body>
        <!-- Le conteneur de notre carte (avec une contrainte CSS pour la taille) -->
        <div id="macarte" style="width: auto; height: 800px;"></div>

        <!-- Affichage de la carte -->
        <script type="text/javascript">
            /********************* PARTIE FONCTIONS *********************/
            /* Affichage des marqueurs en fonction des données des écoles */
            function donneesEcoles() {
                for (var i = 0; i < dataEcoles.length; i++) {
                    L.marker([dataEcoles[i].longitude, dataEcoles[i].latitude], {icon: iconEcole})
                     .bindPopup(dataEcoles[i].ecole)
                     .addTo(Ecoles);

                     /* TODO : Le clic sur une école détermine les 3 meilleurs parcs : note / DISTANCE */
                }
            }

            /* Récupère et affecte les données des parcs dans des popups */
            function donneesParcs() {
                for (var i = 0; i < dataJeux.length; i++) {
                    var icone;
                    // Catégorie de parcs en fonction de la SUPERFICIE / nbJeux (différents icônes)
                    if ( dataJeux[i].superficie >= 0 && dataJeux[i].superficie <= 15) {
                        icone = parcRouge;
                    } else if (dataJeux[i].superficie > 15 && dataJeux[i].superficie <= 30) {
                        icone = parcOrange;
                    } else {
                        icone = parcVert;
                    }

                    /* Note des parcs avec etoiles*/
                    var star =
                        "<span class='rating'>"
                          +"<input id='rating5' type='radio' name='rating' value='5' >"
                          +"<label for='rating5'>5</label>"
                          +"<input id='rating4' type='radio' name='rating' value='4' >"
                          +"<label for='rating4'>4</label>"
                          +"<input id='rating3' type='radio' name='rating' value='3' >"
                          +"<label for='rating3'>3</label>"
                          +"<input id='rating2' type='radio' name='rating' value='2' >"
                          +"<label for='rating2'>2</label>"
                          +"<input id='rating1' type='radio' name='rating' value='1' >"
                          +"<label for='rating1'>1</label>"
                          +"</span> ";

                    // Assigne la note par défaut de chaque parc
                    var note = dataJeux[i].note;
                    var position = star.search("'"+note); // position de la value pour une note
                    var decalage = position+4;  // pour écrire juste avant la fin de l'input
                    star = star.substr(0, decalage) + "checked" + star.substr(decalage);  // coche la bonne note

                    var formulaire = '<form id="popup-form" action="index.php" method="GET">'
                        + '<label>Superficie : </label>' + dataJeux[i].superficie + ' m²'
                        + '<input id="superficie" type="number" />'
                        + '<table class="popup-table">'
                            + '<tr>'
                            +   '<th>Note:</th>'
                            +   '<td id="note">' + star + '</td>'
                            + '</tr>'
                        + '</table>'
                        + '<button id="btn" type="submit">Modifier</button>'
                        + '</form>';

                    /* Affichage des données des parcs dans les popups */
                    // TODO Pouvoir modif les CHAMPS (+ notes)  => formulaire dans les popups ++ pour les écoles            
                          
                    /* popup (onClick) qui affiche toutes les informations de chaque parc */
                    var popup = L.popup().setContent(contenu(dataJeux, i, formulaire));

                    /* Ajout des infos sur la carte */
                    L.marker([dataJeux[i].longitude, dataJeux[i].latitude], {icon : icone})
                     .bindPopup(popup)
                     .addTo(Jeux);
                }
            }
            
            // Ajout du contenu dans chaque popup pour les parcs
            function contenu(dataJeux, i, formulaire) {
                /* Création des éléments pour le DOM */
                var div = document.createElement("div");
                var titre = document.createElement("h3");
                titre.innerHTML = dataJeux[i].nom;
                div.appendChild(titre);

                /* Tableau pour présenter le formulaire de modification des données */
                var node2 = document.createTextNode('Note : ');

                // Ajout des éléments dans le DOM
                // Ajout du formulaire
                var wrapper = document.createElement('span');
                wrapper.innerHTML = formulaire;
                div.appendChild(wrapper);

                return div;
            }

/*******************************************************************************************************/
                /******************************** Appels ********************************/
/*******************************************************************************************************/
            var carte = L.map('macarte').setView([43.6043 , 1.4437], 12);  // zoom sur Toulouse         
                
            /* Vue de la carte */
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 16,
                minZoom: 12,
                  layers: [
                    Ecoles
                  ]
            }).addTo(carte);
            
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
            var dataEcoles = <?php echo JSON_encode($ecoles); ?>;
            dataEcoles = JSON.parse(dataEcoles);

            var Ecoles = L.layerGroup();  // pour avoir le choix d'afficher les données dans le layer
            donneesEcoles();

            /* Affichage des marqueurs en fonction de données */
            var dataJeux = <?php echo JSON_encode($jeux); ?>;
            dataJeux = JSON.parse(dataJeux);

            /* Données pour les jeux */
            var Jeux = L.layerGroup(); 
            donneesParcs();  // données des popups pour les parcs

            /* Choix de l'affichage des données */
            var overlays = { "Ecoles": Ecoles, "Jeux": Jeux }

            /* Ajout du formulaire sur la carte */
            L.control.layers({},overlays).addTo(carte);
            Jeux.addTo(carte);  // Affichage des aires de jeux par défaut
        </script>
    </body>
</html>