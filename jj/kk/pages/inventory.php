<?php
// Database connection
$host = 'localhost';
$dbname = 'garage_db';
$username = 'root'; // تغيير هذا حسب إعدادات قاعدة البيانات الخاصة بك
$password = ''; // تغيير هذا حسب إعدادات قاعدة البيانات الخاصة بك

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// Handle stock updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_stock':
                $itemId = (int)$_POST['item_id'];
                $newQuantity = (int)$_POST['quantity'];
                $note = isset($_POST['note']) ? trim($_POST['note']) : '';
                
                try {
                    // بدء المعاملة
                    $pdo->beginTransaction();
                    
                    // تحديث كمية المخزون
                    $stmt = $pdo->prepare("UPDATE inventory SET quantity = :quantity, updated_at = NOW() WHERE id = :id");
                    $stmt->execute([
                        'quantity' => $newQuantity,
                        'id' => $itemId
                    ]);
                    
                    // إضافة سجل في جدول معاملات المخزون
                    $stmt = $pdo->prepare("
                        INSERT INTO inventory_transactions 
                        (inventory_id, transaction_type, quantity, reference_type, notes, created_by) 
                        VALUES (:inventory_id, :transaction_type, :quantity, :reference_type, :notes, :created_by)
                    ");
                    
                    // الحصول على الكمية القديمة لتحديد نوع المعاملة
                    $oldQuantityStmt = $pdo->prepare("SELECT quantity FROM inventory WHERE id = :id");
                    $oldQuantityStmt->execute(['id' => $itemId]);
                    $oldQuantity = $oldQuantityStmt->fetchColumn();
                    
                    $transactionType = ($newQuantity > $oldQuantity) ? 'purchase' : 'adjustment';
                    $quantityChange = abs($newQuantity - $oldQuantity);
                    
                    $stmt->execute([
                        'inventory_id' => $itemId,
                        'transaction_type' => $transactionType,
                        'quantity' => $quantityChange,
                        'reference_type' => 'manual_update',
                        'notes' => $note,
                        'created_by' => 1 // يفترض أن المستخدم الحالي هو المسؤول (ID = 1)
                    ]);
                    
                    // تأكيد المعاملة
                    $pdo->commit();
                    
                    // رسالة نجاح
                    $successMessage = "تم تحديث المخزون بنجاح.";
                } catch (PDOException $e) {
                    // التراجع عن المعاملة في حالة حدوث خطأ
                    $pdo->rollBack();
                    $errorMessage = "حدث خطأ أثناء تحديث المخزون: " . $e->getMessage();
                }
                break;
                
            case 'delete':
                $itemId = (int)$_POST['item_id'];
                
                try {
                    // حذف العنصر من المخزون
                    $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = :id");
                    $stmt->execute(['id' => $itemId]);
                    
                    // رسالة نجاح
                    $successMessage = "تم حذف العنصر بنجاح.";
                } catch (PDOException $e) {
                    $errorMessage = "حدث خطأ أثناء حذف العنصر: " . $e->getMessage();
                }
                break;
                
            case 'add_item':
                // استخراج البيانات من النموذج
                $name = trim($_POST['name']);
                $partNumber = trim($_POST['part_number']);
                $category = trim($_POST['category']);
                $description = trim($_POST['description']);
                $quantity = (int)$_POST['quantity'];
                $unit = trim($_POST['unit']);
                $costPrice = (float)$_POST['cost_price'];
                $sellingPrice = (float)$_POST['selling_price'];
                $reorderLevel = (int)$_POST['reorder_level'];
                $location = trim($_POST['location']);
                $supplierId = (int)$_POST['supplier_id'];
                
                try {
                    // إضافة عنصر جديد إلى المخزون
                    $stmt = $pdo->prepare("
                        INSERT INTO inventory 
                        (name, part_number, category, description, quantity, unit, cost_price, selling_price, reorder_level, location, supplier_id) 
                        VALUES 
                        (:name, :part_number, :category, :description, :quantity, :unit, :cost_price, :selling_price, :reorder_level, :location, :supplier_id)
                    ");
                    
                    $stmt->execute([
                        'name' => $name,
                        'part_number' => $partNumber,
                        'category' => $category,
                        'description' => $description,
                        'quantity' => $quantity,
                        'unit' => $unit,
                        'cost_price' => $costPrice,
                        'selling_price' => $sellingPrice,
                        'reorder_level' => $reorderLevel,
                        'location' => $location,
                        'supplier_id' => $supplierId
                    ]);
                    
                    // إضافة سجل في جدول معاملات المخزون
                    $inventoryId = $pdo->lastInsertId();
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO inventory_transactions 
                        (inventory_id, transaction_type, quantity, reference_type, notes, created_by) 
                        VALUES (:inventory_id, :transaction_type, :quantity, :reference_type, :notes, :created_by)
                    ");
                    
                    $stmt->execute([
                        'inventory_id' => $inventoryId,
                        'transaction_type' => 'purchase',
                        'quantity' => $quantity,
                        'reference_type' => 'initial_stock',
                        'notes' => 'إضافة مخزون أولي',
                        'created_by' => 1 // يفترض أن المستخدم الحالي هو المسؤول (ID = 1)
                    ]);
                    
                    // رسالة نجاح
                    $successMessage = "تمت إضافة العنصر بنجاح.";
                } catch (PDOException $e) {
                    $errorMessage = "حدث خطأ أثناء إضافة العنصر: " . $e->getMessage();
                }
                break;
        }
    }
}

// Handle search and filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$supplierFilter = isset($_GET['supplier']) ? (int)$_GET['supplier'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// بناء استعلام SQL
$sql = "SELECT i.*, s.name as supplier_name 
        FROM inventory i 
        LEFT JOIN suppliers s ON i.supplier_id = s.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (i.name LIKE :search OR i.part_number LIKE :search OR i.category LIKE :search)";
    $params['search'] = "%$search%";
}

if (!empty($categoryFilter)) {
    $sql .= " AND i.category = :category";
    $params['category'] = $categoryFilter;
}

if (!empty($supplierFilter)) {
    $sql .= " AND i.supplier_id = :supplier_id";
    $params['supplier_id'] = $supplierFilter;
}

if (!empty($statusFilter)) {
    switch ($statusFilter) {
        case 'In Stock':
            $sql .= " AND i.quantity > i.reorder_level";
            break;
        case 'Low Stock':
            $sql .= " AND i.quantity > 0 AND i.quantity <= i.reorder_level";
            break;
        case 'Out of Stock':
            $sql .= " AND i.quantity <= 0";
            break;
    }
}

// إضافة الترتيب
$sql .= " ORDER BY i.name ASC";

// تنفيذ الاستعلام
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inventoryItems = $stmt->fetchAll();

// Pagination
$totalItems = count($inventoryItems);
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startIndex = ($currentPage - 1) * $itemsPerPage;
$paginatedItems = array_slice($inventoryItems, $startIndex, $itemsPerPage);

// Calculate low stock items
$lowStockItems = array_filter($inventoryItems, function($item) {
    return $item['quantity'] > 0 && $item['quantity'] <= $item['reorder_level'];
});

// Calculate out of stock items
$outOfStockItems = array_filter($inventoryItems, function($item) {
    return $item['quantity'] <= 0;
});

// Calculate total inventory value
$totalValue = array_reduce($inventoryItems, function($carry, $item) {
    return $carry + ($item['quantity'] * $item['selling_price']);
}, 0);

// Get unique categories
$categoriesStmt = $pdo->query("SELECT DISTINCT category FROM inventory ORDER BY category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Get suppliers for dropdown
$suppliersStmt = $pdo->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name");
$suppliers = $suppliersStmt->fetchAll();
?>

<!-- Start of HTML -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المخزون - نظام إدارة الورشة</title>
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <!--<link rel="stylesheet" href="../assets/css/style.css">  Link to general style -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <link rel="stylesheet" href="assets/css/sidebar.css">
   <!-- <link rel="stylesheet" href="../assets/css/inventory.css">  Link to inventory specific style -->
</head>
<body>
<div class="container-fluid py-4">

<!-- Success message if any -->
<?php if (isset($successMessage)): ?>
<div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
    <i class="fas fa-check-circle me-2"></i> <?php echo $successMessage; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Error message if any -->
<?php if (isset($errorMessage)): ?>
<div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $errorMessage; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Page header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h2 class="h3 text-dark fw-bold"><i class="fas fa-boxes me-2 text-primary"></i>إدارة المخزون</h2>
        <p class="text-muted mb-0">إدارة عناصر المخزون، تتبع مستويات المخزون، والمزيد</p>
    </div>
    <div class="d-flex align-items-center">
        <div class="me-3">
            <label class="custom-switch" title="تبديل الوضع الداكن">
                <input type="checkbox" id="darkModeToggle">
                <span class="switch-slider"></span>
            </label>
        </div>
        <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="fas fa-plus me-2"></i> إضافة عنصر جديد
        </button>
    </div>
</div>

<!-- Inventory stats -->
<div class="row mb-4 fade-in" style="animation-delay: 0.1s;">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">إجمالي العناصر</h6>
                        <h4 class="mb-0 fw-bold counter"><?php echo count($inventoryItems); ?></h4>
                        <small class="text-muted"><?php echo count($inventoryItems); ?> عنصر في المخزون</small>
                    </div>
                    <div class="bg-primary-light rounded-circle p-3">
                        <i class="fas fa-boxes text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">قيمة المخزون</h6>
                        <h4 class="mb-0 fw-bold counter">$<?php echo number_format($totalValue, 2); ?></h4>
                        <small class="text-muted">بناءً على المخزون الحالي</small>
                    </div>
                    <div class="bg-success-light rounded-circle p-3">
                        <i class="fas fa-dollar-sign text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">مخزون منخفض</h6>
                        <h4 class="mb-0 fw-bold counter"><?php echo count($lowStockItems); ?></h4>
                        <small class="text-muted">عناصر أقل من مستوى إعادة الطلب</small>
                    </div>
                    <div class="bg-warning-light rounded-circle p-3">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1 small">نفاد المخزون</h6>
                        <h4 class="mb-0 fw-bold counter"><?php echo count($outOfStockItems); ?></h4>
                        <small class="text-muted">عناصر بكمية صفر</small>
                    </div>
                    <div class="bg-danger-light rounded-circle p-3">
                        <i class="fas fa-times-circle text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and filter -->
<div class="card border-0 shadow-sm mb-4 fade-in" style="animation-delay: 0.2s;">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-primary"><i class="fas fa-filter me-2"></i>بحث وتصفية</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="بحث عن عناصر..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">جميع الفئات</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($categoryFilter === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="supplier" class="form-select">
                        <option value="">جميع الموردين</option>
                        <?php foreach($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['id']; ?>" <?php echo ($supplierFilter === (int)$supplier['id']) ? 'selected' : ''; ?>><?php echo $supplier['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="In Stock" <?php echo ($statusFilter === 'In Stock') ? 'selected' : ''; ?>>متوفر</option>
                        <option value="Low Stock" <?php echo ($statusFilter === 'Low Stock') ? 'selected' : ''; ?>>مخزون منخفض</option>
                        <option value="Out of Stock" <?php echo ($statusFilter === 'Out of Stock') ? 'selected' : ''; ?>>نفاد المخزون</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            <?php if (!empty($search) || !empty($categoryFilter) || !empty($supplierFilter) || !empty($statusFilter)): ?>
                <div  || !empty($supplierFilter) || !empty($statusFilter)): ?>
                <div class="mt-3">
                    <a href="index.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> مسح التصفية
                    </a>
                    <span class="ms-2 text-muted">
                        عرض <?php echo count($inventoryItems); ?> من <?php echo count($inventoryItems); ?> عنصر
                    </span>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Inventory table -->
<div class="card border-0 shadow-sm fade-in" style="animation-delay: 0.3s;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 text-primary"><i class="fas fa-list me-2"></i>عناصر المخزون</h5>
        <div>
            <button class="btn btn-sm btn-outline-primary me-2" id="exportBtn">
                <i class="fas fa-file-export me-1"></i> تصدير
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print();">
                <i class="fas fa-print me-1"></i> طباعة
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive responsive-table">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr class="bg-light">
                        <th width="40px">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll"></label>
                            </div>
                        </th>
                        <th>العنصر</th>
                        <th>رقم القطعة</th>
                        <th>الفئة</th>
                        <th>المورد</th>
                        <th>السعر</th>
                        <th>المخزون</th>
                        <th>الحالة</th>
                        <th width="120px">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($paginatedItems) > 0): ?>
                        <?php foreach ($paginatedItems as $item): ?>
                        <tr class="item-row <?php echo ($item['quantity'] > 0 && $item['quantity'] <= $item['reorder_level']) ? 'table-warning' : ''; ?>" data-id="<?php echo $item['id']; ?>">
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input item-checkbox" type="checkbox" value="<?php echo $item['id']; ?>">
                                    <label class="form-check-label"></label>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2 bg-light rounded">
                                        <span class="avatar-text text-primary"><?php echo substr($item['name'], 0, 1); ?></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                        <small class="text-muted">الموقع: <?php echo $item['location']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="text-monospace"><?php echo $item['part_number']; ?></span></td>
                            <td><?php echo $item['category']; ?></td>
                            <td><?php echo $item['supplier_name']; ?></td>
                            <td>$<?php echo number_format($item['selling_price'], 2); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></span>
                                    <?php
                                    // Calculate percentage of stock
                                    $max = max($item['quantity'], $item['reorder_level'] * 2);
                                    $percentage = ($max > 0) ? round(($item['quantity'] / $max) * 100) : 0;
                                    $bar_class = 'bg-success';
                                    
                                    if ($percentage <= 10) {
                                        $bar_class = 'bg-danger';
                                    } elseif ($percentage <= 50) {
                                        $bar_class = 'bg-warning';
                                    }
                                    ?>
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar <?php echo $bar_class; ?>" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                if ($item['quantity'] <= 0) {
                                    echo '<span class="badge bg-danger">نفاد المخزون</span>';
                                } elseif ($item['quantity'] <= $item['reorder_level']) {
                                    echo '<span class="badge bg-warning">مخزون منخفض</span>';
                                } else {
                                    echo '<span class="badge bg-success">متوفر</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link p-0" type="button" id="itemActions<?php echo $item['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="itemActions<?php echo $item['id']; ?>">
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editItemModal" data-item-id="<?php echo $item['id']; ?>"><i class="fas fa-edit me-2 text-info"></i>تعديل</a></li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#updateStockModal" data-item-id="<?php echo $item['id']; ?>" data-item-name="<?php echo $item['name']; ?>" data-item-quantity="<?php echo $item['quantity']; ?>"><i class="fas fa-layer-group me-2 text-primary"></i>تحديث المخزون</a></li>
                                        <li><a class="dropdown-item item-details-btn" href="#" data-item-id="<?php echo $item['id']; ?>"><i class="fas fa-info-circle me-2 text-success"></i>عرض التفاصيل</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger delete-item-btn" href="#" data-item-id="<?php echo $item['id']; ?>" data-item-name="<?php echo $item['name']; ?>"><i class="fas fa-trash me-2 text-danger"></i>حذف</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="py-5">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <h5>لم يتم العثور على عناصر</h5>
                                    <p class="text-muted">حاول تعديل البحث أو التصفية للعثور على ما تبحث عنه.</p>
                                    <a href="index.php" class="btn btn-outline-primary mt-2">مسح جميع عوامل التصفية</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (count($paginatedItems) > 0): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <div>
            <span class="text-muted">عرض <?php echo min($startIndex + 1, $totalItems); ?> إلى <?php echo min($startIndex + $itemsPerPage, $totalItems); ?> من <?php echo $totalItems; ?> عنصر</span>
        </div>
        <div>
            <button id="bulkActionBtn" class="btn btn-sm btn-outline-primary" disabled>
                <i class="fas fa-cog me-1"></i> إجراءات متعددة
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalItems > $itemsPerPage): ?>
<nav aria-label="Page navigation" class="mt-4 fade-in" style="animation-delay: 0.4s;">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage > 1) ? '?page=' . ($currentPage - 1) . (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . (isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '') . (isset($_GET['supplier']) ? '&supplier=' . urlencode($_GET['supplier']) : '') . (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '') : '#'; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php 
        $totalPages = ceil($totalItems / $itemsPerPage);
        $maxPagesToShow = 5;
        $startPage = max(1, min($currentPage - floor($maxPagesToShow / 2), $totalPages - $maxPagesToShow + 1));
        $endPage = min($startPage + $maxPagesToShow - 1, $totalPages);
        
        if ($startPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=1<?php echo (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . (isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '') . (isset($_GET['supplier']) ? '&supplier=' . urlencode($_GET['supplier']) : '') . (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''); ?>">1</a>
            </li>
            <?php if ($startPage > 2): ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#">...</a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?><?php echo (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . (isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '') . (isset($_GET['supplier']) ? '&supplier=' . urlencode($_GET['supplier']) : '') . (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''); ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        
        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#">...</a>
                </li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $totalPages; ?><?php echo (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . (isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '') . (isset($_GET['supplier']) ? '&supplier=' . urlencode($_GET['supplier']) : '') . (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''); ?>"><?php echo $totalPages; ?></a>
            </li>
        <?php endif; ?>
        
        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo ($currentPage < $totalPages) ? '?page=' . ($currentPage + 1) . (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . (isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '') . (isset($_GET['supplier']) ? '&supplier=' . urlencode($_GET['supplier']) : '') . (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '') : '#'; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">إضافة عنصر جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addItemForm" method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_item">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">اسم العنصر <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="part_number" class="form-label">رقم القطعة</label>
                            <input type="text" class="form-control" id="part_number" name="part_number">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label">الفئة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="category" name="category" list="categoryList" required>
                            <datalist id="categoryList">
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label for="supplier_id" class="form-label">المورد</label>
                            <select class="form-select" id="supplier_id" name="supplier_id">
                                <option value="">-- اختر المورد --</option>
                                <?php foreach($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['id']; ?>"><?php echo $supplier['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">الوصف</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="quantity" class="form-label">الكمية <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="unit" class="form-label">الوحدة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unit" name="unit" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cost_price" class="form-label">سعر التكلفة <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="cost_price" name="cost_price" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="selling_price" class="form-label">سعر البيع <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="selling_price" name="selling_price" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reorder_level" class="form-label">الحد الأدنى للكمية (مستوى إعادة الطلب) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="reorder_level" name="reorder_level" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="location" class="form-label">الموقع</label>
                            <input type="text" class="form-control" id="location" name="location">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إضافة العنصر</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div class="modal fade" id="updateStockModal" tabindex="-1" aria-labelledby="updateStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStockModalLabel">تحديث المخزون</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStockForm" method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" id="updateItemId" name="item_id" value="">
                    
                    <p>تحديث المخزون لـ <strong id="updateItemName"></strong></p>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">الكمية الحالية: <span id="currentQuantity"></span></label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" id="decreaseQty"><i class="fas fa-minus"></i></button>
                            <input type="number" class="form-control text-center" id="quantity" name="quantity" min="0" required>
                            <button type="button" class="btn btn-outline-secondary" id="increaseQty"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="note" class="form-label">ملاحظة (اختياري)</label>
                        <textarea class="form-control" id="note" name="note" rows="2" placeholder="أضف ملاحظة حول تحديث المخزون"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تحديث المخزون</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel">تعديل عنصر</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editItemForm" method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_item">
                    <input type="hidden" id="editItemId" name="item_id" value="">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">اسم العنصر <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_part_number" class="form-label">رقم القطعة</label>
                            <input type="text" class="form-control" id="edit_part_number" name="part_number">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_category" class="form-label">الفئة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_category" name="category" list="categoryList" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_supplier_id" class="form-label">المورد</label>
                            <select class="form-select" id="edit_supplier_id" name="supplier_id">
                                <option value="">-- اختر المورد --</option>
                                <?php foreach($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['id']; ?>"><?php echo $supplier['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">الوصف</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_quantity" class="form-label">الكمية <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_unit" class="form-label">الوحدة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_unit" name="unit" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_cost_price" class="form-label">سعر التكلفة <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_cost_price" name="cost_price" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_selling_price" class="form-label">سعر البيع <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_selling_price" name="selling_price" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_reorder_level" class="form-label">الحد الأدنى للكمية (مستوى إعادة الطلب) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_reorder_level" name="reorder_level" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_location" class="form-label">الموقع</label>
                            <input type="text" class="form-control" id="edit_location" name="location">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">تأكيد الحذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle text-danger fa-4x"></i>
                </div>
                <p>هل أنت متأكد من رغبتك في حذف <strong id="deleteItemName"></strong>؟ لا يمكن التراجع عن هذا الإجراء.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteItemForm" method="post" action="">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="deleteItemId" name="item_id" value="">
                    <button type="submit" class="btn btn-danger">حذف العنصر</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle (Required for dropdowns, modals, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Dark mode toggle
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    // Check for saved dark mode preference
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        darkModeToggle.checked = true;
    }
    
    // Toggle dark mode
    darkModeToggle.addEventListener('change', function() {
        if (this.checked) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', null);
        }
    });
    
    // Handle select all checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButton();
        });
    }
    
    // Update bulk action button state
    function updateBulkActionButton() {
        if (bulkActionBtn) {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            bulkActionBtn.disabled = checkedItems.length === 0;
        }
    }
    
    // Add event listeners to item checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionButton);
    });
    
    // Update stock modal
    const updateStockModal = document.getElementById('updateStockModal');
    if (updateStockModal) {
        updateStockModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.getAttribute('data-item-name');
            const itemQuantity = button.getAttribute('data-item-quantity');
            
            document.getElementById('updateItemId').value = itemId;
            document.getElementById('updateItemName').textContent = itemName;
            document.getElementById('currentQuantity').textContent = itemQuantity;
            document.getElementById('quantity').value = itemQuantity;
        });
    }
    
    // Quantity increment/decrement buttons
    const increaseQtyBtn = document.getElementById('increaseQty');
    const decreaseQtyBtn = document.getElementById('decreaseQty');
    
    if (increaseQtyBtn) {
        increaseQtyBtn.addEventListener('click', function() {
            const quantityInput = document.getElementById('quantity');
            quantityInput.value = parseInt(quantityInput.value) + 1;
        });
    }
    
    if (decreaseQtyBtn) {
        decreaseQtyBtn.addEventListener('click', function() {
            const quantityInput = document.getElementById('quantity');
            const newValue = parseInt(quantityInput.value) - 1;
            if (newValue >= 0) {
                quantityInput.value = newValue;
            }
        });
    }
    
    // Delete item confirmation
    const deleteButtons = document.querySelectorAll('.delete-item-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const itemId = this.getAttribute('data-item-id');
            const itemName = this.getAttribute('data-item-name');
            
            document.getElementById('deleteItemId').value = itemId;
            document.getElementById('deleteItemName').textContent = itemName;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        });
    });
    
    // Calculate profit margin on add/edit item form
    const costPriceInput = document.getElementById('cost_price');
    const sellingPriceInput = document.getElementById('selling_price');
    
    if (costPriceInput && sellingPriceInput) {
        function updateProfitMargin() {
            const costPrice = parseFloat(costPriceInput.value) || 0;
            const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
            
            if (costPrice > 0 && sellingPrice > 0) {
                const profitMargin = ((sellingPrice - costPrice) / sellingPrice) * 100;
                // You could display this somewhere in the form
                console.log(`Profit Margin: ${profitMargin.toFixed(2)}%`);
            }
        }
        
        costPriceInput.addEventListener('input', updateProfitMargin);
        sellingPriceInput.addEventListener('input', updateProfitMargin);
    }
    
    // Add animation to stats counters
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.innerText.replace(/[^\d]/g, ''));
        let count = 0;
        const duration = 1500;
        const increment = Math.ceil(target / (duration / 30));
        
        const timer = setInterval(() => {
            count += increment;
            if (count >= target) {
                counter.innerText = counter.innerText.includes('$') ? '$' + target.toLocaleString() : target.toLocaleString();
                clearInterval(timer);
            } else {
                counter.innerText = counter.innerText.includes('$') ? '$' + count.toLocaleString() : count.toLocaleString();
            }
        }, 30);
    });

    // Add event listener for the bulk action button
    const bulkActionButton = document.getElementById('bulkActionBtn');
    if (bulkActionButton) {
        bulkActionButton.addEventListener('click', function() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            if (checkedItems.length > 0) {
                const selectedIds = Array.from(checkedItems).map(cb => cb.value);
                // Placeholder action: Show selected IDs in an alert
                alert(`الإجراءات المتعددة ستطبق على العناصر ذات الـ ID: ${selectedIds.join(', ')}`);
                // TODO: Replace alert with actual bulk action logic (e.g., show dropdown/modal)
            } else {
                alert('يرجى تحديد عنصر واحد على الأقل لتطبيق الإجراءات المتعددة.');
            }
        });
    }

    // Edit Item Modal - Populate ID and basic fields (Placeholder)
    const editItemModal = document.getElementById('editItemModal');
    if (editItemModal) {
        editItemModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-item-id');
            
            // Set the item ID in the hidden field
            document.getElementById('editItemId').value = itemId;
            
            // --- Placeholder: Populate form fields --- 
            // In a real application, you would make an AJAX call here to get item details
            // and populate all fields (edit_name, edit_part_number, etc.)
            // For now, just populate the name field as a test
            document.getElementById('edit_name').value = "جاري تحميل بيانات العنصر " + itemId + "..."; 
            // Clear other fields or set placeholder text
            document.getElementById('edit_part_number').value = "...";
            document.getElementById('edit_category').value = "...";
            document.getElementById('edit_description').value = "...";
            document.getElementById('edit_quantity').value = 0;
            document.getElementById('edit_unit').value = "...";
            document.getElementById('edit_cost_price').value = 0;
            document.getElementById('edit_selling_price').value = 0;
            document.getElementById('edit_reorder_level').value = 0;
            document.getElementById('edit_location').value = "...";
            document.getElementById('edit_supplier_id').value = "";
            
            // Example AJAX call structure (to be implemented later):
            /*
            fetch('get_inventory_item.php?id=' + itemId)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        document.getElementById('editItemId').value = data.item.id;
                        document.getElementById('edit_name').value = data.item.name;
                        document.getElementById('edit_part_number').value = data.item.part_number;
                        document.getElementById('edit_category').value = data.item.category;
                        document.getElementById('edit_description').value = data.item.description;
                        document.getElementById('edit_quantity').value = data.item.quantity;
                        document.getElementById('edit_unit').value = data.item.unit;
                        document.getElementById('edit_cost_price').value = data.item.cost_price;
                        document.getElementById('edit_selling_price').value = data.item.selling_price;
                        document.getElementById('edit_reorder_level').value = data.item.reorder_level;
                        document.getElementById('edit_location').value = data.item.location;
                        document.getElementById('edit_supplier_id').value = data.item.supplier_id;
                    } else {
                        alert('خطأ في جلب بيانات العنصر.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching item data:', error);
                    alert('خطأ في الشبكة عند جلب بيانات العنصر.');
                });
            */
        });
    }
});
</script>
</div>
</body>
</html>