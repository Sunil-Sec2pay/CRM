<?php
//namespace ConfigSettings;

require_once('config_db.php');
require_once('MysqliDb.php');
require_once 'CommonHelpers.php'; 


class LeadsManager
{
    private $db;
    // pubi $timezone =  ;
    public function __construct()
    {
        global $dbX;
        global $dbUserName;
        global $dbPassword;
        global $dbName;
        $dbX = new MysqliDb('localhost', 'root', $dbPassword, $dbName);
        $dbX->autoReconnect = false;
        $dbX->connect();
        if(!$dbX) {
            die("Database error");
        }
    }

    public function getLeadsRocords(){
        global $dbX;
        try {
            $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;  
            $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;  
            $searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';  
            $orderColumn = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;  
            $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';  
            $columns = $dbX->rawQuery("SHOW COLUMNS FROM myleadsrecords");
            $columnNames = [];
            foreach ($columns as $column) {
                $columnNames[] = $column['Field'];  
            }
            $orderBy = $columnNames[$orderColumn] ?? 'ID';  
            $dbX->where('1 = 1'); 
            if ($searchValue != '') {
                foreach ($columnNames as $column) {
                    $dbX->orWhere("$column LIKE '%" . $dbX->escape($searchValue) . "%'");
                }
            }
            $dbX->orderBy($orderBy, $orderDir);
            $leadsRecords = $dbX->get("myleadsrecords", [$start, $length]);
            $totalRecordsQuery = $dbX->rawQueryOne("SELECT COUNT(*) AS total FROM myleadsrecords");
            $totalRecords = $totalRecordsQuery['total'];
            if (!empty($leadsRecords)) {
                $response['status'] = 'success';
                $response['message'] = "Leads records retrieved successfully.";
                $response['data'] = $leadsRecords;  
                $response['recordsTotal'] = $totalRecords;  
                $response['recordsFiltered'] = $totalRecords;  
            } else {
                $response['status'] = 'failed';
                $response['message'] = "No leads records found.";
                $response['data'] = [];
                $response['recordsTotal'] = 0;
                $response['recordsFiltered'] = 0;
            }
        } catch (Exception $e) {
            $response['status'] = 'failed';
            $response['message'] = "something went to wrong. try again...!";
            $response['data'] = [];
        }
        return $response;
    }
    public function getLeads()
    {
        global $dbX;
        try {
            $dbX->join("lead_assigned la", "myleadsrecords.ID = la.lead_id", "LEFT");
            $dbX->join("myadmins24 u", "la.user_id = u.ID", "LEFT");
            $dbX->join("myadmins24 owner", "myleadsrecords.CURRENT_OWNER_ID = owner.ID", "LEFT"); 
            $dbX->groupBy("myleadsrecords.ID");
            $leads = $dbX->get("myleadsrecords", null, [
                'myleadsrecords.ID AS lead_id',
                'myleadsrecords.PRIMARY_NAME AS lead_name',
                'myleadsrecords.PRIMARY_TEXT AS lead_remark',
                'myleadsrecords.PRODUCT AS lead_product',
                'myleadsrecords.PRIMARY_PHONE AS lead_phone',
                'myleadsrecords.CREATED_AT AS created_timestamp',
                'myleadsrecords.STATUS AS lead_status',
                'myleadsrecords.SOURCE AS lead_source',
                'IF(COUNT(la.lead_id) > 0, 
                    GROUP_CONCAT(DISTINCT u.ADMIN_NAME ORDER BY u.ID ASC SEPARATOR ", "), 
                    owner.ADMIN_NAME
                ) AS assigned_users',
                 'IF(COUNT(la.lead_id) > 0, 
                    GROUP_CONCAT(DISTINCT u.ID ORDER BY u.ID ASC SEPARATOR ", "), 
                    owner.ID
                ) AS assigned_user_ids'
            ]);
            $employees = $dbX->get("myadmins24", null, ['ID', 'ADMIN_NAME']);
            if (!empty($leads) && !empty($employees)) {
                $response['status'] = 'success';
                $response['tasks'] = $leads;
                $response['employees'] = $employees;
            } else {
                $response['status'] = 'failed';
                $response['message'] = "Leads not retrieved";
            }
        } catch (Exception  $e) {
            $response['status']    = 'failed';
            // $response['message']   = "something went to wrong. try again...!";
            $response['message']   = $e->getMessage();
        }
        return $response;
    }
    public function getLeadsDetails(){
        global $dbX;
        if($_POST['lead_id']){
            try{
                $leadsDetail = $dbX->where('ID', $_POST['lead_id'])->get('myleadsrecords');
                $users = $dbX->where('ROLE_ID', 1)->orderBy('ID', 'DESC')->get('users', null, ['ID', 'USER_NAME']);
                if (!empty($leadsDetail)) {
                    $response['status'] = 'success';
                    $response['message'] = "Users retrieved successfully.";
                    $response['leadsDetail'] = $leadsDetail;
                    $response['users'] = $users;
                } else {
                    $response['status'] = 'failed';
                    $response['message'] = "Lead Data Not Found.";
                }
            } catch (Exception  $e) {
                $response['status'] = 'failed';
                $response['message'] = "something went to wrong. try again...!";
            }
        }else{
            $response['status'] = 'failed';
            $response['message'] = "missing parameter.";
        }
        return $response;
    }
    public function leadAssignToUser(){
        if (!isset($_POST['lead_id'])) {
            $response['status'] = 'failed';
            $response['message'] = 'lead is required';
            return $response;
            exit;
        }
        $leadId = $_POST['lead_id'];
        global $dbX;
        try {
            $users = $dbX->where('ROLE_ID', 1)->orderBy('ID', 'DESC')->get('users', null, ['ID', 'USER_NAME']);
            $lastAssignedUser = $dbX->orderBy('ID', 'DESC')->getOne('lead_assigned', 'USER_ID');
            $nextUserIndex = 0;
            if ($lastAssignedUser) {
                $lastAssignedUserIndex = array_search($lastAssignedUser['USER_ID'], array_column($users, 'ID'));
                $nextUserIndex = ($lastAssignedUserIndex + 1) % count($users);
            } else {
                $nextUserIndex = 0;
            }
            $isAssigned = $this->$dbX->where('LEAD_ID', $leadId)->getValue('lead_assigned', 'COUNT(*)');
            if ($isAssigned) {
                $response['status'] = 'success';
                $response['message'] = "Lead is already assigned to user: {$users[$lastAssignedUserIndex]['USER_NAME']}";
                $response['user'] = $users[$lastAssignedUserIndex];
            } else {
                $nextUser = $users[$nextUserIndex];
                $result = $this->$dbX->insert('lead_assigned', [
                    'LEAD_ID' => $leadId,
                    'USER_ID' => $nextUser['ID'],
                    'CREATED_TIMESTAMP' => date('Y-m-d H:i:s')
                ]);
                if ($result) {
                    activityLogs($userId, 'set_lead_tousers', '', $newData);
                    $response['status'] = 'success';
                    $response['message'] = "Lead successfully assigned to user: {$nextUser['USER_NAME']}";
                    $response['user'] = $nextUser;
                } else {
                    $response['status'] = 'failed';
                    $response['message'] = 'Failed to assign lead.';
                }
            }
        } catch (Exception  $e) {
            $response['status'] = 'failed';
            $response['message'] = "something went to wrong. try again...!";
        }
        return $response;
    }
    public function leadStatusUpdate(){
        global $dbX;
        $helper = new CommonHelpers();
        if (!isset($_POST['lead_id'])) {
            $response['status'] = 'failed';
            $response['message'] = 'Lead is required';
            return $response;
            exit;
        }
        try {
            $new_lead_status = strtoupper($_POST["new_lead_status"]);
            $validTransitions = [
                "NEW"            => ["CONTACTED", "QUALIFIED", "OPPORTUNITY", "DEMO_SCHEDULED", "DEMO_DONE", "IN_NEGOTIATION", "CONVERTED", "DISQAULIFIED", "LOST", "LIVE", "OTHER"],
                "CONTACTED"      => ["QUALIFIED", "DISQAULIFIED", "LOST", "LIVE", "OTHER"],
                "QUALIFIED"      => ["OPPORTUNITY", "DISQAULIFIED", "LOST", "LIVE", "OTHER"],
                "OPPORTUNITY"    => ["DEMO_SCHEDULED", "DISQAULIFIED", "LOST", "LIVE", "OTHER"],
                "DEMO_SCHEDULED" => ["DEMO_DONE", "DISQAULIFIED", "LOST", "LIVE", "OTHER"],
                "DEMO_DONE"      => ["IN_NEGOTIATION", "DISQAULIFIED", "LOST", "LIVE", "OTHER"],
                "IN_NEGOTIATION" => ["CONVERTED", "DISQAULIFIED", "LOST", "LIVE", "OTHER"],
                "CONVERTED"      => ["LIVE", "DISQAULIFIED", "LOST", "OTHER"],
                "DISQAULIFIED"   => ["LOST", "OTHER"],
                "LOST"           => ["OTHER"],
                "LIVE"           => [],
                "OTHER"          => []
            ];
            if (!array_key_exists($new_lead_status, $validTransitions)) {
                $response['status'] = 'failed';
                $response['message'] = "Lead status not valid";
                return $response;
                exit;
            }
            $oldData = $dbX->where('ID', $_POST["lead_id"])->getOne('myleadsrecords');
            $old_status = strtoupper($oldData['STATUS']);
            if (!in_array($new_lead_status, $validTransitions[$old_status])) {
                $response['status'] = 'failed';
                $response['message'] = "Lead status cannot be reverted back from {$old_status} to {$new_lead_status}";
                echo json_encode($response);
                exit;
            }
            $data = [
                'STATUS' => $new_lead_status
            ];
            $result = $dbX->where('ID', $_POST["lead_id"])->update('myleadsrecords', $data);
            $helper->activityLogs($loggedInUserId = 1, $action = "lead status changed {$old_status} to {$new_lead_status}", $oldData, $data);
            if ($result) {
                $response['status'] = 'success';
                $response['message'] = "Lead status successfully updated to {$new_lead_status}";
            } else {
                $response['status'] = 'failed';
                $response['message'] = 'Failed to change lead status.';
            }
        } catch (Exception $e) {
            $response['status'] = 'failed';
            // $response['message'] = $e->getMessage();
            $response['message'] = "something went to wrong..!";
        }
        return $response;
    }
    function setLeadAssignToUsers(){
        global $dbX;
        $helper = new CommonHelpers();
        if (!empty($_POST["lead_id"]) && !empty($_POST["user_id"])) {
            $currentUsers = $dbX->where('LEAD_ID', $_POST['lead_id'])->get('lead_assigned', null, 'USER_ID');
            $existingUserIds = array_column($currentUsers, 'USER_ID');
            $usersToAdd = array_diff($_POST['user_id'], $existingUserIds);
            $usersToRemove = array_diff($existingUserIds, $_POST['user_id']);
            try {
                $dbX->startTransaction();
                $dataToInsert = [];
                foreach ($usersToAdd as $userId) {
                    $dataToInsert[] = [
                        'LEAD_ID' => $_POST['lead_id'],
                        'USER_ID' => $userId,
                        'CREATED_TIMESTAMP' => date('d-m-Y H:i:s')
                    ];
                }
                if (!empty($dataToInsert)) {
                    $dbX->insertMulti('lead_assigned', $dataToInsert);
                    foreach ($dataToInsert as $data) {
                        $helper->activityLogs($loggedInUserId = 1, $action = "set_lead_assign_to_user lead assign for the users", '', $data);
                    }
                }
                if (!empty($usersToRemove)) {
                    $dbX->where('LEAD_ID', $_POST['lead_id'])->where('USER_ID', $usersToRemove,'IN');
                    $dbX->delete('lead_assigned');
                    $activityLogs->activityLogs($loggedInUserId = 1, $action = "Unassign the users", $usersToRemove, $usersToAdd);
                }
                $dbX->commit();
                $response['status'] = 'success';
                $response['message'] = "Lead assignments updated.";
            } catch (Exception $e) {
                $dbX->rollback();
                $response['status'] = 'failed';
                $response['message'] = "something went to wrong. try again...!";
            }
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Lead ID and User IDs are required.';
        }
         return $response;
    }

    public function setLeadFolloUp(){
        global $dbX;
        if (!empty($_POST["follow_up_index"]) && !empty($_POST["follow_up_date"]) && !empty($_POST["follow_up_notes"])) {
            $leadId    = $_POST["leadId"];
            $index     = $_POST["follow_up_index"];
            $date      = $_POST["follow_up_date"];
            $notes     = $_POST["follow_up_notes"];
            $todayDate = new DateTime();
            $columns = [
                1 => ["date" => "FIRST_FOLLOWUP_DATE", "remark" => "FIRST_FOLLOWUP_REMARK"],
                2 => ["date" => "SECOND_FOLLOWUP_DATE", "remark" => "SECOND_FOLLOWUP_REMARK"],
                3 => ["date" => "THREE_FOLLOWUP_DATE", "remark" => "THREE_FOLLOWUP_REMARK"],
                4 => ["date" => "FOUR_FOLLOWUP_DATE", "remark" => "FOUR_FOLLOWUP_REMARK"],
                5 => ["date" => "FIVE_FOLLOWUP_DATE", "remark" => "FIVE_FOLLOWUP_REMARK"]
            ];
            if($date < $todayDate) {
                $response['status'] = 'failed';
                $response['message'] = "select current or future date.";
            }
            if (!isset($columns[$index])) {
                $response['status'] = 'failed';
                $response['message'] = "Invalid follow-up index.";
            }
            try {
                $remarks = $dbX->where('ID', $leadId)->getOne('myleadsrecords');
                if (!empty($remarks[$columns[$index]['date']]) && !empty($remarks[$columns[$index]['remark']])) {
                    $response['status'] = 'failed';
                    $response['message'] = "Follow-up already added.";
                    return $response;
                }
                $updateData = [
                    $columns[$index]['date']   => $date,
                    $columns[$index]['remark'] => $notes
                ];

                $udpated = $dbX->where('ID', $leadId)->update('myleadsrecords', $updateData);
                if(isset($udpated)){
                    $response['status'] = 'success';
                    $response['message'] = "Lead follow Up updated.";
                }
            } catch (Exception $e) {
                $response['status'] = 'failed';
                $response['message'] = "something went to wrong. try again...!";
            }
        }else{
            $response['status'] = 'failed';
            $response['message'] = 'parameter are missing.';
        }
        return $response;
    }
    public function setLeadNote(){
        global $dbX;
        if (!empty($_POST["noteInput"]) && !empty($_POST["leadId"])) {
            $leadId    = $_POST["leadId"];
            $noteInput = $_POST["noteInput"];
            $userId    = 1;  // logged-in User ID
            try{
                $result = $dbX->insert('lead_notes', [
                    'LEAD_ID'    => $leadId,
                    'USER_ID'    => $userId,
                    'NOTE'  => $noteInput, 
                    'NOTED_DATE' => date('Y-m-d H:i:s'),
                ]);
                if ($result) {
                    $response['status']  = 'success';
                    $response['message'] = 'Note added successfully.';
                } else {
                    $response['status']  = 'failed';
                    $response['message'] = 'Failed to add note.';
                }
            }catch (Exception $e){
                $response['status']  = 'failed';
                $response['message'] = "something went to wrong. try again...!";
            }
        } else {
            $response['status']  = 'failed';
            $response['message'] = 'Parameters are missing.';
        }
        
        return $response;
    }
    public function getLeadNotes() {
        global $dbX;
        if (!empty($_POST["lead_id"])) {
            $leadId = $_POST["lead_id"];
            try {
                $leadNotes = $dbX
                    ->join("users u", "u.ID = ln.USER_ID", "LEFT")
                    ->where("ln.LEAD_ID", $leadId)
                    ->orderBy("ln.ID", "DESC")
                    ->get("lead_notes ln", null, [
                        "ln.ID",
                        "ln.NOTED_DATE",
                        "ln.NOTE",
                        "u.USER_NAME as userName",
                    ]);
                if (!empty($leadNotes)) {
                    $html = "";
                    foreach ($leadNotes as $note) {
                        $profileImage = "./static/avatars/001m.jpg";
                        $html .= '
                            <div class="chat-item mb-3 ">
                                <div class="row align-items-end">
                                    <div class="col-auto">
                                        <span class="avatar avatar-1" style="background-image: url(' . $profileImage . ')"></span>
                                    </div>
                                    <div class="col col-lg-9">
                                        <div class="chat-bubble chat-bubble-me">
                                            <div class="chat-bubble-title">
                                                <div class="row">
                                                    <div class="col chat-bubble-author">' . htmlspecialchars($note["userName"]) . '</div>
                                                    <div class="col-auto chat-bubble-date">' . date("h:i A", strtotime($note["NOTED_DATE"])) . '</div>
                                                </div>
                                            </div>
                                            <div class="chat-bubble-body">
                                                <p>' . htmlspecialchars($note["NOTE"]) . '</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                    }
                    $response = [
                        "status" => "success",
                        "html"   => $html
                    ];
                } else {
                    $response = [
                        "status"  => "failed",
                        "message" => "No notes found.",
                        "html"    => "<p class='text-muted'>No notes available.</p>"
                    ];
                }
            } catch (Exception $e) {
                $response = [
                    "status"  => "failed",
                    "message" => $e->getMessage()
                ];
            }
        } else {
            $response = [
                "status"  => "failed",
                "message" => "Lead ID is missing."
            ];
        }
        return $response;
    }
    public function getLeadAssignedToUsers(){
        global $dbX;
        $response = [];
        if (!empty($_POST["lead_id"])) {
            $users = $dbX->join('lead_assigned ua', 'ua.USER_ID = u.ID AND ua.LEAD_ID = ' . $_POST['lead_id'], 'LEFT')
                         ->get('users u', null, 'u.ID, u.USER_NAME, ua.LEAD_ID');  
            if ($users) {
                $response['status'] = 'success';
                $response['users'] = $users;
                $response['message'] = "Lead assignments fetched successfully.";
            } else {
                $response['status'] = 'failed';
                $response['message'] = 'No users found or no assignments for the given lead.';
            }
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Lead ID is required.';
        }
        return $response;
    }

    public function getLeadStatus(){
        global $dbX;
        if (!empty($_POST["lead_id"])) {
            $statuses = 
                [
                    "NEW"            => "New",
                    "CONTACTED"      => "Contacted",
                    "QUALIFIED"      => "Qualified",
                    "OPPORTUNITY"    => "Opportunity",
                    "DEMO_SCHEDULED" => "Demo Schedule",
                    "DEMO_DONE"      => "Demo Done",
                    "IN_NEGOTIATION" => "In Negotiation",
                    "CONVERTED"      => "Converted",
                    "DISQAULIFIED"   => "Disqaulified",
                    "LOST"           => "Lost",
                    "LIVE"           => "Live",
                    "OTHER"          => "Other",
                ];
                $leadStatus = $dbX->where('ID', $_POST["lead_id"])->getOne('myleadsrecords', ['ID', 'STATUS']);
            if (!empty($leadStatus)) {
                $response['status'] = 'success';
                $response['leadStatuses'] = $statuses;
                $response['leadStatus'] = $leadStatus;
                return $response;
            } else {
                $response['status'] = 'failed';
                $response['message'] = 'Error fetching values.';
                return $response;
            }
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Missing parameter.';
            return $response;
        }
    }
}