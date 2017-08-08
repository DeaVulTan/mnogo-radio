<?php require_once("../include/const.inc.php"); ?>
<?php require_once("../include/functions.inc.php"); ?>
<?php
	if (!empty($_POST['vote_group']) and !is_nan($_POST['vote_id'])) {
		$RadioDB = ConnectDB(RADIO_DBHOST, RADIO_DBUSER, RADIO_DBPASS, RADIO_DBBASE, true) or die("Error Radio.CONNECT: ".mysql_error());
		$VoteCheckQuery = "SELECT `vote_log_id` FROM `votes_logs` WHERE `vote_log_date` = '".date("Y-m-d")."' AND `vote_group` = '".addslashes($_POST['vote_group'])."' AND `vote_log_ipaddr` = '".$_SERVER['REMOTE_ADDR']."'";
		$VoteCheckResult = mysql_query($VoteCheckQuery, $RadioDB) or die("Error Radio.VoteCheckQuery: ".mysql_error());
		if (mysql_num_rows($VoteCheckResult) == 0) {
			$VoteUpdateQuery = "UPDATE `votes` SET `votes`.`vote_counter` = `votes`.`vote_counter` + 1 WHERE `vote_id` = '".$_POST['vote_id']."' AND `votes`.`vote_added` > '".date("Y-m-d", (mktime(0, 0, 0, date("m"), date("d"), date("Y")) - VOTE_PERIOD))."'";
			mysql_query($VoteUpdateQuery, $RadioDB) or die("Error Radio.VoteUpdateQuery: ".mysql_error());
			$VoteLogUpdateQuery = "INSERT INTO `votes_logs` (`vote_artist_id`, `vote_group`, `vote_log_date`, `vote_log_ipaddr`, `vote_log_useragent`) VALUES ('".$_POST['vote_id']."', '".addslashes($_POST['vote_group'])."', '".date("Y-m-d")."', '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."')";
			mysql_query($VoteLogUpdateQuery, $RadioDB) or die("Error Radio.VoteLogVoteLogUpdateQuery: ".mysql_error());
			unset ($VoteUpdateQuery, $VoteLogUpdateQuery);
			$GLOBALS['VoteUpdateResult'] = array("key" => stripslashes($_POST['vote_group']),
												 "value" => "<div class=\"ok\">Ваш голос учтен</div>");
		}
		else {
			$GLOBALS['VoteUpdateResult'] = array("key" => stripslashes($_POST['vote_group']),
												 "value" => "<div class=\"error\">Вы уже проголосовали</div>");
		}
		unset ($VoteCheckResult, $VoteCheckQuery);
		mysql_close($RadioDB);
		
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Author" content="Wolf Ness">
<title>myRadio</title>
<link href="/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php include("/var/www/portal.stream.uz/htdocs/toppanel.php"); ?>
<div class="outer">
<div class="header">
    <?php /*
    <div id="langpanel">
        <?php
            foreach($SiteLanguages as $lang['key'] => $lang['values']) {
                ?><a href="<?= CurrentURL(); ?>/?lang=<?= $lang['key']; ?>"><img class="flag" src="<?= '/img/lang-' . $lang['key'] . '.png'; ?>" title="<?= $lang['values'][SelectedLanguage]; ?>"></a><?php
            }
        ?>
    </div>  
    */ ?>
</div>
<table cellpadding="0" cellspacing="2" class="table_channels">
<tr height="80px">
    <td style="width: <?php echo round(960 / (count(array_keys($Channels)) + 1)); ?>px; background-color:#<?php $ChannelCellColorCurrent = GetNewChannelCellColor($ChannelCellColorCurrent, $ChannelCellColorIncrement); echo $ChannelCellColorCurrent.$ChannelCellColorCurrent.$ChannelCellColorCurrent; ?>;">
        <div class="channel-title">
            RadioSTREAM
        </div>
        <div class="channel-listen">
            <object class="listen-online" title="<?= $lang['listen_online']; ?>" type="application/x-shockwave-flash" data="button/musicplayer.swf?&amp;b_bgcolor=62A62B&amp;b_fgcolor=62A62B&amp;b_colors=FFFFFF,FFFFFF,FFFFFF,FFFFFF&amp;song_url=http://myradio.uz:8000/live" width="17" height="17"><param name="movie" value="/button/musicplayer.swf?&amp;b_bgcolor=62A62B&amp;b_fgcolor=62A62B&amp;b_colors=FFFFFF,FFFFFF,FFFFFF,FFFFFF&amp;song_url=http://radio.stream.uz:8000/live" /><param name="wmode" value="transparent"></object>
            <div class="listen-playlist"><a href="http://myradio.uz:8000/live.m3u"><?= $lang['download_playlist']; ?></a></div>
        </div>
    </td>
<?php
$ChannelNames = array_keys($Channels);
if (count($ChannelNames)) {
foreach ($ChannelNames as $Channel) {
    ?>
    <td style="width: <?php echo round(960 / (count(array_keys($Channels)) + 1)); ?>px; background-color:#<?php $ChannelCellColorCurrent = GetNewChannelCellColor($ChannelCellColorCurrent, $ChannelCellColorIncrement); echo $ChannelCellColorCurrent.$ChannelCellColorCurrent.$ChannelCellColorCurrent; ?>;">
        <div class="channel-title">
            <?php echo stripslashes($Channel); ?>
        </div>
        <div class="channel-listen">
            <object class="listen-online" title="<?= $lang['listen_online']; ?>" type="application/x-shockwave-flash" data="button/musicplayer.swf?&amp;b_bgcolor=62A62B&amp;b_fgcolor=62A62B&amp;b_colors=FFFFFF,FFFFFF,FFFFFF,FFFFFF&amp;song_url=http://myradio.uz:8000/RadioSTREAM-<?php echo preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))); ?>" width="17" height="17"><param name="movie" value="/button/musicplayer.swf?&amp;b_bgcolor=62A62B&amp;b_fgcolor=62A62B&amp;b_colors=FFFFFF,FFFFFF,FFFFFF,FFFFFF&amp;song_url=http://myradio.uz:8000/RadioSTREAM-<?php echo preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))); ?>" /><param name="wmode" value="transparent"></object>
            <div class="listen-playlist"><a href="http://myradio.uz:8000/RadioSTREAM-<?php echo preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))); ?>.m3u"><?= $lang['download_playlist']; ?></a></div>
        </div>
        <?php
        /*
        $IsOnAir = fopen("http://myradio.uz:8000/RadioSTREAM-".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))).".m3u", "r");
        if ($IsOnAir) {
        echo "<div class=\"ok\" style=\"width:40%; margin:5px 10px;\">ON AIR</div>";
        }
        else {
        echo "<div class=\"error\" style=\"width:75%; margin:5px auto;\">OFFLINE</div>";
        }
        fclose($IsOnAir);
        */
        ?>
    </td>
    <?php
}
}
?>
</tr>
</table>
<div class="sidebar sidebar-left">
	<?php
		for($ChannelNum = 0; $ChannelNum < ceil(count($ChannelNames) / 2); $ChannelNum++) {
			echo GetChannelVote($ChannelNames[$ChannelNum]);
		}
	?>
</div>
<div class="sidebar sidebar-right">
	<?php
		for($ChannelNum = $ChannelNum; $ChannelNum < count($ChannelNames); $ChannelNum++) {
			echo GetChannelVote($ChannelNames[$ChannelNum]);
		}
	?>
</div>
<?php
	unset ($ChannelNum);
?>
<div class="main">
<h1><?= $lang['welcome']; ?></h1>
<?php
/*
<div style="text-align: center; color: #F00000; margin: 0 auto 20px auto; font-size: 20px;">Новогодняя программа</div>
<a href="http://myradio.uz:8000/live.m3u"><img src="/radio2012-logo540.jpg" alt="Радио STREAM. Эпизод III. Нефинальный аккорд. 31 декабря с 19:00" /></a>
<br /><br />
<p style="text-align: center; font-size: 22px; font-weight: bold; color: #F00; margin: 0 0 15px 0;">Не пропустите!</p>
<p style="text-align: center; font-size: 14px; color: #B00;"><span style="color: #F00; font-weight:bold; font-size:18px;">31 декабря</span> на myRadio мы представляем Вашему вниманию специальный проект "Sharq Telekom" посвященный Новому Году. В эфире прозвучат композиции, присланные в музыкальную новогоднюю сборку<br /><a style="color: #006699;" href="http://mymusic.uz/album/9846/">NEW YEAR STREAMUSIC-2012</a>, а также многочисленные поздравления как от абонентов, так и от сотрудников Компании.</p>
<p style="text-align: center; font-size: 14px; color: #B00;">Конечно же, никакая новогодняя программа не обойдется без юмора, конкурсов, сюрпризов и приятных новостей. Но всему свое время.</p>
<p style="text-align: center; font-size: 14px; color: #B00;">Трансляция будет открыта для всех желающих,<br />как для абонентов ООО "Sharq Telekom", так и пользователей других<br />Интернет-провайдеров со всего мира.</p>
<p style="text-align: center; font-size: 14px; color: #B00;">Начало новогодней программы <span style="color: #F00; font-weight:bold; font-size:18px;">в 19:00</span>.</p>
<p style="text-align: center; font-size: 20px; font-weight: bold; color: #F00; margin: 0 0 8px 0;">Встречайте Новый Год вместе с myRadio и "Sharq Telekom"!</p>
<p style="text-align: center; font-size: 14px; font-weight: bold; color: #F00; margin: 0 0 15px 0;"><a href="http://myforum.uz/topic/38139/" style="color: #017be3;">Участвуйте в обсуждении на нашем форуме!</a></p>
<p style="text-align: center;"><a style="border: 0; outline: 0;" href="http://myradio.uz:8000/live.m3u"><img style="border: 0; outline: 0;" src="/img/nylisten.jpg" alt="Слушать ONLINE" /></a></p>
*/ ?>

<?php
/*
<p style="text-align: center; font-size: 22px; font-weight: bold; color: #F00; margin: 0 0 15px 0;">Не пропустите!</p>
<p style="text-align: center; font-size: 14px; color: #B00;"><span style="color: #F00; font-weight:bold; font-size:18px;">31 декабря</span> на myRadio мы представляем Вашему вниманию специальный проект "SHARQ TELEKOM" посвященный Новому Году. В эфире прозвучат все композиции, присланные в музыкальную новогоднюю сборку<br /><a style="color: #006699;" href="http://myforum.uz/topic/34398/">NEW YEAR STREAMUSIC-2011</a>, а также многочисленные <a style="color: #006699;" href="http://2011.stream.uz/">поздравления</a> как от абонентов, так и от сотрудников Компании.</p>
<p style="text-align: center; font-size: 14px; color: #B00;">Конечно же, никакая новогодняя программа не обойдется без юмора, сюрпризов и приятных новостей. Но всему свое время.</p>
<p style="text-align: center; font-size: 14px; color: #B00;">Трансляция будет открыта для всех желающих,<br />как для абонентов ООО "SHARQ TELEKOM", так и пользователей других<br />Интернет-провайдеров.</p>
<p style="text-align: center; font-size: 14px; color: #B00;">Начало новогодней программы <span style="color: #F00; font-weight:bold; font-size:18px;">в 19:00</span>.</p>
<p style="text-align: center; font-size: 20px; font-weight: bold; color: #F00; margin: 0 0 15px 0;">Встречайте Новый Год вместе с myRadio!</p>
<p style="text-align: center;"><a style="border: 0; outline: 0;" href="http://myradio.uz:8000/live.m3u"><img style="border: 0; outline: 0;" src="/img/nytest.jpg" alt="Слушать ONLINE" /></a></p>
*/
?>
<?php
foreach($lang['header'] as $line) {
    echo "<p>" . $line . "</p>";
}
?>
<div style="margin:8px auto; height:60px; width:468px; text-align:center;">

<!--/* OpenX Javascript Tag v2.8.7 */-->

<script type='text/javascript'><!--//<![CDATA[
   var m3_u = (location.protocol=='https:'?'https://adv.stream.uz/www/delivery/ajs.php':'http://adv.stream.uz/www/delivery/ajs.php');
   var m3_r = Math.floor(Math.random()*99999999999);
   if (!document.MAX_used) document.MAX_used = ',';
   document.write ("<scr"+"ipt type='text/javascript' src='"+m3_u);
   document.write ("?campaignid=30");
   document.write ('&amp;cb=' + m3_r);
   if (document.MAX_used != ',') document.write ("&amp;exclude=" + document.MAX_used);
   document.write (document.charset ? '&amp;charset='+document.charset : (document.characterSet ? '&amp;charset='+document.characterSet : ''));
   document.write ("&amp;loc=" + escape(window.location));
   if (document.referrer) document.write ("&amp;referer=" + escape(document.referrer));
   if (document.context) document.write ("&context=" + escape(document.context));
   if (document.mmm_fo) document.write ("&amp;mmm_fo=1");
   document.write ("'><\/scr"+"ipt>");
//]]>--></script><noscript><a href='http://adv.stream.uz/www/delivery/ck.php?n=afb6894a&amp;cb=990864' target='_blank'><img src='http://adv.stream.uz/www/delivery/avw.php?campaignid=30&amp;cb=990864&amp;n=afb6894a' border='0' alt='' /></a></noscript>

</div>
<h2><?= $lang['channel']; ?> RadioSTREAM</h2>
<p><?= $lang['channels']['RadioSTREAM']; ?></p>
<!-- <p class="on-air"><span class="on-air-channel"><a href="http://myradio.uz:8000/live.m3u">Now playing</a>:</span>  <?= file_get_contents('/var/www/radio.stream.uz/htdocs/nowplaying.txt'); ?></p> --> 
<?php
if (count($ChannelNames)) {
	foreach ($ChannelNames as $Channel) {
		$ChannelArtistsFileName = PLAYLIST_PATH."RadioSTREAM-".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel)))."-Artists.html";
		//if (file_exists($ChannelArtistsFileName)) {
			echo "<h2>".$lang['channel']." ".$Channel."</h2>";
			echo "<p>".$lang['channels'][$Channel]."</p>";
			echo "<p class=\"on-air\"><span class=\"on-air-channel\"><a href=\"http://myradio.uz:8000/RadioSTREAM-".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))).".m3u\">" . $lang['on_air'] . "</a>:</span> ";
			$ChannelArtistsFile = fopen($ChannelArtistsFileName, "r");
			$ChannelArtistsList = fgets($ChannelArtistsFile);
			echo $ChannelArtistsList;
			unset ($ChannelArtistsList);
			fclose($ChannelArtistsFile);
			echo "</p>";
		//}
	}
}
?>
<div style="margin:8px auto; height:60px; width:468px; text-align:center;">

<!--/* OpenX Javascript Tag v2.8.7 */-->

<script type='text/javascript'><!--//<![CDATA[
   var m3_u = (location.protocol=='https:'?'https://adv.stream.uz/www/delivery/ajs.php':'http://adv.stream.uz/www/delivery/ajs.php');
   var m3_r = Math.floor(Math.random()*99999999999);
   if (!document.MAX_used) document.MAX_used = ',';
   document.write ("<scr"+"ipt type='text/javascript' src='"+m3_u);
   document.write ("?campaignid=31");
   document.write ('&amp;cb=' + m3_r);
   if (document.MAX_used != ',') document.write ("&amp;exclude=" + document.MAX_used);
   document.write (document.charset ? '&amp;charset='+document.charset : (document.characterSet ? '&amp;charset='+document.characterSet : ''));
   document.write ("&amp;loc=" + escape(window.location));
   if (document.referrer) document.write ("&amp;referer=" + escape(document.referrer));
   if (document.context) document.write ("&context=" + escape(document.context));
   if (document.mmm_fo) document.write ("&amp;mmm_fo=1");
   document.write ("'><\/scr"+"ipt>");
//]]>--></script><noscript><a href='http://adv.stream.uz/www/delivery/ck.php?n=ae03cceb&amp;cb=68990' target='_blank'><img src='http://adv.stream.uz/www/delivery/avw.php?campaignid=31&amp;cb=68990&amp;n=ae03cceb' border='0' alt='' /></a></noscript>

</div>
</div>
<table cellpadding="0" cellspacing="0" style="width:100%; border-top:solid 1px #CCC;">
<tr style="height:36px;">
	<td style="width:220px;">
    <div style="margin-top:3px; width:100%; text-align:center; font-size:10px;">
    <img src="/img/stuz_minilogo.png" align="left" style="margin-right:4px; margin-left:10px;" alt="" /><div style="padding-top:4px;">&copy; Sharq Telekom, 2005 - <?php echo date("Y"); ?><div style="font-size:9px; margin-top:0px; color:#C1C1C1;">Code & Design by NessawolF</div></div>
    </div>
    </td>
    <td style="text-align:right;">
    <?php // <a href="http://bem.uz" target="_blank"><img src="http://myforum.uz/button-bem.jpg" alt="БЭМ" /></a> ?>
    <img src="/mycounter/mycounter.php" alt="" height="31" width="88" />
    </td>
</tr>
</table>
</div>
</body>
</html>
