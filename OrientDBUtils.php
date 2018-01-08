<?php
//require_once 'OrientDB\\OrientDB.php';
require "PhpOrient/vendor/autoload.php";
use PhpOrient\PhpOrient;

$hostname = 'localhost';
$port     = 2424;
$username = 'root';
$password = '12345';
$database = 'CoOccurrenceGraph';
$dbuser = 'admin';
$dbpassword = 'admin';
$label_subject = 'topic';
$label_id = 'name';
$v_table = 'dinh';

$client;
$sessionToken;

if ($_REQUEST["q"] == "getSubject"){
	connectToDB($hostname, $port, $username, $password, $database, $dbuser, $dbpassword);
	getSubject();
}
else {
	connectToDB($hostname, $port, $username, $password, $database, $dbuser, $dbpassword);
	queryDB($_REQUEST["q"]);
}

function connectOrientDB($hostname, $port, $username, $password) {
	global $client;
	$client = new PhpOrient();
	$client->hostname = $hostname;
	$client->port     = $port;
	$client->username = $username;
	$client->password = $password;
	$client->setSessionToken(true);

	$client->connect();
	global $sessionToken;
	$sessionToken = $client->getSessionToken();

	//print_r($client->dbList());
}

function connectToDB($hostname, $port, $username, $password, $database, $dbuser, $dbpassword){
	global $client;
	connectOrientDB($hostname, $port, $username, $password);

	$db = $client->dbOpen($database, $dbuser, $dbpassword);
}

function getClient($sessionToken){
	global $hostname, $port;
    global $client;
    $client = new PhpOrient($hostname, $port);
    $client->setSessionToken($sessionToken);
}

function getToken(){
    global $client;
    global $sessionToken;

    $sessionToken = $client->getSessionToken();

    if ($sessionToken == ''){
        $client->setSessionToken(true);
        $sessionToken = $client->getSessionToken();
    }
}

function getSubject(){
	global $client;
	global $label_subject, $v_table;
	$sql = "SELECT DISTINCT(". $label_subject .") FROM ". $v_table;

	$result = $client->query($sql);
	$subject = array();

	foreach ($result as $record) {
		$data = $record->getOData();
		$subject[] = $data['DISTINCT'];
	}

	print_r(json_encode($subject));
}

function queryDB($subject){
	global $client;
	global $label_subject;
	global $label_id, $v_table;

	$sql = "SELECT FROM " . $v_table . " WHERE ". $label_subject ." = '". $subject . "' LIMIT -1";

	$result = $client->query($sql);

	//var_dump($result);

	$arrNodes = "";
	$arrDict = array();
	$arrOut = array();

	foreach ($result as $record) {
		$data = $record->getOData();
		$rid = $record->getRid();

		// if ($arrNodes == "") $arrNodes = '{ "id" : "' . $data['ID'] . '"}';
		// else $arrNodes = $arrNodes . ', { "id" : "' . $data['ID'] . '"}';
		if ($arrNodes == "") $arrNodes = "{ id : '" . $data[$label_id] . "'}";
		else $arrNodes = $arrNodes . ", { id : '" . $data[$label_id] . "'}";
		$arrDict["$rid"] = $data[$label_id];

		$sql2 = "SELECT out(cooccurr_with) FROM " . $v_table . " WHERE @rid = ". $rid . " LIMIT -1";
		$all_out = $client->query($sql2);

		$arrTemp = array();

		//print_r('AAA<br>');
		//var_dump($rid);

		foreach ($all_out as $out) {
			$row = $out->getOData();
			//$arrTemp += $row['out']
			//var_dump($row['out']);
			foreach ($row['out'] as $item) {
				$arrTemp[] = ($item->__toString());
			}
		}

		if (!empty($arrTemp)) $arrOut[$data[$label_id]] = $arrTemp;

		//print_r($data['ID'] . '<br>');
		//print_r($rid . '<br>');
		//var_dump($all_out);
		//print_r($data['out_cooccurr_with'] . '<br>');
	}
	//print_r($arrNodes);
	//print_r($arrDict);
	//var_dump($arrOut);

	$arrLinks = "";

	foreach ($arrOut as $v => $outs) {
		foreach ($outs as $out) {
			// if ($arrLinks == "") $arrLinks = '{ "source" : "' . $v . '", "target" : "' . $arrDict[$out] . '"}';
			// else $arrLinks = $arrLinks . ', { "source" : "' . $v . '", "target" : "' . $arrDict[$out] . '"}';
			if ($arrLinks == "") $arrLinks = "{ source : '" . $v . "', target : '" . $arrDict[$out] . "'}";
			else $arrLinks = $arrLinks . ", { source : '" . $v . "', target : '" . $arrDict[$out] . "'}";
		}
	}
	//var_dump($arrDict);
	//var_dump($arrNodes);
	//var_dump($arrLinks);

	// $graph = '{"node" : [' . $arrNodes . '], "links" : [' . $arrLinks . ']}';
	$graph = '{nodes : [' . $arrNodes . '], links : [' . $arrLinks . ']}';
	print_r($graph);
}

?>