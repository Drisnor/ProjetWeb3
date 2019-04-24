<!DOCTYPE html>
<html lang="fr">
    <head>
        <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css" />
        <script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet-geometryutil@0.9.1/src/leaflet.geometryutil.min.js"></script>
        <script src="leaflet-knn.min.js"></script>
        <link type="text/css" rel="stylesheet" href="CSS/EtoileCSS.css">
        <title>Projet Web3</title>

        <!-- Données de la BDD -->
        <?php include 'PHP/loadData.php' ?>

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
        <!-- Affichage et traitement des données de la carte -->
        <script type="text/javascript">
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

            var iconEcole  = new LeafIcon({ iconUrl: 'https://i.postimg.cc/50XtqXKN/Icon-Ecole.png' });
            var parcVert   = new LeafIcon({ iconUrl: 'https://i.postimg.cc/ZYf404TK/ParcV.png' });
            var parcOrange = new LeafIcon({ iconUrl: 'https://i.postimg.cc/8cCGwnbS/ParcO.png' });
            var parcRouge  = new LeafIcon({ iconUrl: 'https://i.postimg.cc/gjBW7qM6/ParcR.png' });

            /* Données des écoles */
            var dataEcoles = <?php echo JSON_encode($ecoles); ?>;
            dataEcoles = JSON.parse(dataEcoles);

            var Ecoles = L.layerGroup();  // pour avoir le choix d'afficher les données dans le layer
            donneesEcoles(dataEcoles);  // données des popups pour les écoles

            /* Affichage des marqueurs en fonction de données */
            var dataJeux = <?php echo JSON_encode($jeux); ?>;
            dataJeux = JSON.parse(dataJeux);

            /* Données pour les jeux */
            var Jeux = L.layerGroup(); 
            donneesParcs(dataJeux);  // données des popups pour les parcs

            /* Choix de l'affichage des données */
            var overlays = { "Ecoles": Ecoles, "Jeux": Jeux }

            /* Ajout du formulaire sur la carte */
            L.control.layers({},overlays).addTo(carte);
            Jeux.addTo(carte);  // Affichage des aires de jeux par défaut
            Ecoles.addTo(carte);

            /*******************************************************************************************************/
                /******************************** PARTIE FONCTIONS ********************************/
            /*******************************************************************************************************/            
            /* Affichage des marqueurs en fonction des données des écoles */
            function donneesEcoles(dataEcoles) {
                for (var i = 0; i < dataEcoles.length; i++) {
                   var formulaireEcoles =
                        '<table class="popup-table' + dataEcoles[i].id + '">'
                            + '<tr>'
                            +    '<th>Adresse :</th>'
                            +    '<td id="adr" name="adr">' + dataEcoles[i].libelle + '</td>'
                            + '</tr>'
                            + '<tr>'
                            +   '<th>Telephone :</th>'
                            +   '<td id="tel" name="tel">' + dataEcoles[i].tel + '</td>'
                            + '</tr>'
                        + '</table>'
                        + '</form>';
                        
                    var popup = L.popup().setContent(contenuEcoles(dataEcoles, i, formulaireEcoles));
                    
                    var customOptions = {'minWidth': '300'};
                    L.marker([dataEcoles[i].longitude, dataEcoles[i].latitude], {icon: iconEcole})
                     .bindPopup(popup,customOptions)
                     .on('click', clicEcole)  // traite le clic sur les écoles
                     .addTo(Ecoles);
                }
            }


            /* Récupère et affecte les données des parcs dans des popups */
            function donneesParcs(dataJeux) {
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

                    var formulaire ='<table class="popup-table">'
                            + '<tr>'
                                + '<input id="superficie" name="superficie" type="number"' + 'value="'+ dataJeux[i].superficie +'"/> m²'
                            + '</tr>'

                            + '<tr>'
                            +   '<th>Note:</th>'
                            +   '<td id='+note+' name="note">' + star + '</td>'
                            + '</tr>'
                        + '</table>'
                        + '<button data=\"'+ dataJeux[i].id +'\" type="submit">Modifier</button>'
                        ;

                    /* Affichage des données des parcs dans les popups */
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

                // Ajout du formulaire
                var wrapper = document.createElement('form');
                wrapper.innerHTML = formulaire;

                // Ecouteur sur les formulaires des parcs pour màj les données
                wrapper.onsubmit = function(e){
                    e.preventDefault();
                    let id = $(e.srcElement).find('button').attr('data');
                    let superficie = $(e.srcElement).find('#superficie').val();
                    let note = $(e.srcElement).find('input[type=radio]:checked').val();
                    console.log(superficie, note);

                   $.ajax({
                    url: 'PHP/updateJeux.php',
                    type: 'GET',
                    data: {id: id, superficie: superficie, note:note},
                    success: function(data) {
                        return true;  //modifications locales déjà effectuées
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("Données des parcs non mises à jour !");
                        return false;
                    }
                   });
                }  
                // Ajout des éléments dans le DOM
                div.appendChild(titre);
                div.appendChild(wrapper);

                return div;
            }

            function contenuEcoles(dataJeux, i, formulaire) {
                /* Création des éléments pour le DOM */
                var div = document.createElement("div");
                var titre = document.createElement("h3");
                titre.innerHTML = dataJeux[i].ecole;

                // Ajout du formulaire
                var wrapper = document.createElement('span');
                wrapper.innerHTML = formulaire;

                // Ajout des éléments dans le DOM
                div.appendChild(titre);
                div.appendChild(wrapper);

                return div;
            }

            /* Affiche le nom, la superficie et la note d'un parc (en fonction d'un layer de parc) */
            function getParcInfos(parc) {
                    var infos = $(parc.layer._popup._content).prop('children');
                    var nom = infos[0].innerHTML;
                    var form = infos[1];
                    let superficie = $(form).find('#superficie').val();
                    let note = $(form).find('input[type=radio]:checked').val();
                    console.log("Parc : nom ", nom, "superficie", superficie, "note", note);
            } 

            /* Trouve les n parcs les plus proches d'une école sélectionnée */
            function nParcsProches(posEcole, parc, n) {
                //console.log("id1", idEcole);

                // Recherche du parc1
                if (parc == null) { 
                    parc = L.GeometryUtil.closestLayer(carte, [Jeux], posEcole);  // => Le parc le plus proche de l'école sélectionnée
                    //var distance = parc.distance;  // TODO afficher dans un tableau sur le site => + avoir tous les parcs dans un rayon autour de l'école => Tri par meilleure note
                    var coordsParc = parc.latlng;
                    var marker = L.marker([coordsParc.lat, coordsParc.lng])
                                    .bindPopup("" + 0)
                                    .addTo(carte);
                    // TODO : Supprimer les marqueurs quand on change d'école (var globale avec le nom de l'école sélectionnée et CHEKC == au e.nom(event onClick))
                    
                    getParcInfos(parc); /* Récupère les données du parc (courant) le plus proche (superficie, note) */
                }

                /* recherche d'autres parcs */
                var parcs = [];  // "n-ième" parc à la position n-1 dans le tableau
                parcs.push(parc);  // on garde le 1er parc trouvé

                // On recherche d'autres parcs proches, en supprimant toujours les parcs trouvés précedemment                   
                for(var i = 1 ; i < n ; i++) {
                    Jeux.removeLayer(parc.layer._leaflet_id); // supprime le parc précédent de la liste de recherche
                    parc = L.GeometryUtil.closestLayer(carte, [Jeux], posEcole);  // => Le "n-ième" parc le plus proche de l'école sélectionnée
                    var coordsParc = parc.latlng;
                    var marker = L.marker([coordsParc.lat, coordsParc.lng])
                                    .bindPopup("" + i)
                                    .addTo(carte);

                    getParcInfos(parc); /* Récupère les données du parc (courant) le plus proche (superficie, note) */
                    parcs.push(parc);  // on garde les autres parcs trouvés
                    carte.addLayer(parcs[i-1].layer);  // on remet le l'ancien parc (gardé dans parcs) sur la carte
                }

                // A la fin de la recherche, on replace les n parcs trouvés dans la liste des parcs (Jeux)
                for ( var j = 0 ; j < parcs.length ; j++) {
                    Jeux.addLayer(parcs[j].layer);
                }

                console.log("Parcs trouvés : ", parcs);  // TODO afficher dans un tableau sur le site => + avoir tous les parcs dans un rayon autour de l'école => Tri par meilleure note
            }

            /* Le clic sur une école détermine les 3 meilleurs parcs : DISTANCE */
            function clicEcole(e) {
                var id = e.target._leaflet_id;
                var coords = e.latlng;
                var posEcole = new L.latLng(coords.lat, coords.lng);

                var n = 3;  // les 3 parcs les plus proches
                nParcsProches(posEcole, null, n);
            }            

        </script>
    </body>
</html>