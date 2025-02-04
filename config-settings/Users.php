<?php
require_once('config_db.php');
require_once('MysqliDb.php');

class Users
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
    public function getUsers()
    {
        global $dbX;
        try{
            $users = $dbX->where('ROLE','EMPLOYEE')->orderBy('ID', 'DESC')->get('myadmins24');
            if (!empty($users)) {
                $response['status'] = 'success';
                $response['message'] = "Users retrieved successfully.";
                $response['users'] = $users;
            } else {
                $response['status'] = 'error';
                $response['message'] = "No users found.";
            }
        } catch (Exception  $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}