<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'dbconn.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !empty($data['recipient_name']) && !empty($data['date']) && !empty($data['contact_no']) &&
        !empty($data['year_level']) && !empty($data['department']) && !empty($data['address']) &&
        !empty($data['item_report_id'])
    ) {
        $recipient_name = htmlspecialchars(strip_tags($data['recipient_name']));
        $date = htmlspecialchars(strip_tags($data['date']));
        $contact_no = htmlspecialchars(strip_tags($data['contact_no']));
        $year_level = htmlspecialchars(strip_tags($data['year_level']));
        $department = htmlspecialchars(strip_tags($data['department']));
        $address = htmlspecialchars(strip_tags($data['address']));
        $item_report_id = htmlspecialchars(strip_tags($data['item_report_id']));

        $query = "INSERT INTO claim_report (recipient_name, date, contact_no, year_level, department, address, item_report_id) 
                  VALUES (:recipient_name, :date, :contact_no, :year_level, :department, :address, :item_report_id)";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':recipient_name', $recipient_name);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':contact_no', $contact_no);
        $stmt->bindParam(':year_level', $year_level);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':item_report_id', $item_report_id);

        if ($stmt->execute()) {
            // Update the item status to 'CLAIMED'
            $updateQuery = "UPDATE item_report SET status = 'CLAIMED' WHERE id = :item_report_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':item_report_id', $item_report_id);
            $updateStmt->execute();

            echo json_encode(['success' => true]);
        } else {
            $errorInfo = $stmt->errorInfo();
            echo json_encode(['success' => false, 'message' => 'Claim report could not be created.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incomplete data.']);
    }
}
?>
