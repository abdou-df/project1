<?php
// تضمين ملفات الإعداد والدوال المساعدة
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// التحقق من تسجيل الدخول
// إذا لم تكن الدالة checkLogin موجودة، استخدم دالة بديلة للتحقق
if (function_exists('checkLogin')) {
    checkLogin();
} else {
    // التحقق من وجود جلسة مستخدم
    session_start();
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول
        header("Location: ../login.php");
        exit;
    }
}

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

// الحصول على قائمة العملاء
$customers = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, phone, email FROM customers ORDER BY name")->fetchAll();

// الحصول على قائمة الخدمات
$services = $pdo->query("SELECT id, name, price, duration, description FROM services ORDER BY name")->fetchAll();

// الحصول على قائمة الميكانيكيين
$mechanics = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role = 'mechanic' ORDER BY name")->fetchAll();

// معالجة النموذج عند الإرسال
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من البيانات المرسلة
    $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
    $vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
    $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
    $mechanic_id = isset($_POST['mechanic_id']) && !empty($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : 'scheduled';
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    // التحقق من صحة البيانات
    if ($customer_id <= 0) {
        $errors[] = "يرجى اختيار العميل";
    }
    
    if ($vehicle_id <= 0) {
        $errors[] = "يرجى اختيار المركبة";
    }
    
    if ($service_id <= 0) {
        $errors[] = "يرجى اختيار الخدمة";
    }
    
    if (empty($date)) {
        $errors[] = "يرجى تحديد التاريخ";
    }
    
    if (empty($start_time)) {
        $errors[] = "يرجى تحديد وقت البدء";
    }
    
    if (empty($end_time)) {
        $errors[] = "يرجى تحديد وقت الانتهاء";
    }
    
    // إذا لم تكن هناك أخطاء، قم بإنشاء بطاقة العمل
    if (empty($errors)) {
        try {
            // إدخال بطاقة العمل الجديدة
            $stmt = $pdo->prepare("
                INSERT INTO appointments (
                    customer_id, vehicle_id, service_id, date, start_time, end_time, 
                    user_id, status, notes, created_at
                ) VALUES (
                    :customer_id, :vehicle_id, :service_id, :date, :start_time, :end_time, 
                    :user_id, :status, :notes, NOW()
                )
            ");
            
            $stmt->execute([
                ':customer_id' => $customer_id,
                ':vehicle_id' => $vehicle_id,
                ':service_id' => $service_id,
                ':date' => $date,
                ':start_time' => $start_time,
                ':end_time' => $end_time,
                ':user_id' => $mechanic_id,
                ':status' => $status,
                ':notes' => $notes
            ]);
            
            $job_card_id = $pdo->lastInsertId();
            
            // تخطي إدخال السجل في جدول التاريخ لأنه غير موجود
            // كود تاريخ بطاقة العمل - سيتم تنفيذه فقط إذا كان الجدول موجودًا
            try {
                // التحقق من وجود الجدول قبل محاولة الإدراج
                $checkTableQuery = "SHOW TABLES LIKE 'appointment_history'";
                $tableExists = $pdo->query($checkTableQuery)->rowCount() > 0;
                
                if ($tableExists) {
                    // إضافة سجل في جدول التاريخ
                    $stmt = $pdo->prepare("
                        INSERT INTO appointment_history (
                            appointment_id, status, notes, user_id, created_at
                        ) VALUES (
                            :appointment_id, :status, :notes, :user_id, NOW()
                        )
                    ");
                    
                    $stmt->execute([
                        ':appointment_id' => $job_card_id,
                        ':status' => $status,
                        ':notes' => 'تم إنشاء بطاقة العمل',
                        ':user_id' => $_SESSION['user_id']
                    ]);
                }
            } catch (PDOException $historyError) {
                // تجاهل أي أخطاء متعلقة بجدول التاريخ
                // نسجل الخطأ فقط (اختياري)
                error_log("خطأ في جدول التاريخ: " . $historyError->getMessage());
            }
            
            $success = true;
            
            // إعادة التوجيه إلى صفحة تفاصيل بطاقة العمل
            header("Location: index.php?page=job_card_details&id=" . $job_card_id . "&created=1");
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "حدث خطأ أثناء إنشاء بطاقة العمل: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<!--<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء بطاقة عمل جديدة - <?php echo htmlspecialchars($settings['garage_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
    <style>
        /* أنماط إضافية خاصة بصفحة إنشاء بطاقة العمل */
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .form-section-title i {
            margin-left: 10px;
            color: #4a6cf7;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-group {
            flex: 1 0 250px;
            margin: 0 10px 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #4a6cf7;
            outline: none;
        }
        
        .select2-container {
            width: 100% !important;
        }
        
        .customer-vehicle-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .customer-section, .vehicle-section {
            flex: 1;
            min-width: 300px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .action-buttons .btn {
            min-width: 120px;
        }
        
        .customer-info, .vehicle-info {
            background-color: #f9f9f9;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 500;
            width: 100px;
            color: #666;
        }
        
        .info-value {
            flex: 1;
        }
        
        .add-new-btn {
            display: flex;
            align-items: center;
            color: #4a6cf7;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            margin-top: 5px;
        }
        
        .add-new-btn i {
            margin-left: 5px;
        }
        
        .add-new-btn:hover {
            text-decoration: underline;
        }
        
        .service-details {
            background-color: #f9f9f9;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .service-price {
            font-size: 18px;
            font-weight: 600;
            color: #4a6cf7;
            margin-top: 10px;
        }
        
        .service-duration {
            color: #666;
            margin-top: 5px;
        }
        
        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .time-slot {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .time-slot:hover {
            border-color: #4a6cf7;
            background-color: #f0f4ff;
        }
        
        .time-slot.selected {
            background-color: #4a6cf7;
            color: white;
            border-color: #4a6cf7;
        }
        
        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 100%;
            }
        }
    </style>
</head>-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء بطاقة عمل جديدة - <?php echo htmlspecialchars($settings['garage_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Tajawal:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/create_job_card.css">
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header class="page-header">
            <div class="header-content">
                <h1>إنشاء بطاقة عمل جديدة</h1>
                <p>إنشاء موعد صيانة جديد وتعيين الخدمات والميكانيكي</p>
            </div>
            <div class="header-actions">
                <a href="../job_cards.php" class="btn btn-outline">
                    <i class="fas fa-arrow-right"></i> العودة إلى بطاقات العمل
                </a>
            </div>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                تم إنشاء بطاقة العمل بنجاح!
            </div>
        <?php endif; ?>

        <!-- Form Section -->
        <form method="POST" action="" id="createJobCardForm">
            <div class="form-container">
                <!-- Customer & Vehicle Section -->
                <div class="form-section">
                    <h2 class="form-section-title">
                        <i class="fas fa-user"></i> معلومات العميل والمركبة
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_id">اختر العميل <span class="required">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-control select2" required>
                                <option value="">-- اختر العميل --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>">
                                        <?php echo htmlspecialchars($customer['name']); ?> - 
                                        <?php echo htmlspecialchars($customer['phone']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" id="addNewCustomer" class="add-new-btn">
                                <i class="fas fa-plus-circle"></i> إضافة عميل جديد
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <label for="vehicle_id">اختر المركبة <span class="required">*</span></label>
                            <select name="vehicle_id" id="vehicle_id" class="form-control select2" required disabled>
                                <option value="">-- اختر المركبة --</option>
                                <!-- سيتم تحميل المركبات بناءً على العميل المحدد -->
                            </select>
                            <button type="button" id="addNewVehicle" class="add-new-btn" disabled>
                                <i class="fas fa-plus-circle"></i> إضافة مركبة جديدة
                            </button>
                        </div>
                    </div>
                    
                    <div class="customer-vehicle-section">
                        <div class="customer-section">
                            <div class="customer-info" id="customerInfo" style="display: none;">
                                <h3>معلومات العميل</h3>
                                <div class="info-row">
                                    <div class="info-label">الاسم:</div>
                                    <div class="info-value" id="customerName"></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">الهاتف:</div>
                                    <div class="info-value" id="customerPhone"></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">البريد:</div>
                                    <div class="info-value" id="customerEmail"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="vehicle-section">
                            <div class="vehicle-info" id="vehicleInfo" style="display: none;">
                                <h3>معلومات المركبة</h3>
                                <div class="info-row">
                                    <div class="info-label">الطراز:</div>
                                    <div class="info-value" id="vehicleModel"></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">السنة:</div>
                                    <div class="info-value" id="vehicleYear"></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">اللوحة:</div>
                                    <div class="info-value" id="vehiclePlate"></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">VIN:</div>
                                    <div class="info-value" id="vehicleVin"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Service Section -->
                <div class="form-section">
                    <h2 class="form-section-title">
                        <i class="fas fa-tools"></i> معلومات الخدمة
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="service_id">اختر الخدمة <span class="required">*</span></label>
                            <select name="service_id" id="service_id" class="form-control select2" required>
                                <option value="">-- اختر الخدمة --</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['id']; ?>" 
                                            data-price="<?php echo $service['price']; ?>"
                                            data-duration="<?php echo $service['duration']; ?>"
                                            data-description="<?php echo htmlspecialchars($service['description']); ?>">
                                        <?php echo htmlspecialchars($service['name']); ?> - 
                                        <?php echo formatCurrency($service['price'], $settings['currency_symbol']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="service-details" id="serviceDetails" style="display: none;">
                        <h3>تفاصيل الخدمة</h3>
                        <p id="serviceDescription"></p>
                        <div class="service-price" id="servicePrice"></div>
                        <div class="service-duration" id="serviceDuration"></div>
                    </div>
                </div>
                
                <!-- Schedule Section -->
                <div class="form-section">
                    <h2 class="form-section-title">
                        <i class="fas fa-calendar-alt"></i> جدولة الموعد
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">تاريخ الموعد <span class="required">*</span></label>
                            <input type="date" name="date" id="date" class="form-control" required
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="start_time">وقت البدء <span class="required">*</span></label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_time">وقت الانتهاء <span class="required">*</span></label>
                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mechanic_id">تعيين ميكانيكي</label>
                            <select name="mechanic_id" id="mechanic_id" class="form-control select2">
                                <option value="">-- اختر ميكانيكي --</option>
                                <?php foreach ($mechanics as $mechanic): ?>
                                    <option value="<?php echo $mechanic['id']; ?>">
                                        <?php echo htmlspecialchars($mechanic['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">حالة بطاقة العمل</label>
                            <select name="status" id="status" class="form-control">
                                <option value="scheduled">مجدولة</option>
                                <option value="confirmed">مؤكدة</option>
                                <option value="in_progress">قيد التنفيذ</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Notes Section -->
                <div class="form-section">
                    <h2 class="form-section-title">
                        <i class="fas fa-sticky-note"></i> ملاحظات إضافية
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex: 1 0 100%;">
                            <label for="notes">ملاحظات</label>
                            <textarea name="notes" id="notes" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="action-buttons">
                    <a href="../job_cards.php" class="btn btn-outline">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> إنشاء بطاقة العمل
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Add New Customer Modal -->
    <div class="modal" id="addCustomerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>إضافة عميل جديد</h2>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">الاسم الأول <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">الاسم الأخير <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">رقم الهاتف <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">البريد الإلكتروني</label>
                            <input type="email" id="email" name="email" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">العنوان</label>
                            <input type="text" id="address" name="address" class="form-control">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveCustomerBtn">حفظ</button>
            </div>
        </div>
    </div>

    <!-- Add New Vehicle Modal -->
    <div class="modal" id="addVehicleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>إضافة مركبة جديدة</h2>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addVehicleForm">
                    <input type="hidden" id="vehicle_customer_id" name="customer_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="make">الشركة المصنعة <span class="required">*</span></label>
                            <input type="text" id="make" name="make" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="model">الموديل <span class="required">*</span></label>
                            <input type="text" id="model" name="model" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="year">سنة الصنع <span class="required">*</span></label>
                            <input type="number" id="year" name="year" class="form-control" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="license_plate">رقم اللوحة <span class="required">*</span></label>
                            <input type="text" id="license_plate" name="license_plate" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vin">رقم الهيكل (VIN)</label>
                            <input type="text" id="vin" name="vin" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="color">اللون</label>
                            <input type="text" id="color" name="color" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mileage">عداد المسافة</label>
                            <input type="number" id="mileage" name="mileage" class="form-control" min="0">
                        </div>
                        <div class="form-group">
                            <label for="fuel_type">نوع الوقود</label>
                            <select id="fuel_type" name="fuel_type" class="form-control">
                                <option value="gasoline">بنزين</option>
                                <option value="diesel">ديزل</option>
                                <option value="electric">كهربائي</option>
                                <option value="hybrid">هجين</option>
                                <option value="other">أخرى</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveVehicleBtn">حفظ</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <script>
        $(document).ready(function() {
            // تهيئة Select2
            $('.select2').select2({
                dir: "rtl",
                language: "ar"
            });
            
            // عند تغيير العميل
            $('#customer_id').change(function() {
                const customerId = $(this).val();
                
                if (customerId) {
                    // تفعيل حقل المركبة
                    $('#vehicle_id').prop('disabled', false);
                    $('#addNewVehicle').prop('disabled', false);
                    
                    // عرض معلومات العميل
                    $.ajax({
                        url: '../api/get_customer.php',
                        type: 'GET',
                        data: { id: customerId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                const customer = response.data;
                                $('#customerName').text(customer.first_name + ' ' + customer.last_name);
                                $('#customerPhone').text(customer.phone);
                                $('#customerEmail').text(customer.email || 'غير متوفر');
                                $('#customerInfo').show();
                                
                                // تحميل مركبات العميل
                                loadVehicles(customerId);
                            }
                        }
                    });
                } else {
                    // إعادة تعطيل حقل المركبة
                    $('#vehicle_id').prop('disabled', true).val('').trigger('change');
                    $('#addNewVehicle').prop('disabled', true);
                    $('#customerInfo').hide();
                    $('#vehicleInfo').hide();
                }
            });
            
            // تحميل مركبات العميل
            function loadVehicles(customerId) {
                $.ajax({
                    url: '../api/get_vehicles.php',
                    type: 'GET',
                    data: { customer_id: customerId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const vehicles = response.data;
                            let options = '<option value="">-- اختر المركبة --</option>';
                            
                            vehicles.forEach(function(vehicle) {
                                options += `<option value="${vehicle.id}" 
                                            data-make="${vehicle.make}" 
                                            data-model="${vehicle.model}" 
                                            data-year="${vehicle.year}" 
                                            data-plate="${vehicle.license_plate}" 
                                            data-vin="${vehicle.vin || ''}">
                                            ${vehicle.year} ${vehicle.make} ${vehicle.model} - ${vehicle.license_plate}
                                            </option>`;
                            });
                            
                            $('#vehicle_id').html(options).trigger('change');
                        }
                    }
                });
            }
            
            // عند تغيير المركبة
            $('#vehicle_id').change(function() {
                const vehicleId = $(this).val();
                
                if (vehicleId) {
                    // عرض معلومات المركبة
                    const selectedOption = $(this).find('option:selected');
                    const make = selectedOption.data('make');
                    const model = selectedOption.data('model');
                    const year = selectedOption.data('year');
                    const plate = selectedOption.data('plate');
                    const vin = selectedOption.data('vin');
                    
                    $('#vehicleModel').text(make + ' ' + model);
                    $('#vehicleYear').text(year);
                    $('#vehiclePlate').text(plate);
                    $('#vehicleVin').text(vin || 'غير متوفر');
                    $('#vehicleInfo').show();
                } else {
                    $('#vehicleInfo').hide();
                }
            });
            
            // عند تغيير الخدمة
            $('#service_id').change(function() {
                const serviceId = $(this).val();
                
                if (serviceId) {
                    // عرض معلومات الخدمة
                    const selectedOption = $(this).find('option:selected');
                    const price = selectedOption.data('price');
                    const duration = selectedOption.data('duration');
                    const description = selectedOption.data('description');
                    
                    $('#serviceDescription').text(description);
                    $('#servicePrice').text('السعر: <?php echo $settings['currency_symbol']; ?>' + price);
                    $('#serviceDuration').text('المدة المقدرة: ' + duration + ' دقيقة');
                    $('#serviceDetails').show();
                    
                    // حساب وقت الانتهاء تلقائيًا
                    calculateEndTime(duration);
                } else {
                    $('#serviceDetails').hide();
                }
            });
            
            // حساب وقت الانتهاء تلقائيًا
            function calculateEndTime(duration) {
                const startTime = $('#start_time').val();
                
                if (startTime && duration) {
                    const startDate = new Date('2000-01-01T' + startTime + ':00');
                    const endDate = new Date(startDate.getTime() + duration * 60000);
                    
                    const hours = endDate.getHours().toString().padStart(2, '0');
                    const minutes = endDate.getMinutes().toString().padStart(2, '0');
                    
                    $('#end_time').val(hours + ':' + minutes);
                }
            }
            
            // عند تغيير وقت البدء
            $('#start_time').change(function() {
                const duration = $('#service_id').find('option:selected').data('duration');
                if (duration) {
                    calculateEndTime(duration);
                }
            });
            
            // فتح نافذة إضافة عميل جديد
            $('#addNewCustomer').click(function() {
                $('#addCustomerModal').addClass('active');
            });
            
            // فتح نافذة إضافة مركبة جديدة
            $('#addNewVehicle').click(function() {
                const customerId = $('#customer_id').val();
                $('#vehicle_customer_id').val(customerId);
                $('#addVehicleModal').addClass('active');
            });
            
            // إغلاق النوافذ المنبثقة
            $('.close-modal').click(function() {
                $(this).closest('.modal').removeClass('active');
            });
            
            // حفظ عميل جديد
            $('#saveCustomerBtn').click(function() {
                const formData = $('#addCustomerForm').serialize();
                
                $.ajax({
                    url: '../api/add_customer.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const newCustomer = response.data;
                            
                            // إضافة العميل الجديد إلى القائمة
                            const newOption = new Option(
                                newCustomer.first_name + ' ' + newCustomer.last_name + ' - ' + newCustomer.phone,
                                newCustomer.id,
                                true,
                                true
                            );
                            
                            $('#customer_id').append(newOption).trigger('change');
                            
                            // إغلاق النافذة المنبثقة
                            $('#addCustomerModal').removeClass('active');
                            $('#addCustomerForm')[0].reset();
                        } else {
                            alert('حدث خطأ: ' + response.message);
                        }
                    }
                });
            });
            
            // حفظ مركبة جديدة
            $('#saveVehicleBtn').click(function() {
                const formData = $('#addVehicleForm').serialize();
                
                $.ajax({
                    url: '../api/add_vehicle.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const newVehicle = response.data;
                            
                            // إضافة المركبة الجديدة إلى القائمة
                            const newOption = new Option(
                                newVehicle.year + ' ' + newVehicle.make + ' ' + newVehicle.model + ' - ' + newVehicle.license_plate,
                                newVehicle.id,
                                true,
                                true
                            );
                            
                            // إضافة البيانات للخيار الجديد
                            $(newOption).data('make', newVehicle.make);
                            $(newOption).data('model', newVehicle.model);
                            $(newOption).data('year', newVehicle.year);
                            $(newOption).data('plate', newVehicle.license_plate);
                            $(newOption).data('vin', newVehicle.vin);
                            
                            $('#vehicle_id').append(newOption).trigger('change');
                            
                            // إغلاق النافذة المنبثقة
                            $('#addVehicleModal').removeClass('active');
                            $('#addVehicleForm')[0].reset();
                        } else {
                            alert('حدث خطأ: ' + response.message);
                        }
                    }
                });
            });
            
            // التحقق من النموذج قبل الإرسال
            $('#createJobCardForm').submit(function(e) {
                const customer_id = $('#customer_id').val();
                const vehicle_id = $('#vehicle_id').val();
                const service_id = $('#service_id').val();
                const date = $('#date').val();
                const start_time = $('#start_time').val();
                const end_time = $('#end_time').val();
                
                if (!customer_id || !vehicle_id || !service_id || !date || !start_time || !end_time) {
                    e.preventDefault();
                    alert('يرجى ملء جميع الحقول المطلوبة');
                    return false;
                }
                
                return true;
            });
        });

        $(document).ready(function() {
    // تهيئة Select2 بشكل صحيح
    $('.select2').select2({
        dir: "rtl",
        language: "ar",
        placeholder: function() {
            return $(this).data('placeholder') || '-- اختر --';
        },
        width: '100%',
        dropdownAutoWidth: true,
        allowClear: true
    });
    
    // إصلاح مشكلة توجيه النص في حقول التاريخ والوقت
    $('input[type="date"], input[type="time"]').on('focus', function() {
        $(this).css('direction', 'ltr');
    }).on('blur', function() {
        if (!$(this).val()) {
            $(this).css('direction', 'rtl');
        }
    });
    
    // تحسين مظهر القوائم المنسدلة
    $('select.form-control').on('change', function() {
        if ($(this).val()) {
            $(this).addClass('has-value');
        } else {
            $(this).removeClass('has-value');
        }
    });
    
    // تطبيق التحقق من الحقول المطلوبة
    $('form').on('submit', function() {
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
            }
        });
    });
    
    // إزالة فئة الخطأ عند تغيير القيمة
    $('input, select, textarea').on('input change', function() {
        if ($(this).hasClass('is-invalid')) {
            $(this).removeClass('is-invalid');
            if ($(this).val()) {
                $(this).addClass('is-valid');
            }
        }
    });
});
    </script>
</body>
</html>