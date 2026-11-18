/* Admin: bảng size/màu của sản phẩm */
(function () {
  'use strict';
  var body = document.getElementById('varBody');
  if (!body) return;
  var idx = 0;

  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML.replace(/"/g, '&quot;'); }

  function addRow(r) {
    r = r || { id: 0, size: '', color: '', hex: '#111111', stock: 10, image: '' };
    var i = idx++;
    var imgCell = '';
    if (window.CAN_PICK_IMAGE) {
      var opts = '<option value="">— Ảnh chính —</option>' + (window.PRODUCT_IMAGES || []).map(function (f) {
        return '<option value="' + esc(f) + '"' + (f === r.image ? ' selected' : '') + '>' + esc(f) + '</option>';
      }).join('');
      imgCell = '<td><select class="form-select form-select-sm" name="variants[' + i + '][image]">' + opts + '</select></td>';
    }
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td><input type="hidden" name="variants[' + i + '][id]" value="' + (r.id || 0) + '">' +
      '<input class="form-control form-control-sm" name="variants[' + i + '][size]" maxlength="20" required value="' + esc(r.size) + '"></td>' +
      '<td><input class="form-control form-control-sm" name="variants[' + i + '][color]" maxlength="50" required value="' + esc(r.color) + '"></td>' +
      '<td><input class="form-control form-control-color" type="color" name="variants[' + i + '][hex]" value="' + esc(r.hex || '#111111') + '"></td>' +
      '<td><input class="form-control form-control-sm" type="number" min="0" name="variants[' + i + '][stock]" value="' + (parseInt(r.stock, 10) || 0) + '" style="width:90px"></td>' +
      imgCell +
      '<td><button type="button" class="btn btn-sm btn-outline-danger" aria-label="Xóa dòng"><i class="bi bi-x-lg"></i></button></td>';
    tr.querySelector('button').addEventListener('click', function () { tr.remove(); });
    body.appendChild(tr);
  }

  function existing() {
    var set = {};
    body.querySelectorAll('tr').forEach(function (tr) {
      var s = tr.querySelector('[name$="[size]"]').value.trim().toLowerCase();
      var c = tr.querySelector('[name$="[color]"]').value.trim().toLowerCase();
      set[s + '|' + c] = true;
    });
    return set;
  }

  (window.VARIANT_ROWS || []).forEach(addRow);
  if (!body.children.length) addRow();

  document.getElementById('addRow').addEventListener('click', function () { addRow(); });

  document.getElementById('genBtn').addEventListener('click', function () {
    var sizes = document.getElementById('genSizes').value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    var colors = document.getElementById('genColors').value.split(',').map(function (s) {
      var p = s.split(':'); var hex = (p[1] || '').trim();
      return { name: (p[0] || '').trim(), hex: /^#[0-9a-fA-F]{6}$/.test(hex) ? hex : '#111111' };
    }).filter(function (c) { return c.name; });
    var stock = parseInt(document.getElementById('genStock').value, 10) || 0;
    if (!sizes.length || !colors.length) { alert('Hãy nhập ít nhất 1 size và 1 màu.'); return; }

    // xóa dòng trống mặc định trước khi thêm
    body.querySelectorAll('tr').forEach(function (tr) {
      if (!tr.querySelector('[name$="[size]"]').value.trim() && !tr.querySelector('[name$="[color]"]').value.trim()) tr.remove();
    });
    var have = existing();
    colors.forEach(function (c) {
      sizes.forEach(function (s) {
        if (have[s.toLowerCase() + '|' + c.name.toLowerCase()]) return;
        addRow({ id: 0, size: s, color: c.name, hex: c.hex, stock: stock, image: '' });
      });
    });
  });
})();
