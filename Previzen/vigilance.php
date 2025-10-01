<?php
$title = "PreviZen";
$description = "Vigilance météo dans la France entière";
$h1 = "Vigilance";
$lang = $_GET['lang'] ?? 'fr';

include "./include/functions.inc.php";

$alerts = getVigilanceAlertsForFrance('./data/communes.csv');
require "./include/header.inc.php";
?>
<section>
<h2>Alertes météo actives en France</h2>
<article>

    <?php if (empty($alerts)): ?>
        <p>Aucune alerte météo active actuellement.</p>
    <?php else: ?>
        <div class="alertes-cards">
            <?php foreach ($alerts as $a): ?>
                <div class="alerte-card">
                    <h3><?= htmlspecialchars($a['event']) ?></h3>
                    <p class="ville">📍 Zone : <strong><?= htmlspecialchars($a['areas']) ?></strong> (source : <?= htmlspecialchars($a['source_city']) ?>)</p>
                    <p class="niveau">🟠 Niveau : <strong><?= htmlspecialchars($a['severity']) ?></strong></p>
                    <p class="periode">⏱️ Du <strong><?= date('d/m H:i', strtotime($a['effective'])) ?></strong> au <strong><?= date('d/m H:i', strtotime($a['expires'])) ?></strong></p>
                    <details>
                        <summary>ℹ️ Détails</summary>
                        <p><?= nl2br(htmlspecialchars($a['desc'])) ?></p>
                    </details>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>
<article id="alert-advice">
    <h2 class="alert-title">⚠️ Conseils en cas d’alerte</h2>
    <p class="alert-intro">En cas de vigilance <strong>orange</strong> ou <strong>rouge</strong>, adoptez les bons réflexes :</p>
    <ul class="alert-list">
        <li>Restez informé via les médias ou les canaux officiels</li>
        <li>Évitez les déplacements non essentiels</li>
        <li>Rangez ou sécurisez les objets pouvant être emportés par le vent</li>
        <li>Suivez les consignes de sécurité des autorités locales</li>
    </ul>
</article>
</section>



<?php require "./include/footer.inc.php"; ?>
