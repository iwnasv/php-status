<!DOCTYPE html>
<html>
<!--
    TODO:
    * EMAIL CHECKBOX
    * LIVE FILTER (JS)
-->
<head>
    <title>Process Watcher</title>
    <script src="script.js" defer></script> <!-- Link the script.js file -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Process Watcher</h1>
    <input id="filter" oninput="filterTableRows()" type="text" placeholder="Filter by process name"> <!-- Call filterTableRows on each keystroke -->
    <table>
        <tr>
            <th>Process Name</th>
            <th>Process ID</th>
            <th>Action</th>
        </tr>
        <?php
        $processes = shell_exec('ps -e -o comm,pid --no-header');
        $processes = explode("\n", trim($processes));

        foreach ($processes as $process) {
            list($processName, $pid) = preg_split('/\s+/', $process);
            ?>
            <tr id="row_<?php echo $pid; ?>">
                <td><?php echo $processName; ?></td>
                <td><?php echo $pid; ?></td>
                <td>
                    <button id="watch_<?php echo $pid; ?>" onclick="watchProcess(<?php echo $pid; ?>, '<?php echo $processName; ?>')">Watch</button>
                    <button id="email_<?php echo $pid; ?>" onclick="showEmailForm(<?php echo $pid; ?>, '<?php echo $processName; ?>')">Email</button>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Hidden email form -->
    <div id="emailForm" style="display: none;">
        <h2>Email Notification</h2>
        <form id="emailNotificationForm" onsubmit="sendEmail(<?= $pid; ?>)">
            <label for="recipientEmail">Recipient Email Address:</label>
            <input type="text" id="recipientEmail" required>
            <br>
            <label for="attachmentPath">Attachment File Path:</label>
            <input type="text" id="attachmentPath" required>
            <br>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
