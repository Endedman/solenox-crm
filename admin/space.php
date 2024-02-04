<?php
// Do as you know. No support for this file.
$disk_free = disk_free_space("/");
$disk_total = disk_total_space("/");
$disk_usage = 100 - ($disk_free / $disk_total) * 100;
$disk_free1 = disk_free_space("/ftpfs");
$disk_total1 = disk_total_space("/ftpfs");
$disk_usage1 = 100 - ($disk_free1 / $disk_total1) * 100;

?>
<p>Main server</p>
<div style="width: 100%; background: #ddd; margin-top: 20px; height: 20px; position: relative;">
    <!-- <div style="position: absolute; left: 0; top: 0; height: 10px; background: red; width: <?php echo $disk_usage ?>%;">
    </div> -->
    <div style="position: absolute; left: 0%; top: 0; height: 20px; background: green; display: flex; justify-content: space-evenly;
    align-items: center; color:white; width: <?php echo $disk_usage; ?>%;">
        <?php echo round($disk_free / 1000000000, 1) . 'GB free from  ' . round($disk_total / 1000000000, 1) . 'GB // ' . round($disk_usage, 1) ?>%
    </div>
</div>
<p>19TB server</p>
<div style="width: 100%; background: #ddd; margin-top: 20px; height: 20px; position: relative;">
    <!-- <div style="position: absolute; left: 0; top: 0; height: 10px; background: red; width: <?php echo $disk_usage1 ?>%;">
    </div> -->
    <div style="position: absolute; left: 0%; top: 0; height: 20px; background: green; display: flex; justify-content: space-evenly;
    align-items: center; color:white; width: <?php echo $disk_usage1; ?>%;">
        <?php echo round($disk_free1 / 1000000000000, 1) . 'TB free from  ' . round($disk_total1 / 1000000000000, 1) . 'TB // ' . round($disk_usage1, 1) ?>%
    </div>
</div>
<?php
// echo ($df/1024/1024/1024)."GB<br>";
// //echo ($dfFree/1024/1024)."<br>";
// //echo ($dfUsed/1024/1024/1024)."GB<br>";
// echo ($folderSize/1024/1024)."MB<br>";
