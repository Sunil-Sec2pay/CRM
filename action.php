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

if(isset($_POST['action'])){
    if ($_POST['action'] === 'get_users') {
        $query = "SELECT ID, USER_NAME FROM users WHERE ROLE_ID = 1 ORDER BY ID DESC";
        $statement = $connection->prepare($query);
        if ($statement->execute()) {
            $users = $statement->fetchAll(PDO::FETCH_OBJ);
            if (!empty($users)) {
                $response['status'] = 'success';
                $response['message'] = "Users retrieved successfully.";
                $response['users'] = $users;
            } else {
                $response['status'] = 'error';
                $response['message'] = "No users found.";
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = "Something Went to worng";
        }
        echo json_encode($response);
        exit;
    }
    if($_POST['action']=='set_lead_tousers'){
        if (!isset($input['lead_id'])) {
            $response['status'] = 'error';
            $response['message'] = 'lead is required';
            echo json_encode($response);
            exit;
        }
        $leadId = $input['lead_id'];
        try {
            $query = "
                        SELECT ID, USER_NAME FROM users WHERE ROLE_ID = 1 ORDER BY ID DESC";
            $statement = $connection->prepare($query);
            $statement->execute();
            $users = $statement->fetchAll(PDO::FETCH_ASSOC);
            $lastAssignedQuery = "
                        SELECT USER_ID FROM lead_assigned ORDER BY ID DESC LIMIT 1";
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
                $lastAssignedUserIndex = array_search($lastAssignedUser, array_column($users, 'ID'));
                $nextUserIndex = ($lastAssignedUserIndex + 1) % count($users);

            } else {
                $nextUserIndex = 0;
            }
            $checkLeadAssignedQuery = "
                        SELECT COUNT(*) FROM lead_assigned WHERE LEAD_ID = :LEAD_ID";
            $statement = $connection->prepare($checkLeadAssignedQuery);
            $statement->execute([   
                ':LEAD_ID' => $leadId,
            ]);
            $isAssigned = $statement->fetchColumn();
            if ($isAssigned) {
                $response['status'] = 'success';
                $response['message'] = "Lead is already assigned to user: {$user_name['user_name']}";
                $response['user'] = $user_name;
            } else {
                $insertQuery = "
                    INSERT INTO lead_assigned (LEAD_ID, USER_ID, CREATED_TIMESTAMP)
                    VALUES (:LEAD_ID, :USER_ID, :CREATED_TIMESTAMP)
                ";
                $statement = $connection->prepare($insertQuery);
                $result = $statement->execute([
                    ':LEAD_ID' => $leadId,
                    ':USER_ID' => $nextUser['id'],
                    ':CREATED_TIMESTAMP' => date('Y-m-d H:i:s'),
                ]);
                activityLogs($userId, 'set_lead_tousers', '', $newData);
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
    }
    if($_POST['action']=='get_leads'){
        try {
            $sql_leads = "
                SELECT
                    leads.ID AS lead_id,
                    leads.LEAD_NAME AS lead_name,
                    leads.LEAD_REMARK AS lead_remark,
                    leads.LEAD_PRODUCT AS lead_product,
                    leads.LEAD_PHONE AS lead_phone,
                    leads.CREATED_TIMESTAMP AS created_timestamp,
                    leads.LEAD_STATUS AS lead_status,
                    leads.LEAD_SOURCE AS lead_source,
                    GROUP_CONCAT(users.USER_NAME SEPARATOR ', ') AS assigned_users,
                    GROUP_CONCAT(users.ID SEPARATOR ', ') AS assigned_user_ids
                FROM
                    leads
                LEFT JOIN lead_assigned ON leads.ID = lead_assigned.lead_id
                LEFT JOIN users ON lead_assigned.user_id = users.ID
                GROUP BY
                    leads.ID
            ";
            $stmt_leads = $connection->prepare($sql_leads);
            $stmt_leads->execute();
            $leads = $stmt_leads->fetchAll(PDO::FETCH_ASSOC);
            $sql_employees = "SELECT ID, USER_NAME FROM users";
            $stmt_employees = $connection->prepare($sql_employees);
            $stmt_employees->execute();
            $employees = $stmt_employees->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($leads) && !empty($employees)){
                $response['status']    = 'success';
                $response['tasks']     = $leads;
                $response['employees'] = $employees;
            }else{
                $response['status']    = 'error';
                $response['message']   = "Leads not retrive";
            }
            echo json_encode($response);
        } catch (PDOException $e) {
            $response['status']    = 'error';
            $response['message']   = $e->getMessage();
            echo json_encode($response);
        }
    }
    if($_POST['action']=='set_lead_status'){
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
                $response['status'] = 'error';
                $response['message'] = "Lead status not Matched";
            }

            $oldData = getDetails($connection, "leads", "ID = :ID", [':ID' => $_POST["lead_id"]]);
            $statement = $connection->prepare("UPDATE leads SET LEAD_STATUS = :LEAD_STATUS WHERE ID = :ID");
                $data =array(
                    ':LEAD_STATUS' => $new_lead_status,
                    ':ID'   => $_POST["lead_id"]
                );
               $result = $statement->execute($data);
               activityLogs($connection,$loggedInUserId = 1, $action="lead status changed ".$oldData['LEAD_STATUS']." to ".$new_lead_status,$oldData,$data);
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
    }
    if($_POST['action']=='set_lead_assign_to_user'){
        if (!empty($_POST["lead_id"]) && !empty($_POST["user_id"])) {
            $currentUsers = $connection->prepare("SELECT USER_ID FROM lead_assigned WHERE LEAD_ID = :LEAD_ID");
            $currentUsers->execute([':LEAD_ID' => $_POST['lead_id']]);
            $existingUserIds = $currentUsers->fetchAll(PDO::FETCH_COLUMN);
            $usersToAdd = array_diff($_POST['user_id'], $existingUserIds); 
            $usersToRemove = array_diff($existingUserIds, $_POST['user_id']); 
            try {
                $connection->beginTransaction();
                $stmtInsert = $connection->prepare("INSERT INTO lead_assigned (LEAD_ID, USER_ID, CREATED_TIMESTAMP) VALUES (:LEAD_ID, :USER_ID, :CREATED_TIMESTAMP)");
                foreach ($usersToAdd as $userId) {
                    $data =array(
                        ':LEAD_ID' => $_POST['lead_id'],
                        ':USER_ID' => $userId,
                        ':CREATED_TIMESTAMP' => $timezone,
                    );
                    $stmtInsert->execute($data);
                    activityLogs($connection,$loggedInUserId = 1, $action="set_lead_assign_to_user lead assign for the users",'',$data);
                }
                if (!empty($usersToRemove)) {
                    $stmtRemove = $connection->prepare("DELETE FROM lead_assigned WHERE LEAD_ID = ? AND USER_ID IN (" . implode(',', array_fill(0, count($usersToRemove), '?')) . ")");
                    $stmtRemove->execute(array_merge([$_POST['lead_id']], $usersToRemove));
                    activityLogs($connection,$loggedInUserId = 1, $action="Unassing the users_id",$usersToRemove,$usersToAdd);
                }
                $connection->commit();
                $response['status'] = 'success';
                $response['message'] = "Lead assignments updated.";
            } catch (PDOException $e) {
                $connection->rollBack();
                $response['status'] = 'error';
                $response['message'] = $e->getMessage();
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Lead ID and User IDs are required.';
        }
        echo json_encode($response);
    }
}else{
    $response['error'] = 'Invalid Request';
    echo json_encode($response);
    exit;
}




function activityLogs($connection,$loggedInUserId = null, $action="",$previousValue=null,$newValue=null){
    $stmtLog = $connection->prepare("INSERT INTO activity_log (user_id, action, previous_value, new_value) VALUES (:user_id, :action, :previous_value, :new_value)");
    $stmtLog->execute([
        ':user_id' => $loggedInUserId,
        ':action' => $action,
        ':previous_value' => json_encode($previousValue), 
        ':new_value' => json_encode($newValue),
    ]);
}
function getDetails($connection, $table, $conditions, $params) {
    $query = "SELECT * FROM {$table} WHERE {$conditions}";
    $stmt = $connection->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


?>