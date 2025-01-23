<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sec2pay_leads";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql_leads = "
    SELECT
    leads.ID AS lead_id,
    leads.LEAD_NAME as lead_name,
    leads.LEAD_REMARK as lead_remark,
    leads.LEAD_PRODUCT as lead_product,
    leads.LEAD_PHONE as lead_phone,
    leads.CREATED_TIMESTAMP as created_timestamp,
    leads.LEAD_STATUS as lead_status,
    leads.LEAD_SOURCE as lead_source,
    GROUP_CONCAT(users.USER_NAME SEPARATOR ', ') AS assigned_users
FROM
    leads
LEFT JOIN lead_assigned ON leads.ID = lead_assigned.lead_id
LEFT JOIN users ON lead_assigned.user_id = users.ID
GROUP BY
    leads.ID
";

$result_leads = $conn->query($sql_leads);

$leads = [];
if ($result_leads->num_rows > 0) {
    while ($row = $result_leads->fetch_assoc()) {
        $leads[] = $row;
    }
}

// Query to fetch employees
$sql_employees = "SELECT ID, USER_NAME FROM users";
$result_employees = $conn->query($sql_employees);
$employees = [];
if ($result_employees->num_rows > 0) {
    while ($row = $result_employees->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Return leads and employees as JSON
$response = [
    'tasks' => $leads,
    'employees' => $employees
];

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
