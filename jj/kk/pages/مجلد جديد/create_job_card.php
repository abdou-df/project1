<?php
// تضمين ملفات الإعداد والدوال المساعدة
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

// جلب الإعدادات من قاعدة البيانات
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// تهيئة متغيرات النموذج والأخطاء
$formData = [
    'customer_id' => '',
    'vehicle_id' => '',
    'service_id' => '',
    'mechanic_id' => '',
    'date' => date('Y-m-d'),
    'start_time' => '09:00',
    'end_time' => '10:00',
    'status' => 'scheduled',
    'notes' => ''
];
$errors = [];
$success = false;
$customer_vehicles = [];

// جلب بيانات العملاء
$customers = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM customers ORDER BY name")->fetchAll();

// جلب بيانات الخدمات
$services = $pdo->query("SELECT id, name, duration, price FROM services WHERE status = 'active' ORDER BY name")->fetchAll();

// جلب الميكانيكيين
$mechanics = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role = 'mechanic' AND status = 'active' ORDER BY name")->fetchAll();

// معالجة تحديد العميل وجلب مركباته
if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
    $formData['customer_id'] = (int)$_GET['customer_id'];
    
    // جلب مركبات العميل المحدد
    $stmt = $pdo->prepare("SELECT id, CONCAT(year, ' ', make, ' ', model, ' (', license_plate, ')') as vehicle_name FROM vehicles WHERE customer_id = ? AND status = 'active' ORDER BY year DESC");
    $stmt->execute([$formData['customer_id']]);
    $customer_vehicles = $stmt->fetchAll();
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // جلب البيانات من النموذج
    $formData = [
        'customer_id' => isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : '',
        'vehicle_id' => isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : '',
        'service_id' => isset($_POST['service_id']) ? (int)$_POST['service_id'] : '',
        'mechanic_id' => isset($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : null,
        'date' => isset($_POST['date']) ? $_POST['date'] : '',
        'start_time' => isset($_POST['start_time']) ? $_POST['start_time'] : '',
        'end_time' => isset($_POST['end_time']) ? $_POST['end_time'] : '',
        'status' => isset($_POST['status']) ? $_POST['status'] : 'scheduled',
        'notes' => isset($_POST['notes']) ? $_POST['notes'] : ''
    ];
    
    // التحقق من صحة البيانات
    if (empty($formData['customer_id'])) {
        $errors[] = 'يجب اختيار العميل';
    }
    
    if (empty($formData['vehicle_id'])) {
        $errors[] = 'يجب اختيار المركبة';
    }
    
    if (empty($formData['service_id'])) {
        $errors[] = 'يجب اختيار الخدمة';
    }
    
    if (empty($formData['date'])) {
        $errors[] = 'يجب تحديد التاريخ';
    } elseif (strtotime($formData['date']) < strtotime(date('Y-m-d'))) {
        $errors[] = 'لا يمكن تحديد موعد في تاريخ سابق';
    }
    
    if (empty($formData['start_time'])) {
        $errors[] = 'يجب تحديد وقت البداية';
    }
    
    if (empty($formData['end_time'])) {
        $errors[] = 'يجب تحديد وقت النهاية';
    }
    
    if (strtotime($formData['date'] . ' ' . $formData['start_time']) >= strtotime($formData['date'] . ' ' . $formData['end_time'])) {
        $errors[] = 'يجب أن يكون وقت النهاية بعد وقت البداية';
    }
    
    // إذا لم يكن هناك أخطاء، أضف بطاقة العمل الجديدة
    if (empty($errors)) {
        try {
            // التحقق من تعارض المواعيد للميكانيكي
            if (!empty($formData['mechanic_id'])) {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM appointments 
                    WHERE user_id = ? AND date = ? AND status NOT IN ('completed', 'cancelled') 
                    AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))
                ");
                $stmt->execute([
                    $formData['mechanic_id'], 
                    $formData['date'],
                    $formData['start_time'], $formData['start_time'],
                    $formData['end_time'], $formData['end_time'],
                    $formData['start_time'], $formData['end_time']
                ]);
                $conflicts = $stmt->fetchColumn();
                
                if ($conflicts > 0) {
                    $errors[] = 'الميكانيكي المحدد لديه موعد آخر في نفس الوقت';
                }
            }
            
            // إذا لم تكن هناك تعارضات، أضف البطاقة
            if (empty($errors)) {
                $stmt = $pdo->prepare("
                    INSERT INTO appointments 
                    (customer_id, vehicle_id, service_id, user_id, date, start_time, end_time, status, notes, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([
                    $formData['customer_id'],
                    $formData['vehicle_id'],
                    $formData['service_id'],
                    $formData['mechanic_id'],
                    $formData['date'],
                    $formData['start_time'],
                    $formData['end_time'],
                    $formData['status'],
                    $formData['notes']
                ]);
                
                $jobCardId = $pdo->lastInsertId();
                $success = true;
                
                // إعادة توجيه المستخدم إلى صفحة بطاقة العمل الجديدة أو إلى قائمة البطاقات
                if (isset($_POST['saveAndView'])) {
                    header("Location: job_card_details.php?id=" . $jobCardId);
                    exit();
                } else {
                    header("Location: job-card.php?status=success&message=تم إنشاء بطاقة العمل بنجاح");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'حدث خطأ أثناء إنشاء بطاقة العمل: ' . $e->getMessage();
        }
    }
    
    // تحديث مركبات العميل بعد اختيار العميل من النموذج
    if (!empty($formData['customer_id'])) {
        $stmt = $pdo->prepare("SELECT id, CONCAT(year, ' ', make, ' ', model, ' (', license_plate, ')') as vehicle_name FROM vehicles WHERE customer_id = ? AND status = 'active' ORDER BY year DESC");
        $stmt->execute([$formData['customer_id']]);
        $customer_vehicles = $stmt->fetchAll();
    }
}

// دالة مساعدة لتحديد المدة الزمنية للخدمة
function getServiceDuration($serviceId, $services) {
    foreach ($services as $service) {
        if ($service['id'] == $serviceId) {
            return $service['duration'];
        }
    }
    return 60; // المدة الافتراضية
}

// حساب وقت الانتهاء تلقائيًا بناءً على وقت البداية ومدة الخدمة
if (!empty($formData['service_id']) && !empty($formData['start_time'])) {
    $duration = getServiceDuration($formData['service_id'], $services);
    $startTime = strtotime($formData['start_time']);
    $endTime = $startTime + $duration * 60;
    $formData['end_time'] = date('H:i', $endTime);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء بطاقة عمل جديدة - <?php echo htmlspecialchars($settings['garage_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/job_cards.css">
    <style>
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-left: 0.5rem;
            color: var(--primary-color);
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: -0.5rem;
        }
        
        .form-group {
            flex: 1 0 calc(33.333% - 1rem);
            margin: 0.5rem;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
            gap: 1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
        }
        
        .alert.alert-danger {
            background-color: #fee;
            color: #c33;
            border-right: 4px solid #c33;
        }
        
        .alert.alert-success {
            background-color: #efe;
            color: #3c3;
            border-right: 4px solid #3c3;
        }
        
        .alert-icon {
            margin-left: 1rem;
            font-size: 1.2rem;
        }
        
        .error-list {
            margin: 0;
            padding: 0 1.5rem;
        }
        
        .error-list li {
            margin-bottom: 0.5rem;
        }
        
        .error-list li:last-child {
            margin-bottom: 0;
        }
        
        .btn-new-customer {
            display: inline-flex;
            align-items: center;
            margin-right: 1rem;
            font-size: 0.9rem;
            color: var(--primary-color);
            cursor: pointer;
        }
        
        .btn-new-customer i {
            margin-left: 0.4rem;
        }
        
        .customer-empty-state {
            text-align: center;
            padding: 2rem;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .customer-empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .customer-empty-state h4 {
            margin-bottom: 1rem;
            color: #666;
        }
        
        .time-conflict-warning {
            display: none;
            background-color: #fff3cd;
            color: #856404;
            padding: 0.75rem;
            margin-top: 0.5rem;
            border-radius: 4px;
            border-right: 4px solid #ffeeba;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-left">
                <h1>إنشاء بطاقة عمل جديدة</h1>
                <p>إدخال تفاصيل بطاقة العمل الجديدة</p>
            </div>
            <div class="header-right">
                <a href="job-card.php" class="btn btn-outline">
                    <i class="fas fa-arrow-right ml-1"></i> العودة إلى بطاقات العمل
                </a>
            </div>
        </header>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div>
                <p><strong>يوجد أخطاء في النموذج:</strong></p>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p><strong>تم إنشاء بطاقة العمل بنجاح!</strong></p>
                <p>يمكنك الآن مشاهدة جميع بطاقات العمل أو إنشاء بطاقة عمل جديدة.</p>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="jobCardForm">
            <div class="form-container">
                <!-- بيانات العميل والمركبة -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-user-circle"></i> بيانات العميل والمركبة
                    </h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_id">العميل <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center">
                                <select id="customer_id" name="customer_id" required onchange="this.form.submit()">
                                    <option value="">-- اختر العميل --</option>
                                    <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>" <?php echo ($formData['customer_id'] == $customer['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($customer['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <a href="add_customer.php?redirect=create_job_card.php" class="btn-new-customer">
                                    <i class="fas fa-plus-circle"></i> عميل جديد
                                </a>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="vehicle_id">المركبة <span class="text-danger">*</span></label>
                            <?php if (empty($formData['customer_id'])): ?>
                                <select id="vehicle_id" name="vehicle_id" disabled>
                                    <option value="">-- اختر العميل أولاً --</option>
                                </select>
                            <?php elseif (empty($customer_vehicles)): ?>
                                <div class="customer-empty-state">
                                    <i class="fas fa-car"></i>
                                    <h4>لا توجد مركبات لهذا العميل</h4>
                                    <a href="add_vehicle.php?customer_id=<?php echo $formData['customer_id']; ?>&redirect=create_job_card.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus-circle"></i> أضف مركبة جديدة
                                    </a>
                                </div>
                            <?php else: ?>
                                <select id="vehicle_id" name="vehicle_id" required>
                                    <option value="">-- اختر المركبة --</option>
                                    <?php foreach ($customer_vehicles as $vehicle): ?>
                                    <option value="<?php echo $vehicle['id']; ?>" <?php echo ($formData['vehicle_id'] == $vehicle['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($vehicle['vehicle_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="mt-2">
                                    <a href="add_vehicle.php?customer_id=<?php echo $formData['customer_id']; ?>&redirect=create_job_card.php" class="btn-new-customer">
                                        <i class="fas fa-plus-circle"></i> مركبة جديدة
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- تفاصيل الخدمة -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-tools"></i> تفاصيل الخدمة
                    </h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="service_id">الخدمة <span class="text-danger">*</span></label>
                            <select id="service_id" name="service_id" required onchange="updateEndTime()">
                                <option value="">-- اختر الخدمة --</option>
                                <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>" 
                                        data-duration="<?php echo $service['duration']; ?>"
                                        data-price="<?php echo $service['price']; ?>"
                                        <?php echo ($formData['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($service['name'] . ' (' . $service['duration'] . ' دقيقة - ' . $service['price'] . ' ' . $settings['currency_symbol'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="mechanic_id">الميكانيكي</label>
                            <select id="mechanic_id" name="mechanic_id">
                                <option value="">-- غير محدد --</option>
                                <?php foreach ($mechanics as $mechanic): ?>
                                <option value="<?php echo $mechanic['id']; ?>" <?php echo ($formData['mechanic_id'] == $mechanic['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mechanic['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">الحالة</label>
                            <select id="status" name="status">
                                <option value="scheduled" <?php echo ($formData['status'] == 'scheduled') ? 'selected' : ''; ?>>مجدولة</option>
                                <option value="confirmed" <?php echo ($formData['status'] == 'confirmed') ? 'selected' : ''; ?>>مؤكدة</option>
                                <option value="in_progress" <?php echo ($formData['status'] == 'in_progress') ? 'selected' : ''; ?>>قيد التنفيذ</option>
                                <option value="completed" <?php echo ($formData['status'] == 'completed') ? 'selected' : ''; ?>>مكتملة</option>
                                <option value="cancelled" <?php echo ($formData['status'] == 'cancelled') ? 'selected' : ''; ?>>ملغاة</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- تفاصيل الموعد -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-calendar-alt"></i> تفاصيل الموعد
                    </h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">التاريخ <span class="text-danger">*</span></label>
                            <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo $formData['date']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="start_time">وقت البداية <span class="text-danger">*</span></label>
                            <input type="time" id="start_time" name="start_time" required value="<?php echo $formData['start_time']; ?>" onchange="updateEndTime()">
                        </div>
                        
                        <div class="form-group">
                            <label for="end_time">وقت النهاية <span class="text-danger">*</span></label>
                            <input type="time" id="end_time" name="end_time" required value="<?php echo $formData['end_time']; ?>">
                            <div id="timeConflictWarning" class="time-conflict-warning">
                                <i class="fas fa-exclamation-triangle"></i> تنبيه: الميكانيكي المحدد لديه موعد آخر في هذا الوقت
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ملاحظات إضافية -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-clipboard-list"></i> ملاحظات إضافية
                    </h2>
                    <div class="form-row">
                        <div class="form-group" style="flex-basis: 100%;">
                            <label for="notes">ملاحظات</label>
                            <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($formData['notes']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="window.location.href='job-card.php'" class="btn btn-outline">إلغاء</button>
                    <button type="submit" name="saveAndNew" class="btn btn-outline btn-primary">حفظ وإنشاء بطاقة جديدة</button>
                    <button type="submit" name="saveAndView" class="btn btn-primary">حفظ وعرض البطاقة</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // تحديث وقت الانتهاء بناءً على وقت البداية ومدة الخدمة
        function updateEndTime() {
            const serviceSelect = document.getElementById('service_id');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            if (serviceSelect.selectedIndex <= 0 || !startTimeInput.value) {
                return;
            }
            
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            const durationMinutes = parseInt(selectedOption.dataset.duration);
            
            if (!isNaN(durationMinutes) && durationMinutes > 0) {
                const [hours, minutes] = startTimeInput.value.split(':').map(Number);
                
                const startDate = new Date();
                startDate.setHours(hours, minutes, 0);
                
                const endDate = new Date(startDate.getTime() + durationMinutes * 60000);
                const endHours = String(endDate.getHours()).padStart(2, '0');
                const endMinutes = String(endDate.getMinutes()).padStart(2, '0');
                
                endTimeInput.value = `${endHours}:${endMinutes}`;
            }
            
            // تحقق من تعارض المواعيد
            checkMechanicAvailability();
        }
        
        // التحقق من توفر الميكانيكي في الوقت المحدد
        function checkMechanicAvailability() {
            const mechanicId = document.getElementById('mechanic_id').value;
            const date = document.getElementById('date').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            const warningElement = document.getElementById('timeConflictWarning');
            
            if (!mechanicId || !date || !startTime || !endTime) {
                warningElement.style.display = 'none';
                return;
            }
            
            // إرسال طلب AJAX للتحقق من تعارض المواعيد
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_mechanic_availability.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    const response = JSON.parse(this.responseText);
                    if (response.conflict) {
                        warningElement.style.display = 'block';
                    } else {
                        warningElement.style.display = 'none';
                    }
                }
            };
            
            xhr.send(`mechanic_id=${mechanicId}&date=${date}&start_time=${startTime}&end_time=${endTime}`);
        }
        
        // إضافة مستمعي الأحداث
        document.addEventListener('DOMContentLoaded', function() {
            // تحديث وقت النهاية عند تحميل الصفحة
            updateEndTime();
            
            // تحديث عند تغيير الميكانيكي
            document.getElementById('mechanic_id').addEventListener('change', checkMechanicAvailability);
            
            // تحديث عند تغيير التاريخ
            document.getElementById('date').addEventListener('change', checkMechanicAvailability);
        });
    </script>
</body>
</html>
