<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-head"><div class="container"><h1>Sản phẩm từ REST API</h1>
<p class="mb-0 text-muted small">Dữ liệu tải bằng <code>fetch()</code> từ <code><?= site_url('api/products') ?></code></p></div></div>
<div class="container section">
    <div class="d-flex gap-2 mb-4">
        <input id="q" class="form-control" style="max-width:320px" placeholder="Tìm theo tên / mã...">
        <select id="cat" class="form-select" style="max-width:220px"><option value="">Tất cả danh mục</option></select>
    </div>
    <div id="state" class="text-muted">Đang tải...</div>
    <div class="row g-3" id="grid"></div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
  var API = <?= json_encode(site_url('api')) ?>;
  var grid = document.getElementById('grid'), state = document.getElementById('state');
  var q = document.getElementById('q'), cat = document.getElementById('cat'), t;
  var fmt = function (n) { return new Intl.NumberFormat('vi-VN').format(n) + ' đ'; };
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

  fetch(API + '/categories').then(function (r) { return r.json(); }).then(function (j) {
    j.data.forEach(function (c) { var o = document.createElement('option'); o.value = c.id; o.textContent = c.name; cat.appendChild(o); });
  });

  function load() {
    state.textContent = 'Đang tải...';
    var p = new URLSearchParams(); if (q.value) p.set('keyword', q.value); if (cat.value) p.set('category', cat.value);
    fetch(API + '/products?' + p).then(function (r) { return r.json(); }).then(function (j) {
      state.textContent = j.data.length ? j.data.length + ' sản phẩm' : 'Không có sản phẩm phù hợp.';
      grid.innerHTML = j.data.map(function (x) {
        return '<div class="col-6 col-md-4 col-lg-3"><a class="p-card" href="' + esc(x.url) + '">' +
          '<div class="p-thumb">' + (x.image ? '<img src="' + esc(x.image) + '" alt="">' : '') + '</div>' +
          '<div class="p-body"><span class="p-cat">' + esc(x.category) + '</span><h3 class="p-name">' + esc(x.name) + '</h3>' +
          '<div class="p-price">' + fmt(x.price) + '</div></div></a></div>';
      }).join('');
    }).catch(function () { state.textContent = 'Không tải được dữ liệu.'; });
  }
  q.addEventListener('input', function () { clearTimeout(t); t = setTimeout(load, 300); });
  cat.addEventListener('change', load);
  load();
})();
</script>
<?= $this->endSection() ?>
