<?php
if (!isset($_GET['pid']) || !isset($_GET['email']) || !isset($_GET['file'])) {
    echo "Missing required parameters.";
    exit(1);
}

$pid = $_GET['pid'];
$email = $_GET['email'];
$file = $_GET['file'];
// todo: filter
// Construct the command to execute the email-on-exit.sh script
// $command = "email-on-exit.sh -p $pid -e $email -f $file";
$command = "email-on-exit.sh -p $pid -e $email -f $file";
// todo: change the script to allow recepient and attachment arguments

$output = shell_exec($command);

// Output the result
echo $output;
?>
