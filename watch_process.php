<?php
if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    // Check if the process exists
    exec("ps -p $pid -o comm=", $output);

    if (!empty($output) && $output[0] !== "COMMAND") {
        // Process is still running
        header('Content-Type: application/json');
        echo json_encode(true);
    } else {
        // Process has exited
        $processes = isset($_COOKIE['watched_processes']) ? json_decode($_COOKIE['watched_processes'], true) : [];

        if (isset($processes[$pid])) {
            unset($processes[$pid]);
            if (empty($processes)) {
                // No more watched processes, remove the cookie
                setcookie('watched_processes', '', time() - 3600); // Expire the cookie
            } else {
                // Update the watched processes cookie
                setcookie('watched_processes', json_encode($processes));
            }
        }

        header('Content-Type: application/json');
        echo json_encode(false);
    }
}
?>
