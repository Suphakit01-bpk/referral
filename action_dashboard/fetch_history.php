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
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $rowsPerPage = 10;
    $offset = ($page - 1) * $rowsPerPage;

    // Build WHERE clause based on search parameters
    $where = ["creator_hospital = :creator_hospital"];
    $params = [':creator_hospital' => $userHospital];

    if (!empty($_GET['nationalId'])) {
        $where[] = "national_id LIKE :nationalId";
        $params[':nationalId'] = '%' . $_GET['nationalId'] . '%';
    }

    if (!empty($_GET['fullName'])) {
        $where[] = "full_name_tf ILIKE :fullName";
        $params[':fullName'] = '%' . $_GET['fullName'] . '%';
    }

    if (!empty($_GET['hospital_tf'])) {
        $where[] = "hospital_tf = :hospital_tf";
        $params[':hospital_tf'] = $_GET['hospital_tf'];
    }

    if (!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
        $where[] = "transfer_date::date BETWEEN :startDate AND :endDate";
        $params[':startDate'] = $_GET['startDate'];
        $params[':endDate'] = $_GET['endDate'];
    }

    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $where[] = "status = :status";
        $params[':status'] = $_GET['status'];
    }

    $where[] = "(status = 'อนุมัติ' AND approved_date::timestamp <= CURRENT_TIMESTAMP - INTERVAL '7 days' OR status = 'ยกเลิก')";

    // Count total rows for pagination
    $countSql = "SELECT COUNT(*) FROM transfer_form WHERE " . implode(" AND ", $where);
    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);
    $totalRows = $stmt->fetchColumn();
    $totalPages = ceil($totalRows / $rowsPerPage);

    // Main query with pagination
    $sql = "SELECT 
        id, national_id, full_name_tf, hospital_tf,
        transfer_date::date, COALESCE(status, 'รอการอนุมัติ') as status,
        creator_hospital, billing_type, insurance_company,
        company, address, phone, age, purpose, diagnosis,
        reason, approved_hospital, approved_date::timestamp
    FROM transfer_form 
    WHERE " . implode(" AND ", $where) . "
    ORDER BY 
        CASE 
            WHEN status = 'ยกเลิก' THEN 1
            WHEN status = 'อนุมัติ' THEN 2
            ELSE 3
        END,
        transfer_date DESC
    LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql);
    $params[':limit'] = $rowsPerPage;
    $params[':offset'] = $offset;
    $stmt->execute($params);
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
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRows' => $totalRows,
            'rowsPerPage' => $rowsPerPage
        ],
        'hospital' => $userHospital
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