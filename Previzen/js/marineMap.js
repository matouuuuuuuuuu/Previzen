document.addEventListener("DOMContentLoaded", () => {
    if (!stations || stations.length === 0) return;

    const getColorFromWind = (wind) => {
        if (wind < 10) return 'green';
        if (wind < 20) return 'yellow';
        if (wind < 30) return 'orange';
        return 'red';
    };

    const features = stations.map(station => {
        return new ol.Feature({
            geometry: new ol.geom.Point(ol.proj.fromLonLat([station.lon, station.lat])),
            name: station.name,
            vent: station.vent || 0
        });
    });

    const vectorSource = new ol.source.Vector({ features });

    const vectorLayer = new ol.layer.Vector({
        source: vectorSource,
        style: feature => {
            const color = getColorFromWind(feature.get('vent'));
            return new ol.style.Style({
                image: new ol.style.Circle({
                    radius: 6,
                    fill: new ol.style.Fill({ color }),
                    stroke: new ol.style.Stroke({ color: '#fff', width: 2 })
                })
            });
        }
    });

    const map = new ol.Map({
        target: 'map',
        layers: [
            new ol.layer.Tile({ source: new ol.source.OSM() }),
            vectorLayer
        ],
        view: new ol.View({
            center: ol.proj.fromLonLat([2.2137, 46.2276]),
            zoom: 6
        })
    });

    const popup = document.createElement('div');
    popup.id = 'popup';
    popup.style = 'background: white; padding: 5px; border: 1px solid black; position: absolute;';
    document.body.appendChild(popup);

    const overlay = new ol.Overlay({
        element: popup,
        positioning: 'bottom-center',
        stopEvent: true,
        offset: [0, -10],
    });

    map.addOverlay(overlay);

    map.on('click', function (evt) {
        const feature = map.forEachFeatureAtPixel(evt.pixel, f => f);
        if (feature) {
            const name = feature.get('name');
            const vent = feature.get('vent');
            overlay.setPosition(evt.coordinate);
            popup.innerHTML = `<strong>${name}</strong><br>Vent : ${vent} km/h`;
        } else {
            overlay.setPosition(undefined);
            popup.innerHTML = '';
        }
    });

    // Centrage dynamique selon la zone sélectionnée
    const zone = typeof selectedZone !== "undefined" ? selectedZone : new URLSearchParams(window.location.search).get('zone');
    if (zone) {
        const zonesView = {
            manche: { center: [-1.5, 49.7], zoom: 7.3 },
            atlantique: { center: [-2.3, 46.0], zoom: 7.2 },
            mediterranee: { center: [4.8, 43.3], zoom: 7.4 }
        };
        const selected = zonesView[zone.toLowerCase()];
        if (selected) {
            map.getView().animate({
                center: ol.proj.fromLonLat(selected.center),
                zoom: selected.zoom,
                duration: 1000
            });
        }
    }
});
