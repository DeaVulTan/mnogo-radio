<?
//коннект к базе
$DBHost = 'localhost';
$DBUser = 'radiostream';
$Pass = 'nee7ziCiej';
$DBname = 'radiostream';
$db = mysql_connect( $DBHost, $DBUser, $Pass );
if($db==FALSE)
{
	$string = mysql_error( $db );
}
mysql_select_db( $DBname );
if(mysql_errno()!=0 )
{
	$string = mysql_error( $db );
};
//конец коннекта к базе

while( !$fp=fopen('counter.dat', 'r') ) usleep(10000);
$countPerDay=intval(fgets($fp));
$countAll=intval(fgets($fp));
$date=fgets($fp);
fclose($fp);

//данные если уже считали
 	$countAll=0;
   	$countPerDay=0;
	$sql="SELECT count( * ) FROM `Counter`
		WHERE date_format( `vremya` , '%Y-%m-%d' ) = date_format( Now( ) , '%Y-%m-%d' )";
	$rezult=mysql_query($sql);
	$itog1=mysql_fetch_array($rezult);
	$countPerDay+=$itog1[0];
	$sql="SELECT count( * ) FROM `Counter`";
	$rezult=mysql_query($sql);
	$itog=mysql_fetch_array($rezult);
	$countAll+= $itog[0];
//конец данные если уже считали
	
if ( trim($date) != date("d.m.y") )
{
    $countPerDay=0;
    while( !$fp=fopen('counter.dat', 'w') ) usleep(10000);
    while ( !flock($fp, LOCK_EX) ) usleep(10000);    
    fputs($fp, $countPerDay . "\n");
    fputs($fp, $countAll . "\n");
    fputs($fp, date("d.m.y") . "\n");    
    fflush($fp);
    flock($fp,LOCK_UN);
    fclose($fp);
}

if ( !isset($_COOKIE["Counter"]) )
{
    $countPerDay++; $countAll++;
    while( !$fp=fopen('counter.dat', 'w') ) usleep(10000);
    while ( !flock($fp, LOCK_EX) ) usleep(10000);    
    fputs($fp, $countPerDay . "\n");
    fputs($fp, $countAll . "\n");
    fputs($fp, date("d.m.y") . "\n");        
    fflush($fp);
    flock($fp,LOCK_UN);
    fclose($fp);
    
    $timeEx=mktime(23, 59, 59);
    setcookie("Counter", "true", $timeEx);

//данные в базу если еще не считали
    //записали в базу
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $zapros="INSERT INTO `Counter` ( `id` , `vremya` , `ip` , `brouzer` )
             VALUES ('',Now(),'".$ip."', '".$agent."')";
    $rezultat=mysql_query($zapros);
    
  $countAll=401256;
  $countPerDay=0;
    
    //заново пересчитали
    $sql="SELECT count( * ) FROM `Counter`
		WHERE date_format( `vremya` , '%Y-%m-%d' ) = date_format( Now( ) , '%Y-%m-%d' )";
	$rezult=mysql_query($sql);
	$itog1=mysql_fetch_array($rezult);
	$countPerDay+=$itog1[0];
	$sql="SELECT count( * ) FROM `Counter`";
	$rezult=mysql_query($sql);
	$itog=mysql_fetch_array($rezult);
	$countAll+= $itog[0];
//конец данные в базу если еще не считали	
}

$im=imagecreatefrompng("counter.png");
$bg=imagecolorallocate($im, 0, 0, 0);
$white=imagecolorallocate($im, 255, 255, 255);

// all
$x=imagesx($im)-imagefontwidth("")*strlen($countAll)-5;
imagestring($im, 1.5, $x, 17, $countAll, $white);

// perDay
$x=imagesx($im)-imagefontwidth("")*strlen($countPerDay)-5;
imagestring($im, 1.5, $x, 4, $countPerDay, $white);

header("Content-Type: image/png");
imagejpeg($im);
?>