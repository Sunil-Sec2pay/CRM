<?php
//namespace ConfigSettings;

require_once('config_db.php');
require_once('MysqliDb.php');

class CommonHelpers
{
    private $db;
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

    public function activityLogs( $loggedInUserId = null, $action="",$previousValue=null,$newValue=null){
        global $dbX;
        $dbX->insert('activity_log', [
            'user_id' => $loggedInUserId,
            'action' => $action,
            'previous_value' => json_encode($previousValue),
            'new_value' => json_encode($newValue),
        ]);
    }
}
?>