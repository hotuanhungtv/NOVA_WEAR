# NOVA Wear – CodeIgniter 4

Website bán quần áo NOVA Wear, chuyển từ PHP thuần sang **CodeIgniter 4** (MVC), kèm:

- **Chọn size & màu** ở trang sản phẩm (tồn kho riêng cho từng tổ hợp size + màu, đổi ảnh theo màu)
- **Nhận xét & đánh giá** của khách (1–5 sao, lọc theo sao, huy hiệu "Đã mua hàng", kiểm duyệt trong admin)
- **Sửa lỗi giao diện header** (xem bên dưới)

## Chạy bằng Docker

```bash
docker compose down -v      # nếu đã chạy bản cũ (để nạp lại CSDL mới)
docker compose up -d --build
```

Lần đầu container tự chạy `composer install` (cần Internet, mất 1–2 phút). Sau đó:

| Trang | Địa chỉ |
|---|---|
| Cửa hàng | http://localhost:8088 |
| Quản trị | http://localhost:8088/admin |
| phpMyAdmin | http://localhost:8081 |

| Tài khoản | Email | Mật khẩu |
|---|---|---|
| Khách thử | khach@novawear.local | Khach@123 |
| Admin | admin@novawear.local | (mật khẩu trong seed cũ của bạn) |
| Nhân viên | nhanvien1@novawear.local | (như seed cũ) |

Tài khoản "Khách thử" đã có 1 đơn **Hoàn thành** (áo thun Olive + quần short Đen) nên có thể thử viết đánh giá có huy hiệu "Đã mua hàng".

> Lỗi `require_once(/var/www/src/config/session.php)` bạn gặp là của **bản PHP thuần cũ** (mount sai thư mục). Bản này mount cả dự án vào `/var/www/html` và Apache trỏ vào `public/`.

## Chạy không dùng Docker

```bash
composer install            # tự chép file cấu hình chuẩn của CI4 (bin/setup.php)
cp env.example .env         # sửa baseURL + thông tin MySQL
# import database/schema.sql rồi database/seed.sql vào MySQL
php spark serve
```

## Đã sửa gì ở giao diện?

Lỗi trong ảnh chụp của bạn do header cũ ép 3 cột `25% / 50% / 25%`: cột phải quá hẹp nên chữ bị xuống dòng.

1. **Hotline** "Đặt hàng nhanh / 0900 000 000" bị bẻ nhiều dòng → dùng `white-space: nowrap`, rút gọn theo màn hình.
2. **Giỏ hàng**: badge số lượng đè lên giá tiền, giá bị tách "629.000 / đ" → badge gắn vào icon túi, giá không xuống dòng.
3. **Thanh topbar** bị cắt chữ → topbar cuộn đi bình thường, chỉ header + menu đen dính khi cuộn.
4. Header chia lưới `1fr | logo | 1fr`; dưới 1200px ẩn chữ hotline/tài khoản; dưới 992px có nút ☰ (offcanvas) và ô tìm kiếm riêng.
5. Viết lại toàn bộ CSS (`public/assets/css/nova.css`), bỏ các lớp ghi đè chồng chéo của bản cũ.

## Size & màu

- Bảng mới `product_variants` (Size, Color, ColorHex, StockQuantity, ImageFile). Tồn kho nay tính bằng tổng các biến thể (cột `products.StockQuantity` đã bỏ).
- Giỏ hàng lưu theo **biến thể**; đơn hàng (`orderdetail`) lưu `VariantID, Size, Color`.
- Đặt hàng dùng transaction + `SELECT ... FOR UPDATE`, trừ kho đúng biến thể; hủy đơn (khách hoặc admin) hoàn kho đúng biến thể.
- Admin → Sản phẩm → Sửa: bảng size/màu, có nút **"Tạo nhanh"** (vd. `S, M, L, XL` × `Đen:#111111, Trắng:#f4f4f1`) và gán ảnh riêng cho từng màu.
- Dữ liệu mẫu: mỗi sản phẩm có 1–3 màu; màu phụ dùng ảnh của sản phẩm cùng loại có sẵn (bạn thay bằng ảnh thật trong admin).

## Đánh giá

- Khách đăng nhập viết/sửa/xóa đánh giá của mình (mỗi khách 1 bài / sản phẩm).
- Trang sản phẩm: điểm trung bình, biểu đồ số sao (bấm để lọc), phân trang, tên khách được che (`Nguyễn M.A.`).
- Thẻ sản phẩm hiện sao + số đánh giá + chấm màu.
- Admin → **Đánh giá**: ẩn/hiện hoặc xóa bài (nhân viên và admin đều dùng được).
- Muốn **chỉ cho khách đã mua** mới được đánh giá: đặt `nova_reviewRequirePurchase: "true"` trong `compose.yaml` (hoặc `nova.reviewRequirePurchase = true` trong `.env`).

## Cấu trúc

```
app/Config/Routes.php, Filters.php     route + filter (customer / staff / adminonly) + CSRF toàn cục
app/Controllers/                       Home, Products, Reviews, Cart, Checkout, Orders, Auth, Account, Api
app/Controllers/Admin/                 Dashboard, Orders, Products, Categories, Shippers, Customers, Employees, Reviews
app/Models/                            ProductModel, ReviewModel
app/Libraries/CartService.php          giỏ hàng theo size/màu
app/Helpers/nova_helper.php            money(), stars(), mask_name()...
app/Views/                             layouts/, partials/, pages/, account/, admin/
public/assets/{css,js,img}             nova.css, admin.css, product.js, admin-product.js
database/schema.sql, seed.sql          CSDL mới
bin/setup.php                          chép file cấu hình mặc định của CI4 sau composer install
```

## Thay đổi so với bản cũ cần biết

- Địa chỉ đẹp: `/products`, `/products/12`, `/cart`, `/checkout`, `/orders`, `/admin/...` (không còn `.php`).
- Mật khẩu đăng ký tối thiểu **8 ký tự** (đồng bộ với trang đổi mật khẩu).
- Trang "đặt hàng thành công" chỉ xem được đơn **của chính mình** (bản cũ ai biết số đơn cũng xem được).
- Sản phẩm đã có trong đơn hàng khi "xóa" sẽ chuyển sang **Ngừng bán**.
