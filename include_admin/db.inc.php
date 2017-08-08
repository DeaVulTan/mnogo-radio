<?php
require_once('const.inc.php');
$music_db = mysql_connect(MUSIC_DB_HOST,MUSIC_DB_USER,MUSIC_DB_PASSWORD) or  die("Error: connect");
$radio_db = mysql_connect(RADIO_DB_HOST,RADIO_DB_USER,RADIO_DB_PASSWORD) or  die("Error: connect");
mysql_select_db(RADIO_DB_NAME, $radio_db) or die("Error: select db");
mysql_select_db(MUSIC_DB_NAME, $music_db) or die("Error: select db");
?>