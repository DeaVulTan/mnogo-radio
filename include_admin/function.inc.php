<?php
function GeneratePlaylists() {
	global $radio_db;
	$sql = "SELECT * FROM streams";
	$streams_result = mysql_query($sql,$radio_db);
	while( $stream = mysql_fetch_assoc($streams_result) ) {
		$sql = "SELECT * FROM songs WHERE stream_id = {$stream['stream_id']}";
		if( isset($songs_result) ) mysql_free_result($songs_result);
		$songs_result = mysql_query($sql,$radio_db);
		$playlist = fopen($stream['playlist'],'w');
		while( $song = mysql_fetch_assoc($songs_result) ) {
			fwrite($playlist,MEDIA_PATH."artist/{$song['artist_id']}/{$song['album_id']}/{$song['track_id']}.mp3\n");
		}
		fclose($playlist);
	}
}
function GenerateStreamFM() {
	global $radio_db;
	global $music_db;
	
	$sql = "SELECT * FROM streams WHERE name = 'StreamFM'";
	$stream_result = mysql_query($sql,$radio_db);
	if( !$stream = mysql_fetch_assoc($stream_result) ) die('No such stream');
	
	$sql = "DELETE FROM songs WHERE stream_id = {$stream['stream_id']}";
	mysql_query($sql,$radio_db);
	
	$sql = "SELECT track_id, artist_id, album_id FROM tracks ORDER BY rand() DESC LIMIT 100";
	$tracks_reiting_result = mysql_query($sql,$music_db);
	while( $track = mysql_fetch_assoc($tracks_reiting_result) ) $tracks[] = $track;
	mysql_free_result($tracks_reiting_result);
	
	$sql = "SELECT tracks.track_id, tracks.artist_id, tracks.album_id, COUNT(downloads.id) AS sumdownload FROM tracks, downloads WHERE downloads.type = 'track' AND tracks.track_id=downloads.track_id AND (TO_DAYS(NOW()) - TO_DAYS(downloads.date) <= 7) GROUP BY tracks.track_id ORDER BY sumdownload DESC LIMIT 100";
	$tracks_reiting_result = mysql_query($sql,$music_db);
	while( $track = mysql_fetch_assoc($tracks_reiting_result) ) $tracks[] = $track;
	mysql_free_result($tracks_reiting_result);
	
	$sql = "SELECT tracks.track_id, tracks.artist_id, tracks.album_id, COUNT(downloads.id) AS sumdownload FROM tracks, downloads WHERE downloads.type = 'track' AND tracks.track_id=downloads.track_id AND (TO_DAYS(NOW()) - TO_DAYS(downloads.date) <= 1) GROUP BY tracks.track_id ORDER BY sumdownload DESC LIMIT 100";
	$tracks_reiting_result = mysql_query($sql,$music_db);
	while( $track = mysql_fetch_assoc($tracks_reiting_result) ) $tracks[] = $track;
	mysql_free_result($tracks_reiting_result);
	
	$trackids = array();
	foreach( $tracks as $track ) {
		if( in_array($track['track_id'],$trackids) ) continue;
		$trackids[] = $track['track_id'];
		$sql = "INSERT INTO songs(track_id,artist_id,album_id,stream_id) VALUES({$track['track_id']},{$track['artist_id']},{$track['album_id']},{$stream['stream_id']})";
		mysql_query($sql,$radio_db);
	}
}
function GeneratePOPRNB() {
	global $radio_db;
	global $music_db;
	
	$sql = "SELECT * FROM streams WHERE name = 'POPRNB'";
	$stream_result = mysql_query($sql,$radio_db);
	if( !$stream = mysql_fetch_assoc($stream_result) ) die('No such stream');
	
	$sql = "DELETE FROM songs WHERE stream_id = {$stream['stream_id']}";
	mysql_query($sql,$radio_db);
	
	$sql = "SELECT track_id, artist_id, album_id FROM tracks WHERE track_genre IN (3,13,14,54,86,90,98,132,147) ORDER BY rand() DESC LIMIT 250";
	$tracks_result = mysql_query($sql,$music_db);
	while( $track = mysql_fetch_assoc($tracks_result) ) $tracks[] = $track;
	mysql_free_result($tracks_result);
	
	$trackids = array();
	foreach( $tracks as $track ) {
		if( in_array($track['track_id'],$trackids) ) continue;
		$trackids[] = $track['track_id'];
		$sql = "INSERT INTO songs(track_id,artist_id,album_id,stream_id) VALUES({$track['track_id']},{$track['artist_id']},{$track['album_id']},{$stream['stream_id']})";
		mysql_query($sql,$radio_db);
	}
}

function GenerateRAP() {
	global $radio_db;
	global $music_db;
	
	$sql = "SELECT * FROM streams WHERE name = 'RAP'";
	$stream_result = mysql_query($sql,$radio_db);
	if( !$stream = mysql_fetch_assoc($stream_result) ) die('No such stream');
	
	$sql = "DELETE FROM songs WHERE stream_id = {$stream['stream_id']}";
	mysql_query($sql,$radio_db);
	
	$sql = "SELECT track_id, artist_id, album_id FROM tracks WHERE track_genre IN (7,15,27) ORDER BY track_hitmp3 DESC LIMIT 150";
	$tracks_result = mysql_query($sql,$music_db);
	while( $track = mysql_fetch_assoc($tracks_result) ) $tracks[] = $track;
	mysql_free_result($tracks_result);
	
	$trackids = array();
	foreach( $tracks as $track ) {
		if( in_array($track['track_id'],$trackids) ) continue;
		$trackids[] = $track['track_id'];
		$sql = "INSERT INTO songs(track_id,artist_id,album_id,stream_id) VALUES({$track['track_id']},{$track['artist_id']},{$track['album_id']},{$stream['stream_id']})";
		mysql_query($sql,$radio_db);
	}
}

function GenerateELECTRONIC() {
	global $radio_db;
	global $music_db;
	
	$sql = "SELECT * FROM streams WHERE name = 'ELECTRONIC'";
	$stream_result = mysql_query($sql,$radio_db);
	if( !$stream = mysql_fetch_assoc($stream_result) ) die('No such stream');
	
	$sql = "DELETE FROM songs WHERE stream_id = {$stream['stream_id']}";
	mysql_query($sql,$radio_db);
	
	$sql = "SELECT track_id, artist_id, album_id FROM tracks WHERE track_genre IN (18,26,31,34,35,52,55,112,127) ORDER BY rand() DESC LIMIT 400";
	$tracks_result = mysql_query($sql,$music_db);
	while( $track = mysql_fetch_assoc($tracks_result) ) $tracks[] = $track;
	mysql_free_result($tracks_result);
	
	$trackids = array();
	foreach( $tracks as $track ) {
		if( in_array($track['track_id'],$trackids) ) continue;
		$trackids[] = $track['track_id'];
		$sql = "INSERT INTO songs(track_id,artist_id,album_id,stream_id) VALUES({$track['track_id']},{$track['artist_id']},{$track['album_id']},{$stream['stream_id']})";
		mysql_query($sql,$radio_db);
	}
}

function GenerateROCK() {
	global $radio_db;
	global $music_db;
	
	$sql = "SELECT * FROM streams WHERE name = 'ROCK'";
	$stream_result = mysql_query($sql,$radio_db);
	if( !$stream = mysql_fetch_assoc($stream_result) ) die('No such stream');
	
	$sql = "DELETE FROM songs WHERE stream_id = {$stream['stream_id']}";
	mysql_query($sql,$radio_db);
	
	$sql = "SELECT track_id, artist_id, album_id FROM tracks WHERE track_genre IN (1,5,9,17,19,20,22,29,40,43,47,49,78,79,81,91,92,93,94,99,121,129,131,137,138,144) ORDER BY track_hitmp3 DESC LIMIT 250";
	$tracks_result = mysql_query($sql,$music_db);
	while( $track = mysql_fetch_assoc($tracks_result) ) $tracks[] = $track;
	mysql_free_result($tracks_result);
	
	$trackids = array();
	foreach( $tracks as $track ) {
		if( in_array($track['track_id'],$trackids) ) continue;
		$trackids[] = $track['track_id'];
		$sql = "INSERT INTO songs(track_id,artist_id,album_id,stream_id) VALUES({$track['track_id']},{$track['artist_id']},{$track['album_id']},{$stream['stream_id']})";
		mysql_query($sql,$radio_db);
	}
}

?>
