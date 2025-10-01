<?php
    $title = "PreviZen";
    $description = "Page d'accueil de PreviZen – prévisions météo fiables et interactives pour chaque région de France";
    $h1 = "Prévision météo fiable sur 7 jours";
    $lang = $_GET['lang'] ?? 'fr';

    include "./include/functions.inc.php";

    $ip = getClientIP();
    $geo = getCityFromIPInfo($ip) ?? ['ville' => 'Paris', 'cp' => null];
    $villeClient = $geo['ville'];
    $villeClient = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $villeClient);

    $codePostal = $geo['cp'];

    file_put_contents('stats.csv', "$villeClient," . date('Y-m-d') . "\n", FILE_APPEND);
    setcookie("last_city", $villeClient, time() + (86400 * 30), "/");


    
    $weatherData = getTodayWeatherData($villeClient);
    $forecast = getNextHoursForecast($villeClient);
    $dayDetails = getDayDetails($villeClient);
    $regions_departements = chargerRegionsEtDepartements('./data/v_region_2024.csv', './data/v_departement_2024.csv');

    include "./include/header.inc.php";
?>




<section>

    <article>
        <h2>Bienvenue sur PreviZen</h2>
        <p style="text-align: center;">
            Consultez les prévisions météo détaillées à 7 jours pour chaque région de France.
        </p>

        <?php if ($forecast): ?>
            <p style="text-align: center;"><strong>Ville détectée :</strong> <?= htmlspecialchars($villeClient) ?></p>

            <div class="meteo-detail">
                <img src="images/<?= $forecast['image'] ?>" alt="Image météo" class="meteo-img">
                <div class="meteo-blocs">
                    <?php foreach (['matin', 'midi', 'soir'] as $moment): ?>
                        <?php if (isset($forecast['conditions'][$moment])): ?>
                            <div class="bloc">
                                <h3><?= ucfirst($moment) ?></h4>
                                <p><?= $forecast['conditions'][$moment]['condition'] ?></p>
                                <p><?= $forecast['conditions'][$moment]['t'] ?>°C</p>
                                <p>Vent <?= $forecast['conditions'][$moment]['vent'] ?> km/h</p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Météo indisponible pour le moment.</p>
        <?php endif; ?>

        <?php if ($dayDetails): ?>
            <details class="details-box">
                <summary class="detail-btn">Plus de détails</summary>
                <ul>
                    <li>Temp. minimale : <?= $dayDetails['tmin'] ?>°C</li>
                    <li>Temp. maximale : <?= $dayDetails['tmax'] ?>°C</li>
                    <li>Précipitations : <?= $dayDetails['precipitation'] ?> mm</li>
                    <li>Vent moyen : <?= $dayDetails['wind'] ?> km/h</li>
                    <li>Rafales : <?= $dayDetails['gust'] ?> km/h</li>
                </ul>
            </details>
        <?php endif; ?>
    </article>

	<article>
	<h2>Choix de la météo régionale via la carte interactive</h2>
	<?php include "./include/carte-interactive.inc.php"; ?>
	</article>

	<!-- Pour survoler les departements et avoir leurs noms -->
	<div id="tooltip" class="tooltip"></div>

	<style>
	.tooltip {
	position: absolute;
	background-color: rgba(0, 0, 0, 0.85);
	color: white;
	padding: 4px 8px;
	font-size: 13px;
	border-radius: 4px;
	pointer-events: none;
	white-space: nowrap;
	display: none;
	z-index: 1000;
	}
	</style>

	<script>
	document.addEventListener('DOMContentLoaded', () => {
	const tooltip = document.getElementById('tooltip');

	document.querySelectorAll('path[data-nom]').forEach(path => {
		path.addEventListener('mousemove', e => {
		const nom = path.getAttribute('data-nom');
		const num = path.getAttribute('data-numerodepartement') || '';
		tooltip.innerText = `${num} – ${nom}`;
		tooltip.style.left = (e.pageX + 10) + 'px';
		tooltip.style.top = (e.pageY - 20) + 'px';
		tooltip.style.display = 'block';
		});

		path.addEventListener('mouseleave', () => {
		tooltip.style.display = 'none';
		});
	});
	});
	</script>


    <article class="cards-section">
        <h3>🌤️ À propos de PreviZen</h3>
        <p style="text-align: center;">Votre assistant météo fiable et accessible. Profitez de prévisions personnalisées pour chaque ville de France, sans publicité ni géolocalisation forcée.</p>
        

        <div class="card">
            <h4>📊 Statistiques en temps réel</h4>
            <ul>
                <li><strong>+1200</strong> villes analysées depuis le lancement</li>
                <li>Météo actualisée <strong>toutes les 30 minutes</strong></li>
                <li>Dernière consultation : <strong><?= htmlspecialchars($villeClient) ?></strong></li>
            </ul>
        </div>

        <div class="card">
            <h4>✅ Nos engagements</h4>
            <ul>
                <li>Données issues de <strong>WeatherAPI</strong></li>
                <li>Respect complet de la vie privée</li>
                <li>Optimisé pour tous les écrans</li>
            </ul>
        </div>
    </article>
</section>

<?php
$derniere = getDerniereConsultation();
if ($derniere): ?>
    <div class="info-consultation">
        📌 Dernière ville consultée : <strong><?= htmlspecialchars($derniere['ville']) ?></strong><br>
        📅 le <em><?= htmlspecialchars($derniere['date']) ?></em>
    </div>
<?php endif; ?>

<? include "./include/footer.inc.php";?>
