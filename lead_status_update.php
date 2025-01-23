<?php

include('config-settings/functions.php');
header('Content-Type: application/json');
$username = 'root';
$password = '';
$connection = new PDO( 'mysql:host=localhost;dbname=sec2pay_leads', $username, $password );
date_default_timezone_set("Asia/Calcutta");   
$timezone =  date('d-m-Y H:i:s');

$response = [];
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST; 
}
if (!isset($input['lead_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'lead is required';
    echo json_encode($response);
    exit;
}
try {
    
    $new_lead_status = $_POST["new_lead_status"];

    switch ($new_lead_status) {
        case "To Do":
            $new_lead_status= "To Do";
            break;
        case "In Progress":
            $new_lead_status= "In Progress";
            break;
        case "Pending":
            $new_lead_status= "Pending";
            break;
        case "Hold":
            $new_lead_status= "Hold";
            break;
        case "Done":
            $new_lead_status= "Done";
            break;
        case "Completed":
            $new_lead_status= "Completed";
            break;
    default:
        echo "Your favorite color is neither red, blue, nor green!";
    }
    $statement = $connection->prepare("UPDATE leads SET LEAD_STATUS = :LEAD_STATUS WHERE ID = :ID");
       $result = $statement->execute(
            array(
                ':LEAD_STATUS' => $new_lead_status,
                ':ID'   => $_POST["lead_id"]
            )
       );
       if ($result) {
            $response['status'] = 'success';
            $response['message'] = "Lead status successfully ".$new_lead_status;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to change lead status.';
        }

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}
echo json_encode($response);
?>