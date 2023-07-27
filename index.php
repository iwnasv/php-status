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

// Read the disk usage information from the /proc/mounts file
$mounts = file('/proc/mounts');
foreach ($mounts as $mount) {
    list($device, $mountPoint) = preg_split('/\s+/', $mount);
    if (strpos($device, '/dev/sd') === 0) {
        $disks[$device] = $mountPoint;
    }
}

$diskUsage = array();
foreach ($disks as $device => $mountPoint) {
    exec("df -hP $mountPoint", $output); // Use -P option for proper output parsing
    list($filesystem, $size, $used, $available, $percentage, $mounted) = preg_split('/\s+/', $output[1]);

    $diskUsage[$mountPoint] = array(
        'usage' => $percentage,
        'free' => $available
    );
}

var_dump($disks); var_dump($diskUsage); var_dump($mountedFilesystems);
// Top 3 processes using most processor time
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
    <a href="/process.php"><h2>Process watcher</h2></a>
</body>
</html>
