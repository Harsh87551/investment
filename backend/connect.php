<?php error_reporting(1); session_start();
date_default_timezone_set('Asia/Dubai');
$dd= date("d/m/Y"); $rd= date("Y/m/d"); $dt= date("h:i:s A"); $now = date("d/m/Y, h:i:s A"); $nowex=date('Y/n/j G:i:s'); $newnow=strtotime($nowex); 
$ipa = $_SERVER['REMOTE_ADDR']; $newrd=date("Y-m-d"); $year = time() + 31536000;

    //$con = mysqli_connect("localhost","sriramias_ab9","Sjm7p0?9","sriramias_db_ab1");
	$con = mysqli_connect("p:localhost","u490792554_fjaaz2026","h3:QPa=WbsB+$7wU","u490792554_fjaaz2026");
    mysqli_query($con,'SET character_set_results=utf8');        
    mysqli_query($con,'SET names=utf8');
    mysqli_query($con,'SET character_set_client=utf8');        
    mysqli_query($con,'SET character_set_connection=utf8');
    mysqli_query($con,'SET collation_connection=utf8_general_ci');
    mysqli_query($con,"SET SESSION sql_mode = ''"); 
	mysqli_query($con,"SET GLOBAL sql_mode=''");
	$encriptionkey="SRIAmI2023";
	$baseurl="https://invest.fundedjaaz.com";

	 ?>
