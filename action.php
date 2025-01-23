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
$leadId = $input['lead_id'];
try {
    $query = "SELECT id, user_name FROM users where role_id=1 ORDER BY id";
    $statement = $connection->prepare($query);
    $statement->execute();
    $users = $statement->fetchAll(PDO::FETCH_ASSOC);
    $lastAssignedQuery = "SELECT user_id FROM lead_assigned ORDER BY lead_assign_id DESC LIMIT 1";
    $statement = $connection->prepare($lastAssignedQuery);
    $statement->execute();
    $lastAssignedUser = $statement->fetchColumn();
    $nextUserIndex = 0; 
    // if ($lastAssignedUser !== false) {
    //     foreach ($users as $index => $user) {
    //         if ($user['id'] == $lastAssignedUser) {
    //             $user_name = $user;
    //             print_r($index);
    //             print_r(count($users));
    //             $nextUserIndex = ($index + 1) % count($users);
    //             print_r($nextUserIndex);
    //             break;
    //         }
    //     }
    // }
    if ($lastAssignedUser !== false) {
        $lastAssignedUserIndex = array_search($lastAssignedUser, array_column($users, 'id'));
        $nextUserIndex = ($lastAssignedUserIndex + 1) % count($users);

    } else {
        $nextUserIndex = 0;
    }
    $checkLeadAssignedQuery = "SELECT COUNT(*) FROM lead_assigned WHERE lead_id = :lead_id";
    $statement = $connection->prepare($checkLeadAssignedQuery);
    $statement->execute([   
        ':lead_id' => $leadId,
    ]);
    $isAssigned = $statement->fetchColumn();

    if ($isAssigned) {
        $response['status'] = 'success';
        $response['message'] = "Lead is already assigned to user: {$user_name['user_name']}";
        $response['user'] = $user_name;
    } else {
        $insertQuery = "
            INSERT INTO lead_assigned (lead_id, user_id, craeted_timestamp)
            VALUES (:lead_id, :user_id, :craeted_timestamp)
        ";
        $statement = $connection->prepare($insertQuery);
        $result = $statement->execute([
            ':lead_id' => $leadId,
            ':user_id' => $nextUser['id'],
            ':craeted_timestamp' => date('Y-m-d H:i:s'),
        ]);

        if ($result) {
            $response['status'] = 'success';
            $response['message'] = "Lead successfully assigned to user: {$nextUser['user_name']}";
            $response['user'] = $nextUser;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to assign lead.';
        }
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}
echo json_encode($response);






?>