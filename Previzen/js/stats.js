fetch('stats_data.php')
  .then(response => response.json())
  .then(data => {
    const cities = data.cities;
    const dates = data.daily;

    // Pie chart for cities
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: Object.keys(cities),
        datasets: [{
          label: 'Consultations par ville',
          data: Object.values(cities),
          borderWidth: 1
        }]
      }
    });

    // Line chart for daily views
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    new Chart(lineCtx, {
      type: 'line',
      data: {
        labels: Object.keys(dates),
        datasets: [{
          label: 'Consultations par jour',
          data: Object.values(dates),
          fill: false,
          tension: 0.1,
          borderColor: '#003366'
        }]
      }
    });
  });
