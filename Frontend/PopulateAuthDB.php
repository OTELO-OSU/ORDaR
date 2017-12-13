<?php
$config                    = parse_ini_file('AuthDB.ini');
$csv = array_map('str_getcsv', file($argv[1]));
unset($csv[0]);
try {
$dbh = new PDO('mysql:host='.$config['host'].';dbname='.$config['database'].'', $config['username'], $config['password']);
} catch (PDOException $e) {
	    print "Error !: " . $e->getMessage() ."\n" ;
	    die();

}
$stmt = $dbh->prepare("INSERT INTO users (name, firstname,mail,mdp,status,mail_validation,type,ORCID_ID,created_at,updated_at) VALUES (:name, :firstname,:mail,:mdp,:status,:mail_validation,:type,:ORCID_ID,:created_at,:updated_at)");
foreach ($csv as $key => $value) {
	$stmt->bindParam(':name', $value[1]);
	$stmt->bindParam(':firstname', $value[2]);
	$stmt->bindParam(':mail', $value[0]);
	$mdp=bin2hex(openssl_random_pseudo_bytes(16));
	$mdp=password_hash($mdp, PASSWORD_DEFAULT);
	$stmt->bindParam(':mdp', $mdp);
	$status="1";
	$stmt->bindParam(':status', $status);
	$mail_validation="1";
	$stmt->bindParam(':mail_validation', $mail_validation);
	$type="0";
	$stmt->bindParam(':type', $type);
	$ORCID_ID=NULL;
	$stmt->bindParam(':ORCID_ID', $ORCID_ID);
	$date=date('Y-m-d');
	$stmt->bindParam(':created_at', $date);
	$stmt->bindParam(':updated_at', $date);
	$stmt->execute();
}
echo "All users are successfully imported ! \n";


?>