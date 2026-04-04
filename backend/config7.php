<?php ob_start();
date_default_timezone_set('Asia/Kolkata');
$year = time() + 31536000;
$now = date("d/m/Y, h:i:s A");
$ipa = $_SERVER['REMOTE_ADDR'];
$newrd = date("Y-m-d");

if (!isset($con) || !$con instanceof mysqli) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $con = new mysqli("p:localhost", "u490792554_fjaaz2026", "z0Mq9tI&123", "u490792554_fjaaz2026");
        $con->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection error. Please try again later.");
    }
}


$settings = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM jm_settings WHERE id='1'"));


$webtitle = "Funded Jaaz";
$extn = ""; // for alert online payment received..
$baseurl = "https://www.fundedjaaz.com";
$apiurl = "https://www.jaazmarkets.com/api/jaaz-markets-funded-platform";
$apikey = "jmfp_live_2026_production";

// WhatsApp API
$wapiinstanceidz = "699D45E27F2E2"; // 698DB211017BA;
$wapitokenz = "698db2035a3d6"; // 698db2035a3d6; 
?>