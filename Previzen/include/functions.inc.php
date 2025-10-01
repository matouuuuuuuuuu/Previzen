<?php

include  __DIR__ . '/utile.inc.php';
include  __DIR__ . '/config.inc.php';

/**
 * Récupère les données APOD (Astronomy Picture of the Day) depuis l'API de la NASA.
 *
 * @param string $api_key Clé API NASA.
 * @param string $date    Date au format YYYY-MM-DD.
 *
 * @return array|null     Données APOD sous forme de tableau associatif ou null en cas d'erreur.
 */

function get_apod_data(string $api_key, string $date): ?array {
    $url = "https://api.nasa.gov/planetary/apod?api_key=$api_key&date=$date&thumbs=true";
    $response = @file_get_contents($url);
    return $response ? json_decode($response, true) : null;
}

/**
 * Génère le code HTML permettant d’afficher l’image ou la vidéo APOD du jour avec explication.
 *
 * @param string $api_key Clé API NASA.
 * @param string $date    Date au format YYYY-MM-DD.
 *
 * @return string Code HTML à insérer dans la page.
 */

function get_apod_html(string $api_key, string $date): string {
    $url = "https://api.nasa.gov/planetary/apod?api_key=$api_key&date=$date&thumbs=true";
    $response = @file_get_contents($url);
    
    $data = $response ? json_decode($response, true) : null;

    if (!$data) return "<p>Impossible de récupérer les données de la NASA.</p>";

    $html = "";
    
    

    if ($data['media_type'] === 'image') {
        $html .= '<div class="image-container"><img src="' . htmlspecialchars($data['url']) . '" width="400" alt="APOD"/></div>';
    } elseif ($data['media_type'] === 'video') {
        $html .= "<iframe width=\"560\" height=\"315\" src=\"" . htmlspecialchars($data['url']) . "\" frameborder=\"0\" allowfullscreen></iframe>";
    }

    $html .= "<p>" . nl2br(htmlspecialchars($data['explanation'])) . "</p>";

    return $html;
}


/**
 * Génère un bloc HTML contenant les informations de géolocalisation d'une IP via GeoPlugin.
 *
 * @param string $ip Adresse IP à localiser.
 *
 * @return string Code HTML contenant les informations géographiques (ville, pays, continent...).
 */

function get_geoplugin_html(string $ip): string {
    $xml = @simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=$ip");
    if (!$xml) return "<p>Impossible de récupérer les données de GeoPlugin.</p>";

    $html = "<ul>";
    $html .= "<li><strong>IP :</strong> " . htmlspecialchars($ip) . "</li>";
    $html .= "<li><strong>Ville :</strong> " . htmlspecialchars((string)($xml->geoplugin_city ?? 'N/A')) . "</li>";
    $html .= "<li><strong>Région :</strong> " . htmlspecialchars((string)($xml->geoplugin_region ?? 'N/A')) . "</li>";
    $html .= "<li><strong>Pays :</strong> " . htmlspecialchars((string)($xml->geoplugin_countryName ?? 'N/A')) . "</li>";
    $html .= "<li><strong>Continent :</strong> " . htmlspecialchars((string)($xml->geoplugin_continentName ?? 'N/A')) . "</li>";
    $html .= "</ul>";

    return $html;
}

/**
 * Interroge l'API WhatIsMyIP pour obtenir des informations détaillées sur une IP, et les affiche en HTML.
 *
 * @param string $ip  Adresse IP à analyser.
 * @param string $key Clé API WhatIsMyIP.
 *
 * @return string Code HTML listant les informations de géolocalisation et du fournisseur.
 */

function get_whatismyip_html(string $ip, string $key): string {
    $url = "https://api.whatismyip.com/ip-address-lookup.php?key=$key&input=$ip";
    $response = @file_get_contents($url);

    if (!$response) {
        return "<p>Impossible de contacter l’API WhatIsMyIP.</p>";
    }

    $lines = preg_split("/\r\n|\n|\r/", trim($response)); // Gère tous les formats de retour
    $data = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        $parts = explode(':', $line, 2);
        if (count($parts) == 2) {
            $keyName = strtolower(trim($parts[0])); // en minuscules par sécurité
            $value = trim($parts[1]);
            $data[$keyName] = $value;
        }
    }

    $html = "<ul>";
    $html .= "<li><strong>IP :</strong> " . htmlspecialchars($data['ip'] ?? 'N/A') . "</li>";
    $html .= "<li><strong>Ville :</strong> " . htmlspecialchars($data['city'] ?? 'N/A') . "</li>";
    $html .= "<li><strong>Région :</strong> " . htmlspecialchars($data['region'] ?? 'N/A') . "</li>";
    $html .= "<li><strong>Pays :</strong> " . htmlspecialchars($data['country'] ?? 'N/A') . "</li>";
    $html .= "<li><strong>Code postal :</strong> " . htmlspecialchars($data['postalcode'] ?? 'N/A') . "</li>";
    $html .= "<li><strong>Fournisseur :</strong> " . htmlspecialchars($data['isp'] ?? 'N/A') . "</li>";
    $html .= "<li><strong>Latitude :</strong> " . htmlspecialchars($data['latitude'] ?? 'N/A') . "</li>";
    $html .= "<li><strong>Longitude :</strong> " . htmlspecialchars($data['longitude'] ?? 'N/A') . "</li>";
    $html .= "</ul>";

    return $html;
}

/**
 * Vérifie si l'utilisateur a autorisé les cookies via la bannière.
 *
 * @return bool true si les cookies sont autorisés ou pas encore définis, false sinon.
 */

function cookiesAutorises(): bool {
    return !isset($_COOKIE['cookiesAccepted']) || $_COOKIE['cookiesAccepted'] === 'yes';
}

/**
 * Détermine le thème actif en fonction des paramètres GET ou des cookies utilisateur.
 *
 * @return string 'day' ou 'night' selon le thème choisi.
 */

function getTheme(): string {
    if (isset($_GET['style'])) {
        if ($_GET['style'] === 'nuit') return 'night';
        if ($_GET['style'] === 'jour') return 'day';
    }

    if (cookiesAutorises() && isset($_COOKIE['theme'])) {
        return ($_COOKIE['theme'] === 'night_style') ? 'night' : 'day';
    }

    return 'day';
}

/**
 * Génère le chemin d’accès à une icône en fonction du thème actif.
 *
 * @param string $basename Nom de base de l’icône (sans extension ni suffixe de thème).
 *
 * @return string Chemin relatif vers l’image PNG à afficher.
 */

function getIcon($basename) {
    return "/images/{$basename}-" . getTheme() . ".png";
}

function setDerniereConsultation($ville) {
    $data = [
        'ville' => $ville,
        'date' => date('Y-m-d H:i:s')
    ];
    setcookie('derniere_consultation', json_encode($data), time() + (30 * 24 * 60 * 60), "/");
}

function getDerniereConsultation() {
    if (isset($_COOKIE['derniere_consultation'])) {
        return json_decode($_COOKIE['derniere_consultation'], true);
    }
    return null;
}


/**
 * Incrémente et retourne le compteur de visites stocké dans un fichier texte.
 *
 * @param string $fichier Chemin vers le fichier compteur (par défaut './data/compteur.txt').
 *
 * @return int Nombre total de visites après incrémentation.
 */

function compter_visites(string $fichier = './data/compteur.txt'): int {
    if (!file_exists($fichier)) {
        file_put_contents($fichier, 0);
    }

    $visites = (int) file_get_contents($fichier);
    $visites++;
    file_put_contents($fichier, $visites);

    return $visites;
}

/**
 * Effectue un appel à l’API WeatherAPI avec les bons paramètres et la clé dynamique.
 *
 * @param string $endpoint     Endpoint de l’API à appeler (ex. 'current.json', 'forecast.json').
 * @param string $query        Requête de localisation (ville, code postal...).
 * @param array  $extraParams  Paramètres supplémentaires à ajouter à la requête.
 *
 * @return array|null Données de réponse de l’API ou null en cas d’échec.
 */

function callWeatherAPI(string $endpoint, string $query, array $extraParams = []): ?array {
    $base = "http://api.weatherapi.com/v1/";
    
    // ⏳ Sélection dynamique de la bonne clé
    $start = new DateTime(WEATHERAPI_PRO_START);
    $today = new DateTime();
    $interval = $start->diff($today)->days;
    $key = ($interval < 13) ? WEATHERAPI_KEY_PRO1 : WEATHERAPI_KEY_PRO2;

    $params = array_merge([
        'key' => $key,
        'q' => $query,
        'lang' => 'fr'
    ], $extraParams);

    if ($endpoint === "forecast.json" && !isset($params['days'])) {
        $params['days'] = 7;
    }

    $url = $base . $endpoint . '?' . http_build_query($params);

    $response = @file_get_contents($url);
    return $response ? json_decode($response, true) : null;
}


/**
 * Récupère les données météo actuelles pour une ville donnée.
 *
 * @param string $ville Nom de la ville.
 *
 * @return array|null Données météo actuelles ou null si l'appel échoue.
 */

function getTodayWeatherData($ville) {
    $data = callWeatherAPI("current.json", $ville);
    if (!$data) return null;

    return [
        'ville' => $data['location']['name'],
        'cp' => $data['location']['tz_id'], // Pas de CP direct, fallback
        'condition' => $data['current']['condition']['text'],
        'tmin' => $data['current']['temp_c'],
        'tmax' => $data['current']['temp_c'],
        'vent' => $data['current']['wind_kph']
    ];
}

/**
 * Récupère la météo prévue pour les prochaines heures (matin, midi, soir) dans une ville donnée.
 *
 * @param string $ville Nom de la ville.
 * @param int    $jour  Index du jour (0 = aujourd’hui, 1 = demain, ...).
 *
 * @return array|null Données météo organisées par moment de la journée, ou null si indisponible.
 */

function getNextHoursForecast($ville, $jour = 0) {
    $data = callWeatherAPI("forecast.json", $ville);
    if (!$data || !isset($data['forecast']['forecastday'][$jour]['hour'])) return null;

    $hours = $data['forecast']['forecastday'][$jour]['hour'];
    $moments = [8 => 'matin', 12 => 'midi', 18 => 'soir'];

    $result = [
        'ville' => $data['location']['name'],
        'cp' => $data['location']['tz_id'],
        'conditions' => []
    ];

    foreach ($moments as $hour => $moment) {
        if (!isset($hours[$hour])) continue;
        $f = $hours[$hour];
        $result['conditions'][$moment] = [
            'condition' => $f['condition']['text'],
            't' => $f['temp_c'],
            'vent' => $f['wind_kph']
        ];
    }

    $imageLabel = $result['conditions']['midi']['condition']
        ?? $result['conditions']['matin']['condition']
        ?? '';
    $result['image'] = getWeatherImage($imageLabel);

    return $result;
}

/**
 * Retourne le nom de fichier d'une image météo en fonction du libellé météo fourni.
 *
 * @param string $label Libellé météo (ex. : "pluie", "ensoleillé", "nuageux"...).
 *
 * @return string Nom du fichier image correspondant (ex. : 'pluie.png', 'soleil.png', etc.).
 */

function getWeatherImage($label) {
    $label = strtolower($label);
    if (str_contains($label, 'pluie')) return 'pluie.png';
    if (str_contains($label, 'nuage')) return 'nuage.png';
    if (str_contains($label, 'soleil') || str_contains($label, 'ensoleillé') || str_contains($label, 'dégagé')) return 'soleil.png';
    return 'inconnu.png';
}

/**
 * Récupère les détails météo quotidiens pour aujourd’hui dans une ville donnée.
 *
 * @param string $ville Nom de la ville pour laquelle obtenir les prévisions.
 *
 * @return array|null Tableau associatif contenant la date, le temps, les températures,
 *                    les précipitations, le vent et les rafales, ou null si indisponible.
 */

function getDayDetails($ville) {
    $data = callWeatherAPI("forecast.json", $ville);
    if (!$data || !isset($data['forecast']['forecastday'][0]['day'])) return null;

    $day = $data['forecast']['forecastday'][0]['day'];
    return [
        'date' => $data['forecast']['forecastday'][0]['date'],
        'weather' => $day['condition']['text'],
        'tmin' => $day['mintemp_c'],
        'tmax' => $day['maxtemp_c'],
        'precipitation' => $day['totalprecip_mm'],
        'wind' => $day['maxwind_kph'],
        'gust' => $day['maxwind_kph']
    ];
}

/**
 * Récupère l'adresse IP du client courant.
 *
 * @return string Adresse IP du client, ou '127.0.0.1' par défaut si indisponible.
 */

function getClientIP() {
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Récupère la ville et le code postal associés à une adresse IP via l'API IPInfo.
 *
 * @param string $ip Adresse IP à géolocaliser.
 *
 * @return array|null Tableau associatif contenant les clés 'ville' et 'cp', ou null en cas d'échec.
 */

function getCityFromIPInfo(string $ip): ?array {
    $token = IPINFO_TOKEN; // définie dans config.inc.php
    $url = "http://ipinfo.io/{$ip}/json?token={$token}";
    $response = @file_get_contents($url);
    $data = $response ? json_decode($response, true) : null;

    if (!$data) return null;

    return [
        'ville' => $data['city'] ?? null,
        'cp'    => $data['postal'] ?? null
    ];
}

/**
 * Récupère les informations de localisation (ville, département, région) à partir d’une IP via GeoPlugin.
 *
 * @param string $ip Adresse IP à localiser.
 *
 * @return array Tableau associatif contenant 'ville', 'departement' et 'region', ou null pour les valeurs manquantes.
 */

function getCityFromIP($ip) {
    $url = "http://www.geoplugin.net/json.gp?ip=" . $ip;
    $data = json_decode(file_get_contents($url), true);

    return [
        'ville' => $data['geoplugin_city'] ?? null,
        'departement' => $data['geoplugin_region'] ?? null,       
        'region' => $data['geoplugin_regionName'] ?? null        
    ];
}

/**
 * Charge les régions et leurs départements à partir de deux fichiers CSV.
 *
 * @param string $fichier_regions      Chemin vers le fichier CSV contenant les régions (avec code et nom).
 * @param string $fichier_departements Chemin vers le fichier CSV contenant les départements (avec code et code région).
 *
 * @return array Tableau associatif structuré par nom de région, contenant les départements avec leur numéro et nom.
 *
 * @example
 * [
 *   "Île-de-France" => [
 *     ["numero" => "75", "nom" => "Paris"],
 *     ["numero" => "77", "nom" => "Seine-et-Marne"],
 *     ...
 *   ],
 *   ...
 * ]
 */

function chargerRegionsEtDepartements($fichier_regions, $fichier_departements) {
    $codes_regions = [];
    if (!file_exists($fichier_regions)) {
        echo "Erreur : fichier $fichier_regions introuvable.<br>";
        return [];
    }
    $r = fopen($fichier_regions, "r");
    fgetcsv($r); 
    while ($ligne = fgetcsv($r)) {
        $codes_regions[$ligne[0]] = $ligne[5]; 
    }
    fclose($r);
    $resultat = [];
    if (!file_exists($fichier_departements)) {
        echo "Erreur : fichier $fichier_departements introuvable.<br>";
        return [];
    }
    $d = fopen($fichier_departements, "r");
    fgetcsv($d); 
    while ($ligne = fgetcsv($d)) {
        $code_reg = $ligne[1];
        $code_dep = $ligne[0];
        $nom_dep  = $ligne[5];

        if (isset($codes_regions[$code_reg])) {
            $nom_reg = $codes_regions[$code_reg];
            $resultat[$nom_reg][] = [
                'numero' => $code_dep,
                'nom' => $nom_dep
            ];
        }
    }
    fclose($d);

    return $resultat;
}

/**
 * Génère une version simplifiée des régions avec une liste de leurs départements, indexée par un slug.
 *
 * @param string $fichier_regions      Chemin vers le fichier CSV des régions.
 * @param string $fichier_departements Chemin vers le fichier CSV des départements.
 *
 * @return array Tableau associatif où chaque clé est un slug de nom de région, et la valeur un tableau des codes départements.
 *
 * @example
 * [
 *   "ile-de-france" => ["75", "77", "78", "91", "92", "93", "94", "95"],
 *   "bretagne" => ["22", "29", "35", "56"],
 *   ...
 * ]
 */

function getRegionsDepartementsSimplifie($fichier_regions, $fichier_departements): array {
    $regions_completes = chargerRegionsEtDepartements($fichier_regions, $fichier_departements);
    $resultat = [];

    foreach ($regions_completes as $nom_region => $departements) {
        $slug = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nom_region));
        $slug = str_replace([' ', "'", '’'], ['-', '', ''], $slug);
        $resultat[$slug] = array_column($departements, 'numero');
    }

    return $resultat;
}

/**
 * Récupère les prévisions météo des prochains jours pour une ville donnée.
 *
 * @param string $ville Nom de la ville pour laquelle récupérer les prévisions (accents supprimés automatiquement).
 *
 * @return array Tableau de prévisions journalières, contenant la date, le jour (en français), une icône,
 *               les températures minimales et maximales, ainsi que la vitesse du vent.
 *
 */

function getNextDaysForecast($ville) {
    $ville = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $ville);

    // Appel API
    $data = callWeatherAPI("forecast.json", $ville);
    if (!$data || !isset($data['forecast']['forecastday'])) return [];

    $result = [];
    foreach ($data['forecast']['forecastday'] as $day) {
        $date = DateTime::createFromFormat('Y-m-d', $day['date']);
        $jours = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
        $dayIndex = (int) $date->format('w');
        $dayLabel = $jours[$dayIndex] . ' ' . $date->format('d');

        $result[] = [
            'date' => $day['date'],
            'day' => ucfirst($dayLabel),
            'icon' => $day['day']['condition']['icon'],
            'tmin' => round($day['day']['mintemp_c']),
            'tmax' => round($day['day']['maxtemp_c']),
            'wind' => round($day['day']['maxwind_kph']),
            'gust' => round($day['day']['maxwind_kph']),
        ];
    }

    return $result;
}

/**
 * Récupère les données météo utiles pour une plage dans une ville donnée (air, eau, vent, UV, etc.).
 *
 * @param string $ville Nom de la ville côtière concernée.
 *
 * @return array Tableau associatif contenant les conditions météo (ciel, icône, températures, vent, UV, marée).
 *
 */

function getPlageWeatherData($ville) {
    $data = callWeatherAPI("forecast.json", $ville);

    if (!$data || !isset($data['forecast']['forecastday'])) {
        echo "<pre>ERREUR API pour ville = $ville</pre>";
        var_dump($data);
        return [];
    }

    $day = $data['forecast']['forecastday'][0]['day'];

    return [
        'condition' => $day['condition']['text'],
        'icone' => 'https:' . $day['condition']['icon'], 
        'temp_air' => $day['avgtemp_c'],
        'temp_eau' => estimateWaterTemp($day['avgtemp_c']),
        'vent' => $day['maxwind_kph'],
        'uv' => $day['uv'],
        'maree' => rand(0, 1) ? 'Haute' : 'Basse'
    ];
}

/**
 * Récupère les données d’enneigement quotidien pour les principales stations d’un massif donné.
 *
 * @param string $massif Nom du massif montagneux (ex. : "Alpes", "Pyrénées").
 *
 * @return array Tableau contenant, pour chaque station, la liste des dates et des chutes de neige en cm.
 */

function getSnowDataForMassif(string $massif): array {
    $stations = getTopSkiStationsByMassif($massif);
    $results = [];

    foreach ($stations as $station) {
        $lat = $station['lat'];
        $lon = $station['lon'];
        $name = $station['name'];

        $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&daily=snowfall_sum&timezone=auto";
        $response = @file_get_contents($url);
        if (!$response) continue;

        $data = json_decode($response, true);
        if (!isset($data['daily']['time']) || !isset($data['daily']['snowfall_sum'])) continue;

        $entries = [];
        foreach ($data['daily']['time'] as $i => $date) {
            $entries[] = [
                'date' => $date,
                'snow_cm' => $data['daily']['snowfall_sum'][$i]
            ];
        }

        $results[] = [
            'station' => $name,
            'data' => $entries
        ];
    }

    return $results;
}

/**
 * Retourne la liste des principales stations de ski pour un massif montagneux donné.
 *
 * @param string $massif Nom du massif (ex. : "alpes", "pyrenees", "vosges", "jura", "massif-central", "corse").
 *
 * @return array Tableau des stations avec leur nom, latitude et longitude. Retourne un tableau vide si le massif est inconnu.
 */

function getTopSkiStationsByMassif(string $massif): array {
    $stations = [
        'alpes' => [
            ['name' => 'Tignes', 'lat' => 45.4691, 'lon' => 6.9063],
            ['name' => 'Val Thorens', 'lat' => 45.2974, 'lon' => 6.5796],
            ['name' => 'Alpe d’Huez', 'lat' => 45.0918, 'lon' => 6.0680],
            ['name' => 'Les Deux Alpes', 'lat' => 45.0076, 'lon' => 6.1200],
            ['name' => 'Chamonix', 'lat' => 45.9237, 'lon' => 6.8694]
        ],
        'pyrenees' => [
            ['name' => 'Saint-Lary', 'lat' => 42.8161, 'lon' => 0.3297],
            ['name' => 'Cauterets', 'lat' => 42.8873, 'lon' => -0.1167],
            ['name' => 'Font-Romeu', 'lat' => 42.5044, 'lon' => 2.0362],
            ['name' => 'Ax 3 Domaines', 'lat' => 42.7200, 'lon' => 1.8200],
            ['name' => 'Gourette', 'lat' => 42.9562, 'lon' => -0.3384]
        ],
        'vosges' => [
            ['name' => 'La Bresse', 'lat' => 48.0020, 'lon' => 6.9112],
            ['name' => 'Gérardmer', 'lat' => 48.0724, 'lon' => 6.8763],
            ['name' => 'Ventron', 'lat' => 47.9439, 'lon' => 6.8784],
            ['name' => 'Le Markstein', 'lat' => 47.9427, 'lon' => 7.0383],
            ['name' => 'Ballon d’Alsace', 'lat' => 47.8308, 'lon' => 6.8533]
        ],
        'jura' => [
            ['name' => 'Les Rousses', 'lat' => 46.4839, 'lon' => 6.0650],
            ['name' => 'Métabief', 'lat' => 46.8000, 'lon' => 6.3500],
            ['name' => 'Monts Jura', 'lat' => 46.2963, 'lon' => 5.9551],
            ['name' => 'La Pesse', 'lat' => 46.2932, 'lon' => 5.8497],
            ['name' => 'Bellefontaine', 'lat' => 46.5500, 'lon' => 6.0833]
        ],
        'massif-central' => [
            ['name' => 'Super-Besse', 'lat' => 45.5100, 'lon' => 2.9333],
            ['name' => 'Le Lioran', 'lat' => 45.0491, 'lon' => 2.7556],
            ['name' => 'Mont-Dore', 'lat' => 45.5753, 'lon' => 2.8090],
            ['name' => 'Chastreix', 'lat' => 45.5189, 'lon' => 2.7356],
            ['name' => 'Prat de Bouc', 'lat' => 45.0356, 'lon' => 2.7425]
        ],
        'corse' => [
            ['name' => 'Val d’Ese', 'lat' => 42.0000, 'lon' => 9.1000],
            ['name' => 'Ghisoni', 'lat' => 42.1000, 'lon' => 9.2000],
            ['name' => 'Vergio', 'lat' => 42.2833, 'lon' => 8.9333],
            ['name' => 'Asco', 'lat' => 42.5000, 'lon' => 9.0000],
            ['name' => 'Haut Asco', 'lat' => 42.4270, 'lon' => 9.0400]
        ]
    ];

    return $stations[$massif] ?? [];
}

/**
 * Récupère les données d’enneigement pour une station de ski donnée à partir de sa latitude et longitude.
 *
 * @param string $stationName Nom de la station.
 * @param float  $lat         Latitude de la station.
 * @param float  $lon         Longitude de la station.
 *
 * @return array Tableau contenant les dates et les quantités de neige journalières (en cm) pour la station.
 */

function getSnowDataForStation(string $stationName, float $lat, float $lon): array {
    $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&daily=snowfall_sum&timezone=auto";
    $response = @file_get_contents($url);
    if (!$response) return [];

    $data = json_decode($response, true);
    if (!isset($data['daily']['time']) || !isset($data['daily']['snowfall_sum'])) return [];

    $entries = [];
    foreach ($data['daily']['time'] as $i => $date) {
        $entries[] = [
            'date' => $date,
            'snow_cm' => $data['daily']['snowfall_sum'][$i]
        ];
    }

    return [[
        'station' => $stationName,
        'data' => $entries
    ]];
}

/**
 * Retourne les coordonnées géographiques et le niveau de zoom pour centrer une carte sur un massif donné.
 *
 * @param string $massif Nom du massif montagneux.
 *
 * @return array Tableau associatif contenant les clés 'lat', 'lon' et 'zoom'.
 */

function getMassifMapCenter(string $massif): array {
    $massifCenters = [
        'alpes' => ['lat' => 45.5, 'lon' => 6.5, 'zoom' => 8],
        'pyrenees' => ['lat' => 42.8, 'lon' => 0.3, 'zoom' => 8],
        'vosges' => ['lat' => 48.0, 'lon' => 6.9, 'zoom' => 8],
        'jura' => ['lat' => 46.5, 'lon' => 6.0, 'zoom' => 9],
        'massif-central' => ['lat' => 45.3, 'lon' => 2.8, 'zoom' => 8],
        'corse' => ['lat' => 42.2, 'lon' => 9.1, 'zoom' => 8]
    ];

    return $massifCenters[$massif] ?? ['lat' => 46.5, 'lon' => 2.5, 'zoom' => 6]; 
}

/**
 * Affiche une photo aléatoire d’un massif donné, accompagnée du nom d’un photographe dans une balise <figure>.
 *
 * @param string $massif Nom du massif pour lequel afficher une image .
 *
 * @return void Cette fonction affiche directement du code HTML ou un message d’erreur si aucun fichier n’est disponible.
 */
function displayRandomPhotoFigureByMassif(string $massif) {
    $dossier = "./images/massif/" . strtolower($massif) . "/";
    $extensions_autorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!is_dir($dossier)) {
        echo "<p>Dossier introuvable pour le massif : <strong>$massif</strong></p>";
        return;
    }

    $fichiers = array_filter(scandir($dossier), function($fichier) use ($dossier, $extensions_autorisees) {
        $extension = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
        return is_file($dossier . $fichier) && in_array($extension, $extensions_autorisees);
    });

    if (!empty($fichiers)) {
        $image = $fichiers[array_rand($fichiers)];
        $chemin = $dossier . $image;

        // nom générique à afficher pour figcaption, sans redondance avec alt
        $photographeAlt = [
            "alpes" => "Vincent Munier",
            "jura" => "Laurent Baheux",
            "vosges" => "Alex Strohl ",
            "pyrenees" => "Mathieu Rivrin",
            "massif-central" => "Aurélien Billois",
            "corse" => "Guillaume Scheib"
        ];

        $caption = $photographeAlt[strtolower($massif)] ?? "Paysage du massif " . ucfirst($massif);

        echo '<figure>';
        echo '<img src="' . htmlspecialchars($chemin) . '" alt="' . $dossier. '" style="width:100%; height:250px; object-fit:cover; border-radius:12px;"/>';
        echo '<figcaption>' . htmlspecialchars($caption) . '</figcaption>';
        echo '</figure>';
    } else {
        echo "<p>Aucune image disponible dans le dossier <strong>$dossier</strong>.</p>";
    }
}

/**
 * Retourne la liste des principales stations balnéaires françaises avec leurs coordonnées et données météo.
 *
 * @return array Tableau de stations contenant le nom, la latitude, la longitude et la vitesse du vent.
 */

function getTopBeachStations() {
    $stations = [
        ['name' => 'Nice', 'lat' => 43.7034, 'lon' => 7.2663],
        ['name' => 'Cannes', 'lat' => 43.5528, 'lon' => 7.0174],
        ['name' => 'Marseille', 'lat' => 43.2965, 'lon' => 5.3698],
        ['name' => 'Sète', 'lat' => 43.4035, 'lon' => 3.6928],
        ['name' => 'Le Grau-du-Roi', 'lat' => 43.5384, 'lon' => 4.1352],
        ['name' => 'Argelès-sur-Mer', 'lat' => 42.5391, 'lon' => 3.0232],
        ['name' => 'La Rochelle', 'lat' => 46.1603, 'lon' => -1.1511],
        ['name' => 'Les Sables-d\'Olonne', 'lat' => 46.4969, 'lon' => -1.7831],
        ['name' => 'Pornic', 'lat' => 47.1162, 'lon' => -2.1124],
        ['name' => 'Biarritz', 'lat' => 43.4832, 'lon' => -1.5586],
        ['name' => 'Arcachon', 'lat' => 44.6611, 'lon' => -1.1695],
        ['name' => 'Brest', 'lat' => 48.3904, 'lon' => -4.4861],
        ['name' => 'Saint-Malo', 'lat' => 48.6493, 'lon' => -2.0257],
        ['name' => 'Le Touquet', 'lat' => 50.5225, 'lon' => 1.5823],
        ['name' => 'Ajaccio', 'lat' => 41.9266, 'lon' => 8.7369],
    ];

    foreach ($stations as &$station) {
        $meteo = getPlageWeatherData($station['name']);
        $station['vent'] = $meteo['vent'] ?? 0;
    }

    return $stations;
}

/**
 * Recherche et retourne le code du département correspondant à une ville donnée, à partir d’un fichier CSV.
 *
 * @param string $ville Nom de la ville à rechercher.
 * @param string $csv   Chemin vers le fichier CSV des communes (par défaut './data/communes.csv').
 *
 * @return string|null Code du département sur 2 chiffres, ou null si la ville n'est pas trouvée.
 */

function getDepartementFromCSV(string $ville, string $csv = './data/communes.csv'): ?string {
    if (!file_exists($csv)) return null;

    $handle = fopen($csv, 'r');
    fgetcsv($handle); // saute l'en-tête

    while (($row = fgetcsv($handle)) !== false) {
        $nom = strtolower(trim($row[2])); // nom_standard
        if ($nom === strtolower(trim($ville))) {
            fclose($handle);
            return str_pad($row[12], 2, "0", STR_PAD_LEFT); // dep_code (colonne 12)
        }
    }

    fclose($handle);
    return null;
}

/**
 * Récupère les données de qualité de l’air pour une ville donnée via WeatherAPI.
 *
 * @param string $ville Nom de la ville à analyser.
 *
 * @return array|null Tableau associatif contenant l’indice AQI (US EPA) et les concentrations de polluants (PM2.5, PM10, O3, NO2, CO), ou null si indisponible.
 */

function getAirQualityData(string $ville): ?array {
    $data = callWeatherAPI("current.json", $ville, ['aqi' => 'yes']);
    if (!$data || !isset($data['current']['air_quality'])) return null;

    $aq = $data['current']['air_quality'];

    return [
        'aqi' => (int)($aq['us-epa-index'] ?? 0),
        'PM2.5' => $aq['pm2_5'] ?? null,
        'PM10' => $aq['pm10'] ?? null,
        'O3' => $aq['o3'] ?? null,
        'NO2' => $aq['no2'] ?? null,
        'CO' => $aq['co'] ?? null
    ];
}

/**
 * Sélectionne dynamiquement la clé WeatherAPI à utiliser en fonction de la date de démarrage de la période Pro.
 *
 * @return string Clé API WeatherAPI active (WEATHERAPI_KEY_PRO1 ou WEATHERAPI_KEY_PRO2).
 */

function getCurrentWeatherAPIKey(): string {
    $start = new DateTime(WEATHERAPI_PRO_START);
    $today = new DateTime();
    $interval = $start->diff($today)->days;

    return ($interval < 13) ? WEATHERAPI_KEY_PRO1 : WEATHERAPI_KEY_PRO2;
}

/**
 * Charge les noms des villes depuis un fichier CSV, avec option de filtrage par code département.
 *
 * @param string      $csv         Chemin vers le fichier CSV des communes.
 * @param string|null $departement Code du département à filtrer (facultatif).
 *
 * @return array Tableau contenant les noms des villes triés par ordre alphabétique.
 */

function chargerNomsVillesDepuisCSVParDepartement(string $csv, ?string $departement = null): array {
    if (!file_exists($csv)) return [];

    $villes = [];
    $handle = fopen($csv, 'r');
    $header = fgetcsv($handle, 0, ","); // séparateur: virgule

    $colNom = array_search('nom_standard', $header);
    $colDep = array_search('dep_code', $header);

    if ($colNom === false || $colDep === false) return [];

    while (($row = fgetcsv($handle, 0, ",")) !== false) {
        if (!isset($row[$colNom], $row[$colDep])) continue;

        $nom = trim($row[$colNom]);
        $dep = trim($row[$colDep]);

        if (!$departement || $dep === $departement) {
            $villes[] = $nom;
        }
    }

    fclose($handle);
    sort($villes);
    return $villes;
}

/**
 * Récupère les dernières actualités météo en France via l'API GNews.
 *
 * @param int $max Nombre maximum d'articles à récupérer (par défaut 10).
 *
 * @return array Tableau d’articles d’actualité, ou tableau vide en cas d’échec.
 */

function getMeteoNews($max = 10) {
    $apiKey = GNEWS_API; 
    $query = "météo OR intempéries OR canicule OR orage OR neige";
    $url = "https://gnews.io/api/v4/search?q=" . urlencode($query) . "&lang=fr&country=fr&max=$max&token=$apiKey";

    $response = @file_get_contents($url);
    if ($response === false) return [];

    $data = json_decode($response, true);
    return $data['articles'] ?? [];
}

/**
 * Récupère et filtre les alertes météo de vigilance en France fournies par WeatherAPI,
 * en ne conservant que celles correspondant à des villes françaises connues (issues d’un fichier CSV).
 *
 * @param string $csv Chemin vers le fichier CSV contenant la liste des communes françaises (par défaut './data/communes.csv').
 *
 * @return array Tableau d’alertes filtrées, contenant les informations essentielles : titre, type, zones, sévérité, validité, description.
 */

function getVigilanceAlertsForFrance(string $csv = './data/communes.csv'): array {
    $data = callWeatherAPI("alerts.json", "France");
    $alerts = $data['alerts']['alert'] ?? [];

    if (!file_exists($csv)) return [];

    // Charger toutes les communes valides dans un tableau
    $villes_fr = [];
    $handle = fopen($csv, 'r');
    fgetcsv($handle); // Ignore l'en-tête
    while (($row = fgetcsv($handle)) !== false) {
        $villes_fr[strtolower($row[2])] = true; // clé = nom_standard (colonne 2)
    }
    fclose($handle);

    $result = [];
    $seen = [];

    foreach ($alerts as $a) {
        $area = strtolower($a['areas'] ?? '');
        $ville_concernee = null;

        // Vérifie si une ville FR est mentionnée dans la zone
        foreach ($villes_fr as $nom => $_) {
            if (strpos($area, $nom) !== false) {
                $ville_concernee = $nom;
                break;
            }
        }

        if (!$ville_concernee) continue;

        // Vérifie qu’on n’a pas déjà affiché cette alerte
        $id = md5(($a['headline'] ?? '') . ($a['areas'] ?? '') . ($a['event'] ?? ''));
        if (isset($seen[$id])) continue;
        $seen[$id] = true;

        $result[] = [
            'headline' => $a['headline'] ?? '',
            'event' => $a['event'] ?? '',
            'areas' => $a['areas'] ?? '',
            'severity' => $a['severity'] ?? '',
            'effective' => $a['effective'] ?? '',
            'expires' => $a['expires'] ?? '',
            'desc' => $a['desc'] ?? '',
            'source_city' => $data['location']['name'] ?? '?'
        ];
    }

    return $result;
}

?>
