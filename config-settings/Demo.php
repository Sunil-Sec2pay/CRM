<?php
require_once('config_db.php');
require_once('MysqliDb.php');

class Demo
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

    public function leadAnalytics($data) {
        global $dbX;
    
        if ($data['loggedInAdmin'] == 'ADMIN') {
            $whereCondition = "date(CREATED_AT) BETWEEN '".$data["startDate"]."' AND '".$data["endDate"]."'" ;
        } else {
            $whereCondition = "date(CREATED_AT) BETWEEN '".$data["startDate"]."' AND '".$data["endDate"]."' AND CURRENT_OWNER_ID=".$data['userId'] ;
        }
    
        $output['todayLeadsCount'] = $dbX->rawQueryValue("SELECT COUNT(*) FROM myLeadsRecords WHERE $whereCondition");
    
        $output["productStats"] = $dbX->rawQuery("SELECT COUNT(*) as 'COUNT', PRODUCT AS 'PRODUCT' FROM myLeadsRecords WHERE $whereCondition GROUP BY PRODUCT");
    
        $output["totalLeads"] = $dbX->rawQueryValue("SELECT COUNT(*) as 'TOTAL_COUNT' FROM myLeadsRecords WHERE $whereCondition LIMIT 1");
    
        $output["leadSources"] = $dbX->rawQuery("SELECT SOURCE, COUNT(*) as 'COUNT' FROM myLeadsRecords WHERE $whereCondition GROUP BY SOURCE");
    
        $output["conversionRate"] = $dbX->rawQueryValue("SELECT 
            (COUNT(CASE WHEN STATUS = 'CONVERTED' THEN 1 ELSE NULL END) / COUNT(*)) * 100 AS 'CONVERSION_RATE' 
            FROM myLeadsRecords WHERE $whereCondition");
    
        $output["avgResponseTime"] = $dbX->rawQueryValue("SELECT AVG(TIMESTAMPDIFF(MINUTE, CREATED_AT, FIRST_FOLLOWUP_DATE)) as 'AVG_RESPONSE_TIME' FROM myLeadsRecords WHERE $whereCondition");
    
    
        $output["topUsers"] = $dbX->rawQuery("SELECT ID, COUNT(*) as 'CONVERTED' FROM myLeadsRecords WHERE STATUS='CONVERTED' AND $whereCondition GROUP BY CURRENT_OWNER_ID ORDER BY CONVERTED DESC LIMIT 5");
    
        $output["statusWiseLeads"] = $dbX->rawQuery("SELECT
            COUNT(CASE WHEN status='NEW' THEN 1 ELSE NULL END) as 'NEW',
            COUNT(CASE WHEN status='CONTACTED' THEN 1 ELSE NULL END) as 'CONTACTED',
            COUNT(CASE WHEN status='QUALIFIED' THEN 1 ELSE NULL END) as 'QUALIFIED',
            COUNT(CASE WHEN status='OPPORTUNITY' THEN 1 ELSE NULL END) as 'OPPORTUNITY',
            COUNT(CASE WHEN status='DEMO_SCHEDULED' THEN 1 ELSE NULL END) as 'DEMO_SCHEDULED',
            COUNT(CASE WHEN status='DEMO_DONE' THEN 1 ELSE NULL END) as 'DEMO_DONE',
            COUNT(CASE WHEN status='IN_NEGOTIATION' THEN 1 ELSE NULL END) as 'IN_NEGOTIATION',
            COUNT(CASE WHEN status='CONVERTED' THEN 1 ELSE NULL END) as 'CONVERTED',
            COUNT(CASE WHEN status='DISQAULIFIED' THEN 1 ELSE NULL END) as 'DISQAULIFIED',
            COUNT(CASE WHEN status='LOST' THEN 1 ELSE NULL END) as 'LOST',
            COUNT(CASE WHEN status='LIVE' THEN 1 ELSE NULL END) as 'LIVE',
            COUNT(CASE WHEN status='OTHER' THEN 1 ELSE NULL END) as 'OTHER'
            FROM myLeadsRecords WHERE $whereCondition");
    
        return $output;
    }
}

$demo = new Demo();
$data['startDate'] = "2025-01-01";  
$data['endDate'] = "2025-02-03";    
$data['userId'] = 1;
$data['loggedInAdmin'] = 'ADMIN';
$result = $demo->leadAnalytics($data);

echo "<pre>";
print_r($result);
?>