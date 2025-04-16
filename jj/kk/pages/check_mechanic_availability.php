<?php
// تضمين ملفات الاتصال بقاعدة البيانات
require_once dirname(__FILE__) . '/../config/config.php';

// بيانات الطلب
$mechanicId = isset($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : 0;
$date = isset($_POST['date']) ? $_POST['date'] : '';
$startTime = isset($_POST['start_time']) ? $_POST['start_time'] : '';
$endTime = isset($_POST['end_time']) ? $_POST['end_time'] : '';
$appointmentId = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;

$response = ['conflict' => false];

// التحقق من توفر الميكانيكي
if ($mechanicId && $date && $startTime && $endTime) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $query = "
            SELECT COUNT(*) FROM appointments 
            WHERE user_id = ? 
            AND date = ? 
            AND status NOT IN ('completed', 'cancelled')
            AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))
        ";
        
        // استثناء الموعد الحالي في حالة التعديل
        if ($appointmentId > 0) {
            $query .= " AND id != ?";
        }
        
        $stmt = $pdo->prepare($query);
        
        $params = [
            $mechanicId, 
            $date, 
            $startTime, $startTime, 
            $endTime, $endTime, 
            $startTime, $endTime
        ];
        
        if ($appointmentId > 0) {
            $params[] = $appointmentId;
        }
        
        $stmt->execute($params);
        $conflicts = $stmt->fetchColumn();
        
        $response['conflict'] = ($conflicts > 0);
        
    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
    }
}

// إرسال النتيجة كـ JSON
header('Content-Type: application/json');
echo json_encode($response);
