SET NAMES utf8mb4;

CREATE TABLE categories (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(200) NOT NULL,
    Description TEXT
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE suppliers (
    SupplierID INT AUTO_INCREMENT PRIMARY KEY,
    SupplierName VARCHAR(200) NOT NULL,
    ContactName VARCHAR(100),
    Address VARCHAR(200),
    City VARCHAR(100),
    PostalCode VARCHAR(20),
    Country VARCHAR(100),
    Phone VARCHAR(20)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE customers (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerName VARCHAR(100) NOT NULL,
    ContactName VARCHAR(100),
    Address VARCHAR(200),
    City VARCHAR(100),
    PostalCode VARCHAR(20),
    Country VARCHAR(100),
    Phone VARCHAR(20),
    Email VARCHAR(255) NULL,
    PasswordHash VARCHAR(255) NULL,
    IsActive BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT uq_customers_email UNIQUE (Email)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE employees (
    EmployeeID INT AUTO_INCREMENT PRIMARY KEY,
    LastName VARCHAR(50) NOT NULL,
    FirstName VARCHAR(50) NOT NULL,
    BirthDate DATE,
    Photo VARCHAR(255),
    Notes TEXT,
    Email VARCHAR(255) NULL,
    PasswordHash VARCHAR(255) NULL,
    Role VARCHAR(20) NOT NULL DEFAULT 'staff',
    CONSTRAINT uq_employees_email UNIQUE (Email),
    CONSTRAINT chk_employees_role CHECK (Role IN ('admin', 'staff'))
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE shippers (
    ShipperID INT AUTO_INCREMENT PRIMARY KEY,
    ShipperName VARCHAR(100) NOT NULL,
    Phone VARCHAR(20)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

/* Tồn kho nay nằm ở product_variants (theo size + màu). */
CREATE TABLE products (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    ProductCode VARCHAR(50) NOT NULL UNIQUE,
    ProductName VARCHAR(255) NOT NULL,
    Description TEXT,
    Unit VARCHAR(20),
    Price DECIMAL(12,2) NOT NULL DEFAULT 0,
    IsActive BOOLEAN NOT NULL DEFAULT TRUE,
    SupplierID INT NOT NULL,
    CategoryID INT NOT NULL,
    CONSTRAINT chk_products_price CHECK (Price >= 0),
    CONSTRAINT fk_products_supplier FOREIGN KEY (SupplierID) REFERENCES suppliers(SupplierID),
    CONSTRAINT fk_products_category FOREIGN KEY (CategoryID) REFERENCES categories(CategoryID)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE product_images (
    ProductImageID INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    ImageFile VARCHAR(255) NOT NULL,
    AltText VARCHAR(255),
    IsPrimary BOOLEAN NOT NULL DEFAULT FALSE,
    SortOrder INT NOT NULL DEFAULT 0,
    CONSTRAINT uq_product_image UNIQUE (ProductID, ImageFile),
    CONSTRAINT chk_product_image_sort CHECK (SortOrder >= 0),
    CONSTRAINT fk_product_images_product FOREIGN KEY (ProductID)
        REFERENCES products(ProductID) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

/* MỚI: mỗi dòng = 1 tổ hợp size + màu của một sản phẩm */
CREATE TABLE product_variants (
    VariantID INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    Size VARCHAR(20) NOT NULL,
    Color VARCHAR(50) NOT NULL,
    ColorHex CHAR(7) NOT NULL DEFAULT '#111111',
    StockQuantity INT NOT NULL DEFAULT 0,
    ImageFile VARCHAR(255) NULL,
    SortOrder INT NOT NULL DEFAULT 0,
    CONSTRAINT uq_variant UNIQUE (ProductID, Size, Color),
    CONSTRAINT chk_variant_stock CHECK (StockQuantity >= 0),
    CONSTRAINT fk_variant_product FOREIGN KEY (ProductID)
        REFERENCES products(ProductID) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    OrderDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    TotalAmount DECIMAL(12,2) NOT NULL DEFAULT 0,
    Status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    CustomerID INT NOT NULL,
    EmployeeID INT,
    ShipperID INT,
    CONSTRAINT chk_orders_total CHECK (TotalAmount >= 0),
    CONSTRAINT fk_orders_customer FOREIGN KEY (CustomerID) REFERENCES customers(CustomerID),
    CONSTRAINT fk_orders_employee FOREIGN KEY (EmployeeID) REFERENCES employees(EmployeeID),
    CONSTRAINT fk_orders_shipper FOREIGN KEY (ShipperID) REFERENCES shippers(ShipperID)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

/* VariantID / Size / Color: lưu lại size & màu khách đã chọn lúc đặt hàng */
CREATE TABLE orderdetail (
    OrderDetailID INT AUTO_INCREMENT PRIMARY KEY,
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(12,2) NOT NULL,
    OrderID INT NOT NULL,
    ProductID INT NOT NULL,
    VariantID INT NULL,
    Size VARCHAR(20) NULL,
    Color VARCHAR(50) NULL,
    CONSTRAINT chk_orderdetail_quantity CHECK (Quantity > 0),
    CONSTRAINT chk_orderdetail_unitprice CHECK (UnitPrice >= 0),
    CONSTRAINT fk_orderdetail_order FOREIGN KEY (OrderID) REFERENCES orders(OrderID) ON DELETE CASCADE,
    CONSTRAINT fk_orderdetail_product FOREIGN KEY (ProductID) REFERENCES products(ProductID),
    CONSTRAINT fk_orderdetail_variant FOREIGN KEY (VariantID) REFERENCES product_variants(VariantID) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

/* MỚI: nhận xét & đánh giá của khách hàng (mỗi khách 1 bài / sản phẩm) */
CREATE TABLE reviews (
    ReviewID INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    CustomerID INT NOT NULL,
    Rating TINYINT NOT NULL,
    Title VARCHAR(120) NULL,
    Comment TEXT NOT NULL,
    IsVisible BOOLEAN NOT NULL DEFAULT TRUE,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt DATETIME NULL,
    CONSTRAINT uq_review_once UNIQUE (ProductID, CustomerID),
    CONSTRAINT chk_review_rating CHECK (Rating BETWEEN 1 AND 5),
    CONSTRAINT fk_review_product FOREIGN KEY (ProductID) REFERENCES products(ProductID) ON DELETE CASCADE,
    CONSTRAINT fk_review_customer FOREIGN KEY (CustomerID) REFERENCES customers(CustomerID) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
