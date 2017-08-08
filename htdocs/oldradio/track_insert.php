<?php
	define('PLAYLIST_NAME','/var/www/radio.stream.uz/streams/2010.pls');
	define('MEDIA_PATH','/var/www/radio.stream.uz/media/');
	
	define('RADIO_DB_HOST','localhost');
	define('RADIO_DB_NAME','radiostream');
	define('RADIO_DB_USER','radiostream');
	define('RADIO_DB_PASSWORD','nee7ziCiej');
	
	define('MUSIC_DB_HOST','localhost');
	define('MUSIC_DB_NAME','music_stream_uz');
	define('MUSIC_DB_USER','musicstream');
	define('MUSIC_DB_PASSWORD','nosarkormp3');
	
	$radio_db = mysql_connect(RADIO_DB_HOST, RADIO_DB_USER, RADIO_DB_PASSWORD, true);
	mysql_select_db(RADIO_DB_NAME, $radio_db);
	
	$music_db = mysql_connect(MUSIC_DB_HOST, MUSIC_DB_USER, MUSIC_DB_PASSWORD, true);
	mysql_select_db(MUSIC_DB_NAME, $music_db);
	
	if (isset($_POST['track_insert'])) {
		if (empty($_POST['track_id'])) {
			$error = "<div style='color:#CC0000;'>Неверный Track ID</div>";
		}
		if (empty($error) and is_nan($_POST['track_id'])) {
			$error = "<div style='color:#CC0000;'>Неверный Track ID</div>";
		}
		/*
		if (empty($error) and $_POST['stream_id'] == 0) {
			$error = "<div style='color:#CC0000;'>Не выбран Stream ID</div>";
		}
		*/
		if (empty($error)) {
			$result = mysql_query("SELECT `track_id`, `artist_id`, `album_id` FROM `tracks` WHERE `track_id` = '".$_POST['track_id']."'", $music_db) or die("Error SELECT: ".mysql_error());
		}
		if (empty($error) and mysql_num_rows($result) == 0) {
			$error = "<div style='color:#CC0000;'>Трек не найден</div>";
		}
		/*
		if (empty($error)) {
			$row = mysql_fetch_array($result);
			mysql_query("INSERT INTO `songs` (`track_id`, `artist_id`, `album_id`, `stream_id`) VALUES ('".$row['track_id']."', '".$row['artist_id']."', '".$row['album_id']."', '".$_POST['stream_id']."')", $radio_db) or die("Error INSERT: ".mysql_error());
			$error = "<div style='color:#00CC00;'>Трек добавлен (ID: ".mysql_insert_id($radio_db).")</div>";
		}
		*/
		if (empty($error)) {
			$row = mysql_fetch_array($result);
			$pls = fopen(PLAYLIST_NAME, "a+");
			fwrite($pls, MEDIA_PATH."artist/".$row['artist_id']."/".$row['album_id']."/".$row['track_id'].".mp3\n");
			fclose($pls);
			unset ($row, $result);
			$error = "<div style='color:#00CC00;'>Трек добавлен</div>";
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Добавить трек в базу радио</title>
</head>

<body>
<div style="text-align:center;">
<?php if (!empty($error)) { echo $error; unset($error); } ?>
<br />
<form name="track_insert" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
Track ID:
<input name="track_id" type="text" maxlength="10" style="width:100px;" /><br />
<?php /*
Stream ID:
<select name="stream_id" style="width:100px;">
<option value="0">---</option>
*/ ?>
<?php
	/*
	$result = mysql_query("SELECT `stream_id`, `name` FROM `streams` ORDER BY `stream_id`", $radio_db);
	while ($row = mysql_fetch_array($result)) {
		?><option value="<?php echo $row['stream_id']; ?>"<?php if ($_POST['stream_id'] == $row['stream_id']) { echo "selected=\"selected\""; } ?>><?php echo $row['name']; ?></option><?php
	}
	*/
?>
<?php /*
</select><br />
*/ ?>
<input name="track_insert" type="submit" style="width:100px;" value="Submit" /><br />
</form>
</div>
</body>
</html>
<?php
	mysql_close($music_db);
	mysql_close($radio_db);
?>
