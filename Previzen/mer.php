<?php
$title = "PreviZen";
$description = "La météo des plages disponible en un clic";
$h1 = "Prévision météo sur le littoral francais métropolitain";
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'fr';

include './include/functions.inc.php';

$departementsCotiers = [
    // Méditerranée
    '06', '13', '30', '34', '11', '66', '83',

    // Atlantique
    '17', '16', '33', '40', '64',

    // Bretagne / Loire
    '29', '22', '35', '44', '85', '56',

    // Manche / Nord
    '50', '14', '76', '62', '59'
];

$plage = null;
$meteoPlage = null;

if (isset($_GET['plage'])) {
    $plage = htmlspecialchars($_GET['plage']);
    $dep = getDepartementFromCSV($plage);
    if ($dep && in_array($dep, $departementsCotiers)) {
        $meteoPlage = getPlageWeatherData($plage);
    } else {
        $meteoPlage = null;
        $villeInvalide = true;
    }
}

// ➕ Ajout dynamique des vents pour les stations
$stations = getTopBeachStations();
foreach ($stations as &$station) {
    $meteo = getPlageWeatherData($station['name']);
    $station['vent'] = $meteo['vent'] ?? 0;
}

include "./include/header.inc.php";
?>
<section>
    <article class="choix-cote" id="choix">
        <h2>Choisissez une côte</h2>

        <?php if (!isset($_GET['zone'])): ?>
            <div class="cote-cards">
                <a href="mer.php?zone=manche#choix" class="cote-card manche">🌊 Manche</a>
                <a href="mer.php?zone=atlantique#choix" class="cote-card atlantique">🌬 Atlantique</a>
                <a href="mer.php?zone=mediterranee#choix" class="cote-card mediterranee">☀️ Méditerranée</a>
            </div>
        <?php else:
            $zone = $_GET['zone'];
            $stationsParZone = [
                'manche' => ['Dieppe', 'Le Havre', 'Cherbourg', 'Granville', 'Saint-Malo'],
                'atlantique' => ['La Rochelle', 'Arcachon', 'Royan', 'Biarritz', 'Soulac-sur-Mer'],
                'mediterranee' => ['Nice', 'Cannes', 'Sète', 'Marseille', 'Argelès-sur-Mer']
            ];
        ?>
            <div class="cote-cards">
                <?php foreach ($stationsParZone[$zone] as $station): ?>
                    <a href="mer.php?plage=<?= urlencode($station) ?>" class="cote-card"><?= htmlspecialchars($station) ?></a>
                <?php endforeach; ?>
            </div>
            <p><a href="mer.php" class="btn secondary">🔙 Retour au choix des côtes</a></p>
        <?php endif; ?>

        <div class="autre-ville">
            <h3>Ou entrez une ville manuellement</h3>
            <form method="get">
            <label for="plage" class="visually-hidden">Nom d'une plage</label>
            <input type="text" id="plage" name="plage" placeholder="Ex. : Biarritz, Nice, La Baule..." required="required"/>

                <button type="submit">Voir la météo</button>
            </form>
        </div>

        <?php if (isset($villeInvalide) && $villeInvalide): ?>
            <div class="alerte-ville">❌ Désolé, <strong><?= htmlspecialchars($plage) ?></strong> ne semble pas être une station balnéaire.</div>
        <?php endif; ?>

        <?php if ($meteoPlage): ?>
        <article class="meteo-local">
        <h2>Prévision météo à <?= htmlspecialchars($plage) ?></h2>
        <p>Consultez les conditions météo détaillées pour votre station balnéaire.</p>

        <div class="meteo-principale">
            <div class="temperature">
                <span class="temp-val"><?= round($meteoPlage['temp_air']) ?>°</span>
                <span class="temp-ressenti">Eau : <?= round($meteoPlage['temp_eau']) ?>°</span>
            </div>
            <div class="meteo-condition">
            <img src="<?= $meteoPlage['icone'] ?>" alt="Condition météo">
            <span><?= htmlspecialchars($meteoPlage['condition']) ?></span>
            </div>
            <div class="vent">
                Vent : <?= $meteoPlage['vent'] ?> km/h
            </div>
        </div>

        <div class="previsions-heures">
            <div class="carte-moment">
                <h4>UV</h4>
                <p><?= $meteoPlage['uv'] ?></p>
            </div>
            <div class="carte-moment">
                <h4>Marée</h4>
                <p><?= $meteoPlage['maree'] ?></p>
            </div>
        </div>

        <details class="details-box">
            <summary class="detail-btn">Plus de détails</summary>
            <ul>
                <li><strong>Condition :</strong> <?= $meteoPlage['condition'] ?></li>
                <li><strong>Température de l’air :</strong> <?= round($meteoPlage['temp_air']) ?> °C</li>
                <li><strong>Température de l’eau :</strong> <?= round($meteoPlage['temp_eau']) ?> °C</li>
                <li><strong>Vent moyen :</strong> <?= $meteoPlage['vent'] ?> km/h</li>
                <li><strong>Indice UV :</strong> <?= $meteoPlage['uv'] ?></li>
                <li><strong>Marée :</strong> <?= $meteoPlage['maree'] ?></li>
            </ul>
        </details>
    </article>
    <?php endif; ?>

    </article>



    <article id="carte-france">
    <h2>Carte des vents pour les principales stations balnéaires françaises.</h2>
    <p style="text-align: center;">Sélectionnez une des principales stations marquées sur la carte pour voir les rafales de vent</p>

    <div id="map" style="height: 500px; width: 100%; border-radius: 12px; margin-top: 1rem;"></div>

    <div class="map-legend">
        <strong>Légende :</strong>
        <span class="legend-dot" style="background-color: green;"></span> Vent faible (&lt; 10 km/h)
        <span class="legend-dot" style="background-color: yellow;"></span> Vent modéré (10–20 km/h)
        <span class="legend-dot" style="background-color: orange;"></span> Vent soutenu (20–30 km/h)
        <span class="legend-dot" style="background-color: red;"></span> Vent fort (&gt; 30 km/h)
    </div>

    <script>
        const stations = <?= json_encode($stations) ?>;
        const selectedZone = "<?= $_GET['zone'] ?? '' ?>";
    </script>
    <script src="js/marineMap.js"></script>
    </article>


    <!-- SECTION 3 : Conseils -->
    <article id="conseils">
    <h2>Conseils pour une baignade en toute sécurité</h2>

    <div class="conseils-wrapper">
        <div class="card-info">
        <h3>Drapeaux de baignade</h3>
        <ul>
            <li><span class="circle green"></span> <strong>Vert :</strong> baignade autorisée et surveillée. Conditions favorables.</li>
            <li><span class="circle orange"></span> <strong>Orange :</strong> baignade autorisée mais dangereuse. Soyez vigilants.</li>
            <li><span class="circle red"></span> <strong>Rouge :</strong> baignade interdite pour votre sécurité.</li>
            <li><span class="circle blackwhite"></span> <strong>Damier :</strong> zone réservée aux activités nautiques (surf, planche...)</li>
        </ul>
        </div>

        <div class="card-info">
        <h3>Numéros d'urgence à connaître</h3>
        <ul>
            <li><strong>112</strong> - Urgences européennes (accidents, détresse)</li>
            <li><strong>15</strong> - SAMU (urgence médicale)</li>
            <li><strong>18</strong> - Pompiers (incendie, sauvetage)</li>
            <li><strong>196</strong> - Secours en mer (CROSS) depuis un téléphone mobile</li>
        </ul>
        </div>
    </div>
    </article>
</section>




<?php require "./include/footer.inc.php"; ?>
