#!/usr/bin/php
<?php
	echo "<br />";
	require_once("../include/const.inc.php");
	require_once("../include/functions.inc.php");
	
	$RadioDB = ConnectDB(RADIO_DBHOST, RADIO_DBUSER, RADIO_DBPASS, RADIO_DBBASE, true) or die("Error Radio.CONNECT: ".mysql_error());
	$MusicDB = ConnectDB(MUSIC_DBHOST, MUSIC_DBUSER, MUSIC_DBPASS, MUSIC_DBBASE, true) or die("Error Music.CONNECT: ".mysql_error());
	
	$d0VotePeriod = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	$d7VotePeriod = $d0VotePeriod - VOTE_PERIOD;
	$d14VotePeriod = $d0VotePeriod - VOTE_PERIOD * 2;
	
	echo "Today: ".date("Y-m-d", $d0VotePeriod)." (".$d0VotePeriod.")<br />";
	echo "7 days earlier: ".date("Y-m-d", $d7VotePeriod)." (".$d7VotePeriod.")<br />";
	echo "14 days earlier: ".date("Y-m-d", $d14VotePeriod)." (".$d14VotePeriod.")<br />";
	echo "<br />";
	
	foreach (array_keys($Channels) as $Channel) {
		echo "Channel: ".$Channel."<br />";
		// ”дал€ем возможные записи о старых голосовани€х
		$mored14InitQuery = "DELETE FROM `votes` WHERE `votes`.`vote_added` <= '".date("Y-m-d", $d14VotePeriod)."'";
		mysql_query($mored14InitQuery, $RadioDB) or die("Error Radio.mored14InitQuery: ".mysql_error());
		unset($mored14InitQuery);
		
		// ≈сли отсутствуют 14-дневные записи, добавл€ем их из случайного массива
		$ChannelArtistsFileName = PLAYLIST_PATH."RadioSTREAM-".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel)))."-Artists.html";
		if (file_exists($ChannelArtistsFileName)) {
			unlink($ChannelArtistsFileName);
		}
		$ChannelArtistsList = "";
		$d14InitQuery = "SELECT * FROM `votes` WHERE `votes`.`vote_group` = '".addslashes($Channel)."' AND `votes`.`vote_added` >= '".date("Y-m-d", $d14VotePeriod)."' AND `vote_added` <= '".date("Y-m-d", $d7VotePeriod)."' ORDER BY `vote_counter` DESC";
		$d14InitResult = mysql_query($d14InitQuery, $RadioDB) or die("Error Radio.d14InitQuery: ".mysql_error());
		if (mysql_num_rows($d14InitResult) == 0) {
			$d14EmptyVoteQuery = "SELECT * FROM `artists` WHERE (";
			if (count($VoteExceptions != 0)) {
				foreach($VoteExceptions as $Exception) {
					$d14EmptyVoteQuery .= "`artists`.`artist_name` NOT LIKE '".$Exception."' OR ";
				}
				$d14EmptyVoteQuery = substr($d14EmptyVoteQuery, 0, (strlen($d14EmptyVoteQuery) - 3));
				$d14EmptyVoteQuery .= ") AND (";
			}
			foreach($Channels[$Channel] as $Genre) {
				$d14EmptyVoteQuery .= "`artists`.`artist_genre` = '".$Genre."' OR ";
			}
			$d14EmptyVoteQuery = substr($d14EmptyVoteQuery, 0, (strlen($d14EmptyVoteQuery) - 3));
			$d14EmptyVoteQuery .= ") ";
			$d14EmptyVoteQuery .= " ORDER BY RAND() LIMIT ".$VoteArtists;
			echo "d14EmptyVoteQuery: ".$d14EmptyVoteQuery."<br /><br />"; // DEBUG
			$d14EmptyVoteResult = mysql_query($d14EmptyVoteQuery, $MusicDB) or die("Error Music.d14EmptyVoteQuery: ".mysql_error());
			
			$CurrentPlaylistArtistsArray = array();
			$ChannelArtistsListArray_IDs = array();
			$ChannelArtistsListArray_Names = array();
			$d14EmptyVoteArtistsQuery = "INSERT INTO `votes` (`vote_group`, `vote_artist_id`, `vote_artist_name`, `vote_added`, `vote_counter`) VALUES ";
			while($d14EmptyVoteArray = mysql_fetch_array($d14EmptyVoteResult)) {
				$d14EmptyVoteArtistsQuery .= " ('".addslashes($Channel)."', '".$d14EmptyVoteArray['artist_id']."', '".addslashes($d14EmptyVoteArray['artist_name'])."', '".date("Y-m-d", $d14VotePeriod)."', '0'), ";
				if (count($CurrentPlaylistArtistsArray) < $VoteWinners) {
					array_push($CurrentPlaylistArtistsArray, $d14EmptyVoteArray['artist_id']);
					array_push($ChannelArtistsListArray_IDs, $d14EmptyVoteArray['artist_id']);
					array_push($ChannelArtistsListArray_Names, $d14EmptyVoteArray['artist_name']);
				}
			}
			$d14EmptyVoteArtistsQuery = substr($d14EmptyVoteArtistsQuery, 0, (strlen($d14EmptyVoteArtistsQuery) - 2));
			unset ($d14EmptyVoteArray, $d14EmptyVoteResult, $d14EmptyVoteQuery);
			
			echo "d14EmptyVoteArtistsQuery: ".$d14EmptyVoteArtistsQuery."<br /><br />"; // DEBUG
			mysql_query($d14EmptyVoteArtistsQuery, $RadioDB) or die("Error Radio.d14EmptyVoteArtistsQuery: ".mysql_error());
			unset($d14EmptyVoteArtistsQuery);
		}
		// ≈сли в Ѕƒ есть записи с результатами 14-дневного голосовани€
		else {
			$CurrentPlaylistArtistsArray = array();
			$ChannelArtistsListArray_IDs = array();
			$ChannelArtistsListArray_Names = array();
			while($d14InitArray = mysql_fetch_array($d14InitResult)) {
				if (count($CurrentPlaylistArtistsArray) < $VoteWinners) {
					array_push($CurrentPlaylistArtistsArray, $d14InitArray['vote_artist_id']);
					array_push($ChannelArtistsListArray_IDs, $d14InitArray['vote_artist_id']);
					array_push($ChannelArtistsListArray_Names, $d14InitArray['vote_artist_name']);
				}
			}
		}
		$ChannelArtistsListArray = array_combine($ChannelArtistsListArray_IDs, $ChannelArtistsListArray_Names);
		unset($ChannelArtistsListArray_IDs, $ChannelArtistsListArray_Names);
		asort($ChannelArtistsListArray);
		while (list($ArtistID, $ArtistName) = each($ChannelArtistsListArray)) {
			$ChannelArtistsList .= "<a href=\"http://www.hits.uz/artist/".$ArtistID."/\">".stripslashes($ArtistName)."</a>, ";
		}
		unset ($ChannelArtistsListArray);
		
		$ChannelArtistsList = substr($ChannelArtistsList, 0, (strlen($ChannelArtistsList) - 2));
		$ChannelArtistsFile = fopen($ChannelArtistsFileName, "a+");
		fwrite($ChannelArtistsFile, $ChannelArtistsList);
		fclose($ChannelArtistsFile);
		unset($ChannelArtistsList, $ChannelArtistsFileName);
		
		// ≈сли отсутствуют 7-дневные записи, добавл€ем их из случайного массива
		$d7InitQuery = "SELECT * FROM `votes` WHERE `votes`.`vote_group` = '".addslashes($Channel)."' AND `votes`.`vote_added` >= '".date("Y-m-d", $d7VotePeriod)."' AND `vote_added` <= '".date("Y-m-d", $d0VotePeriod)."' ORDER BY `vote_counter` DESC";
		$d7InitResult = mysql_query($d7InitQuery, $RadioDB) or die("Error Radio.d7InitQuery: ".mysql_error());
		if (mysql_num_rows($d7InitResult) == 0) {
			$d7EmptyVoteQuery = "SELECT * FROM `artists` WHERE (";
			if (count($VoteExceptions != 0)) {
				foreach($VoteExceptions as $Exception) {
					$d7EmptyVoteQuery .= "`artists`.`artist_name` NOT LIKE '".$Exception."' OR ";
				}
				$d7EmptyVoteQuery = substr($d7EmptyVoteQuery, 0, (strlen($d7EmptyVoteQuery) - 3));
				$d7EmptyVoteQuery .= ") AND (";
			}
			foreach($Channels[$Channel] as $Genre) {
				$d7EmptyVoteQuery .= "`artists`.`artist_genre` = '".$Genre."' OR ";
			}
			$d7EmptyVoteQuery = substr($d7EmptyVoteQuery, 0, (strlen($d7EmptyVoteQuery) - 3));
			$d7EmptyVoteQuery .= ") ";
			$d7EmptyVoteQuery .= " ORDER BY RAND() LIMIT ".$VoteArtists;
			echo "d7EmptyVoteQuery: ".$d7EmptyVoteQuery."<br /><br />"; // DEBUG
			$d7EmptyVoteResult = mysql_query($d7EmptyVoteQuery, $MusicDB) or die("Error Music.d7EmptyVoteQuery: ".mysql_error());
			
			$CurrentTotalArtistsArray = array();
			$d7EmptyVoteArtistsQuery = "INSERT INTO `votes` (`vote_group`, `vote_artist_id`, `vote_artist_name`, `vote_added`, `vote_counter`) VALUES ";
			while($d7EmptyVoteArray = mysql_fetch_array($d7EmptyVoteResult)) {
				$d7EmptyVoteArtistsQuery .= " ('".addslashes($Channel)."', '".$d7EmptyVoteArray['artist_id']."', '".addslashes($d7EmptyVoteArray['artist_name'])."', '".date("Y-m-d", $d7VotePeriod)."', '0'), ";
				// $LastVoteDate = date("Y-m-d", $d7VotePeriod);
				array_push($CurrentTotalArtistsArray, $d7EmptyVoteArray['artist_id']);
			}
			$d7EmptyVoteArtistsQuery = substr($d7EmptyVoteArtistsQuery, 0, (strlen($d7EmptyVoteArtistsQuery) - 2));
			unset ($d7EmptyVoteArray, $d7EmptyVoteResult, $d7EmptyVoteQuery);
			
			echo "d7EmptyVoteArtistsQuery: ".$d7EmptyVoteArtistsQuery."<br /><br />"; // DEBUG
			mysql_query($d7EmptyVoteArtistsQuery, $RadioDB) or die("Error Radio.d7EmptyVoteArtistsQuery: ".mysql_error());
			unset($d7EmptyVoteArtistsQuery);
		}
		else {
			$CurrentTotalArtistsArray = array();
			while($d7InitArray = mysql_fetch_array($d7InitResult)) {
				// $LastVoteDate = $d7InitArray['vote_added'];
				array_push($CurrentTotalArtistsArray, $d7InitArray['vote_artist_id']);
			}
		}
		// —оздаем новый плейлист из исполнителей-лидеров прошлого голосовани€
		$PlaylistQuery = "SELECT `track_id`, `artist_id`, `album_id` FROM `tracks` WHERE (";
		for ($TrackMins = 1; $TrackMins < $PlaylistMaxTrackTime; $TrackMins++) {
			$PlaylistQuery .= "`tracks`.`track_time` LIKE '";
			if (strlen($TrackMins) == 1) {
				$PlaylistQuery .= "0";
			}
			$PlaylistQuery .= $TrackMins.":%' OR ";
		}
		$PlaylistQuery = substr($PlaylistQuery, 0, (strlen($PlaylistQuery) - 3));
		$PlaylistQuery .= ") AND (";
		foreach ($CurrentPlaylistArtistsArray as $CurrentPlaylistArtist) {
			$PlaylistQuery .= " `tracks`.`artist_id` = '".$CurrentPlaylistArtist."' OR ";
		}
		$PlaylistQuery = substr($PlaylistQuery, 0, (strlen($PlaylistQuery) - 3));
		$PlaylistQuery .= ") ORDER BY RAND() LIMIT ".$PlaylistMaxTracks;
		echo "Playlist Query: ".$PlaylistQuery."<br /><br />";
		$PlaylistResult = mysql_query($PlaylistQuery, $MusicDB) or die("Error Music.PlaylistQuery: ".mysql_error());
		$ChannelPlaylistFileName = PLAYLIST_PATH."RadioSTREAM-".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $Channel))).".pls";
		if (file_exists($ChannelPlaylistFileName)) {
			unlink($ChannelPlaylistFileName);
		}
		while ($PlaylistArray = mysql_fetch_array($PlaylistResult)) {
			$ChannelPlaylistFile = fopen($ChannelPlaylistFileName, "a+");
			fwrite($ChannelPlaylistFile, MEDIA_PATH."artist/".$PlaylistArray['artist_id']."/".$PlaylistArray['album_id']."/".$PlaylistArray['track_id'].".mp3\n");
			fclose($ChannelPlaylistFile);
		}
		unset($ChannelPlaylistFileName);
		unset($PlaylistArray, $PlaylistResult, $PlaylistQuery);
		
		// ≈сли подошел период нового голосовани€, создаем его
		$LastVoteDateQuery = "SELECT `vote_added` FROM `votes` WHERE `vote_group` = '".addslashes($Channel)."' ORDER BY `vote_added` DESC LIMIT 1";
		$LastVoteDateResult = mysql_query($LastVoteDateQuery, $RadioDB) or die("Error Radio.LastVoteDateQuery: ".mysql_error());
		$LastVoteDate = mysql_result($LastVoteDateResult, 0, 0);
		$LastVoteDate_Month = substr($LastVoteDate, 5, 2);
		$LastVoteDate_Day   = substr($LastVoteDate, 8, 2);
		$LastVoteDate_Year  = substr($LastVoteDate, 0, 4);
		// echo "<br />DEBUG: ".$LastVoteDate." | ".$LastVoteDate_Year."-".$LastVoteDate_Month."-".$LastVoteDate_Day." | ".date("Y-m-d", $d7VotePeriod)." | ".(mktime(0, 0, 0, date("m"), date("d"), date("Y")) - mktime(0, 0, 0, $LastVoteDate_Month, $LastVoteDate_Day, $LastVoteDate_Year)). " | ".VOTE_PERIOD; // DEBUG
		// die();
		unset($LastVoteDateResult, $LastVoteDateQuery, $LastVoteDate);
		
		if ((mktime(0, 0, 0, date("m"), date("d"), date("Y")) - mktime(0, 0, 0, $LastVoteDate_Month, $LastVoteDate_Day, $LastVoteDate_Year)) >= VOTE_PERIOD) {
			$d0VoteQuery = "SELECT * FROM `artists` WHERE (";
			if (count($VoteExceptions != 0)) {
				foreach($VoteExceptions as $Exception) {
					$d0VoteQuery .= "`artists`.`artist_name` NOT LIKE '".$Exception."' OR ";
				}
				$d0VoteQuery = substr($d0VoteQuery, 0, (strlen($d0VoteQuery) - 3));
				$d0VoteQuery .= ") AND (";
			}
			foreach($Channels[$Channel] as $Genre) {
				$d0VoteQuery .= "`artists`.`artist_genre` = '".$Genre."' OR ";
			}
			$d0VoteQuery = substr($d0VoteQuery, 0, (strlen($d0VoteQuery) - 3));
			$d0VoteQuery .= ") AND (";
			foreach($CurrentTotalArtistsArray as $CurrentArtist) {
				$d0VoteQuery .= "`artists`.`artist_id` != '".$CurrentArtist."' OR ";
			}
			$d0VoteQuery = substr($d0VoteQuery, 0, (strlen($d0VoteQuery) - 3));
			$d0VoteQuery .= ")";
			$d0VoteQuery .= " ORDER BY RAND() LIMIT ".$VoteArtists;
			echo "d0VoteQuery: ".$d0VoteQuery."<br /><br />"; // DEBUG
			$d0VoteResult = mysql_query($d0VoteQuery, $MusicDB) or die("Error Music.d0VoteQuery: ".mysql_error());
			
			$d0VoteArtistsQuery = "INSERT INTO `votes` (`vote_group`, `vote_artist_id`, `vote_artist_name`, `vote_added`, `vote_counter`) VALUES ";
			
			while($d0VoteArray = mysql_fetch_array($d0VoteResult)) {
				$d0VoteArtistsQuery .= " ('".addslashes($Channel)."', '".$d0VoteArray['artist_id']."', '".addslashes($d0VoteArray['artist_name'])."', '".date("Y-m-d")."', '0'), ";
			}
			$d0VoteArtistsQuery = substr($d0VoteArtistsQuery, 0, (strlen($d0VoteArtistsQuery) - 2));
			unset ($d0VoteArray, $d0VoteResult, $d0VoteQuery);
			
			echo "d0VoteArtistsQuery: ".$d0VoteArtistsQuery."<br /><br />"; // DEBUG
			mysql_query($d0VoteArtistsQuery, $RadioDB) or die("Error Radio.d0VoteArtistsQuery: ".mysql_error());
			unset($d0VoteArtistsQuery);
		}
		unset ($LastVoteDate_Month, $LastVoteDate_Day, $LastVoteDate_Year, $LastVoteDate);
		unset ($CurrentTotalArtistsArray, $CurrentPlaylistArtistsArray);
	}
	
	unset ($d0VotePeriod, $d7VotePeriod, $d14VotePeriod);
	
	mysql_close($MusicDB);
	mysql_close($RadioDB);
?>