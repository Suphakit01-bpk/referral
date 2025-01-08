<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '..\db_connect.php';

try {
    if (!isset($_SESSION['hospital']) || empty($_SESSION['hospital'])) {
        throw new Exception('ไม่พบข้อมูลโรงพยาบาล กรุณาเข้าสู่ระบบใหม่');
    }

    $userHospital = $_SESSION['hospital'];
    
    // Build WHERE conditions
    $whereConditions = ["hospital_tf = :hospital", "status != 'ยกเลิก'"];
    $params = [':hospital' => $userHospital];
    
    // Add search conditions
    if (!empty($_GET['nationalId'])) {
        $whereConditions[] = "national_id LIKE :nationalId";
        $params[':nationalId'] = '%' . $_GET['nationalId'] . '%';
    }
    
    if (!empty($_GET['fullName'])) {
        $whereConditions[] = "LOWER(full_name_tf) LIKE LOWER(:fullName)";
        $params[':fullName'] = '%' . $_GET['fullName'] . '%';
    }
    
    if (!empty($_GET['startDate'])) {
        $whereConditions[] = "transfer_date::date >= :startDate";
        $params[':startDate'] = $_GET['startDate'];
    }
    
    if (!empty($_GET['endDate'])) {
        $whereConditions[] = "transfer_date::date <= :endDate";
        $params[':endDate'] = $_GET['endDate'];
    }
    
    if (!empty($_GET['status'])) {
        $whereConditions[] = "status = :status";
        $params[':status'] = $_GET['status'];
    }

    $whereClause = implode(' AND ', $whereConditions);

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) FROM transfer_form WHERE $whereClause";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $totalRows = $countStmt->fetchColumn();
    
    // Add pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    $totalPages = ceil($totalRows / $limit);

    // Main query with pagination
    $sql = "SELECT 
        national_id, 
        full_name_tf, 
        creator_hospital,
        hospital_tf,
        transfer_date::date,
        COALESCE(status, 'อนุมัติ') as status
    FROM transfer_form 
    WHERE $whereClause
    AND (status IS NULL OR status != 'รอการอนุมัติ')  -- กรองข้อมูลที่มีสถานะเป็น 'รอการอนุมัติ'
    ORDER BY transfer_date DESC
    LIMIT :limit OFFSET :offset";


    $stmt = $conn->prepare($sql);
    
    // Bind all parameters including pagination
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates
    foreach ($data as &$row) {
        if (isset($row['transfer_date'])) {
            $date = new DateTime($row['transfer_date']);
            $row['transfer_date'] = $date->format('m/d/Y');
        }
    }

    echo json_encode([
        'success' => true, 
        'data' => $data,
        'hospital' => $userHospital,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRows' => $totalRows,
            'rowsPerPage' => $limit
        ]
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching data'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn = null;
?>