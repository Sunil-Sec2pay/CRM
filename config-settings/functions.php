<?php

include('connection.php');
$username = 'root';
$password = '';
$connection = new PDO( 'mysql:host=localhost;dbname=sec2pay_leads', $username, $password );
date_default_timezone_set("Asia/Calcutta");   
$timezone =  date('d-m-Y H:i:s');


function assignLeadsToUserOld($leadId){
    $query = "SELECT * FROM users ";
    $statement = $connection->prepare($query);
    $statement->execute();
    $results = $statement->fetchAll();
    foreach($results as $row)
    {
        $user_id = $row['id'];
        $leadAssignedQuery = "SELECT * FROM lead_assigned";
        $statement = $connection->prepare($leadAssignedQuery);
        $statement->execute();
        $leadAssignedDatas = $statement->fetchAll();
        foreach($leadAssignedDatas as $leadAssignedData)
        {
            if($leadAssignedData['user_id'] == $user_id){
                return "alaredy lead assignd user".$row['user_name'];
            }
            
            $statement = $connection->prepare("
            INSERT INTO lead_assigned (lead_id, user_id, craeted_timestamp) 
            VALUES (:lead_id, :user_id, :craeted_timestamp)
           ");
           $result = $statement->execute(
            array(
             ':lead_id' => $leadId,
             ':user_id' => $user_id,
             ':craeted_timestamp' => $timezone,
            )
           );
            if(!empty($result))
            {
                echo 'Data Inserted';
            }
        } 

    }
}

function assignLeadsToUser($leadId, $connection, $timezone) {
    $query = "SELECT id, user_name FROM users";
    $statement = $connection->prepare($query);
    $statement->execute();
    $users = $statement->fetchAll();
    $leadAssignedQuery = "SELECT user_id FROM lead_assigned WHERE lead_id = :lead_id";
    $statement = $connection->prepare($leadAssignedQuery);
    $statement->execute([':lead_id' => $leadId]);
    $existingLeadAssignments = $statement->fetchAll(PDO::FETCH_COLUMN);
    foreach ($users as $user) {
        $user_id = $user['id'];
        $user_name = $user['user_name'];
        if (in_array($user_id, $existingLeadAssignments)) {
            return "Lead is already assigned to user: $user_name";
        }
    }
    foreach ($users as $user) {
        $user_id = $user['id'];
        if (!in_array($user_id, $existingLeadAssignments)) {
            $insertQuery = "
                INSERT INTO lead_assigned (lead_id, user_id, created_timestamp)
                VALUES (:lead_id, :user_id, :created_timestamp)
            ";
            $statement = $connection->prepare($insertQuery);
            $result = $statement->execute([
                ':lead_id' => $leadId,
                ':user_id' => $user_id,
                ':created_timestamp' => $timezone,
            ]);

            if ($result) {
                return "Lead successfully assigned to user: {$user['user_name']}";
            } else {
                return "Failed to assign lead.";
            }
        }
    }
    return "No available users to assign the lead.";
}

?>