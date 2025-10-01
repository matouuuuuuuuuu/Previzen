<?php
$title = "PreviZen";
$description = "PreviZen Homepage – reliable and interactive weather forecasts for each region of France";
$h1 = "Reliable 10-Day Weather Forecast";
$lang = $_GET['lang'] ?? 'en';

include "./include/functions.inc.php";

$ip = getClientIP();
$geo = getCityAndCPFromIP($ip);

$villeClient = $geo['ville'] ?? 'Paris';
echo "<!-- IP: $ip -->";
echo "<!-- Detected city: $villeClient -->";

$weatherData = getTodayWeatherData($villeClient);
$forecast = getNextHoursForecast($villeClient);
$dayDetails = getDayDetails($villeClient);
$regions_departements = chargerRegionsEtDepartements('./data/v_region_2024.csv', './data/v_departement_2024.csv');

include "./include/header.inc.php";
?>

<section>
    <h2>Welcome to PreviZen</h2>
    <p>Check detailed 10-day weather forecasts for each region of France.</p>

    <?php if ($forecast): ?>
        <p><strong>Detected city:</strong> <?= htmlspecialchars($villeClient) ?></p>

        <div class="meteo-detail">
            <img src="images/<?= $forecast['image'] ?>" alt="Weather image" class="meteo-img">
            <div class="meteo-blocs">
                <?php foreach (['matin', 'midi', 'soir'] as $moment): ?>
                    <?php if (isset($forecast['conditions'][$moment])): ?>
                        <div class="bloc">
                            <h4><?= ucfirst($moment) ?></h4>
                            <p><?= $forecast['conditions'][$moment]['condition'] ?></p>
                            <p><?= $forecast['conditions'][$moment]['t'] ?>°C</p>
                            <p>Wind <?= $forecast['conditions'][$moment]['vent'] ?> km/h</p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <p>Weather data currently unavailable.</p>
    <?php endif; ?>

    <?php if ($dayDetails): ?>
        <details class="details-box">
            <summary class="detail-btn">More details</summary>
            <ul>
                <li>Min. temp: <?= $dayDetails['tmin'] ?>°C</li>
                <li>Max. temp: <?= $dayDetails['tmax'] ?>°C</li>
                <li>Precipitations: <?= $dayDetails['precipitation'] ?> mm</li>
                <li>Average wind: <?= $dayDetails['wind'] ?> km/h</li>
                <li>Gusts: <?= $dayDetails['gust'] ?> km/h</li>
            </ul>
        </details>
    <?php endif; ?>
</section>

<section>
    <h2>Manual Weather Selection</h2>
    <form method="get">
        <label for="region">Region:</label>
        <select name="region" id="region" onchange="this.form.submit()">
            <option value="">-- Select a region --</option>
            <?php foreach ($regions_departements as $regionName => $departements): ?>
                <option value="<?= $regionName ?>" <?= isset($_GET['region']) && $_GET['region'] === $regionName ? 'selected' : '' ?>>
                    <?= $regionName ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (isset($_GET['region'], $regions_departements[$_GET['region']])): ?>
            <br><br>
            <label for="departement">Department:</label>
            <select name="departement" id="departement" onchange="this.form.submit()">
                <option value="">-- Select a department --</option>
                <?php foreach ($regions_departements[$_GET['region']] as $dep): ?>
                    <option value="<?= $dep['numero'] ?>" <?= (isset($_GET['departement']) && $_GET['departement'] === $dep['numero']) ? 'selected' : '' ?>>
                        <?= $dep['nom'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if (isset($_GET['departement'])): ?>
            <br><br>
            <label for="ville">City:</label>
            <input type="text" name="ville" id="ville" placeholder="Enter a city" required>
            <button type="submit">Get Weather</button>
        <?php endif; ?>
    </form>

    <?php if (isset($_GET['ville']) && !empty($_GET['ville'])):
        $villeManuelle = trim($_GET['ville']);
        $meteoManuelle = getTodayWeatherData($villeManuelle);
        $forecastManuelle = getNextHoursForecast($villeManuelle);
        $detailsManuelle = getDayDetails($villeManuelle);
    ?>

    <section>
        <h2>Weather for <?= htmlspecialchars($villeManuelle) ?></h2>

        <?php if ($forecastManuelle): ?>
            <div class="meteo-detail">
                <img src="images/<?= $forecastManuelle['image'] ?>" alt="Weather image" class="meteo-img">
                <div class="meteo-blocs">
                    <?php foreach (['matin', 'midi', 'soir'] as $moment): ?>
                        <?php if (isset($forecastManuelle['conditions'][$moment])): ?>
                            <div class="bloc">
                                <h4><?= ucfirst($moment) ?></h4>
                                <p><?= $forecastManuelle['conditions'][$moment]['condition'] ?></p>
                                <p><?= $forecastManuelle['conditions'][$moment]['t'] ?>°C</p>
                                <p>Wind <?= $forecastManuelle['conditions'][$moment]['vent'] ?> km/h</p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Weather data not available for this city.</p>
        <?php endif; ?>

        <?php if ($detailsManuelle): ?>
            <details class="details-box">
                <summary class="detail-btn">More details</summary>
                <ul>
                    <li>Min. temp: <?= $detailsManuelle['tmin'] ?>°C</li>
                    <li>Max. temp: <?= $detailsManuelle['tmax'] ?>°C</li>
                    <li>Precipitations: <?= $detailsManuelle['precipitation'] ?> mm</li>
                    <li>Average wind: <?= $detailsManuelle['wind'] ?> km/h</li>
                    <li>Gusts: <?= $detailsManuelle['gust'] ?> km/h</li>
                </ul>
            </details>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</section>
