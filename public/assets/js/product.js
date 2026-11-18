/* Trang chi tiết sản phẩm: bộ chọn MÀU + SIZE, đổi ảnh theo màu, kiểm tra tồn kho */
(function () {
  'use strict';

  var variants = window.NOVA_VARIANTS || [];
  var elSwatches = document.getElementById('swatches');
  var elSizes = document.getElementById('sizes');
  var elColorName = document.getElementById('colorName');
  var elNote = document.getElementById('stockNote');
  var elVariant = document.getElementById('variantId');
  var elError = document.getElementById('pickError');
  var elMain = document.getElementById('mainImage');
  var elQty = document.getElementById('qty');
  var form = document.getElementById('addForm');
  if (!elSwatches || !elSizes || !form) return;

  var state = { color: null, size: null };

  // ---- gom danh sách màu / size (giữ đúng thứ tự trong CSDL) ----
  var colors = [], sizes = [];
  variants.forEach(function (v) {
    var c = colors.find(function (x) { return x.name === v.color; });
    if (!c) { c = { name: v.color, hex: v.hex, image: '', stock: 0 }; colors.push(c); }
    if (!c.image && v.image) c.image = v.image;
    c.stock += v.stock;
    if (sizes.indexOf(v.size) === -1) sizes.push(v.size);
  });

  function find(color, size) {
    return variants.find(function (v) { return v.color === color && v.size === size; }) || null;
  }

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text) n.textContent = text;
    return n;
  }

  function showImage(src) {
    if (!elMain || !src) return;
    elMain.src = src;
    document.querySelectorAll('#thumbs button').forEach(function (b) {
      b.classList.toggle('active', b.getAttribute('data-src') === src);
    });
  }

  function renderColors() {
    elSwatches.innerHTML = '';
    colors.forEach(function (c) {
      var b = el('button', 'swatch' + (c.stock <= 0 ? ' soldout' : '') + (state.color === c.name ? ' active' : ''));
      b.type = 'button';
      b.style.setProperty('--c', c.hex);
      b.title = c.name + (c.stock <= 0 ? ' (hết hàng)' : '');
      b.setAttribute('role', 'radio');
      b.setAttribute('aria-checked', state.color === c.name ? 'true' : 'false');
      b.setAttribute('aria-label', 'Màu ' + c.name);
      b.addEventListener('click', function () { pickColor(c.name); });
      elSwatches.appendChild(b);
    });
    elColorName.textContent = state.color || 'Chọn màu';
  }

  function renderSizes() {
    elSizes.innerHTML = '';
    sizes.forEach(function (s) {
      var v = state.color ? find(state.color, s) : null;
      var available = v && v.stock > 0;
      var b = el('button', 'size-btn' + (state.size === s ? ' active' : ''), s);
      b.type = 'button';
      b.disabled = state.color ? !available : false;
      b.setAttribute('role', 'radio');
      b.setAttribute('aria-checked', state.size === s ? 'true' : 'false');
      if (state.color && !available) b.title = 'Hết hàng';
      b.addEventListener('click', function () { pickSize(s); });
      elSizes.appendChild(b);
    });
  }

  function update() {
    var v = (state.color && state.size) ? find(state.color, state.size) : null;
    elVariant.value = v ? v.id : '';
    elNote.className = 'stock-note';

    if (v) {
      if (v.stock <= 0) { elNote.textContent = 'Phiên bản này đã hết hàng.'; elNote.classList.add('out'); }
      else if (v.stock <= 5) { elNote.textContent = 'Chỉ còn ' + v.stock + ' sản phẩm – nhanh tay!'; elNote.classList.add('low'); }
      else { elNote.textContent = 'Còn hàng (' + v.stock + ' sản phẩm)'; elNote.classList.add('ok'); }
      elQty.max = v.stock;
      if (parseInt(elQty.value, 10) > v.stock) elQty.value = Math.max(1, v.stock);
      elError.style.display = 'none';
    } else {
      elNote.textContent = '';
      elQty.removeAttribute('max');
    }
  }

  function pickColor(name) {
    state.color = name;
    var c = colors.find(function (x) { return x.name === name; });
    if (c && c.image) showImage(c.image);
    // giữ size đang chọn nếu còn hàng ở màu mới
    var keep = state.size && find(name, state.size);
    if (!keep || keep.stock <= 0) state.size = null;
    // nếu chỉ có đúng 1 size còn hàng (vd. Free size) thì chọn luôn
    var avail = variants.filter(function (v) { return v.color === name && v.stock > 0; });
    if (!state.size && avail.length === 1) state.size = avail[0].size;
    renderColors(); renderSizes(); update();
  }

  function pickSize(s) {
    state.size = s;
    renderSizes(); update();
  }

  // ---- ảnh nhỏ ----
  document.querySelectorAll('#thumbs button').forEach(function (b) {
    b.addEventListener('click', function () { showImage(b.getAttribute('data-src')); });
  });

  // ---- số lượng ----
  function clampQty() {
    var max = parseInt(elQty.max, 10) || 99;
    var n = parseInt(elQty.value, 10);
    if (isNaN(n) || n < 1) n = 1;
    elQty.value = Math.min(n, max);
  }
  document.getElementById('qMinus').addEventListener('click', function () { elQty.value = (parseInt(elQty.value, 10) || 1) - 1; clampQty(); });
  document.getElementById('qPlus').addEventListener('click', function () { elQty.value = (parseInt(elQty.value, 10) || 1) + 1; clampQty(); });
  elQty.addEventListener('change', clampQty);

  // ---- bắt buộc chọn màu + size trước khi thêm giỏ ----
  form.addEventListener('submit', function (e) {
    if (!elVariant.value) {
      e.preventDefault();
      elError.textContent = !state.color ? 'Vui lòng chọn màu sắc.' : 'Vui lòng chọn size.';
      elError.style.display = 'block';
      (elSizes.parentNode || document).scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      return;
    }
    clampQty();
  });

  // ---- khởi tạo: chọn sẵn màu đầu tiên còn hàng ----
  var first = colors.find(function (c) { return c.stock > 0; }) || colors[0];
  if (first) pickColor(first.name); else { renderColors(); renderSizes(); }
})();
