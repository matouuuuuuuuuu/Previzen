<?php
$title = "PreviZen";
$description = "La météo des neiges disponible en un clic";
$h1 = "Prévision météo dans les massifs montagneux sur une période de 7 jours";
$lang = $_GET['lang'] ?? 'fr';

include "./include/functions.inc.php";

$stationsJS = [];
$massifs = ['alpes', 'pyrenees', 'vosges', 'jura', 'massif-central', 'corse'];
$center = getMassifMapCenter($_GET['massif'] ?? '');

foreach ($massifs as $massif) {
    $stationsJS = array_merge($stationsJS, getTopSkiStationsByMassif($massif));
}

$selectedStation = $_GET['station'] ?? null;
$snowData = [];

if ($selectedStation) {
    foreach ($stationsJS as $station) {
        if ($station['name'] === $selectedStation) {
            $snowData = getSnowDataForStation($station['name'], $station['lat'], $station['lon']);
            break;
        }
    }
}
?>
<?php include "./include/header.inc.php"; ?>



<section>
  <h2>Massifs montagneux francais</h2>
<article class="bloc-massifs">
  <div class="massif-card">
    <div class="massif-text">
      <h3>Alpes françaises</h3>
      <p>Suivez les chutes de neige et les températures des stations alpines.</p>
      <a href="./neige.php?massif=alpes#carteLien" class="btn primary" style="text-align: center;">Consulter les prévisions d'enneigement</a>
      <a href="./departements.php?region=auvergne-rhône-alpes" class="btn secondary">Consulter les prévisions météo</a>
    </div>
    <div class="massif-image">
      <?php displayRandomPhotoFigureByMassif('alpes'); ?>
    </div>
  </div>

  <div class="massif-card reverse">
    <div class="massif-text">
      <h3>Jura</h3>
      <p>Consultez les conditions météo idéales pour le ski nordique.</p>
      <a href="./neige.php?massif=jura#carteLien" class="btn primary" style="text-align: center;">Consulter les prévisions d'enneigement</a>
      <a href="./departements.php?region=bourgogne-franche-comté" class="btn secondary">Consulter les prévisons météo</a>
    </div>
    <div class="massif-image">
      <?php displayRandomPhotoFigureByMassif('jura'); ?>
    </div>
  </div>

  <div class="massif-card">
    <div class="massif-text">
      <h3>Pyrénées</h3>
      <p>Vérifiez les prévisions météo et les hauteurs de neige dans les Pyrénées.</p>
      <a href="./neige.php?massif=pyrenees#carteLien" class="btn primary" style="text-align: center;">Consulter les prévisions d'enneigement</a>
      <a href="./departements.php?region=occitanie" class="btn secondary">Consulter les prévisions météo</a>
    </div>
    <div class="massif-image">
      <?php displayRandomPhotoFigureByMassif('pyrenees'); ?>
    </div>
  </div>

  <div class="massif-card reverse">
    <div class="massif-text">
      <h3>Vosges</h3>
      <p>Restez informé des précipitations et du froid en altitude.</p>
      <a href="./neige.php?massif=vosges#carteLien" class="btn primary" style="text-align: center;">Consulter les prévisions d'enneigement</a>
      <a href="./departements.php?region=grand-est" class="btn secondary">Consulter les prévisions météo</a>
    </div>
    <div class="massif-image">
      <?php displayRandomPhotoFigureByMassif('vosges'); ?>
    </div>
  </div>

  <div class="massif-card">
    <div class="massif-text">
      <h3>Massif Central</h3>
      <p>Obtenez les bulletins neige et météo au cœur des volcans d’Auvergne.</p>
      <a href="./neige.php?massif=massif-central#carteLien" class="btn primary" style="text-align: center;">Consulter les prévisions d'enneigement</a>
      <a href="./departements.php?region=bourgogne-franche-comté" class="btn secondary">Consulter les prévisions météo</a>
    </div>
    <div class="massif-image">
      <?php displayRandomPhotoFigureByMassif('massif-central'); ?>
    </div>
  </div>

  <div class="massif-card reverse">
    <div class="massif-text">
      <h3>Corse</h3>
      <p>Anticipez les conditions météo en montagne sur l'île de Beauté.</p>
      <a href="./neige.php?massif=corse#carteLien" class="btn primary" style="text-align: center;">Consulter les prévisions d'enneigement</a>
      <a href="./departements.php?region=corse" class="btn secondary">Consulter les prévisions météo</a>
    </div>
    <div class="massif-image">
      <?php displayRandomPhotoFigureByMassif('corse'); ?>
    </div>
  </div>

</article>

<article id="carteLien">
    <h2><?= $h1 ?></h2>
    <p style="text-align:center;">Consultez les prévisions météorologiques en cliquant sur une station sur la carte.</p>

    <div class="grid-neige" style="margin-top: 2rem;">
        <div class="left-column">
            <?php if ($selectedStation): ?>
                <h4>Prévisions pour <?= htmlspecialchars($selectedStation) ?></h4>
                <?php if (!empty($snowData)): ?>
                    <?php foreach ($snowData as $station): ?>
                        <div class="station-card">
                            <h4><?= htmlspecialchars($station['station']) ?></h4>
                            <div class="snow-grid">
                                <?php foreach ($station['data'] as $entry): ?>
                                    <?php
                                        $jour = DateTime::createFromFormat('Y-m-d', $entry['date'])->format('d/m');
                                        $cm = $entry['snow_cm'];
                                        $class = ($cm > 0) ? 'snow-positive' : 'snow-zero';
                                    ?>
                                    <div class="snow-day <?= $class ?>">
                                        <span class="day"><?= $jour ?></span>
                                        <span class="flake">❄️</span>
                                        <span class="cm"><?= $cm ?> cm</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="warning">Aucune donnée disponible pour cette station.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="right-column">
            <h4>📍 Localisation des stations</h4>
            <div id="map" style="height: 500px; width: 100%; border-radius: 12px;"></div>
            <script>
                const stations = <?= json_encode($stationsJS) ?>;
                const mapCenter = {
                    lat: <?= $center['lat'] ?>,
                    lon: <?= $center['lon'] ?>,
                    zoom: <?= $center['zoom'] ?>
                };
            </script>
            <script src="js/mountainMap.js"></script>
        </div>
    </div>
</article>
</section>




<?php include "./include/footer.inc.php"; ?>
