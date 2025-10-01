<?php
$title = "PréviZen - Actualité météo";
$description = "Dernières actualités météo en France et dans le monde";
$h1 = "Actualité météo récente";

include "./include/functions.inc.php";
include "./include/header.inc.php";

$articles = getMeteoNews();
?>

<section class="actus-section">
    <h2><?= $h1 ?></h2>

    <?php if (!empty($articles)): ?>
        <div class="actus-cards-simple">
        <?php foreach ($articles as $article): ?>
            <div class="actus-card">
                <?php if (!empty($article['image'])): ?>
                    <img src="<?= htmlspecialchars($article['image']) ?>" alt="Illustration : <?= htmlspecialchars(mb_strimwidth($article['title'], 0, 30, '...')) ?>" class="actus-img"/>
                    <?php endif; ?>
                <div class="actus-content">
                    <h3><?= htmlspecialchars($article['title']) ?></h3>
                    <a href="<?= htmlspecialchars($article['url']) ?>" target="_blank">🔗 Lire</a>
                </div>
            </div>
        <?php endforeach; ?>

        </div>
    <?php else: ?>
        <p>Aucune actualité disponible pour le moment.</p>
    <?php endif; ?>
</section>


<?php include "./include/footer.inc.php"; ?>
