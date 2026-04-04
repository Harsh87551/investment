<?php include 'config7.php';

if (!isset($_COOKIE['jmadmin'])) {
    header("Location: index?message=Login to continue..");
    exit;
}

// Get logged-in user details
$stmt1 = $con->prepare("SELECT * FROM `jm_admin` WHERE id = ? AND hide = '0' AND status = '1'");
$stmt1->bind_param("i", $_COOKIE['jmadmin']);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($row1 = $result1->fetch_assoc()) {
    $userid = $row1['id'];
    $username = $row1['name'];
    $usercontact = $row1['contact'];
    $useremail = $row1['email'];
    $expassword = $row1['password'];
    $createdby = $row1['createdby'];
    $country = $row1['country'];
    $bio = $row1['bio'];
    $admin = $row1['admin'];
} else {
    header("Location: index?message=Window is open at other place..");
    exit;
}
$stmt1->close();

if ($country == "") {
    $country = "India";
}
$userid = (int) $userid;
?>