<?php
$services = array(
    'nginx',
    'mysql',
    'apache2',
    'tomcat9',
    'solr',
    'ufw',
    'ssh'
    // Add more services to monitor here
);

function getServiceStatus($service) {
    $output = '';
    exec("systemctl is-active $service", $output, $returnCode);

    if ($returnCode === 0) {
        return '<span class="ok">OK</span>';
    } else {
        return '<span class="fail">' . $returnCode . '</span>';
    }
}

// Get client's IP address
$clientIP = $_SERVER['REMOTE_ADDR'];

// Disk usage and free space
$disks = array();

// Check /proc/mounts for mounted filesystems
$mountedFilesystems = shell_exec('cat /proc/mounts | grep "^/dev/sd[a-z]" | awk \'{print $2}\'');
$mountedFilesystems = explode("\n", trim($mountedFilesystems));

foreach ($mountedFilesystems as $filesystem) {
    if (!empty($filesystem)) {
        $disks[$filesystem] = $filesystem;
    }
}

$diskUsage = array();
foreach ($disks as $mountPoint => $filesystem) {
    $output = shell_exec("df -hP $filesystem | awk 'FNR==2{print $5\" \"$4}'");
    list($usage, $free) = preg_split('/\s+/', trim($output));

    $diskUsage[$mountPoint] = array(
        'usage' => $usage,
        'free' => $free
    );
}

// Top 3 processes using the most processor time
$topProcesses = shell_exec('ps -eo pid,comm,%cpu --sort=-%cpu | head -n 4');

// Docker containers and their status
$dockerContainers = array();
exec('docker ps --format "{{.Names}}: {{.Status}}"', $dockerOutput);
foreach ($dockerOutput as $dockerLine) {
    $dockerContainers[] = $dockerLine;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Server Status</title>
    <style>
        .ok {
            color: green;
        }

        .fail {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Server Status</h1>

    <p>Client IP: <?php echo $clientIP; ?></p>

    <h2>Service Status</h2>
    <ul>
        <?php foreach ($services as $service): ?>
            <li><?php echo $service; ?>: <?php echo getServiceStatus($service); ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Disk Usage</h2>
    <table>
        <tr>
            <th>Mount Point</th>
            <th>Usage %</th>
            <th>Free Space</th>
        </tr>
        <?php foreach ($diskUsage as $mountPoint => $usageInfo) { ?>
            <tr>
                <td><?php echo $mountPoint; ?></td>
                <td><?php echo $usageInfo['usage']; ?></td>
                <td><?php echo $usageInfo['free']; ?></td>
            </tr>
        <?php } ?>
    </table>

    <h2>Top Processes by CPU Usage</h2>
    <pre><?php echo $topProcesses; ?></pre>

    <h2>Docker Containers</h2>
    <ul>
        <?php foreach ($dockerContainers as $containerStatus): ?>
            <li><?php echo $containerStatus; ?></li>
        <?php endforeach; ?>
    </ul>

    <a href="/process.php"><h2>Process Watcher</h2></a>

    <script src="script.js"></script>
</body>
</html>
