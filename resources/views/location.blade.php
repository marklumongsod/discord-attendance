<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Share Location</title>
</head>
<body>
  <h1>Share your location for {{ request('action') }}</h1>
  <div id="status">Detecting your location...</div>

  <script>
    const discordId = "{{ request('discord_id') }}";
    const action = "{{ request('action') }}";

    async function sendLocation(lat, lng) {
      const status = document.getElementById('status');
      status.textContent = 'Sending location...';

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
          status.textContent = data.message;
        } else {
          status.textContent = `Error: ${data.message || 'Failed to log time'}`;
        }
      } catch (error) {
        status.textContent = 'Network error. Please try again.';
      }
    }

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        position => {
          sendLocation(position.coords.latitude, position.coords.longitude);
        },
        error => {
          document.getElementById('status').textContent = 'Location access denied or unavailable.';
        },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    } else {
      document.getElementById('status').textContent = 'Geolocation is not supported by your browser.';
    }
  </script>
</body>
</html>
