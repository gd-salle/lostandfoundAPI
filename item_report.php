<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'dbconn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Log received data
    error_log(print_r($data, true));

    if (
        !empty($data['item_name']) && !empty($data['date']) && !empty($data['item_condition']) &&
        !empty($data['time']) && !empty($data['location']) && !empty($data['reporter_name']) &&
        !empty($data['contact_no']) && !empty($data['report_type']) && !empty($data['status'])
    ) {
        $item_name = htmlspecialchars(strip_tags($data['item_name']));
        $date = htmlspecialchars(strip_tags($data['date']));
        $item_condition = htmlspecialchars(strip_tags($data['item_condition']));
        $time = htmlspecialchars(strip_tags($data['time']));
        $location = htmlspecialchars(strip_tags($data['location']));
        $reporter_name = htmlspecialchars(strip_tags($data['reporter_name']));
        $contact_no = htmlspecialchars(strip_tags($data['contact_no']));
        $report_type = htmlspecialchars(strip_tags($data['report_type']));
        $status = htmlspecialchars(strip_tags($data['status']));

        $query = "INSERT INTO item_report (item_name, date, item_condition, time, location, reporter_name, contact_no, report_type, status) 
                  VALUES (:item_name, :date, :item_condition, :time, :location, :reporter_name, :contact_no, :report_type, :status)";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':item_name', $item_name);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':item_condition', $item_condition);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':reporter_name', $reporter_name);
        $stmt->bindParam(':contact_no', $contact_no);
        $stmt->bindParam(':report_type', $report_type);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log(print_r($errorInfo, true));
            echo json_encode(['success' => false, 'message' => 'Item could not be reported.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incomplete data.']);
    }
} elseif ($method == 'GET') {
    $query = "SELECT * FROM item_report WHERE status = 'UNCLAIMED'";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $num = $stmt->rowCount();

    if ($num > 0) {
        $items_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $item = [
                'id' => $id,
                'item_name' => $item_name ?? '',
                'date' => $date ?? '',
                'item_condition' => $item_condition ?? '',
                'time' => $time ?? '',
                'location' => $location ?? '',
                'reporter_name' => $reporter_name ?? '',
                'contact_no' => $contact_no ?? '',
                'report_type' => $report_type ?? '',
                'status' => $status ?? '',
            ];

            array_push($items_arr, $item);
        }
        echo json_encode($items_arr);
    } else {
        echo json_encode([]);
    }
}
?>
