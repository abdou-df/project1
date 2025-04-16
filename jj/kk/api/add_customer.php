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
if (!isset($_POST['first_name']) || empty($_POST['first_name']) ||
    !isset($_POST['last_name']) || empty($_POST['last_name']) ||
    !isset($_POST['phone']) || empty($_POST['phone'])) {
    
    echo json_encode([
        'success' => false,
        'message' => 'جميع الحقول المطلوبة يجب ملؤها'
    ]);
    exit;
}

// تنظيف وتحضير البيانات
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$phone = trim($_POST['phone']);
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';

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
    // التحقق من عدم وجود رقم هاتف مكرر
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = :phone");
    $stmt->execute([':phone' => $phone]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'رقم الهاتف موجود بالفعل لعميل آخر'
        ]);
        exit;
    }
    
    // إدخال العميل الجديد
    $stmt = $pdo->prepare("
        INSERT INTO customers (
            first_name, last_name, phone, email, address, created_at, updated_at
        ) VALUES (
            :first_name, :last_name, :phone, :email, :address, NOW(), NOW()
        )
    ");
    
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':phone' => $phone,
        ':email' => $email,
        ':address' => $address
    ]);
    
    $customer_id = $pdo->lastInsertId();
    
    // استرجاع بيانات العميل المضاف
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, phone, email, address, created_at
        FROM customers
        WHERE id = :id
    ");
    
    $stmt->execute([':id' => $customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // إضافة سجل في تاريخ النشاط
    $activity_message = "تمت إضافة عميل جديد: {$first_name} {$last_name}";
    addActivityLog($_SESSION['user_id'], 'customer_create', $customer_id, $activity_message);
    
    // إرجاع بيانات العميل المضاف
    echo json_encode([
        'success' => true,
        'message' => 'تمت إضافة العميل بنجاح',
        'data' => $customer
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ أثناء إضافة العميل: ' . $e->getMessage()
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