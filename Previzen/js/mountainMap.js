document.addEventListener("DOMContentLoaded", () => {
    if (!stations || stations.length === 0) return;

    const features = stations.map(station => {
        return new ol.Feature({
            geometry: new ol.geom.Point(ol.proj.fromLonLat([station.lon, station.lat])),
            name: station.name
        });
    });

    const vectorSource = new ol.source.Vector({ features });

    const vectorLayer = new ol.layer.Vector({
        source: vectorSource,
        style: new ol.style.Style({
            image: new ol.style.Circle({
                radius: 6,
                fill: new ol.style.Fill({ color: 'red' }),
                stroke: new ol.style.Stroke({ color: '#fff', width: 2 })
            })
        })
    });

    const map = new ol.Map({
        target: 'map',
        layers: [
            new ol.layer.Tile({
                source: new ol.source.OSM()
            }),
            vectorLayer
        ],
        view: new ol.View({
            center: ol.proj.fromLonLat([mapCenter.lon, mapCenter.lat]),
            zoom: mapCenter.zoom
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
            overlay.setPosition(evt.coordinate);
            popup.innerHTML = `<strong>${name}</strong><br><a href="neige.php?station=${encodeURIComponent(name)}#carteLien">Voir les prévisions</a>`;
        } else {
            popup.innerHTML = '';
            overlay.setPosition(undefined);
        }
    });
});
