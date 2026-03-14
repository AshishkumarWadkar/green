/**
 * Dashboard - Home Page (Simplified)
 */

'use strict';

(function () {
  const labelColor = typeof config !== 'undefined' && config.colors ? config.colors.textMuted : '#6d6b77';
  const borderColor = typeof config !== 'undefined' && config.colors ? config.colors.borderColor : '#e6e6e8';
  const primaryColor = typeof config !== 'undefined' && config.colors ? config.colors.primary : '#7367f0';
  const successColor = typeof config !== 'undefined' && config.colors ? config.colors.success : '#28c76f';
  const warningColor = typeof config !== 'undefined' && config.colors ? config.colors.warning : '#ff9f43';
  const infoColor = typeof config !== 'undefined' && config.colors ? config.colors.info : '#00cfe8';

  if (typeof window.dashboardData === 'undefined') return;

  const data = window.dashboardData;

  // Enquiry Trend - Line Chart
  const trendEl = document.querySelector('#enquiryTrendChart');
  if (trendEl && data.trendDates && data.trendCounts) {
    new ApexCharts(trendEl, {
      chart: {
        type: 'line',
        height: 280,
        toolbar: { show: false },
        zoom: { enabled: false }
      },
      stroke: { curve: 'smooth', width: 2 },
      colors: [primaryColor],
      series: [{ name: 'Enquiries', data: data.trendCounts }],
      xaxis: {
        categories: data.trendDates,
        labels: { style: { colors: labelColor } },
        axisBorder: { show: true, color: borderColor },
        axisTicks: { show: true, color: borderColor }
      },
      yaxis: {
        labels: { style: { colors: labelColor } },
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      grid: {
        borderColor: borderColor,
        strokeDashArray: 4,
        xaxis: { lines: { show: true } },
        yaxis: { lines: { show: true } }
      },
      tooltip: { shared: true, intersect: false }
    }).render();
  }

  // Status-wise - Donut Chart
  const statusEl = document.querySelector('#statusWiseChart');
  if (statusEl && data.statusWise && data.statusWise.length) {
    const statusColors = {
      'Accepted': successColor,
      'Cancelled': '#ea5455',
      'Pending': warningColor
    };
    const colors = data.statusWise.map(s => statusColors[s.name] || primaryColor);

    new ApexCharts(statusEl, {
      chart: {
        type: 'donut',
        height: 280
      },
      labels: data.statusWise.map(s => s.name),
      colors: colors,
      series: data.statusWise.map(s => s.total),
      legend: {
        position: 'bottom',
        labels: { colors: labelColor }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '65%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total',
                color: labelColor,
                formatter: function (w) {
                  return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                }
              }
            }
          }
        }
      },
      dataLabels: { enabled: true }
    }).render();
  }

  // Lead Type-wise - Pie Chart
  const leadTypeEl = document.querySelector('#leadTypeWiseChart');
  if (leadTypeEl && data.leadTypeWise && data.leadTypeWise.length) {
    const typeColors = {
      'Hot': '#ea5455',
      'Warm': warningColor,
      'Cold': infoColor
    };
    const colors = data.leadTypeWise.map(s => typeColors[s.name] || primaryColor);

    new ApexCharts(leadTypeEl, {
      chart: {
        type: 'pie',
        height: 280
      },
      labels: data.leadTypeWise.map(s => s.name),
      colors: colors,
      series: data.leadTypeWise.map(s => s.total),
      legend: {
        position: 'bottom',
        labels: { colors: labelColor }
      },
      dataLabels: { enabled: true }
    }).render();
  }

  // Source-wise - Bar Chart
  const sourceEl = document.querySelector('#sourceWiseChart');
  if (sourceEl && data.sourceWise && data.sourceWise.length) {
    new ApexCharts(sourceEl, {
      chart: {
        type: 'bar',
        height: 280,
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          horizontal: true,
          barHeight: '60%',
          borderRadius: 4
        }
      },
      colors: [primaryColor],
      dataLabels: { 
        enabled: true,
        textAnchor: 'start',
        style: { colors: ['#fff'] },
        formatter: function (val, opt) {
          return val;
        },
        offsetX: 0
      },
      series: [{ name: 'Enquiries', data: data.sourceWise.map(s => s.total) }],
      xaxis: {
        categories: data.sourceWise.map(s => s.name),
        labels: { style: { colors: labelColor } }
      },
      yaxis: {
        labels: { style: { colors: labelColor } }
      },
      grid: {
        borderColor: borderColor,
        strokeDashArray: 4
      }
    }).render();
  }

  // Monthly Accepted vs Cancelled - Stacked Bar Chart
  const monthlyEl = document.querySelector('#monthlyStatusChart');
  if (monthlyEl && data.monthlyLabels) {
    new ApexCharts(monthlyEl, {
      chart: {
        type: 'bar',
        height: 280,
        stacked: true,
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          columnWidth: '45%',
          borderRadius: 4
        }
      },
      colors: [warningColor, successColor, '#ea5455'],
      series: [
        { name: 'Pending', data: data.monthlyPending },
        { name: 'Accepted', data: data.monthlyAccepted },
        { name: 'Cancelled', data: data.monthlyCancelled }
      ],
      xaxis: {
        categories: data.monthlyLabels,
        labels: { style: { colors: labelColor } }
      },
      yaxis: {
        labels: { style: { colors: labelColor } }
      },
      legend: {
        position: 'top',
        horizontalAlign: 'left',
        labels: { colors: labelColor }
      },
      dataLabels: { enabled: false }
    }).render();
  }
})();
