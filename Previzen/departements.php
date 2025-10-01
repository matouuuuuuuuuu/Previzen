
<?php
$title = "PreviZen";
$description = "Page d'accueil de PreviZen – prévisions météo fiables et interactives pour chaque région de France";
$h1 = "Prévision météo fiable sur 10 jours";
$lang = $_GET['lang'] ?? 'fr';

include "./include/functions.inc.php";
include "./include/header.inc.php";

$region = $_GET['region'] ?? '';
$region = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $region));
$region = str_replace(' ', '-', $region);
$ville = '';



$region_to_city = [
    'ile-de-france' => 'Paris',
    'auvergne-rhone-alpes' => 'Lyon',
    'provence-alpes-cote-d-azur' => 'Marseille',
    'bourgogne-franche-comte' => 'Dijon',
    'grand-est' => 'Strasbourg',
    'hauts-de-france' => 'Lille',
    'normandie' => 'Caen',
    'bretagne' => 'Rennes',
    'pays-de-la-loire' => 'Nantes',
    'centre-val-de-loire' => 'Orléans',
    'nouvelle-aquitaine' => 'Bordeaux',
    'occitanie' => 'Toulouse',
    'corse' => 'Ajaccio',
    'guadeloupe' => 'Basse-Terre',
    'martinique' => 'Fort-de-France',
    'guyane' => 'Cayenne',
    'la-reunion' => 'Saint-Denis',
    'mayotte' => 'Mamoudzou'
];

$regions_departements = chargerRegionsEtDepartements('./data/v_region_2024.csv', './data/v_departement_2024.csv');

if (isset($region_to_city[$region])) {
    $ville = $region_to_city[$region];
    $forecast = getNextHoursForecast($ville);
    $details = getDayDetails($ville);
}
?>

<section>
  <h2>Météo pour la région : <?= htmlspecialchars(ucwords(str_replace('-', ' ', $region))) ?></h2>

  <?php if ($ville && $forecast): ?>
    <div class="meteo-detail">
        <img src="images/<?= $forecast['image'] ?>" alt="Image météo" class="meteo-img"/>
        <div class="meteo-blocs">
            <?php foreach (['matin', 'midi', 'soir'] as $moment): ?>
                <?php if (isset($forecast['conditions'][$moment])): ?>
                    <div class="bloc">
                        <h3><?= ucfirst($moment) ?></h3>
                        <p><?= $forecast['conditions'][$moment]['condition'] ?></p>
                        <p><?= $forecast['conditions'][$moment]['t'] ?>°C</p>
                        <p>Vent <?= $forecast['conditions'][$moment]['vent'] ?> km/h</p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($details): ?>
      <details class="details-box">
          <summary class="detail-btn">Plus de détails</summary>
          <ul>
              <li>Temp. min : <?= $details['tmin'] ?>°C</li>
              <li>Temp. max : <?= $details['tmax'] ?>°C</li>
              <li>Précipitations : <?= $details['precipitation'] ?> mm</li>
              <li>Vent moyen : <?= $details['wind'] ?> km/h</li>
              <li>Rafales : <?= $details['gust'] ?> km/h</li>
          </ul>
      </details>
    <?php endif; ?>
  <?php else: ?>
    <p>❌ Région inconnue ou météo indisponible.</p>
  <?php endif; ?>
</section>

<div id="svg-container" style="position: relative;">
  <object id="carteSvg" type="image/svg+xml" data="data/carte-interactive.svg"></object>
  <div id="tooltip" style="display:none;position:absolute;background:white;padding:5px;border:1px solid #444;border-radius:4px;font-size:14px;pointer-events:none;z-index:1000;"></div>
</div>
<div id="svg-container" style="position: relative;">
  <object id="carteSvg" type="image/svg+xml" data="data/carte-interactive.svg"></object>
  <div id="tooltip" style="display:none;position:absolute;background:white;padding:5px;border:1px solid #444;border-radius:4px;font-size:14px;pointer-events:none;z-index:1000;"></div>
</div>
<?php
  $matched_region_key = null;
  foreach ($regions_departements as $cle => $deps) {
      $slug = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $cle));
      $slug = str_replace([' ', "'", '’'], ['-', '', ''], $slug);

      if ($slug === $region) {
          $matched_region_key = $cle;
          break;
      }
  }
  if ($matched_region_key): 
 ?>
  <section id="depRegion">
    <h3>Départements de la région</h3>
    <p style="text-align: center;">Voici les départements présents dans la région que vous venez de sélectionner.</p>
    <ul class="cartes-departements">
    <?php foreach ($regions_departements[$matched_region_key] as $dep): ?>
        <li class="coloredLink">
          <a href="departements.php?region=<?= urlencode($region) ?>&departement=<?= urlencode($dep['numero']) ?>#depRegion">
            <?= htmlspecialchars($dep['nom']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php
      $dep_code = $_GET['departement'] ?? null;
      $departements = $regions_departements[$matched_region_key] ?? [];
      $nom_departement = null;

      foreach ($departements as $dep) {
          if ($dep['numero'] === $dep_code) {
              $nom_departement = $dep['nom'];
              break;
          }
      }

      if ($dep_code && $nom_departement):
          $villes_du_dep = chargerNomsVillesDepuisCSVParDepartement('./data/communes.csv', $dep_code);
    ?>


  <hr style="margin: 2em 0;">
  <h3>Rechercher une ville dans le département <?= htmlspecialchars($nom_departement) ?></h3>
    <p style="text-align: center;">Sélectionnez votre ville pour obtenir les prévisions météo personnalisées.</p>
    <form method="GET" action="local.php" style="text-align: center; margin-top: 1em;">
      <input type="hidden" name="departement" value="<?= htmlspecialchars($dep_code) ?>">
      <select name="ville" required style="padding: 0.5em; width: 60%; max-width: 400px;">
        <option value="">-- Choisissez votre ville --</option>
        <?php foreach ($villes_du_dep as $ville): ?>
          <option value="<?= htmlspecialchars($ville) ?>"><?= htmlspecialchars($ville) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="submit" value="Voir la météo" style="padding: 0.5em 1em; margin-left: 0.5em; background-color: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer;">
    </form>

    <?php endif; ?>

  </section>
  <?php endif; ?>

<?php include "./include/footer.inc.php"; ?>
