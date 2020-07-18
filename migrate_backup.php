<?php
set_time_limit(0);
function translateData($table,$data){
	
	$data=array_filter($data);
	$keys=array_keys($data);
    $keys=array_map('addslashes',$keys);
    $keys=join('`,`',$keys);
    $keys="`".$keys."`";
	$vals=array_values($data);
	$vals=array_map('addslashes',$vals);
	$vals=join("','",$vals);
	$vals="'".$vals."'";
	$mysql="insert into `$table`($keys) values($vals);\n";
	
	return $mysql;
}


function deleteData($table,$column_name,$value){
	mysql_query("DELETE FROM $table WHERE $column_name=$value");
}


ini_set('display_errors', 1); 
error_reporting(E_ALL);
$db_host = "localhost";
$db_user = "root";
$db_password = "phpwind.net";

$con=mysql_connect("$db_host","$db_user","$db_password") or die("Error: No BD Connection");

mysql_select_db("cang7");
mysql_query("set names 'utf8'");

$migrate_time="2015-12-30 23:59:59";
$instore_main_file_name="instore_main_sql_".date("Y-m-d__H-i-s").".txt";
$instore_sub_file_name="instore_sub_sql".date("Y-m-d__H-i-s").".txt";

$outstore_main_file_name="outstore_main_sql".date("Y-m-d__H-i-s").".txt";
$outstore_sub_file_name="outstore_sub_sql".date("Y-m-d__H-i-s").".txt";

//instore test 
//open file
$instore_main_file = fopen($instore_main_file_name, "w");
$instore_sub_file = fopen($instore_sub_file_name, "w");

while(true){
	//search and write file
	$instore_main_sql="SELECT * FROM twms_instore_main where ism_date < '$migrate_time' limit 0,50";

	$instore_main_results = mysql_query($instore_main_sql);
	
	if(mysql_num_rows($instore_main_results)==0){
		break;
	}
	
	while($data=mysql_fetch_assoc($instore_main_results)){
		$ism_id = $data["ism_id"];
		$insert_main=translateData("twms_instore_main",$data);
		fwrite($instore_main_file, $insert_main);
		deleteData("twms_instore_main","ism_id",$ism_id);
		
		$instore_sub_sql="SELECT * FROM twms_instore_sub where iss_mainid = '$ism_id'";
		$instore_sub_results = mysql_query($instore_sub_sql);
		while($insubdata=mysql_fetch_assoc($instore_sub_results)){
			$insert_sub=translateData("twms_instore_sub",$insubdata);
			fwrite($instore_sub_file, $insert_sub);
		}
		deleteData("twms_instore_sub","iss_mainid",$ism_id);
	}
}

//close file
fclose($instore_main_file);
fclose($instore_sub_file);

//outstore test
//open file
$outstore_main_file = fopen($outstore_main_file_name, "w");
$outstore_sub_file = fopen($outstore_sub_file_name, "w");

//search and write file
while(true){
	$outstore_main_sql="SELECT * FROM twms_outstore_main where osm_date < '$migrate_time' limit 0,20";
	$outstore_main_results = mysql_query($outstore_main_sql);
	
	if(mysql_num_rows($outstore_main_results)==0){
		break;
	}
	
	while($outdata=mysql_fetch_assoc($outstore_main_results)){
		$osm_id = $outdata["osm_id"];
		$insert_main_out=translateData("twms_outstore_main",$outdata);
		fwrite($outstore_main_file, $insert_main_out);
		deleteData("twms_outstore_main","osm_id",$osm_id);
		
		$outstore_sub_sql="SELECT * FROM twms_outstore_sub where oss_mainid = '$osm_id'";
		$outstore_sub_results = mysql_query($outstore_sub_sql);
		while($outstoresubdata=mysql_fetch_assoc($outstore_sub_results)){
			$insert_sub_out=translateData("twms_outstore_sub",$outstoresubdata);
			fwrite($outstore_sub_file, $insert_sub_out);
		}
		deleteData("twms_outstore_sub","oss_mainid",$osm_id);
	}
}


//close file
fclose($outstore_main_file);
fclose($outstore_sub_file);

mysql_close($con);

?>