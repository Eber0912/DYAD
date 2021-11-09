<?
//ini_set("display_errors", "true ");
ob_start();
session_start();


if(isset($_SESSION["last_submit"])):
    if((time()-$_SESSION["last_submit"])<100):
        exit;
    endif;
endif;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


//configuration
// save into db
$servername = "localhost";
$username = "gnjgmsso_dbuser";
$password = "Admin@123";
$dbname = "gnjgmsso_house";
$email_from_name = "House";
$email_from_email= "info@sydore.com";
$email_to_admin="pdorei@gmail.com";



$select_list_tab3 = trim($_POST["select_list_tab3"]);
$select_list_tab4 = trim($_POST["select_list_tab4"]);
$select_list_tab5 = trim($_POST["select_list_tab5"]);
$select_list_tab6 = trim($_POST["select_list_tab6"]);
$select_list_tab7 = trim($_POST["select_list_tab7"]);
$select_list_tab8 = trim($_POST["select_list_tab8"]);

$text_guest = trim($_POST["text_guest"]);

$name = trim($_POST["first_name"]);
$family = trim($_POST["last_name"]);
$phone = trim($_POST["phone"]);
$email = trim($_POST["email"]);
$street = trim($_POST["street"]);
$city = trim($_POST["city"]);
$zip_code = trim($_POST["zip_code"]);

foreach($_POST as $key=>$value):
	if($value=="on"):
		$damage_types[]=$key;
	endif;
endforeach;


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "INSERT INTO tbl_house set ";
$sql.= "date='".date('Y-m-d')."',";
$sql.= "name='$name', ";
$sql.= "family='$family', ";
$sql.= "phone='$phone', "; 
$sql.= "email='$email', ";
$sql.= "street='$street', ";
$sql.= "city='$city', ";
$sql.= "zip_code='$zip_code', "; 
$sql.= "damage_type='".implode(" & ",$damage_types)."', ";
$sql.= "approx_size='$select_list_tab3', ";
$sql.= "status='$select_list_tab4', ";
$sql.= "completed='$select_list_tab5', ";
$sql.= "financing='$select_list_tab6', ";
$sql.= "insurance_claim='$select_list_tab7', ";
$sql.= "property_changes='$select_list_tab8', "; 
$sql.= "message='$text_guest' ";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully\n";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error."\n";
}

$conn->close();

// save into CSV
$header=array("Name","Family","Phone","Email","Street","City","Zip Code","Damage Type","Approx. Size","Status","Completed","Financing","Insurance Claim","Property Changes","Message");
$data =array(
	$name, 
	$family, 
	$phone, 
	$email,
	$street,
	$city,
	$zip_code, 
	implode(" & ",$damage_types),
	$select_list_tab3,
	$select_list_tab4,
	$select_list_tab5,
	$select_list_tab6,
	$select_list_tab7,
	$select_list_tab8, 
	$text_guest);

$csvfile = time()."-".date("y-m-d").".csv";
$fp = fopen("data/$csvfile","w");

fputcsv($fp, $header);
fputcsv($fp, $data);

fclose($fp);


// start with email 
$body ="";
$body.= "Name = $name\n";
$body.= "Family = $family\n";
$body.= "Phone = $phone\n"; 
$body.= "Email = $email\n";
$body.= "Street = $street\n";
$body.= "City = $city\n";
$body.= "Zip Code = $zip_code\n"; 
$body.= "Damage type = ".implode(" & ",$damage_types)."\n";
$body.= "Approx size = $select_list_tab3\n";
$body.= "Status = $select_list_tab4\n";
$body.= "Completed = $select_list_tab5\n";
$body.= "Financing = $select_list_tab6\n";
$body.= "Insurance Claim = $select_list_tab7\n";
$body.= "Property Changes = $select_list_tab8\n"; 
$body.= "Message= $text_guest ";


        
$mail = new PHPMailer(true);                              // Passing `true` enables exceptions

######################################################################################################
// Sending Email To Admin
//Recipients
$mail->setFrom($email_from_email, $email_from_name);
$mail->addAddress($email_to_admin);     // Add a recipient (Admin)
$mail->addReplyTo($email_from_email, $email_from_name);


//Attachments
$mail->addAttachment("data/$csvfile", "$csvfile");    // Optional name

//Content
$mail->isHTML(true);                                  // Set email format to HTML
$mail->Subject = 'CSV file';
$mail->Body    = $body;
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

$mail->send();

// store the last submit and so it will not send duplicated
$_SESSION["last_submit"]=time();

echo "Email has been sent!\n";

?>