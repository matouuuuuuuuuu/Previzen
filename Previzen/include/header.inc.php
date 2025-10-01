<?php
$style = 'style'; // valeur par défaut
require_once __DIR__ . '/functions.inc.php';


// 1. Thème forcé par GET, utilisé même si les cookies ne sont pas autorisés
if (isset($_GET['style'])) {
    if ($_GET['style'] === 'nuit') {
        $style = 'night_style';
        if (cookiesAutorises()) {
            setcookie('theme', 'night_style', time() + 3600 * 24 * 30, "/");
        }
    } elseif ($_GET['style'] === 'jour') {
        $style = 'style';
        if (cookiesAutorises()) {
            setcookie('theme', 'style', time() + 3600 * 24 * 30, "/");
        }
    }
}
// 2. Si rien en GET, on tente de récupérer depuis le cookie (si autorisé)
elseif (cookiesAutorises() && isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['style', 'night_style'])) {
    $style = $_COOKIE['theme'];
}

// 3. Mémoriser la ville choisie si possible
if (isset($_GET['ville']) && cookiesAutorises()) {
    setcookie('ville', $_GET['ville'], time() + 3600 * 24 * 30, "/");
}

$stylePath = "./style/{$style}.css";

// Détermination du paramètre de style courant pour l'ajouter aux liens
$currentThemeParam = ($style === 'night_style') ? 'nuit' : 'jour';

// Génération du lien pour changer de style sans perdre les paramètres GET existants
$params = $_GET;
$params['style'] = $currentThemeParam === 'jour' ? 'nuit' : 'jour';
$toggleStyleUrl = basename($_SERVER['PHP_SELF']) . '?' . http_build_query($params);


$regions_departements = chargerRegionsEtDepartements('./data/v_region_2024.csv', './data/v_departement_2024.csv');
$departementActuel = $_GET['departement'] ?? null;
$villes = chargerNomsVillesDepuisCSVParDepartement('./data/communes.csv', $departementActuel);
$page = basename($_SERVER['SCRIPT_NAME']);
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="author" content="Albrun Mathis, Khelil Imène"/>
    <meta name="date" content="2025-03-24" />
    <meta name="description" content="<?php echo $description ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="./images/favicon.png"/>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" href="<?php echo $stylePath; ?>"/>

    <?php if ($page === 'statistiques.php'): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <?php endif; ?>

    <?php if (in_array($page, ['mer.php', 'neige.php'])): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.4.0/ol.css" media="print" onload="this.media='all'">
        <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.4.0/ol.css"></noscript>
        <script src="https://cdn.jsdelivr.net/npm/ol@v7.4.0/dist/ol.js" defer></script>
    <?php endif; ?>
</head>

<body id="top">
 

<header>
    <a href="./index.php">
        <img src="./images/logoProjet.webp" alt="Logo du site" width="400" height="160" style="margin-left: 90px;">
    </a>

    <nav>
        <ul class="menu">
            <li><a href="./local.php"><img src="<?php echo getIcon('local'); ?>" alt="Icone localisation" class="nav-icon">Météo locale</a></li>
            <li><a href="./mer.php"><img src="<?php echo getIcon('plage'); ?>" alt="Icone palmier" class="nav-icon">Météo des plages</a>
                <ul class="submenu">
                    <li><a href="mer.php?zone=manche#infos-ville-cotiere">Manche</a></li>                       
                    <li><a href="mer.php?zone=atlantique#infos-ville-cotiere">Côte Atlantique</a></li>                        
                    <li><a href="mer.php?zone=mediterranee#infos-ville-cotiere">Méditerranée</a></li>                    
                </ul>
            </li>
            <li><a href="./neige.php"><img src="<?php echo getIcon('montagne'); ?>" alt="Montagne" class="nav-icon">Météo des neiges</a>
                <ul class="submenu">
                    <li><a href="./neige.php?massif=jura#intro">Jura</a></li>
                    <li><a href="./neige.php?massif=vosges#intro">Vosges</a></li>
                    <li><a href="./neige.php?massif=alpes#intro">Alpes Française</a></li>
                    <li><a href="./neige.php?massif=massif-central#intro">Massif Central</a></li>
                    <li><a href="./neige.php?massif=pyrenees#intro">Pyrénées</a></li>
                </ul>
            </li>
            <li><a href="./air.php"><img src="<?php echo getIcon('pollution'); ?>" alt="Icon Echappement" class="nav-icon">Pollutions</a></li>
        </ul>
    </nav>

    <input type="checkbox" id="sidebar-toggle" hidden>
    <label for="sidebar-toggle" class="sidebar-button" aria-label="Ouvrir le menu">☰ Menu</label>
        <aside class="sidebar">
        <span class="close-button" onclick="document.getElementById('sidebar-toggle').checked = false" role="button" aria-label="Fermer le menu">&times;</span>        
        <h2 style='color: #fff;'>Menu</h2>
        <ul>
            <li><a href="./index.php">🏠 Accueil</a></li>
            <li><a href="./vigilance.php">⚠️ Vigilance</a></li>
            <li><a href="./actus.php">📰 Actus & Dossiers</a></li>
            <li><a href="./statistiques.php">📊 Statistiques</a></li>
			<li><a href="#" onclick="openDressAdvice()">👕 Quelques conseils vestimentaires</a></li>
            <li><a href="./plan.php">🗺️ Plan du site</a></li>

        </ul>
    </aside>

    <div class="header-icons">
    <form method="get" style="display: inline;">
        <?php
        foreach ($_GET as $key => $value) {
            if ($key !== 'style') {
                echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
            }
        }
        ?>
        <input type="hidden" name="style" value="<?= ($style === 'style') ? 'nuit' : 'jour'; ?>">
        <button type="submit" aria-label="Changer le thème">
            <?= ($style === 'style') ? '🌙 Activer Mode Nuit' : '☀️ Activer Mode Jour'; ?>
        </button>
    </form>
    </div>
</header>

<div id="dressModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeDressAdvice()">&times;</span>
        <h3>👕 Conseil vestimentaire</h3>
        <p id="dress-advice-text">Chargement en cours...</p>
    </div>
</div>


<main>

<div class="city-selector-bar">
    <form method="get" action="local.php">
        <label for="region" style="color:#fff;">Choisissez une région</label>
        <select name="region" id="region" onchange="this.form.submit()">
            <option value="">Région</option>
            <?php foreach ($regions_departements as $nomRegion => $departements): ?>
                <option value="<?= $nomRegion ?>" <?= (isset($_GET['region']) && $_GET['region'] === $nomRegion) ? 'selected' : '' ?>>
                    <?= $nomRegion ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (isset($_GET['region'], $regions_departements[$_GET['region']])): ?>
            <label for="departement" class="visually-hidden">Choisissez un département</label>
            <select name="departement" id="departement" onchange="this.form.submit()">
                <option value="">Département</option>
                <?php foreach ($regions_departements[$_GET['region']] as $dep): ?>
                    <option value="<?= $dep['numero'] ?>" <?= (isset($_GET['departement']) && $_GET['departement'] === $dep['numero']) ? 'selected' : '' ?>>
                        <?= $dep['nom'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($departementActuel): ?>
            <label for="ville" class="visually-hidden">Choisissez une ville</label>
            <select name="ville" id="ville">
                <option value="">Ville</option>
                <?php foreach ($villes as $ville): ?>
                    <option value="<?= htmlspecialchars($ville) ?>" <?= ($_GET['ville'] ?? ($_COOKIE['ville'] ?? '')) === $ville ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ville) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Ajouter +</button>
        <?php endif; ?>

    </form>
    </div>

    <h1><?= $h1 ?></h1>
