<?php
// Include database connection
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../includes/functions.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// التحقق من تسجيل الدخول
checkLogin();

// التحقق من وجود معرف العميل
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف العميل مطلوب'
    ]);
    exit;
}

$customer_id = (int)$_GET['id'];

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
    // استعلام للحصول على بيانات العميل
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, phone, email, address, created_at
        FROM customers
        WHERE id = :id
    ");
    
    $stmt->execute([':id' => $customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        echo json_encode([
            'success' => false,
            'message' => 'العميل غير موجود'
        ]);
        exit;
    }
    
    // إرجاع بيانات العميل
    echo json_encode([
        'success' => true,
        'data' => $customer
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ أثناء استرجاع بيانات العميل: ' . $e->getMessage()
    ]);
}
?>