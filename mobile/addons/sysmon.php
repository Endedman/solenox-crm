<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<title>System Monitor</title>
<style>
    .progress-bar {
        background-color: #dedede;
        height: 30px;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 10px;
        position: relative;
    }
    
    .progress-bar span {
        display: block;
        height: 100%;
        background-color: #007BFF;
        transition: width 2s ease-in-out; /* Smooth transition for bar width */
        line-height: 30px; /* Match the bar height */
        color: white;
        text-align: right;
        padding-right: 10px;
    }
    .error {
    background-color: red;
    color: yellow; /* Or any color that provides good contrast against red */
}
.success {
    background-color: green;
    color: black; /* Or any color that provides good contrast against green */
}
</style>
</head>
<body>
<div data-role="header">
    <h1>System Monitor</h1>
</div>

<div data-role="content">
    <b>Использование памяти:</b>
    <div id="memory-bar" class="progress-bar"><span style="width: 0%">0%</span></div>
    
    <b>Использование диска:</b>
    <div id="disk-bar" class="progress-bar"><span style="width: 0%">0%</span></div>
    <b>Использование FTP:</b>
    <div id="ftp-bar" class="progress-bar"><span style="width: 0%">0%</span></div>
    
    <b>Использование ЦП:</b>
    <div id="cpu-bar" class="progress-bar"><span style="width: 0%">0%</span></div>
    <b>Статус портов:</b>
    <ul id="results-list"></ul>
</div>

<script>
function updateSystemStats() {
    // Запрос к `server_stats.php` для получения текущей статистики системы
    fetch('/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>addons/server_stats.php')
        .then(response => response.json())
        .then(data => {
            // Update the memory usage bar
            const memoryBar = document.getElementById('memory-bar').firstElementChild;
            memoryBar.style.width = data.memory_usage + '%';
            memoryBar.textContent = data.memory_usage.toFixed(2) + '%';
            
            // Update the disk usage bar
            const diskBar = document.getElementById('disk-bar').firstElementChild;
            diskBar.style.width = data.disk_usage + '%';
            diskBar.textContent = data.disk_usage.toFixed(2) + '%';
                        
            // Update the ftp usage bar
            const ftpBar = document.getElementById('ftp-bar').firstElementChild;
            ftpBar.style.width = data.ftp_usage + '%';
            ftpBar.textContent = data.ftp_usage.toFixed(2) + '%';
            // Update the CPU usage bar
            const cpuBar = document.getElementById('cpu-bar').firstElementChild;
            cpuBar.style.width = data.cpu_usage + '%';
            cpuBar.textContent = data.cpu_usage.toFixed(2) + '%';
        })
        .catch(error => {
            console.error('Error fetching system stats:', error);
        });
}
// Define a function to update the port status
function updatePortStatus() {
    // Use AJAX to fetch the port check results from 'portmon.php'
    fetch('/<?= JSTORE_WEB_MOBILE_SHORT_DIR ?>addons/portmon.php')
        .then(response => response.text()) // Expecting to receive HTML from PHP script
        .then(html => {
            // Update the list of port statuses with received HTML
            const resultsList = document.getElementById('results-list');
            resultsList.innerHTML = html;

            // Apply the listview refresh if you are using jQuery Mobile (as suggested by your code above)
            // If using pure JavaScript, you can comment out this line
            //$('#results-list').listview().listview('refresh');
        })
        .catch(error => {
            console.error('Error refreshing port statuses:', error);
        });
}
// Set interval to update the statistics every 5 seconds
setInterval(updateSystemStats, 5000);
setInterval(updateSystemStats, 5000); // You would have this from previous examples

// Perform an initial update on page load
updateSystemStats();
updatePortStatus();

</script>
</body>
</html>
