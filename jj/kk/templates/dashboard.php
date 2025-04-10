<?php
// Dashboard page

// Get current date
$currentDate = date('Y-m-d');

// In a real application, these would be fetched from the database
// For demonstration, we'll use dummy data
$stats = [
    'employees' => 830,
    'customers' => 25,
    'suppliers' => 830,
    'products' => 75,
    'sales' => 75,
    'services' => 10,
    'total_services' => 2200,
    'free_services' => 1100,
    'paid_services' => 1100
];

// Recent customers
$recentCustomers = [
    [
        'id' => 1,
        'name' => 'Bendial Joseph',
        'email' => 'bendial.joseph@gmail.com',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 2,
        'name' => 'Peter Parker',
        'email' => 'peter.parker@gmail.com',
        'image' => 'assets/images/default-user.png'
    ],
    [
        'id' => 3,
        'name' => 'Regina Cooper',
        'email' => 'regina.cooper@gmail.com',
        'image' => 'assets/images/default-user.png'
    ]
];

// Calendar events
$calendarEvents = [];
?>


<!-- Stats Cards -->
<div class="stats-dashboard">
    <!-- Row 1: Customers and Employees -->
    <div class="row g-4">
        <!-- Customers Stat -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-customer me-3">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">العملاء</h6>
                            <h2 class="mb-0 counter"><?php echo $stats['customers']; ?></h2>
                            <small class="text-success">
                                <i class="fa-solid fa-arrow-up me-1"></i> 8% هذا الشهر
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employees Stat -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-employees me-3">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">الموظفين</h6>
                            <h2 class="mb-0 counter"><?php echo $stats['employees']; ?></h2>
                            <small class="text-success">
                                <i class="fa-solid fa-arrow-up me-1"></i> 12% هذا الشهر
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Row 2: Sales and Suppliers -->
    <div class="row g-4 mt-4">
        <!-- Sales Stat -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-sales me-3">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">المبيعات</h6>
                            <h2 class="mb-0 counter"><?php echo $stats['sales']; ?></h2>
                            <small class="text-success">
                                <i class="fa-solid fa-arrow-up me-1"></i> 15% هذا الشهر
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Suppliers Stat -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-suppliers me-3">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">الموردين</h6>
                            <h2 class="mb-0 counter"><?php echo $stats['suppliers']; ?></h2>
                            <small class="text-success">
                                <i class="fa-solid fa-arrow-up me-1"></i> 5% هذا الشهر
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Row 3: Products and Services -->
    <div class="row g-4 mt-4">
        <!-- Products Stat -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-products me-3">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">المنتجات</h6>
                            <h2 class="mb-0 counter"><?php echo $stats['products']; ?></h2>
                            <div class="progress mt-2" style="height: 5px; width: 100px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">75% من سعة المخزون</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Services Stat -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-services me-3">
                            <i class="fa-solid fa-wrench"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">الخدمات</h6>
                            <h2 class="mb-0 counter"><?php echo $stats['services']; ?></h2>
                            <small class="text-success">
                                <i class="fa-solid fa-arrow-up me-1"></i> 10% هذا الشهر
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Services and Calendar Row -->
<div class="row g-4 mt-2">
    <!-- Services Stats -->
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="fa-solid fa-wrench me-2 text-primary"></i>الخدمات</h5>
                <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fa-solid fa-eye me-1"></i> عرض الكل
                </a>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="position-relative d-inline-block">
                        <!-- This would be a chart in a real application -->
                        <div class="position-relative chart-container" style="width: 200px; height: 200px;">
                            <div class="donut-chart-placeholder">
                                <div class="donut-segment" style="--segment-color: var(--primary-color); --segment-size: 50%"></div>
                                <div class="donut-segment" style="--segment-color: var(--warning-color); --segment-size: 50%; --segment-start: 50%"></div>
                            </div>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <h2 class="mb-0 counter"><?php echo $stats['total_services']; ?></h2>
                                <p class="text-muted small mb-0">إجمالي الخدمات</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="p-3 service-stat-box border-end">
                            <h4 class="text-primary mb-0 counter"><?php echo $stats['free_services']; ?></h4>
                            <p class="text-muted small mb-0">خدمات مجانية</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 service-stat-box">
                            <h4 class="text-warning mb-0 counter"><?php echo $stats['paid_services']; ?></h4>
                            <p class="text-muted small mb-0">خدمات مدفوعة</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Calendar -->
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="fa-solid fa-calendar-days me-2 text-primary"></i>التقويم</h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="fa-solid fa-plus me-1"></i> موعد جديد
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Calendar would go here in a real application -->
                <div class="calendar-placeholder">
                    <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-sm btn-link text-dark"><i class="fa-solid fa-chevron-left"></i></button>
                        <h6 class="mb-0">مارس 2025</h6>
                        <button class="btn btn-sm btn-link text-dark"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                    <div class="calendar-grid">
                        <!-- Calendar grid would be dynamically generated in a real app -->
                        <div class="calendar-days-header">
                            <div>أحد</div>
                            <div>إثنين</div>
                            <div>ثلاثاء</div>
                            <div>أربعاء</div>
                            <div>خميس</div>
                            <div>جمعة</div>
                            <div>سبت</div>
                        </div>
                        <div class="calendar-days">
                            <!-- Sample calendar days -->
                            <?php for($i = 1; $i <= 31; $i++): ?>
                            <div class="calendar-day <?php echo ($i == 26) ? 'today' : ''; ?>">
                                <span><?php echo $i; ?></span>
                                <?php if($i == 15 || $i == 22): ?>
                                <div class="calendar-event bg-primary"></div>
                                <?php endif; ?>
                                <?php if($i == 18): ?>
                                <div class="calendar-event bg-warning"></div>
                                <?php endif; ?>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities and Customers -->
<div class="row g-4 mt-2">
    <!-- Recent Activities -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>آخر النشاطات</h5>
                <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fa-solid fa-eye me-1"></i> عرض الكل
                </a>
            </div>
            <div class="card-body p-0">
                <div class="activity-timeline">
                    <div class="activity-item">
                        <div class="activity-icon bg-success">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="activity-content">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">تم إكمال خدمة صيانة</h6>
                                <small class="text-muted">منذ 25 دقيقة</small>
                            </div>
                            <p class="text-muted mb-0">تم إكمال صيانة سيارة تويوتا كامري 2022 للعميل أحمد محمد</p>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon bg-primary">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                        <div class="activity-content">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">طلب جديد</h6>
                                <small class="text-muted">منذ ساعتين</small>
                            </div>
                            <p class="text-muted mb-0">تم استلام طلب قطع غيار جديد من العميل خالد عبدالله</p>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon bg-info">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div class="activity-content">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">موعد جديد</h6>
                                <small class="text-muted">منذ 3 ساعات</small>
                            </div>
                            <p class="text-muted mb-0">تم تحديد موعد صيانة دورية لسيارة نيسان التيما 2023</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Customers -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="fa-solid fa-users me-2 text-primary"></i>آخر العملاء</h5>
                <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fa-solid fa-plus me-1"></i> إضافة عميل
                </a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-3 py-3 d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-text bg-primary">م</span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">محمد أحمد</h6>
                            <small class="text-muted">تويوتا كامري 2022</small>
                        </div>
                        <div>
                            <span class="badge bg-success rounded-pill">نشط</span>
                        </div>
                    </li>
                    
                    <li class="list-group-item px-3 py-3 d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-text bg-info">س</span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">سارة خالد</h6>
                            <small class="text-muted">هوندا أكورد 2021</small>
                        </div>
                        <div>
                            <span class="badge bg-warning rounded-pill">قيد الانتظار</span>
                        </div>
                    </li>
                    
                    <li class="list-group-item px-3 py-3 d-flex align-items-center">
                        <div class="avatar me-3">
                            <span class="avatar-text bg-danger">ع</span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">عبدالله محمد</h6>
                            <small class="text-muted">نيسان التيما 2023</small>
                        </div>
                        <div>
                            <span class="badge bg-success rounded-pill">نشط</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js for the dashboard charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize charts and other dashboard functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Charts would be initialized here in a real application
    });
</script>
