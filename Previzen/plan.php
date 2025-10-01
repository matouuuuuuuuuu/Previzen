<?php
$title = "Plan du site";
$description = "Plan du site PreviZen - retrouvez toutes les pages accessibles depuis un seul endroit.";
$h1 = "Plan du site";
include "./include/header.inc.php";
?>

<section class="plan-site">
    <h2>Navigation principale</h2>
    <ul>
        <li><a href="index.php">🏠 Accueil</a></li>
        <li><a href="local.php">📍 Météo locale</a></li>
        <li><a href="mer.php">🏖️ Météo des plages</a></li>
        <li><a href="neige.php">🏔️ Météo des neiges</a></li>
        <li><a href="air.php">🌫️ Pollutions</a></li>
        <li><a href="vigilance.php">⚠️ Vigilance</a></li>
        <li><a href="actus.php">📰 Actus & dossiers</a></li>
        <li><a href="statistiques.php">📊 Statistiques</a></li>
        <li><a href="advice.php">👕 Conseils vestimentaires</a></li>
        <li><a href="tech.php">🛠️ Page technique</a></li>
    </ul>

    <h2>Autres ressources</h2>
    <ul>
        <li><a href="stats_data.php">📈 Données statistiques</a></li>
        <li><a href="stats.csv" download>📂 Télécharger les statistiques (CSV)</a></li>
        <li><a href="./data/sujet_projet.pdf" target="_blank">📄 Sujet de projet (PDF)</a></li>
    </ul>
</section>

<?php
include "./include/footer.inc.php";
?>
