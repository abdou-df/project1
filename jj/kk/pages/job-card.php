<?php
// Include database connection
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// إنشاء اتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// Get settings from database
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$mechanic = isset($_GET['mechanic']) ? (int)$_GET['mechanic'] : 0;
$customer = isset($_GET['customer']) ? (int)$_GET['customer'] : 0;
$vehicle = isset($_GET['vehicle']) ? (int)$_GET['vehicle'] : 0;
$service = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'a.date DESC';
$view = isset($_GET['view']) ? $_GET['view'] : 'table';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;  // ضمان أن الصفحة لا تقل عن 1
$perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10; // ضمان أن عدد العناصر في الصفحة لا يقل عن 1
$offset = ($page - 1) * $perPage;

// Base query for job cards (using appointments table)
$baseQuery = "
    FROM appointments a
    JOIN customers c ON a.customer_id = c.id
    JOIN vehicles v ON a.vehicle_id = v.id
    JOIN services s ON a.service_id = s.id
    LEFT JOIN users u ON a.user_id = u.id
    WHERE 1=1
";

// Add filters to query
$params = [];

if (!empty($status)) {
    $baseQuery .= " AND a.status = :status";
    $params[':status'] = $status;
}

if ($mechanic > 0) {
    $baseQuery .= " AND a.user_id = :mechanic";
    $params[':mechanic'] = $mechanic;
}

if ($customer > 0) {
    $baseQuery .= " AND a.customer_id = :customer";
    $params[':customer'] = $customer;
}

if ($vehicle > 0) {
    $baseQuery .= " AND a.vehicle_id = :vehicle";
    $params[':vehicle'] = $vehicle;
}

if ($service > 0) {
    $baseQuery .= " AND a.service_id = :service";
    $params[':service'] = $service;
}

if (!empty($dateFrom)) {
    $baseQuery .= " AND a.date >= :date_from";
    $params[':date_from'] = $dateFrom;
}

if (!empty($dateTo)) {
    $baseQuery .= " AND a.date <= :date_to";
    $params[':date_to'] = $dateTo;
}

if (!empty($search)) {
    $baseQuery .= " AND (
        a.id LIKE :search 
        OR CONCAT(c.first_name, ' ', c.last_name) LIKE :search 
        OR v.license_plate LIKE :search 
        OR v.vin LIKE :search
        OR s.name LIKE :search
    )";
    $params[':search'] = "%$search%";
}

// Count total records for pagination
$countQuery = "SELECT COUNT(*) as total " . $baseQuery;
$stmt = $pdo->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$totalRecords = $stmt->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Get job cards with pagination and sorting
$query = "
    SELECT 
        a.id, 
        a.date, 
        a.start_time, 
        a.end_time, 
        a.status, 
        a.notes,
        c.id as customer_id, 
        CONCAT(c.first_name, ' ', c.last_name) as customer_name,
        v.id as vehicle_id, 
        v.make, 
        v.model, 
        v.year, 
        v.license_plate, 
        v.vin,
        s.id as service_id, 
        s.name as service_name, 
        s.price as service_price,
        s.duration as service_duration,
        u.id as mechanic_id, 
        CONCAT(u.first_name, ' ', u.last_name) as mechanic_name,
        (SELECT COUNT(*) FROM invoices WHERE appointment_id = a.id) as has_invoice
    " . $baseQuery . "
    ORDER BY " . $sort . "
    LIMIT :offset, :limit
";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT); // الآن قيمة offset مضمونة أن تكون رقماً موجباً أو صفر
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$jobCards = $stmt->fetchAll();

// Get statistics
$statsQuery = "
    SELECT 
        COUNT(*) as total_jobs,
        SUM(CASE WHEN a.status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_count,
        SUM(CASE WHEN a.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
        SUM(CASE WHEN a.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
        SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
        SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
        SUM(CASE WHEN a.date < CURDATE() AND a.status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) as overdue_count
    FROM appointments a
";
$stats = $pdo->query($statsQuery)->fetch();

// Get all mechanics for filter dropdown
$mechanics = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role = 'mechanic' ORDER BY name")->fetchAll();

// Get all customers for filter dropdown
$customers = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM customers ORDER BY name")->fetchAll();

// Get all services for filter dropdown
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();

// Function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'scheduled':
            return 'badge-primary';
        case 'confirmed':
            return 'badge-info';
        case 'in_progress':
            return 'badge-warning';
        case 'completed':
            return 'badge-success';
        case 'cancelled':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}

// Function to get status in Arabic
function getStatusInArabic($status) {
    switch ($status) {
        case 'scheduled':
            return 'مجدول';
        case 'confirmed':
            return 'مؤكد';
        case 'in_progress':
            return 'قيد التنفيذ';
        case 'completed':
            return 'مكتمل';
        case 'cancelled':
            return 'ملغى';
        default:
            return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بطاقات العمل - <?php echo htmlspecialchars($settings['garage_name']); ?></title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
    <link rel="stylesheet" href="assets/css/job_cards.css">
</head>     


<body>
    <div class="container">
        <!-- Header Section -->
        <header class="main-header">
            <div class="header-left">
                <h1>بطاقات العمل</h1>
                <p>إدارة ومتابعة جميع أعمال الصيانة</p>
            </div>
            <div class="header-right">
                <a href="pages/create_job_card.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إنشاء بطاقة عمل جديدة
                </a>
            </div>
        </header>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_jobs']; ?></h3>
                    <p>إجمالي بطاقات العمل</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon scheduled">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['scheduled_count']; ?></h3>
                    <p>مجدولة</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon in-progress">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['in_progress_count']; ?></h3>
                    <p>قيد التنفيذ</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['completed_count']; ?></h3>
                    <p>مكتملة</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon overdue">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['overdue_count']; ?></h3>
                    <p>متأخرة</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="filter-section">
            <form id="filterForm" action="job_cards.php" method="GET">
                <div class="search-box">
                    <input type="text" name="search" placeholder="بحث عن رقم البطاقة، العميل، لوحة الترخيص..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filters">
                    <div class="filter-group">
                        <label for="status">الحالة</label>
                        <select name="status" id="status">
                            <option value="">جميع الحالات</option>
                            <option value="scheduled" <?php echo $status === 'scheduled' ? 'selected' : ''; ?>>مجدولة</option>
                            <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>مؤكدة</option>
                            <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>قيد التنفيذ</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>مكتملة</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>ملغاة</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="mechanic">الميكانيكي</label>
                        <select name="mechanic" id="mechanic">
                            <option value="0">جميع الميكانيكيين</option>
                            <?php foreach ($mechanics as $mech): ?>
                                <option value="<?php echo $mech['id']; ?>" <?php echo $mechanic == $mech['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mech['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="customer">العميل</label>
                        <select name="customer" id="customer">
                            <option value="0">جميع العملاء</option>
                            <?php foreach ($customers as $cust): ?>
                                <option value="<?php echo $cust['id']; ?>" <?php echo $customer == $cust['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cust['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="service">الخدمة</label>
                        <select name="service" id="service">
                            <option value="0">جميع الخدمات</option>
                            <?php foreach ($services as $serv): ?>
                                <option value="<?php echo $serv['id']; ?>" <?php echo $service == $serv['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($serv['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group date-range">
                        <label>نطاق التاريخ</label>
                        <div class="date-inputs">
                            <input type="date" name="date_from" placeholder="من" value="<?php echo $dateFrom; ?>">
                            <span>إلى</span>
                            <input type="date" name="date_to" placeholder="إلى" value="<?php echo $dateTo; ?>">
                        </div>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> تطبيق الفلتر
                    </button>
                    <a href="job_cards.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> إعادة ضبط
                    </a>
                </div>
                
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
                <input type="hidden" name="per_page" value="<?php echo $perPage; ?>">
                <input type="hidden" name="page" value="1">
            </form>
        </div>

        <!-- View Toggle and Bulk Actions -->
        <div class="view-controls">
            <div class="view-toggle">
                <button class="view-btn <?php echo $view === 'table' ? 'active' : ''; ?>" data-view="table">
                    <i class="fas fa-list"></i> جدول
                </button>
                <button class="view-btn <?php echo $view === 'cards' ? 'active' : ''; ?>" data-view="cards">
                    <i class="fas fa-th-large"></i> بطاقات
                </button>
                <button class="view-btn <?php echo $view === 'kanban' ? 'active' : ''; ?>" data-view="kanban">
                    <i class="fas fa-columns"></i> كانبان
                </button>
            </div>
            
            <div class="bulk-actions">
                <span id="selectedCount">0</span> محدد
                <div class="action-buttons">
                    <button id="bulkStatusBtn" class="btn btn-sm btn-outline" disabled>
                        <i class="fas fa-exchange-alt"></i> تغيير الحالة
                    </button>
                    <button id="bulkAssignBtn" class="btn btn-sm btn-outline" disabled>
                        <i class="fas fa-user-plus"></i> تعيين ميكانيكي
                    </button>
                    <button id="bulkDeleteBtn" class="btn btn-sm btn-outline btn-danger" disabled>
                        <i class="fas fa-trash"></i> حذف
                    </button>
                    <button id="bulkExportBtn" class="btn btn-sm btn-outline" disabled>
                        <i class="fas fa-file-export"></i> تصدير
                    </button>
                </div>
            </div>
        </div>

        <!-- Table View -->
        <div id="tableView" class="view-container <?php echo $view === 'table' ? 'active' : ''; ?>">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="checkbox-col">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th class="sortable" data-sort="a.id">رقم البطاقة</th>
                            <th class="sortable" data-sort="customer_name">العميل</th>
                            <th class="sortable" data-sort="CONCAT(v.make, ' ', v.model)">المركبة</th>
                            <th>لوحة الترخيص</th>
                            <th class="sortable" data-sort="service_name">الخدمة</th>
                            <th class="sortable" data-sort="mechanic_name">الميكانيكي</th>
                            <th class="sortable" data-sort="a.date">التاريخ</th>
                            <th class="sortable" data-sort="a.status">الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($jobCards) > 0): ?>
                            <?php foreach ($jobCards as $card): ?>
                                <tr data-id="<?php echo $card['id']; ?>">
                                    <td>
                                        <input type="checkbox" class="row-checkbox" data-id="<?php echo $card['id']; ?>">
                                    </td>
                                    <td>
                                        <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>" class="job-id">
                                            #<?php echo $card['id']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="customer_details.php?id=<?php echo $card['customer_id']; ?>">
                                            <?php echo htmlspecialchars($card['customer_name']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="vehicle_details.php?id=<?php echo $card['vehicle_id']; ?>">
                                            <?php echo htmlspecialchars($card['year'] . ' ' . $card['make'] . ' ' . $card['model']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($card['license_plate']); ?></td>
                                    <td><?php echo htmlspecialchars($card['service_name']); ?></td>
                                    <td>
                                        <?php if ($card['mechanic_id']): ?>
                                            <?php echo htmlspecialchars($card['mechanic_name']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">غير معين</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($card['date'], $settings['date_format']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo getStatusBadgeClass($card['status']); ?>">
                                            <?php echo getStatusInArabic($card['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-dropdown">
                                            <button class="btn-icon dropdown-toggle">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>" class="dropdown-item">
                                                    <i class="fas fa-eye"></i> عرض التفاصيل
                                                </a>
                                                <a href="#" class="dropdown-item edit-job-card" data-id="<?php echo $card['id']; ?>">
                                                    <i class="fas fa-edit"></i> تعديل
                                                </a>
                                                <a href="#" class="dropdown-item change-status" data-id="<?php echo $card['id']; ?>">
                                                    <i class="fas fa-exchange-alt"></i> تغيير الحالة
                                                </a>
                                                <a href="#" class="dropdown-item print-job-card" data-id="<?php echo $card['id']; ?>">
                                                    <i class="fas fa-print"></i> طباعة
                                                </a>
                                                <a href="export_job_card.php?id=<?php echo $card['id']; ?>&format=pdf" class="dropdown-item">
                                                    <i class="fas fa-file-pdf"></i> تصدير PDF
                                                </a>
                                                <?php if ($card['status'] === 'completed' && $card['has_invoice'] == 0): ?>
                                                    <a href="create_invoice.php?job_card_id=<?php echo $card['id']; ?>" class="dropdown-item">
                                                        <i class="fas fa-file-invoice-dollar"></i> إنشاء فاتورة
                                                    </a>
                                                <?php endif; ?>
                                                <div class="dropdown-divider"></div>
                                                <a href="#" class="dropdown-item text-danger delete-job-card" data-id="<?php echo $card['id']; ?>">
                                                    <i class="fas fa-trash"></i> حذف
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="no-data">
                                    <div class="no-data-message">
                                        <i class="fas fa-clipboard-list"></i>
                                        <h3>لا توجد بطاقات عمل</h3>
                                        <p>لم يتم العثور على بطاقات عمل تطابق معايير البحث</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <div class="pagination-info">
                        عرض <?php echo $offset + 1; ?> إلى <?php echo min($offset + $perPage, $totalRecords); ?> من <?php echo $totalRecords; ?> بطاقة
                    </div>
                    <div class="pagination-controls">
                        <a href="#" class="page-link <?php echo $page <= 1 ? 'disabled' : ''; ?>" data-page="<?php echo $page - 1; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($startPage + 4, $totalPages);
                        
                        if ($endPage - $startPage < 4 && $startPage > 1) {
                            $startPage = max(1, $endPage - 4);
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <a href="#" class="page-link <?php echo $i == $page ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <a href="#" class="page-link <?php echo $page >= $totalPages ? 'disabled' : ''; ?>" data-page="<?php echo $page + 1; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </div>
                    <div class="per-page-selector">
                        <label for="perPage">عرض</label>
                        <select id="perPage" name="per_page">
                            <option value="10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $perPage == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $perPage == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                        <span>لكل صفحة</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cards View -->
        <div id="cardsView" class="view-container <?php echo $view === 'cards' ? 'active' : ''; ?>">
            <div class="cards-grid">
                <?php if (count($jobCards) > 0): ?>
                    <?php foreach ($jobCards as $card): ?>
                        <div class="job-card" data-id="<?php echo $card['id']; ?>">
                            <div class="card-header">
                                <div class="card-checkbox">
                                    <input type="checkbox" class="row-checkbox" data-id="<?php echo $card['id']; ?>">
                                </div>
                                <div class="card-id">
                                    <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>">#<?php echo $card['id']; ?></a>
                                </div>
                                <div class="card-status">
                                    <span class="status-badge <?php echo getStatusBadgeClass($card['status']); ?>">
                                        <?php echo getStatusInArabic($card['status']); ?>
                                    </span>
                                </div>
                                <div class="card-actions">
                                    <button class="btn-icon dropdown-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>" class="dropdown-item">
                                            <i class="fas fa-eye"></i> عرض التفاصيل
                                        </a>
                                        <a href="#" class="dropdown-item edit-job-card" data-id="<?php echo $card['id']; ?>">
                                            <i class="fas fa-edit"></i> تعديل
                                        </a>
                                        <a href="#" class="dropdown-item change-status" data-id="<?php echo $card['id']; ?>">
                                            <i class="fas fa-exchange-alt"></i> تغيير الحالة
                                        </a>
                                        <a href="#" class="dropdown-item print-job-card" data-id="<?php echo $card['id']; ?>">
                                            <i class="fas fa-print"></i> طباعة
                                        </a>
                                        <a href="export_job_card.php?id=<?php echo $card['id']; ?>&format=pdf" class="dropdown-item">
                                            <i class="fas fa-file-pdf"></i> تصدير PDF
                                        </a>
                                        <?php if ($card['status'] === 'completed' && $card['has_invoice'] == 0): ?>
                                            <a href="create_invoice.php?job_card_id=<?php echo $card['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-file-invoice-dollar"></i> إنشاء فاتورة
                                            </a>
                                        <?php endif; ?>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item text-danger delete-job-card" data-id="<?php echo $card['id']; ?>">
                                            <i class="fas fa-trash"></i> حذف
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-section">
                                    <div class="card-label">العميل:</div>
                                    <div class="card-value">
                                        <a href="customer_details.php?id=<?php echo $card['customer_id']; ?>">
                                            <?php echo htmlspecialchars($card['customer_name']); ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-section">
                                    <div class="card-label">المركبة:</div>
                                    <div class="card-value">
                                        <a href="vehicle_details.php?id=<?php echo $card['vehicle_id']; ?>">
                                            <?php echo htmlspecialchars($card['year'] . ' ' . $card['make'] . ' ' . $card['model']); ?>
                                        </a>
                                        <div class="license-plate"><?php echo htmlspecialchars($card['license_plate']); ?></div>
                                    </div>
                                </div>
                                <div class="card-section">
                                    <div class="card-label">الخدمة:</div>
                                    <div class="card-value"><?php echo htmlspecialchars($card['service_name']); ?></div>
                                </div>
                                <div class="card-section">
                                    <div class="card-label">الميكانيكي:</div>
                                    <div class="card-value">
                                        <?php if ($card['mechanic_id']): ?>
                                            <?php echo htmlspecialchars($card['mechanic_name']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">غير معين</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-section">
                                    <div class="card-label">التاريخ:</div>
                                    <div class="card-value"><?php echo formatDate($card['date'], $settings['date_format']); ?></div>
                                </div>
                                <div class="card-section">
                                    <div class="card-label">الوقت:</div>
                                    <div class="card-value">
                                        <?php echo date('h:i A', strtotime($card['start_time'])); ?> - 
                                        <?php echo date('h:i A', strtotime($card['end_time'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>" class="btn btn-sm btn-outline">
                                    <i class="fas fa-eye"></i> عرض التفاصيل
                                </a>
                                <a href="#" class="btn btn-sm btn-outline edit-job-card" data-id="<?php echo $card['id']; ?>">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data-message">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>لا توجد بطاقات عمل</h3>
                        <p>لم يتم العثور على بطاقات عمل تطابق معايير البحث</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination for Cards View -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <div class="pagination-info">
                        عرض <?php echo $offset + 1; ?> إلى <?php echo min($offset + $perPage, $totalRecords); ?> من <?php echo $totalRecords; ?> بطاقة
                    </div>
                    <div class="pagination-controls">
                        <a href="#" class="page-link <?php echo $page <= 1 ? 'disabled' : ''; ?>" data-page="<?php echo $page - 1; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($startPage + 4, $totalPages);
                        
                        if ($endPage - $startPage < 4 && $startPage > 1) {
                            $startPage = max(1, $endPage - 4);
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <a href="#" class="page-link <?php echo $i == $page ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <a href="#" class="page-link <?php echo $page >= $totalPages ? 'disabled' : ''; ?>" data-page="<?php echo $page + 1; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </div>
                    <div class="per-page-selector">
                        <label for="perPageCards">عرض</label>
                        <select id="perPageCards" name="per_page">
                            <option value="10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $perPage == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $perPage == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                        <span>لكل صفحة</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kanban View -->
        <div id="kanbanView" class="view-container <?php echo $view === 'kanban' ? 'active' : ''; ?>">
            <div class="kanban-board">
                <div class="kanban-column" data-status="scheduled">
                    <div class="column-header">
                        <h3>مجدولة</h3>
                        <span class="counter"><?php echo $stats['scheduled_count']; ?></span>
                    </div>
                    <div class="column-body">
                        <?php 
                        $scheduledCards = array_filter($jobCards, function($card) {
                            return $card['status'] === 'scheduled';
                        });
                        
                        if (count($scheduledCards) > 0):
                            foreach ($scheduledCards as $card):
                        ?>
                            <div class="kanban-card" data-id="<?php echo $card['id']; ?>">
                                <div class="card-header">
                                    <div class="card-id">
                                        <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>">#<?php echo $card['id']; ?></a>
                                    </div>
                                    <div class="card-actions">
                                        <button class="btn-icon dropdown-toggle">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-eye"></i> عرض التفاصيل
                                            </a>
                                            <a href="#" class="dropdown-item edit-job-card" data-id="<?php echo $card['id']; ?>">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                            <a href="#" class="dropdown-item change-status" data-id="<?php echo $card['id']; ?>">
                                                <i class="fas fa-exchange-alt"></i> تغيير الحالة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="customer-info">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($card['customer_name']); ?>
                                    </div>
                                    <div class="vehicle-info">
                                        <i class="fas fa-car"></i>
                                        <?php echo htmlspecialchars($card['make'] . ' ' . $card['model']); ?>
                                    </div>
                                    <div class="service-info">
                                        <i class="fas fa-tools"></i>
                                        <?php echo htmlspecialchars($card['service_name']); ?>
                                    </div>
                                    <div class="date-info">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo formatDate($card['date'], $settings['date_format']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="empty-column">
                                <i class="fas fa-clipboard-list"></i>
                                <p>لا توجد بطاقات</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="kanban-column" data-status="confirmed">
                    <div class="column-header">
                        <h3>مؤكدة</h3>
                        <span class="counter"><?php echo $stats['confirmed_count']; ?></span>
                    </div>
                    <div class="column-body">
                        <?php 
                        $confirmedCards = array_filter($jobCards, function($card) {
                            return $card['status'] === 'confirmed';
                        });
                        
                        if (count($confirmedCards) > 0):
                            foreach ($confirmedCards as $card):
                        ?>
                            <div class="kanban-card" data-id="<?php echo $card['id']; ?>">
                                <div class="card-header">
                                    <div class="card-id">
                                        <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>">#<?php echo $card['id']; ?></a>
                                    </div>
                                    <div class="card-actions">
                                        <button class="btn-icon dropdown-toggle">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-eye"></i> عرض التفاصيل
                                            </a>
                                            <a href="#" class="dropdown-item edit-job-card" data-id="<?php echo $card['id']; ?>">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                            <a href="#" class="dropdown-item change-status" data-id="<?php echo $card['id']; ?>">
                                                <i class="fas fa-exchange-alt"></i> تغيير الحالة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="customer-info">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($card['customer_name']); ?>
                                    </div>
                                    <div class="vehicle-info">
                                        <i class="fas fa-car"></i>
                                        <?php echo htmlspecialchars($card['make'] . ' ' . $card['model']); ?>
                                    </div>
                                    <div class="service-info">
                                        <i class="fas fa-tools"></i>
                                        <?php echo htmlspecialchars($card['service_name']); ?>
                                    </div>
                                    <div class="date-info">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo formatDate($card['date'], $settings['date_format']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="empty-column">
                                <i class="fas fa-clipboard-list"></i>
                                <p>لا توجد بطاقات</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="kanban-column" data-status="in_progress">
                    <div class="column-header">
                        <h3>قيد التنفيذ</h3>
                        <span class="counter"><?php echo $stats['in_progress_count']; ?></span>
                    </div>
                    <div class="column-body">
                        <?php 
                        $inProgressCards = array_filter($jobCards, function($card) {
                            return $card['status'] === 'in_progress';
                        });
                        
                        if (count($inProgressCards) > 0):
                            foreach ($inProgressCards as $card):
                        ?>
                            <div class="kanban-card" data-id="<?php echo $card['id']; ?>">
                                <div class="card-header">
                                    <div class="card-id">
                                        <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>">#<?php echo $card['id']; ?></a>
                                    </div>
                                    <div class="card-actions">
                                        <button class="btn-icon dropdown-toggle">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-eye"></i> عرض التفاصيل
                                            </a>
                                            <a href="#" class="dropdown-item edit-job-card" data-id="<?php echo $card['id']; ?>">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                            <a href="#" class="dropdown-item change-status" data-id="<?php echo $card['id']; ?>">
                                                <i class="fas fa-exchange-alt"></i> تغيير الحالة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="customer-info">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($card['customer_name']); ?>
                                    </div>
                                    <div class="vehicle-info">
                                        <i class="fas fa-car"></i>
                                        <?php echo htmlspecialchars($card['make'] . ' ' . $card['model']); ?>
                                    </div>
                                    <div class="service-info">
                                        <i class="fas fa-tools"></i>
                                        <?php echo htmlspecialchars($card['service_name']); ?>
                                    </div>
                                    <div class="mechanic-info">
                                        <i class="fas fa-user-cog"></i>
                                        <?php echo $card['mechanic_id'] ? htmlspecialchars($card['mechanic_name']) : 'غير معين'; ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="empty-column">
                                <i class="fas fa-clipboard-list"></i>
                                <p>لا توجد بطاقات</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="kanban-column" data-status="completed">
                    <div class="column-header">
                        <h3>مكتملة</h3>
                        <span class="counter"><?php echo $stats['completed_count']; ?></span>
                    </div>
                    <div class="column-body">
                        <?php 
                        $completedCards = array_filter($jobCards, function($card) {
                            return $card['status'] === 'completed';
                        });
                        
                        if (count($completedCards) > 0):
                            foreach ($completedCards as $card):
                        ?>
                            <div class="kanban-card" data-id="<?php echo $card['id']; ?>">
                                <div class="card-header">
                                    <div class="card-id">
                                        <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>">#<?php echo $card['id']; ?></a>
                                    </div>
                                    <div class="card-actions">
                                        <button class="btn-icon dropdown-toggle">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-eye"></i> عرض التفاصيل
                                            </a>
                                            <?php if ($card['has_invoice'] == 0): ?>
                                                <a href="create_invoice.php?job_card_id=<?php echo $card['id']; ?>" class="dropdown-item">
                                                    <i class="fas fa-file-invoice-dollar"></i> إنشاء فاتورة
                                                </a>
                                            <?php endif; ?>
                                            <a href="#" class="dropdown-item print-job-card" data-id="<?php echo $card['id']; ?>">
                                                <i class="fas fa-print"></i> طباعة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="customer-info">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($card['customer_name']); ?>
                                    </div>
                                    <div class="vehicle-info">
                                        <i class="fas fa-car"></i>
                                        <?php echo htmlspecialchars($card['make'] . ' ' . $card['model']); ?>
                                    </div>
                                    <div class="service-info">
                                        <i class="fas fa-tools"></i>
                                        <?php echo htmlspecialchars($card['service_name']); ?>
                                    </div>
                                    <div class="invoice-info">
                                        <?php if ($card['has_invoice'] > 0): ?>
                                            <i class="fas fa-file-invoice-dollar text-success"></i> تم إنشاء الفاتورة
                                        <?php else: ?>
                                            <i class="fas fa-file-invoice text-warning"></i> لم يتم إنشاء الفاتورة
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="empty-column">
                                <i class="fas fa-clipboard-list"></i>
                                <p>لا توجد بطاقات</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="kanban-column" data-status="cancelled">
                    <div class="column-header">
                        <h3>ملغاة</h3>
                        <span class="counter"><?php echo $stats['cancelled_count']; ?></span>
                    </div>
                    <div class="column-body">
                        <?php 
                        $cancelledCards = array_filter($jobCards, function($card) {
                            return $card['status'] === 'cancelled';
                        });
                        
                        if (count($cancelledCards) > 0):
                            foreach ($cancelledCards as $card):
                        ?>
                            <div class="kanban-card" data-id="<?php echo $card['id']; ?>">
                                <div class="card-header">
                                    <div class="card-id">
                                        <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>">#<?php echo $card['id']; ?></a>
                                    </div>
                                    <div class="card-actions">
                                        <button class="btn-icon dropdown-toggle">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="index.php?page=job_card_details&id=<?php echo $card['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-eye"></i> عرض التفاصيل
                                            </a>
                                            <a href="#" class="dropdown-item change-status" data-id="<?php echo $card['id']; ?>">
                                                <i class="fas fa-exchange-alt"></i> تغيير الحالة
                                            </a>
                                            <a href="#" class="dropdown-item delete-job-card" data-id="<?php echo $card['id']; ?>">
                                                <i class="fas fa-trash"></i> حذف
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="customer-info">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($card['customer_name']); ?>
                                    </div>
                                    <div class="vehicle-info">
                                        <i class="fas fa-car"></i>
                                        <?php echo htmlspecialchars($card['make'] . ' ' . $card['model']); ?>
                                    </div>
                                    <div class="service-info">
                                        <i class="fas fa-tools"></i>
                                        <?php echo htmlspecialchars($card['service_name']); ?>
                                    </div>
                                    <div class="date-info">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo formatDate($card['date'], $settings['date_format']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <div class="empty-column">
                                <i class="fas fa-clipboard-list"></i>
                                <p>لا توجد بطاقات</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Change Status Modal -->
    <div class="modal" id="changeStatusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>تغيير حالة بطاقة العمل</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="changeStatusForm">
                    <input type="hidden" id="statusJobCardId" name="job_card_id">
                    <div class="form-group">
                        <label for="newStatus">الحالة الجديدة</label>
                        <select id="newStatus" name="status" class="form-control" required>
                            <option value="scheduled">مجدولة</option>
                            <option value="confirmed">مؤكدة</option>
                            <option value="in_progress">قيد التنفيذ</option>
                            <option value="completed">مكتملة</option>
                            <option value="cancelled">ملغاة</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="statusNote">ملاحظات (اختياري)</label>
                        <textarea id="statusNote" name="note" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveStatusBtn">حفظ</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>تأكيد الحذف</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>هل أنت متأكد من رغبتك في حذف بطاقة العمل هذه؟ هذا الإجراء لا يمكن التراجع عنه.</p>
                </div>
                <input type="hidden" id="deleteJobCardId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">حذف</button>
            </div>
        </div>
    </div>

    <!-- Bulk Status Change Modal -->
    <div class="modal" id="bulkStatusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>تغيير حالة بطاقات العمل المحددة</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="bulkStatusForm">
                    <input type="hidden" id="bulkJobCardIds" name="job_card_ids">
                    <div class="form-group">
                        <label for="bulkNewStatus">الحالة الجديدة</label>
                        <select id="bulkNewStatus" name="status" class="form-control" required>
                            <option value="scheduled">مجدولة</option>
                            <option value="confirmed">مؤكدة</option>
                            <option value="in_progress">قيد التنفيذ</option>
                            <option value="completed">مكتملة</option>
                            <option value="cancelled">ملغاة</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bulkStatusNote">ملاحظات (اختياري)</label>
                        <textarea id="bulkStatusNote" name="note" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveBulkStatusBtn">حفظ</button>
            </div>
        </div>
    </div>

    <!-- Bulk Assign Mechanic Modal -->
    <div class="modal" id="bulkAssignModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>تعيين ميكانيكي لبطاقات العمل المحددة</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="bulkAssignForm">
                    <input type="hidden" id="bulkAssignJobCardIds" name="job_card_ids">
                    <div class="form-group">
                        <label for="bulkMechanicId">الميكانيكي</label>
                        <select id="bulkMechanicId" name="mechanic_id" class="form-control" required>
                            <option value="">اختر ميكانيكي</option>
                            <?php foreach ($mechanics as $mech): ?>
                                <option value="<?php echo $mech['id']; ?>">
                                    <?php echo htmlspecialchars($mech['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveBulkAssignBtn">حفظ</button>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div class="modal" id="bulkDeleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>تأكيد حذف متعدد</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>هل أنت متأكد من رغبتك في حذف بطاقات العمل المحددة؟ هذا الإجراء لا يمكن التراجع عنه.</p>
                </div>
                <input type="hidden" id="bulkDeleteJobCardIds">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">حذف</button>
            </div>
        </div>
    </div>

    <script src="assets/js/job_cards.js"></script>
    <script>
        // إضافة سلوك الانتقال إلى زر إنشاء بطاقة العمل 
        document.getElementById('createJobCardBtn')?.addEventListener('click', function() {
            window.location.href = 'index.php?page=create_job_card';
        });
    </script>
</body>
</html>