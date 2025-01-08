<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '..\db_connect.php';

try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10; // rows per page
    $offset = ($page - 1) * $limit;

    // Get search parameters
    $searchParams = [];
    $whereConditions = ["creator_hospital = :creator_hospital"];
    
    if (!empty($_GET['nationalId'])) {
        $whereConditions[] = "national_id LIKE :nationalId";
        $searchParams[':nationalId'] = '%' . $_GET['nationalId'] . '%';
    }
    
    if (!empty($_GET['fullName'])) {
        $whereConditions[] = "LOWER(full_name_tf) LIKE LOWER(:fullName)";
        $searchParams[':fullName'] = '%' . $_GET['fullName'] . '%';
    }
    
    if (!empty($_GET['hospital_tf'])) {
        $whereConditions[] = "hospital_tf = :hospital_tf";
        $searchParams[':hospital_tf'] = $_GET['hospital_tf'];
    }
    
    if (!empty($_GET['status'])) {
        $whereConditions[] = "status = :status";
        $searchParams[':status'] = $_GET['status'];
    }
    
    if (!empty($_GET['startDate'])) {
        $whereConditions[] = "transfer_date::date >= :startDate";
        $searchParams[':startDate'] = $_GET['startDate'];
    }
    
    if (!empty($_GET['endDate'])) {
        $whereConditions[] = "transfer_date::date <= :endDate";
        $searchParams[':endDate'] = $_GET['endDate'];
    }

    $whereClause = implode(' AND ', $whereConditions);

    // ตรวจสอบว่ามี hospital ใน session หรือไม่
    if (!isset($_SESSION['hospital']) || empty($_SESSION['hospital'])) {
        throw new Exception('ไม่พบข้อมูลโรงพยาบาล กรุณาเข้าสู่ระบบใหม่');
    }

    $userHospital = $_SESSION['hospital'];

    // Count total matching records
    $countSql = "SELECT COUNT(*) FROM transfer_form WHERE $whereClause";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bindValue(':creator_hospital', $userHospital);
    foreach ($searchParams as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRows = $countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalRows / $limit)); // ต้องมีอย่างน้อย 1 หน้า

    // ตรวจสอบและปรับค่า page ถ้าเกินจำนวนหน้าทั้งหมด
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $limit;
    }

    // Get filtered and paginated data
    $sql = "WITH filtered_data AS (
        SELECT 
            id,
            national_id, 
            full_name_tf, 
            hospital_tf,
            transfer_date::date,
            COALESCE(status, 'รอการอนุมัติ') as status,
            creator_hospital,
            billing_type,
            insurance_company,
            company,
            address,
            phone,
            age,
            purpose,
            diagnosis,
            reason,
            approved_hospital,
            approved_date::timestamp
        FROM transfer_form 
        WHERE $whereClause
        AND status != 'ยกเลิก'
        AND (
            status != 'อนุมัติ' 
            OR (
                status = 'อนุมัติ' 
                AND approved_date::timestamp >= CURRENT_TIMESTAMP - INTERVAL '7 days'
            )
        )
        ORDER BY 
            CASE 
                WHEN status = 'รอการอนุมัติ' THEN 1
                WHEN status = 'อนุมัติ' THEN 2
                ELSE 3
            END,
            transfer_date DESC
    )
    SELECT * FROM filtered_data
    LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':creator_hospital', $userHospital);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($searchParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get actual total rows from filtered data
    $totalRowsSql = "SELECT COUNT(*) FROM (
        SELECT 1 FROM transfer_form 
        WHERE $whereClause
        AND status != 'ยกเลิก'
        AND (
            status != 'อนุมัติ' 
            OR (
                status = 'อนุมัติ' 
                AND approved_date::timestamp >= CURRENT_TIMESTAMP - INTERVAL '7 days'
            )
        )
    ) AS filtered_count";
    
    $totalRowsStmt = $conn->prepare($totalRowsSql);
    $totalRowsStmt->bindValue(':creator_hospital', $userHospital);
    foreach ($searchParams as $key => $value) {
        $totalRowsStmt->bindValue($key, $value);
    }
    $totalRowsStmt->execute();
    $totalRows = $totalRowsStmt->fetchColumn();
    
    // Format dates and count actual displayed rows
    $displayedRows = count($data);
    foreach ($data as &$row) {
        if (isset($row['transfer_date'])) {
            $date = new DateTime($row['transfer_date']);
            $row['transfer_date'] = $date->format('m/d/Y');
        }
    }

    // ปรับปรุงการส่งข้อมูล pagination
    echo json_encode([
        'success' => true, 
        'data' => $data,
        'hospital' => $userHospital,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRows' => $totalRows,
            'displayedRows' => $displayedRows,
            'rowsPerPage' => $limit,
            'hasNextPage' => ($totalRows > $limit && $page < $totalPages),
            'hasPrevPage' => ($page > 1 && $totalRows > $limit)
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