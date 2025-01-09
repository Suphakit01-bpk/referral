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
    
    // Add pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10; // จำนวนรายการต่อหน้า
    $offset = ($page - 1) * $limit;
    
    // Build WHERE conditions
    $whereConditions = [
        "hospital_tf = :hospital", 
        "status != 'ยกเลิก'",
        // แก้ไขเงื่อนไขให้แสดงเฉพาะที่อนุมัติแล้วและอยู่ในช่วง 7 วัน
        "status = 'อนุมัติ' AND approved_date::timestamp >= CURRENT_TIMESTAMP - INTERVAL '7 days'"
    ];
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
    $totalPages = ceil($totalRows / $limit);

    // Main query with pagination
    $sql = "SELECT 
        national_id, 
        full_name_tf, 
        creator_hospital,
        hospital_tf,
        transfer_date::date,
        approved_date,
        COALESCE(status, 'รอการอนุมัติ') as status
    FROM transfer_form 
    WHERE $whereClause
    ORDER BY 
        CASE 
            WHEN status = 'รอการอนุมัติ' THEN 1
            WHEN status = 'อนุมัติ' THEN 2
            ELSE 3
        END,
        transfer_date DESC
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
    
    // Get actual count of displayed rows
    $displayedRows = count($data);

    // Format dates
    foreach ($data as &$row) {
        if (isset($row['transfer_date'])) {
            $date = new DateTime($row['transfer_date']);
            $row['transfer_date'] = $date->format('m/d/Y');
        }
        if (isset($row['approved_date'])) {
            $approvedDate = new DateTime($row['approved_date']);
            $row['approved_date'] = $approvedDate->format('m/d/Y H:i:s');
        }
    }

    echo json_encode([
        'success' => true, 
        'data' => $data,
        'hospital' => $userHospital,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRows' => $displayedRows, // Use actual count instead of total count
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