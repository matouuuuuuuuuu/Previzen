<?php
$title = "PréviZen - Qualité de l'air";
$description = "Indice de qualité de l'air (AQI), polluants et recommandations santé";
$h1 = "Qualité de l'air dans les grandes villes françaises";

include "./include/functions.inc.php";
include "./include/header.inc.php";

$villes = ["Paris", "Lyon", "Marseille", "Toulouse", "Bordeaux", "Nantes", "Lille", "Nice", "Strasbourg", "Rennes"];

$niveaux = ["Bonne", "Modérée", "Mauvaise pour les sensibles", "Mauvaise", "Très mauvaise", "Dangereuse"];
$couleurs = ["#4CAF50", "#FFEB3B", "#FF9800", "#F44336", "#9C27B0", "#000000"];
?>

<section class="container">
    <h2>Ce qu'il faut savoir à propos de la pollution atmosphérique</h2>
    <p style="text-align: center;">
        Retrouvez ci-dessous les données de <strong>qualité de l’air</strong> pour les principales villes françaises. 
        L’indice AQI (Air Quality Index) est calculé selon les normes américaines (EPA) et vous indique la <strong>qualité globale de l’air</strong> 
        de 1 (bonne) à 6 (dangereuse). Chaque carte détaille également les concentrations des principaux polluants atmosphériques (PM2.5, PM10, Ozone, NO₂, CO), 
        ainsi qu’un <strong>conseil santé</strong> adapté à la situation.
    </p>

    <?php foreach ($villes as $ville): ?>
        <?php $airData = getAirQualityData($ville); ?>
        <section style="margin-bottom: 2rem;">
            <h3><?= htmlspecialchars($ville) ?></h3>

            <?php if (!$airData): ?>
                <p>Données de qualité de l'air non disponibles pour cette ville.</p>
            <?php else:
                $aqi = $airData['aqi'];
                $niveau = $niveaux[$aqi - 1] ?? "Inconnue";
                $couleur = $couleurs[$aqi - 1] ?? "#999";
            ?>
                <div style="background:<?= $couleur ?>;color:#333;padding:10px;border-radius:8px;margin-bottom:1rem;">
                    <strong>Indice AQI :</strong> <?= $aqi ?> – <?= $niveau ?>
                </div>

                <table>
                    <thead>
                        <tr><th>Polluant</th><th>Valeur (µg/m³)</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>PM2.5</td><td><?= is_numeric($airData['PM2.5']) ? round($airData['PM2.5'], 1) : "N/A" ?></td></tr>
                        <tr><td>PM10</td><td><?= is_numeric($airData['PM10']) ? round($airData['PM10'], 1) : "N/A" ?></td></tr>
                        <tr><td>Ozone (O₃)</td><td><?= is_numeric($airData['O3']) ? round($airData['O3'], 1) : "N/A" ?></td></tr>
                        <tr><td>Dioxyde d’azote (NO₂)</td><td><?= is_numeric($airData['NO2']) ? round($airData['NO2'], 1) : "N/A" ?></td></tr>
                        <tr><td>Monoxyde de carbone (CO)</td><td><?= is_numeric($airData['CO']) ? round($airData['CO'], 1) : "N/A" ?></td></tr>
                    </tbody>
                </table>

                <h4 style="margin-top:1rem;">Conseil santé</h4>
                <p style="text-align: center;">
                    <?php
                    switch ($aqi) {
                        case 1: echo "Air de bonne qualité, aucune précaution particulière à prendre."; break;
                        case 2: echo "Qualité modérée, les personnes très sensibles peuvent ressentir une légère gêne."; break;
                        case 3: echo "Évitez les activités physiques intenses en extérieur si vous êtes sensible."; break;
                        case 4: echo "Limitez les activités extérieures, surtout pour les groupes à risque."; break;
                        case 5: echo "Très mauvaise qualité, restez à l’intérieur autant que possible."; break;
                        case 6: echo "Dangereux : évitez tout effort à l’extérieur, suivez les consignes sanitaires."; break;
                        default: echo "Informations indisponibles.";
                    }
                    ?>
                </p>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</section>

<?php include "./include/footer.inc.php"; ?>
