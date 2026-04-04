<?php
include 'connect.php'; 

$response = array();
$json_array = array(); // Initialize the first array

if ($_POST['case'] == "course") {
    $resultf = mysqli_query($con, "SELECT coursename AS classname, metadescription, CASE 
                     WHEN pic = '' THEN 'https://admin.sriramsias.com/upload/course/nocourse_pic.jpg' 
                     ELSE pic END AS pic, duration, ID AS courseid 
                                   FROM `courses` 
                                   WHERE hide='0' AND tabname='course' AND publish='1' and istestseries='0'
                                   ORDER BY sortorder ASC");
    while ($rowf = mysqli_fetch_array($resultf)) {
        $json_array[] = $rowf;
    }
    $response['Data'] = $json_array;
    $response['message'] = 'Data displayed';

} elseif ($_POST['case'] == "coursedetails") {
    $resultf = mysqli_query($con, "SELECT coursename, CONCAT('https://www.sriramsias.com/course-details/', courseurl) AS courseurl, metadescription, details, syllabus, branches, faculty, packages, duration, CASE WHEN pic = '' THEN 'https://admin.sriramsias.com/upload/course/nocourse_pic.jpg' ELSE pic END AS pic, learnit, relatedcourse FROM `courses` WHERE ID = '$_POST[courseid]'");
    while ($rowf = mysqli_fetch_array($resultf)) {
        $response['coursedetails'] = $rowf;
    }

} elseif ($_POST['case'] == "mycourse") {
    $resultf = mysqli_query($con, "SELECT t1.sessionid, t1.hide, t1.totalamount, t1.discount, t1.doa, t1.class1, t1.batch1, 
                                   t1.m1subject, t1.m2subject, t1.m3subject, t1.optional, 
                                   t2.coursename AS classname, 
                                   (t1.totalamount - t1.discount - IFNULL(SUM(t3.transactionamount), 0)) AS remaining, 
                                   t4.batchname 
                                   FROM `admissionnew` t1 
                                   INNER JOIN `courses` t2 ON t2.ID = t1.class1 AND t2.istestseries = '0' 
                                   LEFT JOIN `payinstallmentnew` t3 ON t3.ad_id = t1.ID AND t3.paymentstatus = 'Y' 
                                   INNER JOIN `batchnew` t4 ON t4.ID = t1.batch1 
                                   WHERE t1.studentid = '$_POST[stid]' AND t1.validate = '1' 
                                   GROUP BY t1.ID 
                                   ORDER BY t1.ID DESC");
    while ($rowf = mysqli_fetch_array($resultf)) {
        $json_array[] = $rowf;
    }
    $response['Data'] = $json_array;
    $response['message'] = 'Data displayed';

} elseif ($_POST['case'] == "mytestseries") {
    $resultf = mysqli_query($con, "SELECT t1.sessionid, t1.hide, t1.totalamount, t1.discount, t1.doa, t1.class1, t1.batch1, 
                                   t1.m1subject, t1.m2subject, t1.m3subject, t1.optional, 
                                   t2.coursename AS classname, 
                                   (t1.totalamount - t1.discount - IFNULL(SUM(t3.transactionamount), 0)) AS remaining, 
                                   t4.batchname 
                                   FROM `admissionnew` t1 
                                   INNER JOIN `courses` t2 ON t2.ID = t1.class1 AND t2.istestseries = '1' 
                                   LEFT JOIN `payinstallmentnew` t3 ON t3.ad_id = t1.ID AND t3.paymentstatus = 'Y' 
                                   INNER JOIN `batchnew` t4 ON t4.ID = t1.batch1 
                                   WHERE t1.studentid = '$_POST[stid]' AND t1.validate = '1' 
                                   GROUP BY t1.ID 
                                   ORDER BY t1.ID DESC");
    while ($rowf = mysqli_fetch_array($resultf)) {
        $json_array[] = $rowf;
    }
    $response['Data'] = $json_array;
    $response['message'] = 'Data displayed';

} else if ($_POST['case'] == "branches") {
   $resultf = mysqli_query($con, "SELECT ID as branchid, branchname, branchurl, branchpic, displayname, branchint FROM `branches` WHERE hide = '0' AND status='1' ORDER BY ID ASC");
    while ($rowf = mysqli_fetch_array($resultf)) {
        $json_array[] = $rowf;
    }
    $response['Data'] = $json_array;
    $response['message'] = 'Data displayed';


} else if ($_POST['case'] == "branchcourse") {

$resultf = mysqli_query($con, "SELECT ID as courseid, coursename AS classname, metadescription, CASE WHEN pic = '' THEN 'https://admin.sriramsias.com/upload/course/nocourse_pic.jpg' ELSE pic END AS pic, duration, ID AS courseid FROM `courses` WHERE hide='0' AND tabname='course' AND publish='1' AND find_in_set('$_POST[branchid]',`branches`) > 0 ORDER BY sortorder ASC");

while ($rowf = mysqli_fetch_array($resultf)) {
    $rowf['batches'] = array(); // Initialize batches array inside each course

    if ($_POST['stid'] != "") {    
        if ($_POST['type'] == "Online") {   
            $resultp = mysqli_query($con, "SELECT t1.ID as batchid, t1.batchname, t1.classname, t1.packagename, REPLACE(t1.price, '.00', '') as price, REPLACE(t1.price, '.00', '') as amount, REPLACE(t1.partprice, '.00', '') as partprice, t1.type, t1.attachoptional, t2.validate as alreadyenrolled, t3.package as packagename FROM `batchnew` t1 LEFT JOIN `admissionnew` t2 ON t2.batch1=t1.ID and t2.hide='0' and t2.studentid='$_POST[stid]' LEFT JOIN `batchpackage` t3 ON t3.ID=t1.packageid WHERE t1.publish='1' and t1.hide='' and t1.status='1' and t1.type='$_POST[type]' and t1.classname='$rowf[courseid]' ORDER BY t1.price ASC");
        } else {
            $resultp = mysqli_query($con, "SELECT t1.ID as batchid, t1.batchname, t1.classname, t1.packagename, REPLACE(t1.price, '.00', '') as price, REPLACE(t1.price, '.00', '') as amount, REPLACE(t1.partprice, '.00', '') as partprice, t1.type, t1.attachoptional, t2.validate as alreadyenrolled, t3.package as packagename FROM `batchnew` t1 LEFT JOIN `admissionnew` t2 ON t2.batch1=t1.ID and t2.hide='0' and t2.studentid='$_POST[stid]' LEFT JOIN `batchpackage` t3 ON t3.ID=t1.packageid WHERE t1.publish='1' and t1.hide='' and t1.status='1' and t1.branchid='$_POST[branchid]' and t1.type='Offline' and t1.classname='$rowf[courseid]' ORDER BY t1.price ASC");
        }    
    } else {
        if ($_POST['type'] == "Online") {   
            $resultp = mysqli_query($con, "SELECT t1.ID as batchid, t1.batchname, t1.classname, t1.packagename, REPLACE(t1.price, '.00', '') as price, REPLACE(t1.price, '.00', '') as amount, REPLACE(t1.partprice, '.00', '') as partprice, t1.type, t1.attachoptional, t3.package as packagename FROM `batchnew` t1 LEFT JOIN `batchpackage` t3 ON t3.ID=t1.packageid WHERE t1.publish='1' and t1.hide='' and t1.status='1' and t1.type='$_POST[type]' and t1.classname='$rowf[courseid]' ORDER BY t1.price ASC");
        } else {
            $resultp = mysqli_query($con, "SELECT t1.ID as batchid, t1.batchname, t1.classname, t1.packagename, REPLACE(t1.price, '.00', '') as price, REPLACE(t1.price, '.00', '') as amount, REPLACE(t1.partprice, '.00', '') as partprice, t1.type, t1.attachoptional, t3.package as packagename FROM `batchnew` t1 LEFT JOIN `batchpackage` t3 ON t3.ID=t1.packageid WHERE t1.publish='1' and t1.hide='' and t1.status='1' and t1.branchid='$_POST[branchid]' and t1.type='Offline' and t1.classname='$rowf[courseid]' ORDER BY t1.price ASC");
        }
    }

    while ($rowp = mysqli_fetch_array($resultp)) {
        if ($rowp['attachoptional'] == "1") {
            $resultop = mysqli_query($con, "SELECT currentbatchid as optionalid, coursename as subject FROM `courses` WHERE hide='0' AND tabname = 'optional' and status='1' ORDER BY sortorder ASC");
            $json_array2 = array();
            while ($rowop = mysqli_fetch_array($resultop)) {
                $json_array2[] = $rowop;
            }
            $rowp['optionalsubject'] = $json_array2;
        }
        
        if ($rowp['batchid'] == "382" || $rowp['batchid'] == "394") {
            $rowp['tslot'] = "1";
            $resultsl = mysqli_query($con, "SELECT t1.title, t1.seat AS maxlimit, COUNT(CASE WHEN t2.testslot = t1.title THEN t2.ID END) AS slots FROM `testslot` t1 INNER JOIN `admissionnew` t2 ON t2.testslot=t1.title WHERE t2.validate = '1' AND t2.hide = '0' and t1.status='1' and t1.branchid='$_POST[branchid]' and t2.branchid='$_POST[branchid]' GROUP BY t1.title ORDER BY t1.ID ASC");
            $json_array3 = array();
            while ($rowsl = mysqli_fetch_array($resultsl)) {
                $json_array3[] = $rowsl;
            }
            $rowp['testslot'] = $json_array3;
        } else {
            $rowp['tslot'] = "0";
        }

        $rowf['batches'][] = $rowp; // Append batch to course
    }

    $json_array[] = $rowf; // Append course with its batches
}

$response['Data'] = $json_array;
$response['message'] = 'Data displayed';




} else if($_POST['case']=="package"){
if($_POST['type']=="Online"){   
$resultp = mysqli_query($con,"SELECT t1.ID as batchid, t1.batchname, t1.classname, t1.packagename, REPLACE(t1.price, '.00', '') as price, REPLACE(t1.price, '.00', '') as amount, REPLACE(t1.partprice, '.00', '') as partprice, t1.type, t1.attachoptional, t2.validate as alreadyenrolled, t3.package as packagename FROM `batchnew` t1 LEFT JOIN `admissionnew` t2 ON t2.batch1=t1.ID and t2.hide='0' and t2.studentid='$_POST[stid]' LEFT JOIN `batchpackage` t3 ON t3.ID=t1.packageid WHERE t1.publish='1' and t1.hide='' and t1.status='1' and t1.type='$_POST[type]' ORDER BY t1.price ASC"); } else {
$resultp = mysqli_query($con,"SELECT t1.ID as batchid, t1.batchname, t1.classname, t1.packagename, REPLACE(t1.price, '.00', '') as price, REPLACE(t1.price, '.00', '') as amount, REPLACE(t1.partprice, '.00', '') as partprice,t1.type, t1.attachoptional, t2.validate as alreadyenrolled, t3.package as packagename FROM `batchnew` t1 LEFT JOIN `admissionnew` t2 ON t2.batch1=t1.ID and t2.hide='0' and t2.studentid='$_POST[stid]' LEFT JOIN `batchpackage` t3 ON t3.ID=t1.packageid WHERE t1.publish='1' and t1.hide='' and t1.status='1' and t1.branchid='$_POST[branchid]' and t1.type='Offline' ORDER BY t1.price ASC");  }
while ($rowp = mysqli_fetch_array($resultp)) {
    if ($rowp['attachoptional'] == "1") {
        $resultop = mysqli_query($con, "SELECT currentbatchid as optionalid, coursename as subject FROM `courses` WHERE hide='0' AND tabname = 'optional' and status='1' ORDER BY sortorder ASC");
        $json_array2 = array(); // Initialize the second array for each iteration
        while ($rowop = mysqli_fetch_array($resultop)) {$json_array2[] = $rowop;}
        $rowp['optionalsubject'] = $json_array2;}
		
	if ($rowp['batchid'] == "382" || $rowp['batchid'] == "394") {$rowp['tslot']="1";
        $resultsl = mysqli_query($con, "SELECT t1.title, t1.seat AS maxlimit, COUNT(CASE WHEN t2.testslot = t1.title THEN t2.ID END) AS slots FROM `testslot` t1 INNER JOIN `admissionnew` t2 ON t2.testslot=t1.title WHERE t2.validate = '1' AND t2.hide = '0' and t1.status='1' and t1.branchid='$_POST[branchid]' and t2.branchid='$_POST[branchid]' GROUP BY t1.title ORDER BY t1.ID ASC");
        $json_array3 = array(); // Initialize the second array for each iteration
        while ($rowsl = mysqli_fetch_array($resultsl)) {$json_array3[] = $rowsl;}
        $rowp['testslot'] = $json_array3;}	else {$rowp['tslot']="0";}
		
		
    $json_array[] = $rowp; }	
  	$response['Data']=$json_array;  
  	$response['message'] = 'Data displayed';		
	
	
 } else if($_POST['case']=="enrollnow"){ $sessionid=date("ymdHis").rand(10,99);
  $coursex=$_POST['batchid']; $dividefee=$_POST['partialamount']; $sn = $_POST['stid']; $newstid = $_POST['newstid']; $studentname= $_POST['studentname'];	
  $feetype=$_POST['feetype']; $totalamount=$_POST['totalamount'];	$paymode="Online"; 
  if($coursex=="381" || $coursex=="382" || $coursex=="393" || $coursex=="394"){$validate="1"; $statusx="1"; $hidex="0";} else {$validate="0"; $statusx="0"; $hidex="1";}
  if($feetype=="1"){$paidamount=$_POST['totalamount']; } else {$paidamount=$_POST['partialamount'];}
     
	 $resultb = mysqli_query($con,"SELECT * FROM `batchnew` WHERE ID='$coursex'");
     while($rowb = mysqli_fetch_array($resultb)) { $classname=$rowb['classname'];  $packagename=$rowb['packagename']; $branchid=$rowb['branchid'];} 

 if($_POST['optionalid']=="0"){$optionalid="";} else {$optionalid=$_POST['optionalid'];}
 if($_POST['testslot']=="0"){$testslotid="";} else {$testslotid=$_POST['testslot'];}
 $sqlad="INSERT INTO `admissionnew`(`sessionid`,`totalamount`,`totalpaid`,`discount`,`doa`,`ay`,`branchid`,`studentid`, `class1`,`batch1`,`rd`, `createdby`, `createdon`, `validate`,`source`,`studentname`,`sessionidx`,`feetype`,`optional`,`m1subject`,`m2subject`,`m3subject`, `studenttype`, `package1`,`newstudentid`, `testslot`) VALUES
('$sessionid','$totalamount','$paidamount','0','$rd','$ay','$branchid','$sn','$classname','$coursex','$rd','admin','$now','$validate','APP','$studentname','$sessionid','$feetype','$optionalid','$m1subject','$m2subject','$m3subject','new','$packagename','$newstid','$testslotid')";
if (!mysqli_query($con,$sqlad)){die('Error: ' . mysqli_error($con)); }

$sn2 = mysqli_insert_id($con);


$sqlpay="INSERT INTO `payinstallmentnew`(`sessionid`, `transactionamount`,`totalamount`,`mode`,`doa`,`ay`,`branchid`,`studentid`, `ad_id`,`rd`, `createdby`,`createdon`, `validate`, `type`,`new`,`class1`,`sessionidx`,`studentname`,`rtdate`,`stax`,`paymentdate`,`transactiondate`,`premarks`, `studenttype`, `newstudentid`) VALUES
('$sessionid','$paidamount','$totalamount','$paymode','$rd','$ay','$branchid','$sn','$sn2','$rd','APP','$now','$validate','income','new','$classname','$sessionid','$studentname','$rd','$gst','$dd','$dd','APP Registration','new','$newstid')";
if (!mysqli_query($con,$sqlpay)){die('Error: ' . mysqli_error($con)); }
 
   	if($paidamount >="1"){$response['gateway'] = '1'; } else { $response['gateway'] = '0';}
	$response['status'] = '1';
	$response['message'] = 'Enrolled successfully..';



} else {
    $response['message'] = 'No case found';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
