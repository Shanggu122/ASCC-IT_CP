<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Analytics</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/admin-navbar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin-analytics.css') }}">
  <style>
    :root {
      --primary: #004d40;
      --primary-light: #00695c;
      --primary-dark: #003d33;
      --secondary: #f8f9fa;
      --text-dark: #004d40;
      --text-light: #00695c;
      --border: #e0e0e0;
      --shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      background: var(--secondary);
    }

    .main-content {
      padding: 1.5rem;
      width: calc(100% - 240px);
      max-width: 1800px;
      margin: 0 0 0 240px;
      min-height: 100vh;
      box-sizing: border-box;
    }

    .header {
      margin-bottom: 1.5rem;
      padding: 1rem;
      background: white;
      border-radius: 12px;
      box-shadow: var(--shadow);
      text-align: center;
    }

    .header h1 {
      color: var(--text-dark);
      font-size: 1.5rem;
      margin: 0;
    }

    .analytics-container {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      margin: 0 auto;
      max-width: 1400px;
      width: 100%;
      padding: 0 1rem;
    }

    .fixed-section {
      position: sticky;
      top: 0;
      background: var(--secondary);
      z-index: 100;
      padding-bottom: 0.5rem;
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .filters {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 1.5rem;
      padding: 1.25rem;
      background: white;
      border-radius: 12px;
      box-shadow: var(--shadow);
      margin-bottom: 1.5rem;
    }

    .filter-group {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .filter-group label {
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--text-dark);
    }

    .filter-group input[type="date"] {
      padding: 0.5rem 1rem;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 0.9rem;
      color: var(--text-dark);
      background: var(--secondary);
      min-width: 160px;
    }

    .filter-actions {
      display: flex;
      gap: 0.75rem;
      margin-left: auto;
    }

    .filter-actions button {
      padding: 0.5rem 1.25rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .apply-btn {
      background: var(--primary);
      color: white;
    }

    .apply-btn:hover {
      background: var(--primary-dark);
    }

    .reset-btn {
      background: var(--secondary);
      color: var(--text-dark);
    }

    .reset-btn:hover {
      background: var(--border);
    }

    .department-tabs {
      display: flex;
      gap: 1rem;
      background: white;
      padding: 0.75rem;
      border-radius: 12px;
      box-shadow: var(--shadow);
      margin-bottom: 1rem;
    }

    .department-tab {
      flex: 1;
      padding: 1rem 1.5rem;
      border: none;
      background: transparent;
      cursor: pointer;
      border-radius: 8px;
      font-weight: 600;
      color: var(--text-light);
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .department-tab:hover {
      background: var(--secondary);
    }

    .department-tab.active {
      background: var(--primary);
      color: white;
    }

    .department-section {
      display: none;
    }

    .department-section.active {
      display: grid;
      gap: 2rem;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .stat-card {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1.5rem;
      padding: 1.25rem;
      text-align: center;
      background: white;
      border-radius: 12px;
      box-shadow: var(--shadow);
      max-width: 600px;
      margin: 0 auto;
    }

    .stat-card h3 {
      color: var(--text-dark);
      font-size: 1.2rem;
      font-weight: 600;
      margin: 0;
      text-align: right;
      white-space: nowrap;
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--primary);
      line-height: 1;
      font-family: 'Poppins', sans-serif;
      text-align: left;
      min-width: 60px;
    }

    .department-section {
      display: none;
    }

    .department-section.active {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 1rem 0 2rem 0;
      width: 100%;
    }

    .stats-grid {
      background: white;
      padding: 1rem;
      border-radius: 12px;
      box-shadow: var(--shadow);
      margin: 0 auto 2rem auto;
      width: 100%;
      max-width: 800px;
      display: flex;
      justify-content: center;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
      margin: 0 auto 2rem auto;
      width: 100%;
      max-width: 1400px;
    }

    .chart-card {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: var(--shadow);
      height: 380px;
      display: flex;
      flex-direction: column;
      min-width: 0;
    }    .chart-card canvas {
      width: 100% !important;
      height: calc(100% - 70px) !important;
      margin-top: 10px;
    }    @media (max-width: 1200px) {
      .grid {
        grid-template-columns: repeat(2, 1fr);
      }
      .chart-card {
        height: 400px;
      }
    }
    
    @media (max-width: 768px) {
      .grid {
        grid-template-columns: 1fr;
      }
      .analytics-container {
        margin: 0 0.5rem;
      }
    }

    .chart-card {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: var(--shadow);
      min-height: 400px;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .chart-card h2 {
      color: var(--text-dark);
      font-size: 1.1rem;
      font-weight: 600;
      margin: 0 0 1rem 0;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }
    
    .chart-container {
      flex: 1;
      position: relative;
      width: 100%;
      height: 100%;
      min-height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .legend-inline {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
      font-size: 0.85rem;
      justify-content: center;
      width: 100%;
    }

    .legend-inline span {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .swatch {
      width: 12px;
      height: 12px;
      border-radius: 3px;
    }
  </style>
</head>
<body>
  @include('components.navbar-admin')
  <div class="main-content">
    <div class="analytics-container">
      <div class="fixed-section">
        <div class="header">
          <h1>Consultation Analytics Dashboard</h1>
        </div>

        <div class="filters">
          <div class="filter-group">
            <label for="start-date">Start Date:</label>
            <input type="date" id="start-date" placeholder="Select start date">
          </div>
          <div class="filter-group">
            <label for="end-date">End Date:</label>
            <input type="date" id="end-date" placeholder="Select end date">
          </div>
          <div class="filter-actions">
            <button type="button" class="apply-btn" id="apply-range">
              <i class='bx bx-filter-alt'></i> Apply Filters
            </button>
          </div>
        </div>
      </div>

      <div class="department-tabs">
        <button class="department-tab active" data-dept="itis">
          <i class='bx bx-building-house'></i> ITIS Department
        </button>
        <button class="department-tab" data-dept="comsci">
          <i class='bx bx-code-alt'></i> ComSci Department
        </button>
      </div>
    </div>

    <div class="scrollable-content">
      <!-- ITIS Section -->
      <div class="department-section active" id="itis-section">
      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total ITIS Consultations</h3>
          <div class="stat-value" id="itis-total">0</div>
        </div>
      </div>
      <div class="grid">
        <div class="card chart-card">
          <h2>Top ITIS Consultation Topics</h2>
          <div class="legend-inline" id="itisTopicLegend"></div>
          <div class="chart-container">
            <canvas id="itisTopicsChart"></canvas>
          </div>
        </div>
        <div class="card chart-card">
          <h2>ITIS Consultation Activity</h2>
          <div class="chart-container">
            <canvas id="itisActivityChart"></canvas>
          </div>
        </div>
        <div class="card chart-card">
          <h2>ITIS Peak Days</h2>
          <div class="chart-container">
            <canvas id="itisPeakDaysChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- ComSci Section -->
    <div class="department-section" id="comsci-section">
      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total ComSci Consultations</h3>
          <div class="stat-value" id="comsci-total">0</div>
        </div>
      </div>
      <div class="grid">
        <div class="card chart-card">
          <h2>Top ComSci Consultation Topics</h2>
          <div class="legend-inline" id="comsciTopicLegend"></div>
          <div class="chart-container">
            <canvas id="comsciTopicsChart"></canvas>
          </div>
        </div>
        <div class="card chart-card">
          <h2>ComSci Consultation Activity</h2>
          <div class="chart-container">
            <canvas id="comsciActivityChart"></canvas>
          </div>
        </div>
        <div class="card chart-card">
          <h2>ComSci Peak Days</h2>
          <div class="chart-container">
            <canvas id="comsciPeakDaysChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Load Chart.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <!-- Initialize charts -->
  <script>
    // Debug flag
    const DEBUG = true;

    // Constants
    const REFRESH_MS = 15000; // 15s polling interval
    const dateFilters = { start: null, end: null };
    let startInput;
    let endInput;

    // Chart state management
    const charts = {
      itis: { topics: null, activity: null, peak: null },
      comsci: { topics: null, activity: null, peak: null },
      lastHash: null
    };

    // Color schemes
    const palettes = {
      itis: ['#A1DF76', '#63947C', '#31845D', '#02703D', '#16423C'],
      comsci: ['#76A1DF', '#7C6394', '#5D3184', '#3D0270', '#3C1642']
    };

    // Helper function to log debug messages
    function log(message, data = null) {
      if (DEBUG) {
        if (data) {
          console.log(message, data);
        } else {
          console.log(message);
        }
      }
    }

    // Canvas setup helper
    function setCanvasSize(id) {
      const canvas = document.getElementById(id);
      if (!canvas) {
        log(`Canvas ${id} not found`);
        return;
      }
      
      const container = canvas.parentElement;
      const width = container.clientWidth;
      const height = container.clientHeight;
      const dpr = window.devicePixelRatio || 1;

      canvas.style.width = width + 'px';
      canvas.style.height = height + 'px';
      canvas.width = Math.floor(width * dpr);
      canvas.height = Math.floor(height * dpr);
      
      log(`Canvas ${id} sized to ${width}x${height} (dpr: ${dpr})`);
    }

    // Chart builders
    function buildTopicsChart(dept, t) {
      if (!t?.topics?.length) {
        log(`No topic data for ${dept}`);
        return;
      }

      const canvas = document.getElementById(`${dept}TopicsChart`);
      if (!canvas) {
        log(`Topics chart canvas not found for ${dept}`);
        return;
      }

      if (charts[dept].topics) {
        charts[dept].topics.destroy();
      }

      const ctx = canvas.getContext('2d');
      if (!ctx) {
        log(`Could not get 2D context for ${dept} topics chart`);
        return;
      }

      const legend = document.getElementById(`${dept}TopicLegend`);
      if (legend) {
        legend.innerHTML = t.topics.map((topic, i) => 
          `<span><span class="swatch" style="background:${palettes[dept][i%5]}"></span>${topic}</span>`
        ).join('');
      }

            charts[dept].topics = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: t.professors || [],
          datasets: t.topics.map((topic, i) => ({
            label: topic,
            data: t.data[topic] || [],
            backgroundColor: palettes[dept][i%5],
            borderColor: palettes[dept][i%5],
            borderWidth: 1,
            barPercentage: 0.7,
            categoryPercentage: 0.85,
            maxBarThickness: 50
          }))
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              stacked: true,
              grid: { display: false }
            },
            y: {
              stacked: true,
              beginAtZero: true,
              ticks: {
                stepSize: 1,
                precision: 0
              }
            }
          },
          plugins: {
            legend: { display: false }
          }
        }
      });
    }

    function buildActivityChart(dept, a) {
      if (!a?.months?.length) {
        log(`No activity data for ${dept}`);
        return;
      }

      const canvas = document.getElementById(`${dept}ActivityChart`);
      if (!canvas) {
        log(`Activity chart canvas not found for ${dept}`);
        return;
      }

      if (charts[dept].activity) {
        charts[dept].activity.destroy();
      }

      charts[dept].activity = new Chart(canvas, {
        type: 'line',
        data: {
          labels: a.months,
          datasets: a.series.map((s, i) => ({
            label: s.name,
            data: s.data,
            borderColor: palettes[dept][i%5],
            backgroundColor: palettes[dept][i%5],
            tension: 0.4,
            fill: false
          }))
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: { 
                stepSize: 1,
                precision: 0 
              }
            }
          }
        }
      });
    }

    function buildPeakDaysChart(dept, days, weekend) {
      if (!days) {
        log(`No peak days data for ${dept}`);
        return;
      }

      const canvas = document.getElementById(`${dept}PeakDaysChart`);
      if (!canvas) {
        log(`Peak days chart canvas not found for ${dept}`);
        return;
      }

      if (charts[dept].peak) {
        charts[dept].peak.destroy();
      }

      const ctx = canvas.getContext('2d');
      if (!ctx) {
        log(`Could not get 2D context for ${dept} peak days chart`);
        return;
      }

      const labels = Object.keys(days);
      const data = Object.values(days);

      charts[dept].peak = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: labels,
          datasets: [{
            data: data,
            backgroundColor: labels.map((_, i) => palettes[dept][i%5])
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { 
              position: 'right',
              labels: {
                boxWidth: 12,
                padding: 15,
                font: {
                  size: 11
                }
              }
            }
          },
          layout: {
            padding: {
              right: 20
            }
          }
        }
      });
    }

    // Main analytics loader
    async function loadAnalytics(force = false) {
      log('Loading analytics...');

      try {
        const params = new URLSearchParams();
        if (dateFilters.start) params.append('start_date', dateFilters.start);
        if (dateFilters.end) params.append('end_date', dateFilters.end);
        params.append('_', Date.now().toString());

        const response = await fetch('/api/admin/analytics?' + params.toString());
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        log('Received data:', data);

        const hash = JSON.stringify(data);
        if (!force && hash === charts.lastHash) {
          log('No data changes');
          return;
        }
        charts.lastHash = hash;

        // Update stats
        ['itis', 'comsci'].forEach(dept => {
          if (data[dept]) {
            document.getElementById(`${dept}-total`).textContent = data[dept].totalConsultations || 0;

            // Ensure proper canvas sizes
            setCanvasSize(`${dept}TopicsChart`);
            setCanvasSize(`${dept}ActivityChart`);
            setCanvasSize(`${dept}PeakDaysChart`);

            // Build charts
            buildTopicsChart(dept, data[dept].topics);
            buildActivityChart(dept, data[dept].activity);
            buildPeakDaysChart(dept, data[dept].peak_days, data[dept].weekend_days);
          }
        });
      } catch (error) {
        log('Error loading analytics:', error);
      }
    }

    function formatDateForInput(date) {
      const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
      return local.toISOString().split('T')[0];
    }

    function setDefaultDateRange() {
      const today = new Date();
      const defaultEnd = formatDateForInput(today);
      const defaultStartDate = new Date(today.getFullYear(), today.getMonth() - 5, 1);
      const defaultStart = formatDateForInput(defaultStartDate);

      startInput.value = defaultStart;
      endInput.value = defaultEnd;
      dateFilters.start = defaultStart;
      dateFilters.end = defaultEnd;
    }

    function applyDateRange(start, end) {
      if (start && end && new Date(start) > new Date(end)) {
        alert('Start date must be on or before end date.');
        return false;
      }

      dateFilters.start = start || null;
      dateFilters.end = end || null;
      charts.lastHash = null; // force reload
      loadAnalytics(true);
      return true;
    }

    // Tab switching
    document.querySelectorAll('.department-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        const dept = tab.dataset.dept;
        document.querySelectorAll('.department-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.department-section').forEach(s => s.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(`${dept}-section`).classList.add('active');
      });
    });

    // Handle window resizing
    function handleResize() {
      ['itis', 'comsci'].forEach(dept => {
        setCanvasSize(`${dept}TopicsChart`);
        setCanvasSize(`${dept}ActivityChart`);
        setCanvasSize(`${dept}PeakDaysChart`);
        
        if (charts[dept].topics) charts[dept].topics.resize();
        if (charts[dept].activity) charts[dept].activity.resize();
        if (charts[dept].peak) charts[dept].peak.resize();
      });
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
      log('Initializing analytics...');
      startInput = document.getElementById('start-date');
      endInput = document.getElementById('end-date');

      // Set up date filters
      if (startInput && endInput) {
        setDefaultDateRange();

        const applyBtn = document.getElementById('apply-range');

        if (applyBtn) {
          applyBtn.addEventListener('click', () => {
            applyDateRange(startInput.value, endInput.value);
          });
        }
      }

      // Add resize handler with debounce
      let resizeTimeout;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResize, 250);
      });

      // Initial load
      loadAnalytics(true);
      
      // Set up auto-refresh
      setInterval(() => loadAnalytics(false), REFRESH_MS);
      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) loadAnalytics(false);
      });
    });
  </script>
</body>
</html>
