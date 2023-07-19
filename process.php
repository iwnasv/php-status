<!DOCTYPE html>
<html>
<!--
    TODO:
    * EMAIL CHECKBOX
    * LIVE FILTER (JS)
-->
<head>
    <title>Process Watcher</title>
    <script>
        function showNotification(message) {
            // Check if the Notification API is supported
            if ('Notification' in window) {
                // Check the permission status
                if (Notification.permission === 'granted') {
                    // Create and show the notification
                    new Notification('Process Watcher', {
                        body: message,
                        icon: 'path/to/notification-icon.png' // Replace with the path to your notification icon
                    });
                } else if (Notification.permission !== 'denied') {
                    // Request permission from the user
                    Notification.requestPermission().then((permission) => {
                        if (permission === 'granted') {
                            // Create and show the notification
                            new Notification('Process Watcher', {
                                body: message,
                                icon: 'path/to/notification-icon.png' // Replace with the path to your notification icon
                            });
                        }
                    });
                }
            }
        }

        function watchProcess(pid, processName) {
            const watchButton = document.getElementById('watch_' + pid);
            watchButton.disabled = true;
            watchButton.innerHTML = 'Watching...';

            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);

                        if (response === true) {
                            // Process is still running, check again after 2 seconds
                            setTimeout(() => watchProcess(pid, processName), 2000);
                        } else {
                            alert(processName + ' has finished running');
                            watchButton.innerHTML = 'Finished';
                            watchButton.disabled = true;

                            // Change the color of the table cell corresponding to the watched process
                            const processRow = document.getElementById('row_' + pid);
                            processRow.style.backgroundColor = '#f2f2f2'; // Change to your desired color

                            // Remove the process from the table
                            const processTable = processRow.parentElement;
                            processTable.removeChild(processRow);

                            // Remove the process from the watched processes cookie
                            const watchedProcesses = getWatchedProcesses();
                            delete watchedProcesses[pid];
                            setWatchedProcesses(watchedProcesses);
                        }
                    } else {
                        alert('Failed to watch process ' + pid);
                        watchButton.innerHTML = 'Watch';
                        watchButton.disabled = false;
                    }
                }
            };

            // Store the process name in the cookie
            const watchedProcesses = getWatchedProcesses();
            watchedProcesses[pid] = processName;
            setWatchedProcesses(watchedProcesses);

            xhr.open('GET', 'watch_process.php?pid=' + pid, true);
            xhr.send();
        }

        // Function to get the value of a cookie
        function getCookie(name) {
            const value = '; ' + document.cookie;
            const parts = value.split('; ' + name + '=');
            if (parts.length === 2) return parts.pop().split(';').shift();
        }

        // Function to get the watched processes from the cookie
        function getWatchedProcesses() {
            const watchedProcessesCookie = getCookie('watched_processes');
            return watchedProcessesCookie ? JSON.parse(watchedProcessesCookie) : {};
        }

        // Function to set the watched processes in the cookie
        //function setWatchedProcesses(watchedProcesses) {
        //    document.cookie = 'watched_processes=' + JSON.stringify(watchedProcesses) + '; path=/';
        //}
    function setWatchedProcesses(watchedProcesses) {
        const cookieString = Object.keys(watchedProcesses).map((pid) => {
            const processName = watchedProcesses[pid];
            return `${pid}=${encodeURIComponent(processName)}`;
        }).join('; ');

        document.cookie = cookieString + '; path=/';
    }
    </script>
</head>
<body>
    <h1>Process Watcher</h1>
    <table>
        <tr>
            <th>Process Name</th>
            <th>Process ID</th>
            <th>Action</th>
        </tr>
        <tr>
          <td><input id="filter" onchange="console.log('add the live search function here...')" type="text"></input></td>
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
                    <button id="email_<?php echo $pid; ?>" onclick="emailProcess(<?php echo $pid; ?>, '<?php echo $processName; ?>')">Email</button>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
