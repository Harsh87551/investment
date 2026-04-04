<?php include 'connect.php'; $tbl_name="students"; $mahajyoti="0"; $thispanel="SRIRAMs IAS";
      $response = array();
 
 if($_POST['case']=="profiledata"){ 
/*$view = "SELECT t1.*, t2.branch as branchname FROM `$tbl_name` t1 LEFT JOIN `branches` t2 ON t2.branchid=t1.branchid WHERE t1.studentid='$_POST[studentcims]' and t1.logincookie='$_POST[logincookie]' and t1.status='1' and t1.hide='0' and t1.mahajyoti='0'"; */
$view = "SELECT t1.*, t2.branchname FROM `$tbl_name` t1 LEFT JOIN `branches` t2 ON t2.ID=t1.branchid WHERE t1.studentid!='' AND t1.studentid='$_POST[studentcims]' and t1.status='1' and t1.hide='0'"; 
$run_view = mysqli_query($con, $view); 
  if($row = mysqli_fetch_array($run_view)){ 
  $rdx=date("d/m/Y", strtotime($row['rd'])); 
  $response['status'] = '1'; 
  $response['stid']=$row['ID']; 
  $response['newstid']=$row['sessionid'];  
  $response['branchid']=$row['branchid']; 
  $response['branchname']=$row['branchname'];
  $response['gender']=$row['gender']; 
  $response['sdob']=$row['dob']; 
  $response['smobile']= $row['mobile']; 
  $response['stmail']= $row['email']; 
  $response['createdon'] = $row['createdon']; 
  $response['stuid']= $row['studentid']; 
  $response['oldstudent'] = $row['oldstudent']; 
  $response['spassword'] = $row['password']; 
  $response['thisrd'] = $row['rd']; 
  $response['referalcode'] = $row['referalcode']; 
  $response['myreferal'] = $row['myreferal']; 
  $response['expassword']= $row['password']; 
  $response['cpassword']= $row['cpassword']; 
  $response['oldpassword']= $row['oldpassword']; 
  $response['studenttype']=$row['studenttype']; 
  $response['address']=$row['address'];
  $response['say']=$row['ay'];  
  $response['testimonials']=$row['testimonials'];
  $response['appnotification']=$row['appnotification'];
  
  if($row['studenttype']=="govt"){$response['govt']="1";} else { $response['govt']="0";}
  $response['studentname'] = $row['fname'].' '.$row['mname'].' '.$row['lname']; 
  $break_name = explode(' ',$row['fname']);  
  $response['snm'] = $break_name[0]; $response['virtualid']=ucwords($row['stid'].$response['govt'].'@'.$response['snm']);   
  if($row['pic']==""){$response['pic'] = $defaultpic;} else {$response['pic'] = $row['pic']; }
  if($row['token']==""){$response['token'] = "0";} else {$response['token'] = "1"; }

	$resultA = mysqli_query($con,"SELECT * FROM `admissionnew` WHERE newstudentid='$row[sessionid]' and validate='1' and hide='0' ORDER BY ID ASC LIMIT 1"); 
	if($rowA = mysqli_fetch_array($resultA)) {
	$response['thisbatch']=$rowA['batch1']; 
	$response['feeamount']=$rowA['totalamount']; 
	$response['doa']=$rowA['doa']; 
	$response['duedate']=$rowA['duedate']; 
	$response['expiry']=$rowA['expiry'];
	$response['aid']=$rowA['sessionid']; } else {$response['thisbatch']="0";}
	
   $q1p = mysqli_query($con,"SELECT SUM(transactionamount) AS tm FROM `payinstallmentnew` WHERE newstudentid='$row[sessionid]' and paymentstatus='Y'"); 	
   $rt1p = mysqli_fetch_assoc($q1p); 
   $totalpaidp = $rt1p['tm'];
   $response['showmyreferal']=$myreferal;
  
  $response['message'] = 'Profile displayed';
  } else {
  $response['status'] = '0'; 
  $response['message'] = 'Window is open at other place'; }
  
  
  } else  if($_POST['case']=="editprofile"){
   $name = $con -> real_escape_string($_POST['name']);
   $address = $con -> real_escape_string($_POST['address']);
   $city = $con -> real_escape_string($_POST['city']);
   $state = $con -> real_escape_string($_POST['state']);
$sqlx="UPDATE `$tbl_name` SET fname='$name', email='$_POST[email]', address='$address' WHERE studentid= '$_POST[studentcims]'";
if (!mysqli_query($con,$sqlx)){die('Error: ' . mysqli_error($con)); }
  
   $response['status'] = '1'; 
   $response['message'] = 'Profile updated'; 

   
  } else  if($_POST['case']=="updatenotification"){
$sqlx="UPDATE `$tbl_name` SET appnotification='0' WHERE studentid= '$_POST[studentcims]'";
if (!mysqli_query($con,$sqlx)){die('Error: ' . mysqli_error($con)); }
  
   $response['status'] = '1'; 
   $response['message'] = 'Notification status updated'; 
   
    } else if($_POST['case']=="notification"){
    $resultf = mysqli_query($con,"SELECT createdon, notification FROM `appnotifications` WHERE status='1' ORDER BY ID DESC"); 
  	while($rowf = mysqli_fetch_array($resultf)) { $json_array[]=$rowf; } 
  	$response['Data']=$json_array;  
  	$response['message'] = 'Data displayed';
   
  } else  if($_POST['case']=="updatetoken"){
$sqlx="UPDATE `$tbl_name` SET token='$_POST[token]' WHERE studentid= '$_POST[studentcims]'";
if (!mysqli_query($con,$sqlx)){die('Error: ' . mysqli_error($con)); }
  
   $response['status'] = '1'; 
   $response['message'] = 'Token updated'; 
 
   } else  if($_POST['case']=="delete"){
	$sqlx="UPDATE `$tbl_name` SET hide='1' WHERE studentid= '$_POST[studentcims]'";
	if (!mysqli_query($con,$sqlx)){die('Error: ' . mysqli_error($con)); }
  
   $response['status'] = '1'; 
   $response['message'] = 'Account deleted..'; 
     
  } else if($_POST['case']=="profilepic"){
     $path="$baseurl/upload/profile";
   if (!file_exists($path)) {mkdir($path, 0777, true);}
 
 
   if((!empty($_FILES["picname"])) && ($_FILES['picname']['error'] == 0)){
   $filename =strtolower(basename($_FILES['picname']['name']));
   $ext = substr($filename, strrpos($filename, '.') + 1);
   $namefile =  str_replace(".$ext","", $filename);
   $newfilename =date("ymdHis");
    //Determine the path to which we want to save this file
    $ext=".".$ext;
    $newname = $path. '/'. $newfilename.$ext;
    move_uploaded_file($_FILES['picname']['tmp_name'],$newname);  } 
	if($ext!=""){ //$ipic="$newfilename$ext";
	
/*	$photo = "../app/images/logo/$newfilename$ext";

$image_info = getimagesize($photo);
$width = $new_width = $image_info[0];
$height = $new_height = $image_info[1];
$type = $image_info[2];


// Load the image
switch ($type){
    case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($photo); break;
    case IMAGETYPE_GIF: $image = imagecreatefromgif($photo); break;
    case IMAGETYPE_PNG: $image = imagecreatefrompng($photo); break;
	case IMAGETYPE_BMP: $image = imagecreatefromwbmp($photo); break;	
    default:  die('Error loading '.$photo.' - File type '.$type.' not supported');    }

// Create a new, resized image
$new_width = 100;
$new_height = 100; // $height / ($width / $new_width);
$new_image = imagecreatetruecolor($new_width, $new_height);
imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

// Save the new image over the top of the original photo
switch ($type){
    case IMAGETYPE_JPEG: imagejpeg($new_image, $photo, 100);  break;
    case IMAGETYPE_GIF:  imagegif($new_image, $photo); break;
    case IMAGETYPE_PNG:  imagepng($new_image, $photo); break;
    case IMAGETYPE_BMP: imagewbmp($new_image, $photo); break;
	default: die('Error saving image: '.$photo);     }   */      
	
  	//$picdetails= $con -> real_escape_string($_POST['picname']);
	$sqlx="UPDATE `$tbl_name` SET pic='$newname' WHERE studentid= '$_POST[studentcims]'";
	if (!mysqli_query($con,$sqlx)){die('Error: ' . mysqli_error($con)); } }
  
   $response['status'] = '1'; 
   $response['message'] = 'Profile pic updated'; 

  } else {
  $response['status'] = '0'; 
  $response['message'] = 'No case found'; }
        
 header('Content-Type: application/json');
 echo json_encode($response); ?>