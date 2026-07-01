(function () {
  const openButton = document.querySelector('[data-open-location-map]');
  const modal = document.getElementById('location-map-modal');
  const canvas = document.getElementById('location-map-canvas');
  const closeButtons = document.querySelectorAll('[data-close-location-map]');
  const dataNode = document.getElementById('bw-location-map-data');

  if (!openButton || !modal || !canvas || !dataNode || typeof L === 'undefined') {
    return;
  }

  let mapInstance = null;
  let mapReady = false;
  const DEFAULT_VIEW = { center: [50.0755, 14.4378], zoom: 10 };

  function isFiniteNumber(value) {
    return typeof value === 'number' && !Number.isNaN(value);
  }

  function parseMapData() {
    try {
      const parsed = JSON.parse(dataNode.textContent || '{}');
      if (!parsed || typeof parsed !== 'object') {
        return { view: null, points: [] };
      }
      return {
        view: parsed.view && typeof parsed.view === 'object' ? parsed.view : null,
        points: Array.isArray(parsed.points) ? parsed.points : [],
      };
    } catch (e) {
      return { view: null, points: [] };
    }
  }

  function getLatLngFromPoint(point) {
    if (point && isFiniteNumber(point.lat) && isFiniteNumber(point.lng)) {
      return [point.lat, point.lng];
    }

    return null;
  }

  function getPopupHtml(point) {
    const title = point.title ? String(point.title) : '';
    const link = point.url ? String(point.url) : '#';
    const image = point.image ? String(point.image) : '';
    const googleMapLink = point.google_map_link ? String(point.google_map_link) : '';
    let html = '';

    if (googleMapLink) {
      html += '<p style="margin:0 0 8px;"><a href="' + googleMapLink + '" target="_blank" rel="noopener noreferrer">Гугл карта</a></p>';
    }

    if (image) {
      html += '<a href="' + link + '"><img src="' + image + '" alt="' + title + '" loading="lazy" style="display:block;height:auto;margin-bottom:8px;border-radius:6px;"></a>';
    }

    html += '<a href="' + link + '">' + title + '</a>';
    return html;
  }

  function getGoogleMapPopupLink(point, view) {
    const hasLatLng = point && isFiniteNumber(point.lat) && isFiniteNumber(point.lng);
    const viewZoom = view && isFiniteNumber(view.zoom) ? view.zoom : null;
    const title = point && point.title ? String(point.title) : '';

    if (hasLatLng && viewZoom !== null) {
      let url = 'https://www.google.com/maps?ll=' + point.lat + ',' + point.lng + '&z=' + viewZoom;
      if (title) {
        url += '&q=' + encodeURIComponent(title);
      }
      return url;
    }

    return point && point.google_map_link ? String(point.google_map_link) : '';
  }

  function applyMapView(markerLayer, view) {
    if (view && Array.isArray(view.center) && view.center.length === 2 && isFiniteNumber(view.zoom)) {
      mapInstance.setView([view.center[0], view.center[1]], view.zoom);
      return;
    }

    const bounds = markerLayer.getBounds();
    if (bounds.isValid()) {
      mapInstance.fitBounds(bounds, { padding: [40, 40], maxZoom: 11 });
      return;
    }

    mapInstance.setView(DEFAULT_VIEW.center, DEFAULT_VIEW.zoom);
  }

  function adjustPopupAfterLayout(popup) {
    if (!popup || !mapInstance) {
      return;
    }

    popup.update();

    const latLng = popup.getLatLng();
    if (latLng) {
      mapInstance.panInside(latLng, {
        paddingTopLeft: [24, 24],
        paddingBottomRight: [24, 24],
        animate: true,
      });
    }
  }

  function buildMarkers() {
    const mapData = parseMapData();
    const points = mapData.points;
    const view = mapData.view;
    const markerLayer = L.featureGroup();
    let activePopupMarker = null;

    for (let i = 0; i < points.length; i += 1) {
      const point = points[i];
      if (!point) {
        continue;
      }

      const latLng = getLatLngFromPoint(point);

      if (!latLng || Number.isNaN(latLng[0]) || Number.isNaN(latLng[1])) {
        continue;
      }

      const marker = L.marker(latLng);
      const title = point.title ? String(point.title) : '';
      let markerTooltipEnabled = false;

      function bindMarkerTooltip() {
        if (!title || markerTooltipEnabled) {
          return;
        }

        marker.bindTooltip(title, {
          direction: 'top',
          offset: [0, -8],
          opacity: 0.95,
        });
        markerTooltipEnabled = true;
      }

      function unbindMarkerTooltip() {
        if (!markerTooltipEnabled) {
          return;
        }

        marker.closeTooltip();
        marker.unbindTooltip();
        markerTooltipEnabled = false;
      }

      if (title) {
        bindMarkerTooltip();
        // Leaflet can auto-show tooltips on hover if they remain bound.
        // We guard manual opening to avoid mixing tooltip with an open popup.
        marker.on('mouseover', function () {
          if (activePopupMarker !== marker && markerTooltipEnabled) {
            marker.openTooltip();
          }
        });
        marker.on('mouseout', function () {
          marker.closeTooltip();
        });
      }
      const popupPoint = Object.assign({}, point, {
        google_map_link: getGoogleMapPopupLink(point, view),
      });
      const popupHtml = getPopupHtml(popupPoint);
      marker.bindPopup(popupHtml);

      // Critical behavior: while this marker popup is open, tooltip is fully unbound
      // so it cannot reappear on hover and overlap the popup content.
      marker.on('popupopen', function (event) {
        activePopupMarker = marker;
        unbindMarkerTooltip();

        const popup = event && event.popup ? event.popup : marker.getPopup();
        if (!popup) {
          return;
        }

        // Leaflet autopan runs before lazy image dimensions are final,
        // so we re-check visibility after the image has loaded.
        const popupElement = popup.getElement();
        const popupImage = popupElement ? popupElement.querySelector('img') : null;

        if (!popupImage) {
          adjustPopupAfterLayout(popup);
          return;
        }

        if (popupImage.complete) {
          adjustPopupAfterLayout(popup);
          return;
        }

        popupImage.addEventListener('load', function () {
          adjustPopupAfterLayout(popup);
        }, { once: true });

        popupImage.addEventListener('error', function () {
          adjustPopupAfterLayout(popup);
        }, { once: true });
      });
      marker.on('popupclose', function () {
        if (activePopupMarker === marker) {
          activePopupMarker = null;
        }
        bindMarkerTooltip();
      });
      marker.addTo(markerLayer);
    }

    markerLayer.addTo(mapInstance);
    applyMapView(markerLayer, view);
  }

  function initMapIfNeeded() {
    if (mapReady) {
      mapInstance.invalidateSize();
      return;
    }

    mapInstance = L.map('location-map-canvas', {
      zoomControl: true,
      scrollWheelZoom: true,
      attributionControl: false // отключаем дефолтную панель
    });

    // Добавляем только нужную атрибуцию вручную
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19
    }).addTo(mapInstance);

    L.control.attribution({
      prefix: false
    }).addTo(mapInstance);
    // Вставляем свой html
    setTimeout(function () {
      var attr = canvas.querySelector('.leaflet-control-attribution');
      if (attr) {
        attr.innerHTML = '<a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">© OpenStreetMap contributors</a>';
      }
    }, 300);

    buildMarkers();
    mapReady = true;
  }

  function openModal(event) {
    event.preventDefault();
    modal.hidden = false;
    document.body.classList.add('location-map-opened');
    initMapIfNeeded();
  }

  function closeModal() {
    modal.hidden = true;
    document.body.classList.remove('location-map-opened');
  }

  openButton.addEventListener('click', openModal);
  closeButtons.forEach(function (btn) {
    btn.addEventListener('click', closeModal);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !modal.hidden) {
      closeModal();
    }
  });
  function setAttributionLinksTargetBlank() {
    // Wait for attribution control to appear
    setTimeout(function () {
      var attr = canvas.querySelector('.leaflet-control-attribution');
      if (attr) {
        var links = attr.querySelectorAll('a');
        links.forEach(function (a) {
          a.setAttribute('target', '_blank');
          a.setAttribute('rel', 'noopener noreferrer');
        });
      }
    }, 300);
  }
})();
