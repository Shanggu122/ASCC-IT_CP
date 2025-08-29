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
</head>
<body>
  @include('components.navbar-admin')
    <div class="main-content">
    <div class="header">
      <h1>Consultation Analytics</h1>
    </div>
    <div class="grid">
      <div class="card chart-card">
        <h2>Top Consultation Topics</h2>
        <div class="legend-inline" id="topicLegend"></div>
  <canvas id="topicsChart"></canvas>
      </div>
      <div class="card chart-card">
        <h2>Consultation Activity</h2>
  <canvas id="activityChart"></canvas>
      </div>
      <div class="card chart-card">
        <h2>Peak Consultation Days</h2>
  <canvas id="peakDaysChart"></canvas>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    const REFRESH_MS = 15000; // 15s polling interval
    const charts = { topics:null, activity:null, peak:null, lastHash:null };

    async function loadAnalytics(force=false){
      try {
        const res = await fetch('/api/admin/analytics?_=' + Date.now());
        const data = await res.json();
        const hash = JSON.stringify({t:data.topics,a:data.activity,p:data.peak_days,w:data.weekend_days});
        if(!force && hash === charts.lastHash) return; // no change
        charts.lastHash = hash;
        // ensure crisp canvases intrinsic size before (re)creating charts
        setCanvasSize('topicsChart');
        setCanvasSize('activityChart');
        setCanvasSize('peakDaysChart');
        buildTopicsChart(data.topics);
        buildActivityChart(data.activity);
        buildPeakDaysChart(data.peak_days, data.weekend_days);
      }catch(e){console.error('Analytics load failed',e);}    
    }

    function setCanvasSize(id){
      const c = document.getElementById(id);
      if(!c) return;
  const dpr = window.devicePixelRatio || 1; // native scale only
      // target logical display size
  let logicalWidth = c.parentElement.clientWidth - 40; // padding allowance
  const maxWidth = 600; // clamp so it doesn't look oversized
  if(logicalWidth > maxWidth) logicalWidth = maxWidth;
      const logicalHeight = 400;
      c.style.width = logicalWidth + 'px';
      c.style.height = logicalHeight + 'px';
      c.width = Math.round(logicalWidth * dpr);
      c.height = Math.round(logicalHeight * dpr);
    }

    // Prototype palette (soft-to-dark greens + muted teal) matching your design
    // Order used across charts: Programming, Networking, Capstone, (extras), then Mon-Fri for donut
    const palette = [
  '#A1DF76', // light green
  '#63947C', // gray green
  '#31845D', // medium green
  '#02703D', // dark green
  '#16423C', // dark turquoise
  '#0B4E3A', // extra accent
  '#075044'  // extra accent 2
    ];

    function buildTopicsChart(t){
      const ctx = document.getElementById('topicsChart');
      if(charts.topics){ charts.topics.destroy(); charts.topics=null; }
      const departments = t.departments; // x axis
      const topics = t.topics;
      // Build legend
      const legend = document.getElementById('topicLegend');
      legend.innerHTML = topics.map((topic,i)=>`<span><span class="swatch" style="background:${palette[i%palette.length]}"></span>${topic}</span>`).join('');
      const datasets = topics.map((topic,i)=>({
        label: topic,
        data: departments.map(dep=> (t.data[dep] ? t.data[dep][topic] : 0)),
        backgroundColor: palette[i%palette.length],
        categoryPercentage: 0.7,
        barPercentage: 0.85,
        maxBarThickness: 60
      }));
      charts.topics = new Chart(ctx,{type:'bar',data:{labels:departments,datasets},options:{responsive:false,animation:false,plugins:{legend:{display:false}},scales:{x:{stacked:true},y:{stacked:true,beginAtZero:true,suggestedMax:20,ticks:{stepSize:2}}}}});
    }

    function buildActivityChart(a){
      const ctx = document.getElementById('activityChart');
      if(charts.activity){ charts.activity.destroy(); charts.activity=null; }
      let months = a.months.slice();
      const series = a.series; const deptNames = Object.keys(series);
      // Trim trailing months where every dept value is zero
      let last = months.length - 1;
      const allZeroAt = (idx)=> deptNames.every(d=> (series[d][idx]||0) === 0);
      while(last > 0 && allZeroAt(last)) last--;
      if(last < months.length - 1){
        months = months.slice(0,last+1);
        deptNames.forEach(d=>{ series[d] = series[d].slice(0,last+1); });
      }
      const deptColorMap = {
        'IT & IS':'#16423C',
        'CompSci':'#31845D',
      };
      const datasets = deptNames.map((dept,i)=>{
        const color = deptColorMap[dept] || palette[(i+2)%palette.length];
        return {
          label: dept,
          data: series[dept],
          tension:.3,
          fill:false,
          borderColor: color,
          backgroundColor: color,
          pointRadius:4,
          pointHoverRadius:6,
          borderWidth:3,
        };
      });
      charts.activity = new Chart(ctx,{type:'line',data:{labels:months,datasets},options:{responsive:false,animation:false,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true,precision:0,suggestedMax:15,ticks:{stepSize:1}}}}});
    }

    function buildPeakDaysChart(d, weekend){
      const ctx = document.getElementById('peakDaysChart');
      if(charts.peak){ charts.peak.destroy(); charts.peak=null; }
      const order = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
      const dayColors = {
        'Monday':'#A1DF76',
        'Tuesday':'#63947C',
        'Wednesday':'#31845D',
        'Thursday':'#02703D',
        'Friday':'#16423C'
      };
      const labels = order.filter(day=> typeof d[day] !== 'undefined');
      const values = labels.map(k=> d[k] || 0);
      const total = values.reduce((a,b)=>a+b,0);
      if(total === 0){
        const wkVals = ['Saturday','Sunday'].map(k=> weekend && weekend[k] ? weekend[k] : 0);
        const wkTotal = wkVals.reduce((a,b)=>a+b,0);
        if(wkTotal>0){
          charts.peak = new Chart(ctx,{type:'doughnut',data:{labels:['Saturday','Sunday'],datasets:[{data:wkVals,backgroundColor:['#5A8275','#0B3B36']} ]},options:{animation:false,plugins:{title:{display:true,text:'Only weekend bookings so far'},legend:{position:'right'}},devicePixelRatio:window.devicePixelRatio||1}});
          return;
        } else {
          ctx.parentElement.innerHTML = '<h2>Peak Consultation Days</h2><p style="margin:1rem 0 0;font-size:.9rem;color:#333;">No bookings yet.</p>';
          return;
        }
      }
      charts.peak = new Chart(ctx,{type:'doughnut',data:{labels,datasets:[{data:values,backgroundColor:labels.map(day=>dayColors[day]||'#094B40')}]},options:{responsive:false,animation:false,plugins:{legend:{position:'right'}}}});
    }

  // Debug UI removed

    document.addEventListener('DOMContentLoaded', ()=>{ 
      loadAnalytics(true); 
      setInterval(()=>loadAnalytics(false), REFRESH_MS); 
      document.addEventListener('visibilitychange',()=>{ if(!document.hidden) loadAnalytics(false); });
    });
  </script>
</body>
</html>
