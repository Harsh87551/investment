<?php 
include 'connect.php'; 

$tbl_name = "students";
$logincookie = date("ymdHis");
$otp = rand(1000, 9999);
$response = array();
$sessionid = date("ymdHis") . rand(10, 99);

if ($_POST['case'] == "otpsignin") {
    $username1 = $_POST['studentid'];
    $resultdata = mysqli_query($con, "SELECT * FROM `$tbl_name` WHERE (studentid='$username1' OR mobile='$username1') AND status='1' AND hide='0'");
    
    if ($rowdata = mysqli_fetch_array($resultdata)) {
        $mmobile = $rowdata['mobile'];
        
        if ($mmobile != "") {
			$phoneNumber = "91". $mmobile; 
			// Define the data to send
			$messageData = [
				"messaging_product" => "whatsapp",
				"recipient_type" => "individual",
				"to" => $phoneNumber,
				"type" => "template",
				"template" => [
					"name" => "login_otp",
					"language" => [
						"code" => "en"
					],
					"components" => [
						[
							"type" => "body",
							"parameters" => [
								[
									"type" => "text",
									"text" => $otp
								]
							]
						],
						[
							"type" => "button",
							"sub_type" => "url",
							"index" => "0",
							"parameters" => [
								[
									"type" => "text",
									"text" => $otp
								]
							]
						]
					]
				]
			];
			
			// Initialize cURL session
			$ch = curl_init($facebookUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $authToken ]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
			$responsex = curl_exec($ch);
			curl_close($ch);
            // {#var#} is your OTP for {#var#} at {#var#} - {#var#}
            $msg = "$otp is your OTP for Sign In at Mobile App - SRIRAMs IAS";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://smpp.webtechsolution.co/http-tokenkeyapi.php");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "authentic-key=353353686976616e616e643530301604145245&senderid=SRIIAS&route=1&number=$mmobile&message=" . urlencode($msg) . "&templateid=1207161892216614998");
            $output = curl_exec($ch);
            curl_close($ch);
            
            $response['status'] = '1';
            $response['otp'] = $otp;
            $response['contact'] = $mmobile;
            $response['message'] = 'OTP sent successfully';
        } else {
            $response['status'] = '0';
            $response['message'] = 'Sorry! Mobile number is not attached to the ID';
        }
    } else {
        $response['status'] = '0';
        $response['message'] = 'Sorry! Student ID does not exist';
    }
} elseif ($_POST['case'] == "signin" && $_POST['otp1'] != "") {
    $username1 = $_POST['studentid'];
    
    if ($_POST['otp2'] == $_POST['otp1']) {
        $resultdata = mysqli_query($con, "SELECT * FROM `$tbl_name` WHERE (studentid='$username1' OR mobile='$username1') AND status='1' AND hide='0'");
        
        if ($row = mysqli_fetch_array($resultdata)) {
            $sqlup = "UPDATE `$tbl_name` SET token='$_POST[token]' WHERE ID='$row[ID]'";
            if (!mysqli_query($con, $sqlup)) {
                die('Error: ' . mysqli_error($con));
            }
            $response['status'] = '1';
            $response['message'] = 'Login successfully';
            $response['studentcims'] = $row['studentid'];
            $response['logincookie'] = $logincookie;
            $response['studenttype'] = $row['studenttype'];
        }
    } else {
        $response['status'] = '0';
        $response['message'] = 'Sorry! OTP mismatch';
    }
} elseif ($_POST['case'] == "signin" && $_POST['password'] != "") {
    $username1 = $_POST['studentid'];
    $resultdata = mysqli_query($con, "SELECT * FROM `$tbl_name` WHERE (studentid='$username1' OR mobile='$username1') AND status='1' AND hide='0' AND password!='' ORDER BY ID DESC LIMIT 1");
    
    if ($row = mysqli_fetch_array($resultdata)) {
        if ($_POST['password'] == $row['password']) {
            $sqlup = "UPDATE `$tbl_name` SET logincookie='$logincookie', token='$_POST[token]' WHERE ID='$row[ID]'";
            if (!mysqli_query($con, $sqlup)) {
                die('Error: ' . mysqli_error($con));
            }
            $response['status'] = '1';
            $response['message'] = 'Login successfully';
            $response['studentcims'] = $row['studentid'];
            $response['logincookie'] = $logincookie;
            $response['studenttype'] = $row['studenttype'];
        } else {
            $response['status'] = '0';
            $response['message'] = 'Sorry! Password mismatch';
        }
    } else {
        $response['status'] = '0';
        $response['message'] = 'Sorry! No account found';
    }
}

// Additional cases like "otpsignup", "verifysignup", "signup", "changepassword", and "forget" would follow the same formatting structure.
// Each case is modularized for better readability and maintenance.

echo json_encode($response);
?>
