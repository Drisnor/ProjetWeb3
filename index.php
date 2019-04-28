<!DOCTYPE html>
<html lang="fr">
    <head>
        <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css" />
        <script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet-geometryutil@0.9.1/src/leaflet.geometryutil.min.js"></script>
        <script src="leaflet-knn.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
        <link type="text/css" rel="stylesheet" href="CSS/EtoileCSS.css">
        <title>Projet Web3</title>

        <!-- Données de la BDD -->
        <?php include 'PHP/loadData.php' ?>

        <style>
            .leaflet-popup-content {
                width: 200px;
                height: 300px;
                /*overflow-y: scroll; (scrollbar) */
            }

            /* Supprime les espaces entre les <li> */
            ul {
				padding: 0;
        		list-style: none;
			}

		    ul li {
		        display: inline-block;
    		}
        </style>
    </head>

    <body>

        <!-- Le conteneur de notre carte (avec une contrainte CSS pour la taille) -->
        <div id="macarte" style="width: 80%; height: 600px;"></div>
		
		<canvas id="pie-chart" width="800" height="450"></canvas>
        <!-- Affichage et traitement des données de la carte -->
        <script type="text/javascript">
            /*******************************************************************************************************/
                            /******************************** Appels ********************************/
            /*******************************************************************************************************/
            var carte = L.map('macarte').setView([43.6043 , 1.4437], 12);  // zoom sur Toulouse         
                
            /* Vue de la carte */
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
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
                        '<table class="popup-table">'
                            + '<tr>'
                            +    '<th>Adresse :</th>'
                            +    '<td id="adr" name="adr">' + dataEcoles[i].libelle + '</td>'
                            + '</tr>'
                            + '<tr>'
                            +   '<th>Telephone :</th>'
                            +   '<td id="tel" name="tel">' + dataEcoles[i].tel + '</td>'
                            + '</tr>'
                        + '</table>'
                        + '<button class="ecole" type="submit"> Supprimer marqueurs </button>'
                        + '</form>'
                        + '<h3> Meilleurs parcs proches : </h3>'
                        + '<ul name="parcs" id="' + dataEcoles[i].id +'">';
                        
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
                                + '<input class="superficie" name="superficie" type="number"' + 'value="'+ dataJeux[i].superficie +'"/> m²'
                            + '</tr>'

                            + '<tr>'
                            +   '<th>Note:</th>'
                            +   '<td class='+note+' name="note">' + star + '</td>'
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
                    let superficie = $(e.srcElement).find('.superficie').val();
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

            function contenuEcoles(dataEcoles, i, formulaire) {
                /* Création des éléments pour le DOM */
                var div = document.createElement("div");
                var titre = document.createElement("h3");
                titre.innerHTML = dataEcoles[i].ecole;

                // Ajout du formulaire
                var wrapper = document.createElement('form');
                wrapper.innerHTML = formulaire;

                wrapper.onsubmit = function(e){
                    e.preventDefault();
                }

                // Ajout des éléments dans le DOM
                div.appendChild(titre);
                div.appendChild(wrapper);

                return div;
            }

            /* Affiche le nom, la superficie et la note d'un parc dans la popup de l'école la plus proche */
            function getParcInfos(parc, popupEcole, n) {
                var infos = $(parc.layer._popup._content).prop('children');
                var nom = infos[0].innerHTML;
                var form = infos[1];
                let superficie = $(form).find('.superficie').val();
                let note = $(form).find('input[type=radio]:checked').val();
                console.log("Parc : nom ", nom, "superficie", superficie, "note", note);

                /* Si on a pas encore mis tous les meilleurs parcs pour l'école choisie */
                if( $(popupEcole)[0].childElementCount < n ) {
                	$(popupEcole).append('<li id="' + note + '" > nom : ' + nom + " superficie : " + superficie + " note : " + note + "</li>");
                } 
                // !! TODO  ACTUALISER si on change la note
            }

            /* Trouve les n parcs les plus proches d'une école sélectionnée 
               A chaque clic sur une école on affiche la liste des parcs proches classés par notes (dans la popup de l'école)
             */
            function nParcsProches(popupEcole, posEcole, parc, n) {
                var markers = [];  // liste de tous les markers des parcs proches
                var parcs = [];  // "n-ième" parc à la position n-1 dans le tableau

                // Recherche du parc1
                if (parc == null) { 
                    parc = L.GeometryUtil.closestLayer(carte, [Jeux], posEcole);  // => Le parc le plus proche de l'école sélectionnée
                    //var distance = parc.distance;  // TODO afficher dans un tableau sur le site => + avoir tous les parcs dans un rayon autour de l'école => Tri par meilleure note
                    var coordsParc = parc.latlng;
                    var marker = L.marker([coordsParc.lat, coordsParc.lng])
                                    .bindPopup("" + 0)
                                    .addTo(carte);
                    markers.push(marker);
                    parcs.push(parc);  // on garde le 1er parc trouvé
                    getParcInfos(parc, popupEcole, n); /* Affiche le parc dans la popup de l'école la plus proche */
                }

                /* recherche d'autres parcs */
                // On recherche d'autres parcs proches, en supprimant toujours les parcs trouvés précedemment                   
                for(var i = 1 ; i < n ; i++) {
                    Jeux.removeLayer(parc.layer._leaflet_id); // supprime le parc précédent de la liste de recherche
                    parc = L.GeometryUtil.closestLayer(carte, [Jeux], posEcole);  // => Le "n-ième" parc le plus proche de l'école sélectionnée
                    var coordsParc = parc.latlng;
                    var marker = L.marker([coordsParc.lat, coordsParc.lng])
                                    .bindPopup("" + i)
                                    .addTo(carte);
                    markers.push(marker);

                    getParcInfos(parc, popupEcole, n); /* Affiche le parc dans la popup de l'école la plus proche */
                    parcs.push(parc);  // on garde les autres parcs trouvés
                    carte.addLayer(parcs[i-1].layer);  // on remet le l'ancien parc (gardé dans parcs) sur la carte
                }

                // A la fin de la recherche, on replace les n parcs trouvés dans la liste des parcs (Jeux)
                for ( var j = 0 ; j < parcs.length ; j++) {
                    Jeux.addLayer(parcs[j].layer);
                }

                /* Ecouteur sur les boutons "supprimer" des écoles */
                $('.ecole').click(function() {
                    // On supprime les anciens marqueurs 
                    for ( var j = 0 ; j < markers.length ; j++) {
                        carte.removeLayer(markers[j]);
                    }
                    // on ne supprime pas les meilleurs parcs CAR on ne modifie pas la liste des parcs
                    // !! TODO : Actualiser l'ordre des parcs dans la liste si on change la note entre temps ! 
                });

                // Tri des meilleurs parcs par note :  TODO hashmap(nomEcole, note) triée
                // TODO Visualisation graphique : 5 = vert, 4 = orange ... sur les marqueurs des parcs
                $(function(){
                	var idEcole = $(popupEcole).prop('id');
				    var elems = $('#' + idEcole).children('li').remove();
				    elems.sort(function(a,b){
				        return parseInt(a.id) < parseInt(b.id);
				    });
				    $('#' + idEcole).append(elems);
				});
            }

            /* Le clic sur une école détermine les 3 meilleurs parcs : DISTANCE */
            function clicEcole(e) {
                var coords = e.latlng;
                var posEcole = new L.latLng(coords.lat, coords.lng);

                var n = 5;  // les n parcs les plus proches
            	var popupEcole = $(e.target._popup._content).find('ul');
                nParcsProches(popupEcole, posEcole, null, n);
            }    
			

			/************************ PARTIE STATS ************************/
			function getRandomColor() {
				var letters = '0123456789ABCDEF';
				var color = '#';
				for (var i = 0; i < 6; i++) {
					color += letters[Math.floor(Math.random() * 16)];
				}
				return color;
			}

			function Stats(){
				var effectifs = [];
				var nomEffectifs = [];	
				for(var i = dataEcoles.length-1; i > dataEcoles.length-11 ; i--){
					effectifs.push(dataEcoles[i].effectif);
					nomEffectifs.push(dataEcoles[i].ecole);
				}
				new Chart(document.getElementById("pie-chart"),{
					type : 'pie',
					data: {
						labels: nomEffectifs,
						datasets: [{
							label: "Test",
							backgroundColor: [getRandomColor(),getRandomColor(),getRandomColor(),getRandomColor(),getRandomColor(),getRandomColor(),getRandomColor(),getRandomColor(),getRandomColor(),getRandomColor()],
							data: effectifs
						}]
					},
					options: {
						title: {
							display: true,
							text: 'Effectif des dix plus grandes écoles Toulousaines'
						}
					}
				});
			}

			Stats();
        </script>
    </body>
</html>