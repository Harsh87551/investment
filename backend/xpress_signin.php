<?php include 'connect.php'; $tbl_name = "students"; $logincookie = date("ymdHis"); $otp = rand(1000, 9999); $sessionid=date("ymdHis").rand(10,99);
$response = array(); 

// If student login with OTP option..
if ($_POST['case'] == "otpsignin") {
    $username1 = $_POST['studentid'];
    $resultdata = mysqli_query($con, "SELECT * FROM `$tbl_name` WHERE (studentid='$username1' OR mobile='$username1') AND status='1' AND hide='0'");
    if ($rowdata = mysqli_fetch_array($resultdata)) {
        $mmobile = $rowdata['mobile'];
        
		// For mobile App verification at Google Play Store. We can remove it after verification..
		if($username1 == "7503663732"){ $otp = rand(1234, 1234);} 
		
		
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
	
	
	
	
	
	
// If student login with OTP option and verify OTP for login..	
} else if ($_POST['case'] == "signin" && $_POST['otp1'] != "") {
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
	
	
	
	
	
	
// If student login with passowrd option..	
} else if ($_POST['case'] == "signin" && $_POST['password'] != "") {
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



  
  } else if($_POST['case']=="otpsignup"){ 
  
   $mmobile = str_replace('+91','', $_POST['contact']); $mmobile = ltrim($mmobile, "0"); 
   $resultdata = mysqli_query($con,"SELECT * FROM `$tbl_name` WHERE mobile!='' and mobile='$mmobile' and hide='0'"); 
   if($rowdata = mysqli_fetch_array($resultdata)) {
   $response['status'] = '0';  $response['contact'] = $mmobile ; $response['message'] = 'Mobile number already exist'; } else {
   
  	//$msg="<#> $otp is your OTP for SRIRAMs IAS - https://sriramsias.com"; 
	$msg="<#> $otp is your OTP for Sign In at Mobile App - SRIRAMs IAS"; 
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, "http://smpp.webtechsolution.co/http-tokenkeyapi.php");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "authentic-key=353353686976616e616e643530301604145245&senderid=SRIIAS&route=1&number=$mmobile&message=".urlencode($msg)."&templateid=1207161892216614998");
	$output = curl_exec($ch);
	curl_close($ch);
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

  $response['status'] = '1'; $response['otp'] = $otp ; $response['contact'] = $mmobile ; $response['message'] = 'OTP sent successfully'; }
 
 
 
 
 
 
 
 
  
 // Its 2nd step for verify OTP when any user open the App 
 } else if($_POST['case']=="verifysignup" && $_POST['otp1']!=""){  $mmobile = str_replace('+91','', $_POST['contact']); $mmobile = ltrim($mmobile, "0"); 
  	//$mmobile = ltrim($_POST['contact'], "0"); 
  		if($_POST['otp2']== $_POST['otp1']){ 
   			$resultdata = mysqli_query($con,"SELECT * FROM `$tbl_name` WHERE mobile!='' and mobile='$mmobile' and status='1' and hide='0'"); 
   			if($row = mysqli_fetch_array($resultdata)) {
			$response['status'] = '1'; $response['message'] = 'Login successfully'; $response['studentcims'] = $row['studentid'] ;  
  			$response['logincookie'] = $logincookie ;  $response['studenttype'] = $row['studenttype'] ; } else { 
			
			$response['status'] = '1'; $response['message'] = 'Sign Up successfully'; $response['studentcims'] = $mmobile ; 
			$response['logincookie'] = $logincookie ;  $response['studenttype'] = "new" ;  }
			
			} else {
			 $response['status'] = '0'; $response['message'] = 'Sorry! OTP Mismatch'; $response['studentcims'] = ''; }
			 






			 
 // Its 2nd step for verify OTP when any user open the App 
 } else if($_POST['case']=="signup"){ 
     $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!#*";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 6; $i++)     {
    $n = rand(0, $alphaLength);
    $pass[] = $alphabet[$n];   }
    $myreferal=implode($pass);
	
	$resultay = mysqli_query($con,"SELECT * FROM `academicyear` WHERE status = '1' and currentyear='1' ORDER BY ID DESC LIMIT 1");
	while($roway = mysqli_fetch_array($resultay)) {$ay=$roway['academicyear']; }		 
	$name = $con -> real_escape_string($_POST['name']);
	$address = $con -> real_escape_string($_POST['address']);
// Start student table entry... 
$sql="INSERT INTO `$tbl_name`(`status`, `validate`,`sessionid`,`branchid`,`fname`,`address`, `mobile`,`email`, `createdby`,`time`,`rd`, `source`, `studentid`, `password`, `referalcode`, `myreferal`,`studenttype`,`ay`, `token`) VALUES ('1','1','$sessionid','1','$name','$address','$_POST[studentcims]','$_POST[email]','$name', '$dt','$rd','app','$_POST[studentcims]', '$_POST[password]', '','$myreferal', 'new','$ay', '$_POST[token]')";
if (!mysqli_query($con,$sql)){die('Error: ' . mysqli_error($con)); }	

$sn = mysqli_insert_id($con); 
$response['status'] = '1'; $response['message'] = 'Sign Up successfully'; $response['studentcims'] = $_POST['studentcims'] ; 
			$response['logincookie'] = $logincookie ;  $response['studenttype'] = "new" ;




// Its for changes the password..
 } else if($_POST['case']=="changepassword"){
      	$resultdata = mysqli_query($con,"SELECT * FROM `$tbl_name` WHERE studentid='$_POST[studentcims]' and hide='0'"); 
  		while($row = mysqli_fetch_array($resultdata)) {$expassword = $row['password'];
  
    	if($_POST['cpassword']==$expassword){$npassword=$_POST['npassword'];
		$sqlx="UPDATE `$tbl_name` SET password ='$npassword' WHERE studentid='$_POST[studentcims]'"; 
		if (!mysqli_query($con,$sqlx)){die('Error: ' . mysqli_error($con));}
        $response['status'] = '1'; $response['message'] = 'Password updated';   } else {
		$response['status'] = '0'; $response['message'] = 'Current password is wrong'; }    }






				
 } else if($_POST['case']=="forget"){   
   $mmobile = str_replace('+91','', $_POST['contact']); $mmobile = ltrim($mmobile, "0"); 
   $resultdata = mysqli_query($con,"SELECT * FROM `$tbl_name` WHERE mobile!='' and mobile='$mmobile'"); 
   if($rowdata = mysqli_fetch_array($resultdata)) {
 
$variable = array($rowdata['fname'], $rowdata['password'], "Student Panel", "https://student.sriramsias.com"); 
$phoneNumber = "91" . $mmobile;
$messageData = array(
    "messaging_product" => "whatsapp",
    "to" => $phoneNumber,
    "type" => "template",
    "template" => array(
        "name" => "forget_password",
        "language" => array(
            "code" => "en"
        ),
        "components" => array(
            array(
                "type" => "body",
                "parameters" => array_map(function($value) {
                    return array("type" => "text", "text" => $value);
                }, $variable)
            )
        )
    )
);

$ch = curl_init($facebookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $authToken
));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
$responsex = curl_exec($ch);
curl_close($ch);

  $response['status'] = '1'; $response['message'] = 'Password sent successfully'; }	 else {	
  $response['status'] = '0'; $response['message'] = 'Mobile does not exist'; }	 
  

  
   
  } else {
  $response['status'] = '0'; 
  $response['message'] = 'No case found'; }
        
 header('Content-Type: application/json');
 echo json_encode($response);
?>
