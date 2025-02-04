<?php
require_once('config_db.php');

class MyFunctions
{
     
    static  $dbX=null;
    public function __construct()
    {
        require_once ('dbcore/MysqliDb.php');
        global $dbX;
        global $dbUserName;
        global $dbPassword;
        global $dbName;
        $this->$dbX = new MysqliDb ('localhost', 'root', $dbPassword, $dbName);
        
        $this->$dbX->autoReconnect = false;
        $this->$dbX->connect();    
        if(!$this->$dbX) 
        {
            die("Database error");
        }
        else
        {
            echo "connected to db";
        }  
    }
    
    public function getQoute($id)
    {
        global $dbX;
        $this->$dbX->where("ID", $id);
        $qouteInfo=$this->$dbX->getOne("leads");
        print_r($qouteInfo);
        // return $qouteInfo["WELCOME_MSG"]." says ". $qouteInfo["USER"];
    }
}