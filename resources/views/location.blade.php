<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Share Location</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      color: #333;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
      flex: 1;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      background: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
    }

    .header h1 {
      font-size: 2.2rem;
      margin-bottom: 15px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .status-card {
      background: rgba(255, 255, 255, 0.95);
      padding: 25px;
      border-radius: 15px;
      margin-bottom: 25px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      text-align: center;
    }

    #status {
      font-size: 1.1rem;
      font-weight: 500;
      margin-bottom: 15px;
    }

    .status-loading { color: #f39c12; }
    .status-success { color: #27ae60; }
    .status-error { color: #e74c3c; }
    .status-warning { color: #f39c12; }

    .map-container {
      background: rgba(255, 255, 255, 0.95);
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      margin-bottom: 25px;
    }

    #map {
      height: 400px;
      width: 100%;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .map-legend {
      margin-top: 15px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      background: rgba(255, 255, 255, 0.5);
      border-radius: 8px;
    }

    .legend-color {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      border: 2px solid white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .location-info {
      background: rgba(255, 255, 255, 0.95);
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
    }

    .info-item {
      padding: 15px;
      background: rgba(255, 255, 255, 0.6);
      border-radius: 10px;
      border-left: 4px solid #667eea;
    }

    .info-label {
      font-weight: 600;
      color: #555;
      margin-bottom: 5px;
    }

    .info-value {
      font-size: 1.1rem;
      color: #333;
    }

    .loading-spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #667eea;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-right: 10px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .btn-retry {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 25px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 500;
      margin-top: 15px;
      transition: transform 0.2s ease;
    }

    .btn-retry:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    @media (max-width: 768px) {
      .container {
        padding: 15px;
      }

      .header h1 {
        font-size: 1.8rem;
      }

      #map {
        height: 300px;
      }

      .map-legend {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Share Location for <span id="action-text">{{ request('action') }}</span></h1>
      <p>Please allow location access to continue</p>
    </div>

    <div class="status-card">
      <div id="status" class="status-loading">
        <span class="loading-spinner"></span>
        Detecting your location...
      </div>
      <button id="retry-btn" class="btn-retry" style="display: none;" onclick="requestLocation()">
        Try Again
      </button>
    </div>

    <div class="map-container" id="map-container" style="display: none;">
      <div id="map"></div>
      <div class="map-legend">
        <div class="legend-item">
          <div class="legend-color" style="background-color: #e74c3c;"></div>
          <span>Your Location</span>
        </div>
        <div class="legend-item">
          <div class="legend-color" style="background-color: #3498db;"></div>
          <span>Geofence Center</span>
        </div>
        <div class="legend-item">
          <div class="legend-color" style="background-color: rgba(52, 152, 219, 0.3); border: 2px solid #3498db;"></div>
          <span>Allowed Area</span>
        </div>
      </div>
    </div>

    <div class="location-info" id="location-info" style="display: none;">
      <div class="info-grid">
        <div class="info-item">
          <div class="info-label">Status</div>
          <div class="info-value" id="range-status">Checking...</div>
        </div>
        <div class="info-item">
          <div class="info-label">Distance to Nearest Geofence</div>
          <div class="info-value" id="distance-info">Calculating...</div>
        </div>
        <div class="info-item">
          <div class="info-label">Coordinates</div>
          <div class="info-value" id="coordinates-info">Getting location...</div>
        </div>
        <div class="info-item">
          <div class="info-label">Timestamp</div>
          <div class="info-value" id="timestamp-info">-</div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
  <script>
    const discordId = "{{ request('discord_id') }}";
    const action = "{{ request('action') }}";
    let map = null;
    let userMarker = null;
    let geofencesData = [];

    async function fetchGeofences() {
      try {
        const response = await fetch('/api/geofences');
        if (response.ok) {
          geofencesData = await response.json();
          return geofencesData;
        } else {
          console.error('Failed to fetch geofences');
          return [];
        }
      } catch (error) {
        console.error('Error fetching geofences:', error);
        return [];
      }
    }

    function updateStatus(message, className = '') {
      const status = document.getElementById('status');
      if (!status) {
        console.error('Status element not found');
        return;
      }

      // Clear all status classes first
      status.className = '';

      // Add the new class
      if (className) {
        status.className = className;
      }

      // Set the message (use innerHTML for HTML content like spinners)
      status.innerHTML = message;
    }

    function showRetryButton() {
      document.getElementById('retry-btn').style.display = 'inline-block';
    }

    function calculateDistance(lat1, lng1, lat2, lng2) {
      const R = 6371000; // Earth's radius in meters
      const dLat = (lat2 - lat1) * Math.PI / 180;
      const dLng = (lng2 - lng1) * Math.PI / 180;
      const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng/2) * Math.sin(dLng/2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
      return R * c;
    }

    async function initMap(lat, lng) {
      // Fetch geofences from database
      const geofences = await fetchGeofences();

      if (geofences.length === 0) {
        updateStatus('⚠️ No geofences found. Please contact administrator.', 'status-warning');
        return false;
      }

      map = L.map('map').setView([lat, lng], 15);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map);

      // Add user location marker
      userMarker = L.marker([lat, lng], {
        icon: L.divIcon({
          className: 'user-location-marker',
          html: '<div style="background-color: #e74c3c; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
          iconSize: [20, 20],
          iconAnchor: [10, 10]
        })
      }).addTo(map);

      userMarker.bindPopup('<b>Your Location</b><br/>Click to view coordinates').openPopup();

      // Add geofences
      let inRange = false;
      let nearestDistance = Infinity;
      let nearestGeofence = null;

      geofences.forEach(fence => {
        // Add geofence center marker
        L.marker([fence.latitude, fence.longitude], {
          icon: L.divIcon({
            className: 'geofence-marker',
            html: '<div style="background-color: #3498db; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
          })
        }).addTo(map).bindPopup(`<b>${fence.name || 'Geofence #' + fence.id}</b><br/>Radius: ${fence.radius}m`);

        // Add geofence circle with more visible styling
        L.circle([fence.latitude, fence.longitude], {
          color: '#3498db',
          weight: 3,
          opacity: 0.8,
          fillColor: '#3498db',
          fillOpacity: 0.15,
          radius: fence.radius
        }).addTo(map).bindPopup(`<b>${fence.name || 'Geofence #' + fence.id}</b><br/>Allowed Area<br/>Radius: ${fence.radius}m`);

        // Calculate distance
        const distance = calculateDistance(lat, lng, fence.latitude, fence.longitude);
        if (distance < nearestDistance) {
          nearestDistance = distance;
          nearestGeofence = fence;
        }
        if (distance <= fence.radius) {
          inRange = true;
        }
      });

      // Update info panel
      document.getElementById('range-status').textContent = inRange ? '✅ Inside Geofence' : '❌ Outside Geofence';
      document.getElementById('range-status').style.color = inRange ? '#27ae60' : '#e74c3c';
      document.getElementById('distance-info').textContent = `${Math.round(nearestDistance)}m from ${nearestGeofence?.name || 'Geofence #' + nearestGeofence?.id || 'geofence'}`;
      document.getElementById('coordinates-info').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
      document.getElementById('timestamp-info').textContent = new Date().toLocaleString();

      // Show map and info
      document.getElementById('map-container').style.display = 'block';
      document.getElementById('location-info').style.display = 'block';

      return inRange;
    }

    async function sendLocation(lat, lng) {
      updateStatus('<span class="loading-spinner"></span>Sending location...', 'status-loading');

      try {
        const response = await fetch('/api/time-log', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            discord_id: discordId,
            type: action,
            latitude: lat,
            longitude: lng,
          }),
        });

        const data = await response.json();

        if (response.ok) {
          updateStatus(
            `✅ ${data.message}`,
            data.in_range ? 'status-success' : 'status-warning'
          );
        } else {
          updateStatus(`❌ Error: ${data.message || 'Failed to log time'}`, 'status-error');
          showRetryButton();
        }
      } catch (error) {
        updateStatus('❌ Network error. Please check your connection and try again.', 'status-error');
        showRetryButton();
      }
    }

    function requestLocation() {
      document.getElementById('retry-btn').style.display = 'none';
      updateStatus('<span class="loading-spinner"></span>Detecting your location...', 'status-loading');

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          async position => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // Initialize map first
            const inRange = await initMap(lat, lng);

            // Then send location
            sendLocation(lat, lng);
          },
          error => {
            let errorMessage = 'Location access denied or unavailable.';
            switch(error.code) {
              case error.PERMISSION_DENIED:
                errorMessage = '❌ Location access denied. Please enable location permissions and try again.';
                break;
              case error.POSITION_UNAVAILABLE:
                errorMessage = '❌ Location information unavailable. Please try again.';
                break;
              case error.TIMEOUT:
                errorMessage = '❌ Location request timed out. Please try again.';
                break;
            }
            updateStatus(errorMessage, 'status-error');
            showRetryButton();
          },
          {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 300000
          }
        );
      } else {
        updateStatus('❌ Geolocation is not supported by your browser.', 'status-error');
        showRetryButton();
      }
    }

    // Start the location detection process
    requestLocation();
  </script>
</body>
</html>
