<?php
// Include database connection
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// التحقق من تسجيل الدخول
checkLogin();

// التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'طريقة الطلب غير صالحة'
    ]);
    exit;
}

// التحقق من البيانات المطلوبة
if (!isset($_POST['customer_id']) || empty($_POST['customer_id']) ||
    !isset($_POST['make']) || empty($_POST['make']) ||
    !isset($_POST['model']) || empty($_POST['model']) ||
    !isset($_POST['year']) || empty($_POST['year']) ||
    !isset($_POST['license_plate']) || empty($_POST['license_plate'])) {
    
    echo json_encode([
        'success' => false,
        'message' => 'جميع الحقول المطلوبة يجب ملؤها'
    ]);
    exit;
}

// تنظيف وتحضير البيانات
$customer_id = (int)$_POST['customer_id'];
$make = trim($_POST['make']);
$model = trim($_POST['model']);
$year = (int)$_POST['year'];
$license_plate = trim($_POST['license_plate']);
$vin = isset($_POST['vin']) ? trim($_POST['vin']) : '';
$color = isset($_POST['color']) ? trim($_POST['color']) : '';
$mileage = isset($_POST['mileage']) && !empty($_POST['mileage']) ? (int)$_POST['mileage'] : null;
$fuel_type = isset($_POST['fuel_type']) ? trim($_POST['fuel_type']) : 'gasoline';

// التحقق من صحة السنة
$current_year = (int)date('Y');
if ($year < 1900 || $year > ($current_year + 1)) {
    echo json_encode([
        'success' => false,
        'message' => 'سنة الصنع غير صالحة'
    ]);
    exit;
}

// إنشاء اتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage()
    ]);
    exit;
}

try {
    // التحقق من وجود العميل
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE id = :id");
    $stmt->execute([':id' => $customer_id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'العميل غير موجود'
        ]);
        exit;
    }
    
    // التحقق من عدم وجود لوحة ترخيص مكررة
    $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE license_plate = :license_plate AND customer_id != :customer_id");
    $stmt->execute([
        ':license_plate' => $license_plate,
        ':customer_id' => $customer_id
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'رقم لوحة الترخيص موجود بالفعل لمركبة أخرى'
        ]);
        exit;
    }
    
    // التحقق من عدم وجود رقم هيكل مكرر (إذا تم توفيره)
    if (!empty($vin)) {
        $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE vin = :vin AND customer_id != :customer_id");
        $stmt->execute([
            ':vin' => $vin,
            ':customer_id' => $customer_id
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'رقم الهيكل (VIN) موجود بالفعل لمركبة أخرى'
            ]);
            exit;
        }
    }
    
    // إدخال المركبة الجديدة
    $stmt = $pdo->prepare("
        INSERT INTO vehicles (
            customer_id, make, model, year, license_plate, vin, color, mileage, fuel_type, created_at, updated_at
        ) VALUES (
            :customer_id, :make, :model, :year, :license_plate, :vin, :color, :mileage, :fuel_type, NOW(), NOW()
        )
    ");
    
    $stmt->execute([
        ':customer_id' => $customer_id,
        ':make' => $make,
        ':model' => $model,
        ':year' => $year,
        ':license_plate' => $license_plate,
        ':vin' => $vin,
        ':color' => $color,
        ':mileage' => $mileage,
        ':fuel_type' => $fuel_type
    ]);
    
    $vehicle_id = $pdo->lastInsertId();
    
    // استرجاع بيانات المركبة المضافة
    $stmt = $pdo->prepare("
        SELECT id, customer_id, make, model, year, license_plate, vin, color, mileage, fuel_type, created_at
        FROM vehicles
        WHERE id = :id
    ");
    
    $stmt->execute([':id' => $vehicle_id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // إضافة سجل في تاريخ النشاط
    $activity_message = "تمت إضافة مركبة جديدة: {$year} {$make} {$model} - {$license_plate}";
    addActivityLog($_SESSION['user_id'], 'vehicle_create', $vehicle_id, $activity_message);
    
    // إرجاع بيانات المركبة المضافة
    echo json_encode([
        'success' => true,
        'message' => 'تمت إضافة المركبة بنجاح',
        'data' => $vehicle
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ أثناء إضافة المركبة: ' . $e->getMessage()
    ]);
}

// دالة لإضافة سجل في تاريخ النشاط
function addActivityLog($user_id, $activity_type, $reference_id, $description) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (
                user_id, activity_type, reference_id, description, created_at
            ) VALUES (
                :user_id, :activity_type, :reference_id, :description, NOW()
            )
        ");
        
        $stmt->execute([
            ':user_id' => $user_id,
            ':activity_type' => $activity_type,
            ':reference_id' => $reference_id,
            ':description' => $description
        ]);
        
        return true;
    } catch (PDOException $e) {
        // تسجيل الخطأ فقط، لا نريد إيقاف العملية الرئيسية
        error_log('خطأ في إضافة سجل النشاط: ' . $e->getMessage());
        return false;
    }
}
?>