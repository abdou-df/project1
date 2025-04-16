<?php
// Include database connection
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// التحقق من تسجيل الدخول
checkLogin();

// التحقق من وجود معرف العميل
if (!isset($_GET['customer_id']) || empty($_GET['customer_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف العميل مطلوب'
    ]);
    exit;
}

$customer_id = (int)$_GET['customer_id'];

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
    // استعلام للحصول على مركبات العميل
    $stmt = $pdo->prepare("
        SELECT id, make, model, year, license_plate, vin, color, mileage, fuel_type, created_at
        FROM vehicles
        WHERE customer_id = :customer_id
        ORDER BY year DESC, make, model
    ");
    
    $stmt->execute([':customer_id' => $customer_id]);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // إرجاع بيانات المركبات
    echo json_encode([
        'success' => true,
        'data' => $vehicles
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ أثناء استرجاع بيانات المركبات: ' . $e->getMessage()
    ]);
}
?>