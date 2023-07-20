<?php
require '../PHPMailer-master/PHPMailerAutoload.php'; // Include PHPMailer library

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $recipientEmail = $_POST['recipientEmail'];
    $attachmentPath = $_POST['attachmentPath'];
    $pid = $_POST['pid'];
    $processName = $_POST['processName'];

    // Start the loop to check the process status every 5 seconds
    $response = 'true';
    while ($response === 'true') {
        // Check the process status using watch_process.php
        $watchProcessUrl = 'http://localhost/watch_process.php?pid=' . $pid;
        $response = file_get_contents($watchProcessUrl);
        sleep(5); // Wait for 5 seconds before checking again
    }

    // Process has finished, send the email
    $mail = new PHPMailer;
    $mail->isSMTP();
    // Configure SMTP settings and API key
    $mail->Host = 'your_smtp_host';
    $mail->SMTPAuth = true;
    $mail->Username = 'your_gmail_sender_email';
    $mail->Password = 'your_gmail_sender_api_key';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Set email content
    $mail->setFrom('your_gmail_sender_email', 'Process Watcher');
    $mail->addAddress($recipientEmail);
    $mail->addAttachment($attachmentPath);
    $mail->Subject = 'Process Watcher - ' . $processName . ' Finished';
    $mail->Body = $processName . ' has finished running.';

    // Send the email
    if (!$mail->send()) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Email sent successfully!';
    }
} else {
    echo 'Invalid request method.';
}
?>
