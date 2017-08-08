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
<meta name="Author" content="NessawolF [NF], nfstrider@gmail.com">
<title>myRadio</title>
<link href="/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="outer">
<div class="header">&nbsp;</div>
<table cellpadding="0" cellspacing="2" class="table_channels">
<tr height="80px">
<?php
	$ChannelNames = array_keys($Channels);
	foreach ($ChannelNames as $Channel) {
		?>
		<td style="width: <?php echo round(960 / count(array_keys($Channels))); ?>px; background-color:#<?php $ChannelCellColorCurrent = GetNewChannelCellColor($ChannelCellColorCurrent, $ChannelCellColorIncrement); echo $ChannelCellColorCurrent.$ChannelCellColorCurrent.$ChannelCellColorCurrent; ?>;">
			<div class="channel-title"><?php echo stripslashes($Channel); ?></div>
            <div class="channel-listen">
	        	<object class="listen-online" title="Слушать онлайн" type="application/x-shockwave-flash" data="button/musicplayer.swf?&amp;b_bgcolor=62A62B&amp;b_fgcolor=62A62B&amp;b_colors=FFFFFF,FFFFFF,FFFFFF,FFFFFF&amp;song_url=http://radio.stream.uz:8000/RadioSTREAM-<?php echo preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))); ?>" width="17" height="17"><param name="movie" value="/button/musicplayer.swf?&amp;b_bgcolor=62A62B&amp;b_fgcolor=62A62B&amp;b_colors=FFFFFF,FFFFFF,FFFFFF,FFFFFF&amp;song_url=http://radio.stream.uz:8000/RadioSTREAM-<?php echo preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))); ?>" />
    	        </object>
                <div class="listen-playlist"><a href="http://radio.stream.uz:8000/RadioSTREAM-<?php echo preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))); ?>.m3u">Скачать плейлист</a></div>
			</div>
            <?php
				/*
	            $IsOnAir = fopen("http://radio.stream.uz:8000/RadioSTREAM-".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))).".m3u", "r");
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
<h1>Добро пожаловать!</h1>
<p>myRadio - это место, где каждый может слушать ту музыку, которая ему нравится, не заботясь о создании собственных плейлистов, не тратя своё время на поиск и отбор композиций. Достаточно лишь зайти на короткий адрес в Интернете и наслаждаться.</p>
<p>Настройтесь на волну отличного настроения и вечного праздника - на свою собственную волну! Джаз? Рок? Техно? Здесь Вы услышите все многообразие музыкальных жанров стилей и направлений. Именно здесь собраны все самые ярчайшие представители современной популярной музыки, а так же хиты и новинки. Слушайте любимую музыку!</p>
<p>Более того, именно Вы выбираете ту музыку, тех исполнителей, чьи композиции будут в эфире в течение всей недели. Участвуйте в голосованиях, выбирайте только лучших, и обеспечивайте им победу, а себе удовольствие.</p>
<p>Каждый день новая музыка, каждую неделю - новые исполнители!</p>
<div style="margin:8px auto; height:60px; width:468px; text-align:center;">
<script type='text/javascript'><!--//<![CDATA[
   var m3_u = (location.protocol=='https:'?'https://adv.stream.uz/www/delivery/ajs.php':'http://adv.stream.uz/www/delivery/ajs.php');
   var m3_r = Math.floor(Math.random()*99999999999);
   if (!document.MAX_used) document.MAX_used = ',';
   document.write ("<scr"+"ipt type='text/javascript' src='"+m3_u);
   document.write ("?campaignid=1");
   document.write ('&amp;cb=' + m3_r);
   if (document.MAX_used != ',') document.write ("&amp;exclude=" + document.MAX_used);
   document.write (document.charset ? '&amp;charset='+document.charset : (document.characterSet ? '&amp;charset='+document.characterSet : ''));
   document.write ("&amp;loc=" + escape(window.location));
   if (document.referrer) document.write ("&amp;referer=" + escape(document.referrer));
   if (document.context) document.write ("&context=" + escape(document.context));
   if (document.mmm_fo) document.write ("&amp;mmm_fo=1");
   document.write ("'><\/scr"+"ipt>");//]]>--></script><noscript><a href='http://adv.stream.uz/www/delivery/ck.php?n=ae97e0e4&amp;cb=9' target='_blank'><img src='http://adv.stream.uz/www/delivery/avw.php?campaignid=1&amp;cb=9&amp;n=ae97e0e4' border='0' alt='' /></a></noscript>
</div>
<?php
	foreach ($ChannelNames as $Channel) {
		$ChannelArtistsFileName = PLAYLIST_PATH."RadioSTREAM-".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel)))."-Artists.html";
		if (file_exists($ChannelArtistsFileName)) {
			echo "<h2>Канал ".$Channel."</h2>";
			echo "<p>".$ChannelDescriptions[$Channel]."</p>";
			echo "<p class=\"on-air\"><span class=\"on-air-channel\"><a href=\"http://radio.stream.uz:8000/RadioSTREAM-".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))).".m3u\">Сегодня в эфире</a>:</span> ";
			$ChannelArtistsFile = fopen($ChannelArtistsFileName, "r");
			$ChannelArtistsList = fgets($ChannelArtistsFile);
			echo $ChannelArtistsList;
			unset ($ChannelArtistsList);
			fclose($ChannelArtistsFile);
			echo "</p>";
		}
	}
?>
<div style="margin:8px auto; height:60px; width:468px; text-align:center;">
<script type='text/javascript'><!--//<![CDATA[
var m3_u = (location.protocol=='https:'?'https://adv.stream.uz/www/delivery/ajs.php':'http://adv.stream.uz/www/delivery/ajs.php');
var m3_r = Math.floor(Math.random()*99999999999);
if (!document.MAX_used) document.MAX_used = ',';
document.write ("<scr"+"ipt type='text/javascript' src='"+m3_u);
document.write ("?campaignid=7&amp;target=_blank&amp;charset=UTF-8");
document.write ('&amp;cb=' + m3_r);
if (document.MAX_used != ',') document.write ("&amp;exclude=" + document.MAX_used);
document.write ('&amp;charset=UTF-8');
document.write ("&amp;loc=" + escape(window.location));
if (document.referrer) document.write ("&amp;referer=" + escape(document.referrer));
if (document.context) document.write ("&context=" + escape(document.context));
if (document.mmm_fo) document.write ("&amp;mmm_fo=1");
document.write ("'><\/scr"+"ipt>");
//]]>--></script><noscript><a href='http://adv.stream.uz/www/delivery/ck.php?n=ac3cf5ab&amp;cb=87' target='_blank'><img src='http://adv.stream.uz/www/delivery/avw.php?campaignid=7&amp;charset=UTF-8&amp;cb=87&amp;n=ac3cf5ab' border='0' alt='' /></a></noscript>
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
    <img src="/mycounter/mycounter.php" alt="" height="31" width="88" />
    </td>
</tr>
</table>
</div>
</body>
</html>