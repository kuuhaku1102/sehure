/**
 * 相場検索ページ：検索 / フィルタ / 並べ替え / スパークライン / 履歴グラフ
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var table = document.getElementById('tmf-souba-table');
    if (!table) return;

    var tbody = table.querySelector('tbody');
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('.tmf-souba__row'));
    var q = document.getElementById('tmf-souba-q');
    var sortSel = document.getElementById('tmf-souba-sort');
    var chips = Array.prototype.slice.call(document.querySelectorAll('.tmf-chip'));
    var shown = document.getElementById('tmf-souba-shown');
    var empty = document.getElementById('tmf-souba-empty');
    var trendFilter = 'all';

    /* --- スパークライン描画 --- */
    function drawSpark(canvas, series, dir) {
      if (!canvas || !series || series.length < 2) return;
      var ctx = canvas.getContext('2d');
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      var w = canvas.width = 120 * dpr, h = canvas.height = 40 * dpr;
      canvas.style.width = '120px'; canvas.style.height = '40px';
      ctx.scale(dpr, dpr);
      w = 120; h = 40;
      var min = Math.min.apply(null, series), max = Math.max.apply(null, series);
      var range = (max - min) || 1;
      var pad = 4;
      var stepX = (w - pad * 2) / (series.length - 1);
      var pts = series.map(function (v, i) {
        return [pad + i * stepX, h - pad - ((v - min) / range) * (h - pad * 2)];
      });
      var color = dir === 'up' ? '#ff5c5c' : (dir === 'down' ? '#43c6ff' : '#ffcf5c');
      // 塗り
      var grad = ctx.createLinearGradient(0, 0, 0, h);
      grad.addColorStop(0, hexA(color, 0.35));
      grad.addColorStop(1, hexA(color, 0));
      ctx.beginPath();
      ctx.moveTo(pts[0][0], h - pad);
      pts.forEach(function (p) { ctx.lineTo(p[0], p[1]); });
      ctx.lineTo(pts[pts.length - 1][0], h - pad);
      ctx.closePath();
      ctx.fillStyle = grad; ctx.fill();
      // 線
      ctx.beginPath();
      pts.forEach(function (p, i) { i ? ctx.lineTo(p[0], p[1]) : ctx.moveTo(p[0], p[1]); });
      ctx.strokeStyle = color; ctx.lineWidth = 1.6; ctx.lineJoin = 'round'; ctx.stroke();
      // 終点
      var last = pts[pts.length - 1];
      ctx.beginPath(); ctx.arc(last[0], last[1], 2.4, 0, Math.PI * 2);
      ctx.fillStyle = color; ctx.fill();
    }
    function hexA(hex, a) {
      var n = parseInt(hex.slice(1), 16);
      return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + a + ')';
    }

    function trendOf(row) { return row.getAttribute('data-trend'); }

    rows.forEach(function (row) {
      var canvas = row.querySelector('.tmf-spark');
      var raw = row.getAttribute('data-series') || '';
      var series = raw.split(',').map(Number).filter(function (n) { return n > 0; });
      drawSpark(canvas, series, trendOf(row) === 'up' ? 'up' : (trendOf(row) === 'down' ? 'down' : 'flat'));
    });

    /* --- フィルタ適用 --- */
    function apply() {
      var term = (q.value || '').trim().toLowerCase();
      var count = 0;
      rows.forEach(function (row) {
        var okText = !term || row.getAttribute('data-name').indexOf(term) !== -1;
        var okTrend = trendFilter === 'all' || row.getAttribute('data-trend') === trendFilter;
        var visible = okText && okTrend;
        row.style.display = visible ? '' : 'none';
        if (visible) count++;
      });
      if (shown) shown.textContent = count;
      if (empty) empty.hidden = count !== 0;
    }

    /* --- 並べ替え --- */
    function sortRows() {
      var mode = sortSel.value;
      var sorted = rows.slice().sort(function (a, b) {
        switch (mode) {
          case 'price-asc':  return num(a, 'price') - num(b, 'price');
          case 'price-desc': return num(b, 'price') - num(a, 'price');
          case 'fc-desc':    return num(b, 'fc') - num(a, 'fc');
          case 'fc-asc':     return num(a, 'fc') - num(b, 'fc');
          case 'name-asc':   return a.getAttribute('data-sort-name').localeCompare(b.getAttribute('data-sort-name'), 'ja');
          default: return 0;
        }
      });
      sorted.forEach(function (r) { tbody.appendChild(r); });
    }
    function num(row, attr) { return parseFloat(row.getAttribute('data-' + attr)) || 0; }

    /* --- イベント --- */
    var t;
    q.addEventListener('input', function () { clearTimeout(t); t = setTimeout(apply, 120); });
    sortSel.addEventListener('change', sortRows);
    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        chips.forEach(function (c) { c.classList.remove('is-active'); });
        chip.classList.add('is-active');
        trendFilter = chip.getAttribute('data-trend');
        apply();
      });
    });

    /* --- 行クリックで長期履歴グラフを展開 --- */
    rows.forEach(function (row) {
      row.querySelector('.col-card').addEventListener('click', function () {
        toggleDetail(row);
      });
    });

    function toggleDetail(row) {
      var next = row.nextElementSibling;
      if (next && next.classList.contains('tmf-souba__detail')) {
        next.parentNode.removeChild(next);
        return;
      }
      // 他の展開を閉じる
      var open = tbody.querySelector('.tmf-souba__detail');
      if (open) open.parentNode.removeChild(open);

      var code = row.getAttribute('data-code');
      var tr = document.createElement('tr');
      tr.className = 'tmf-souba__detail';
      var td = document.createElement('td');
      td.colSpan = 6;
      td.innerHTML = '<div class="tmf-souba__detail-inner"><canvas class="tmf-bigchart" height="200"></canvas><p class="tmf-souba__loading">読み込み中…</p></div>';
      tr.appendChild(td);
      row.parentNode.insertBefore(tr, row.nextSibling);

      if (!window.TMF_SOUBA || !window.TMF_SOUBA.ajax) {
        renderBig(td, seriesFrom(row));
        return;
      }
      fetch(window.TMF_SOUBA.ajax + '?action=tmf_history&code=' + encodeURIComponent(code))
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.success && res.data && res.data.length > 1) {
            renderBig(td, res.data.map(function (x) { return x.p; }), res.data.map(function (x) { return x.d; }));
          } else {
            renderBig(td, seriesFrom(row));
          }
        })
        .catch(function () { renderBig(td, seriesFrom(row)); });
    }

    function seriesFrom(row) {
      return (row.getAttribute('data-series') || '').split(',').map(Number).filter(function (n) { return n > 0; });
    }

    function renderBig(td, data, labels) {
      var loading = td.querySelector('.tmf-souba__loading');
      if (loading) loading.remove();
      var canvas = td.querySelector('.tmf-bigchart');
      if (!canvas || !data || data.length < 2) { if (loading) loading.textContent = '履歴データが不足しています。'; return; }
      var ctx = canvas.getContext('2d');
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      var W = canvas.clientWidth || 600, H = 200;
      canvas.width = W * dpr; canvas.height = H * dpr; ctx.scale(dpr, dpr);
      var min = Math.min.apply(null, data), max = Math.max.apply(null, data), range = (max - min) || 1;
      var padL = 56, padR = 12, padT = 14, padB = 22;
      var stepX = (W - padL - padR) / (data.length - 1);
      function X(i) { return padL + i * stepX; }
      function Y(v) { return padT + (1 - (v - min) / range) * (H - padT - padB); }
      // グリッド + 目盛
      ctx.strokeStyle = 'rgba(255,255,255,.08)'; ctx.fillStyle = 'rgba(255,255,255,.5)';
      ctx.font = '10px sans-serif'; ctx.textAlign = 'right';
      for (var g = 0; g <= 4; g++) {
        var val = min + (range * g / 4), y = Y(val);
        ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(W - padR, y); ctx.stroke();
        ctx.fillText('¥' + Math.round(val).toLocaleString('ja-JP'), padL - 6, y + 3);
      }
      // 塗り + 線
      var grad = ctx.createLinearGradient(0, padT, 0, H - padB);
      grad.addColorStop(0, 'rgba(255,59,71,.30)'); grad.addColorStop(1, 'rgba(255,59,71,0)');
      ctx.beginPath(); ctx.moveTo(X(0), H - padB);
      data.forEach(function (v, i) { ctx.lineTo(X(i), Y(v)); });
      ctx.lineTo(X(data.length - 1), H - padB); ctx.closePath(); ctx.fillStyle = grad; ctx.fill();
      ctx.beginPath();
      data.forEach(function (v, i) { i ? ctx.lineTo(X(i), Y(v)) : ctx.moveTo(X(i), Y(v)); });
      ctx.strokeStyle = '#ff3b47'; ctx.lineWidth = 2; ctx.lineJoin = 'round'; ctx.stroke();
    }

    sortRows();
    apply();
  });
})();
