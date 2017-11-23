<?php
$restore_file  = "authentication.sql";
$config = parse_ini_file('AuthDB.ini');
$server_name   = $config['host'];
$username      = $config['username'];
$password      = $config['password'];
$cmd = "mysql -h {$server_name} -u {$username} -p{$password}  < $restore_file";
exec($cmd);
?>
