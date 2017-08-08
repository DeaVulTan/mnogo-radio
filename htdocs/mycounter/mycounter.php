<?php
$Site['DBHost'] = "192.168.140.108";
$Site['DBUser'] = "mycounter";
$Site['DBPass'] = "wLeucYG4F4b7pzYB";
$Site['DBBase'] = "mycounter";
$Site['DBTable'] = "mycounter_radio";

$InitialHits = 0;
$InitialHosts = 0;
$TodayHits = 0;
$TodayHosts = 0;
$TotalHits = 0;
$TotalHosts = 0;
$TodayHits = 0;
$TodayHosts = 0;

$SiteDB = mysql_connect($Site['DBHost'], $Site['DBUser'], $Site['DBPass']) or die("Counter: ".mysql_error());
mysql_select_db($Site['DBBase']) or die("Counter: ".mysql_error());

if (!file_exists("mycounter.dat")) {
    $fp = fopen('mycounter.dat', 'a+');
    flock($fp, LOCK_EX);
    fputs($fp, "0" . "\n");
    fputs($fp, "0" . "\n");
    fputs($fp, date("Y-m-d") . "\n");
    fputs($fp, "0" . "\n");
    fputs($fp, "0" . "\n");
    fputs($fp, "0" . "\n");
    fputs($fp, "0" . "\n");
    fflush($fp);
    flock($fp,LOCK_UN);
    fclose($fp);
}

while (!$fp = fopen('mycounter.dat', 'r')) {
    usleep(10000);
}
$TotalHits = intval(fgets($fp));
$TotalHosts = intval(fgets($fp));
$LastCountDate = fgets($fp);
$InitialHits = intval(fgets($fp));
$InitialHosts = intval(fgets($fp));
$TodayHits = intval(fgets($fp));
$TodayHosts = intval(fgets($fp));
fclose($fp);

$Recount = false;
if (trim($LastCountDate) != date("Y-m-d")) {
    $TodayHits = 0;
    $TodayHosts = 0;
    if (file_exists("mycounter.lock")) {
        $LockStat = stat("mycounter.lock");
        if ($LockStat['mtime'] < (time() - 86400)) {
            unlink("mycounter.lock");
            $fp = fopen('mycounter.lock', 'w');
            flock($fp, LOCK_EX);
            fputs($fp, date("Y-m-d H:i:s") . "\n");
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);

            $Recount = true;
        }
        unset($LockStat);
    }
    else {
        $fp = fopen('mycounter.lock', 'w');
        flock($fp, LOCK_EX);
        fputs($fp, date("Y-m-d H:i:s") . "\n");
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        $Recount = true;
    }
}

if ($Recount) {
    // INITIAL
    $Count['Query'] = "SELECT `count_type`, COUNT(*) as `count_initial`
                                FROM `".$Site['DBTable']."`
                                WHERE `count_datetime` <= '".date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), (date("d") - 70), date("Y")))."'
                                GROUP BY `count_type`";
    $Count['Result'] = mysql_query($Count['Query'], $SiteDB) or die("Counter: ".mysql_error());
    while ($Count['Row'] = mysql_fetch_array($Count['Result'])) {
        switch($Count['Row']['count_type']) {
            case "hit":
            $InitialHits += $Count['Row']['count_initial'];
            break;

            case "host":
            $InitialHosts += $Count['Row']['count_initial'];
            break;
        }
    }

    // TOTAL
    $Count['Query'] = "SELECT `count_type`, COUNT(*) as `count_total`
                            FROM `".$Site['DBTable']."`
                            WHERE `count_datetime` > '".date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), (date("d") - 70), date("Y")))."'
                            GROUP BY `count_type`";
    $Count['Result'] = mysql_query($Count['Query'], $SiteDB) or die("Counter: ".mysql_error());
    while ($Count['Row'] = mysql_fetch_array($Count['Result'])) {
        switch($Count['Row']['count_type']) {
            case "hit":
            $TotalHits = $InitialHits + $Count['Row']['count_total'];
            break;

            case "host":
            $TotalHosts = $InitialHosts + $Count['Row']['count_total'];
            break;
        }
    }
    if (file_exists("mycounter.dat")) {
        unlink ("mycounter.dat");
    }

    while (!$fp = fopen('mycounter.dat', 'a+')) {
        usleep(10000);
    }
    while (!flock($fp, LOCK_EX)) {
        usleep(10000);
    }
    
    fputs($fp, $TotalHits . "\n");
    fputs($fp, $TotalHosts . "\n");
    fputs($fp, date("Y-m-d") . "\n");
    fputs($fp, $InitialHits . "\n");
    fputs($fp, $InitialHosts . "\n");
    fputs($fp, "0" . "\n");
    fputs($fp, "0" . "\n");
    fflush($fp);
    flock($fp,LOCK_UN);
    fclose($fp);

    $Count['Query'] = "DELETE FROM `".$Site['DBTable']."` WHERE `count_datetime` <= '".date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), (date("d") - 70), date("Y")))."'";
    $Count['Result'] = mysql_query($Count['Query'], $SiteDB) or die("Counter: ".mysql_error());

//    $Count['Query'] = "OPTIMIZE TABLE `".$Site['DBTable']."`";
//    $Count['Result'] = mysql_query($Count['Query'], $SiteDB) or die("Counter: ".mysql_error());

    $LastCountDate = date("Y-m-d")."\n";

    if (file_exists("mycounter.lock")) {
        unlink("mycounter.lock");
    }
}

if (empty($TodayHits) or empty($TodayHosts)) {
    $Count['Query'] = "SELECT `count_type`, COUNT(*) as `count_total`
                            FROM `".$Site['DBTable']."`
                            WHERE `count_datetime` >= '".date("Y-m-d")." 00:00:00'
                                AND `count_datetime` <= '".date("Y-m-d")." 23:59:59'
                            GROUP BY `count_type`";
    $Count['Result'] = mysql_query($Count['Query'], $SiteDB) or die("Counter: ".mysql_error());
    while ($Count['Row'] = mysql_fetch_array($Count['Result'])) {
       switch($Count['Row']['count_type']) {
           case "hit":
           $TodayHits = $Count['Row']['count_total'];
           break;

           case "host":
           $TodayHosts = $Count['Row']['count_total'];
           break;
       }
    }
}

if (!isset($_COOKIE["myCounter"])) {
    $TodayHosts++;
    $TodayHits++;
    $CountAddType = "host";
    
    $timeEx=mktime(23, 59, 59);
    setcookie("myCounter", "true", $timeEx);
}
else {
    $TodayHits++;
    $CountAddType = "hit";
}

switch ($CountAddType) {
    case "host":
    $Count['Query'] = "INSERT INTO `".$Site['DBTable']."` (`count_datetime`, `count_type`, `user_ip`, `user_agent`)
                            VALUES ('".date("Y-m-d H:i:s")."', 'host', '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."'),
                                   ('".date("Y-m-d H:i:s")."', 'hit', '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."')";
    break;

    case "hit":
    $Count['Query'] = "INSERT INTO `".$Site['DBTable']."` (`count_datetime`, `count_type`, `user_ip`, `user_agent`)
                            VALUES ('".date("Y-m-d H:i:s")."', 'hit', '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."')";
    break;
}
$Count['Result'] = mysql_query($Count['Query'], $SiteDB) or die("Counter: ".mysql_error());
unset ($Count);

mysql_close($SiteDB);
unset ($Site);

while (!$fp = fopen('mycounter.dat', 'w')) {
    usleep(10000);
}
while (!flock($fp, LOCK_EX)) {
    usleep(10000);
}
fputs($fp, $TotalHits . "\n");
fputs($fp, $TotalHosts . "\n");
fputs($fp, $LastCountDate);
fputs($fp, $InitialHits . "\n");
fputs($fp, $InitialHosts . "\n");
fputs($fp, $TodayHits . "\n");
fputs($fp, $TodayHosts . "\n");
fflush($fp);
flock($fp,LOCK_UN);
fclose($fp);

$TotalHits += $TodayHits;
$TotalHosts += $TodayHosts;

$im = imagecreatefrompng("mycounter.png");
$TextColor = imagecolorallocate($im, 0, 0, 0);

$x = imagesx($im) - imagefontwidth(1) * strlen($TodayHits) - 2;
imagestring($im, 1.5, $x, 1, $TodayHits, $TextColor);

$x = imagesx($im) - imagefontwidth(1) * strlen($TodayHosts) - 2;
imagestring($im, 1.5, $x, 8, $TodayHosts, $TextColor);

$x = imagesx($im) - imagefontwidth(1) * strlen($TotalHits) - 2;
imagestring($im, 1.5, $x, 16, $TotalHits, $TextColor);

$x = imagesx($im) - imagefontwidth(1) * strlen($TotalHosts) - 2;
imagestring($im, 1.5, $x, 23, $TotalHosts, $TextColor);

header("Content-Type: image/png");
imagepng($im);
?>
