(() => {
  const canvas = document.getElementById("reports-chart");
  const dataNode = document.getElementById("chart-data");
  if (!canvas || !dataNode || !window.Chart) return;
  let data;
  try { data = JSON.parse(dataNode.textContent); } catch (_) { return; }
  const css = getComputedStyle(document.documentElement);
  new Chart(canvas, {
    type: "line",
    data: {
      labels: data.labels,
      datasets: [{
        data: data.values,
        borderColor: "#ffb020",
        backgroundColor: "rgba(255,176,32,.12)",
        fill: true,
        borderWidth: 2,
        tension: .38,
        pointBackgroundColor: "#e5252a",
        pointBorderColor: "#f4f5f7",
        pointRadius: 4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { intersect: false, mode: "index" } },
      scales: {
        x: { grid: { display: false }, ticks: { color: "#8d9ab0", font: { family: "Inter", size: 10 } } },
        y: { beginAtZero: true, ticks: { precision: 0, color: "#8d9ab0", font: { family: "Inter", size: 10 } }, grid: { color: "rgba(255,255,255,.08)" } },
      },
    },
  });
})();
