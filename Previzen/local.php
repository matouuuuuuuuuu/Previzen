<?php 
$title = "PreviZen";
$description = "La météo locale disponible en un clic";
$h1 = "Prévision météo dans votre secteur sur une semaine";
$lang = $_GET['lang'] ?? 'fr';
setlocale(LC_TIME, 'fr_FR.UTF-8');
include "./include/functions.inc.php";

$villeClient = $_GET['ville'] ?? null;

if (!$villeClient) {
    $ip = getClientIP();
    $geo = getCityFromIPInfo($ip);
    $villeClient = $geo['ville'] ?? 'Paris';
}

setDerniereConsultation($villeClient);


$villeClient = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $villeClient);

$selectedDay = isset($_GET['jour']) ? intval($_GET['jour']) : 0;

$weatherData = getTodayWeatherData($villeClient);
$dayDetails = getDayDetails($villeClient);
$forecast = getNextHoursForecast($villeClient);
$weekForecast = getNextDaysForecast($villeClient);
$selectedForecast = $weekForecast[$selectedDay] ?? $weekForecast[0];

include "./include/header.inc.php";
?>

<section class="meteo-local">
    <h2>Prévision météo à <?= htmlspecialchars($villeClient) ?></h2>
    <p>Consultez les prévisions météo détaillées à 7 jours pour chaque région de France.</p>

    <?php if ($weatherData): ?>
        <div class="meteo-principale">
            <div class="temperature">
                <span class="temp-val"><?= $selectedForecast['tmax'] ?>°</span>
                <span class="temp-ressenti">Ressenti <?= $selectedForecast['tmin'] ?>°</span>
            </div>
            <div class="meteo-condition">
                <img src="<?= $selectedForecast['icon'] ?>" alt="Condition météo"/>
                <span><?= htmlspecialchars($selectedForecast['condition'] ?? '') ?></span>
            </div>
            <div class="vent">
                Vent : <?= $selectedForecast['wind'] ?> km/h
            </div>
        </div>

    <?php endif; ?>

    <?php if ($forecast): ?>
        <div class="previsions-heures">
            <?php foreach (['matin', 'midi', 'soir'] as $moment): ?>
                <?php if (isset($forecast['conditions'][$moment])): ?>
                    <div class="carte-moment">
                        <h3><?= ucfirst($moment) ?></h3>
                        <p><?= $forecast['conditions'][$moment]['t'] ?>°C</p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        
    
        <?php if ($weekForecast): ?>
            <div class="week-forecast">
                <?php foreach ($weekForecast as $index => $day): ?>
                    <a href="?ville=<?= urlencode($villeClient) ?>&jour=<?= $index ?>" class="forecast-day<?= $index === $selectedDay ? ' active' : '' ?>">                        <div class="day-label"><?= $day['day'] ?></div>
                        <img src="<?= $day['icon'] ?>" alt="Météo <?= htmlspecialchars($day['date']) ?>" />           
                         <div class="temps">
                            <span class="tmin"><?= $day['tmin'] ?>°</span> /
                            <span class="tmax"><?= $day['tmax'] ?>°</span>
                        </div>
                        <div class="vent"><?= $day['wind'] ?> km/h</div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


    
    
    
        <?php else: ?>
        <p>Météo indisponible pour le moment.</p>
    <?php endif; ?>

    <?php if ($dayDetails): ?>
        <details class="details-box">
            <summary class="detail-btn">Plus de détails</summary>
            <ul>
                <li>Temp. min : <?= $dayDetails['tmin'] ?>°C</li>
                <li>Temp. max : <?= $dayDetails['tmax'] ?>°C</li>
                <li>Précipitations : <?= $dayDetails['precipitation'] ?> mm</li>
                <li>Vent moyen : <?= $dayDetails['wind'] ?> km/h</li>
                <li>Rafales : <?= $dayDetails['gust'] ?> km/h</li>
            </ul>
        </details>
    <?php endif; ?>
</section>

<?php
require "./include/footer.inc.php";
?>
