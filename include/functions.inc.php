<?php
	function ConnectDB($host, $user, $pass, $base, $new) {
		$site_active = true;
		$db = mysql_connect($host, $user, $pass, $new) or $site_active == false;
		mysql_select_db($base, $db) or $site_active == false;
		mysql_query("SET NAMES 'utf8'", $db) or $site_active == false;
		mysql_query("SET character_set_client = 'utf8'", $db) or $site_active == false;
	    mysql_query("SET character_set_results = 'utf8'", $db) or $site_active == false;
		
		if ($site_active == false) {
			$db = false;
		}
		return $db;
	}
	
	function GenerateQuery($type, $table, $params, $where_params) {
		switch(mb_convert_case($type, MB_CASE_UPPER)) {
			case "INSERT":
			$query = "INSERT INTO `".$table."` (";
			foreach (array_keys($params) as $item) {
				$query .= "`".$item."`, ";
			}
			$query = substr($query, 0, (strlen($query) - 2));
			$query .= ") VALUES (";
			foreach (array_values($params) as $item) {
				$query .= "'".$item."', ";
			}
			$query = substr($query, 0, (strlen($query) - 2));
			$query .= ")";
			break;

			case "UPDATE":
			$query = "UPDATE `".$table."` SET ";
			while (list($field, $value) = each($params)) {
				$query .= "`".$field."` = '".$value."', ";
			}
			$query = substr($query, 0, (strlen($query) - 2));
			$query .= " WHERE ";
			while (list($field, $value) = each($where_params)) {
				$query .= "`".$field."` = '".$value."' AND ";
			}
			$query = substr($query, 0, (strlen($query) - 5));
			default:

			break;
		}
		return $query;
	}
	
	function GetNewChannelCellColor($current, $increment) {
		$current = dechex(hexdec($current) + $increment);
		return $current;
	}
	
	function SetChannelCellColor($current, $increment) {
		$current = GetNewChannelCellColor($current, $increment);
		return $current.$current.$current;
	}
	
	function GetChannelVote($ChannelTitle) {
		$out = "";
		$RadioDB = ConnectDB(RADIO_DBHOST, RADIO_DBUSER, RADIO_DBPASS, RADIO_DBBASE, true) or die("Error Radio.CONNECT: ".mysql_error());
		$VoteCounterSumQuery = "SELECT SUM(`vote_counter`) AS `vote_counter_sum` FROM `votes` WHERE `votes`.`vote_group` = '".addslashes($ChannelTitle)."' AND `votes`.`vote_added` > '".date("Y-m-d", (mktime(0, 0, 0, date("m"), date("d"), date("Y")) - VOTE_PERIOD))."'";
		$VoteCounterSumQueryResult = mysql_query($VoteCounterSumQuery, $RadioDB) or die("Error Radio.VoteCounterSumQuery: ".mysql_error());
		$VoteCounterSum = mysql_result($VoteCounterSumQueryResult, 0, 0);
		unset($VoteCounterSumQueryResult, $VoteCounterSumQuery);
		
		// echo date("Y-m-d", (mktime(0, 0, 0, date("m"), date("d"), date("Y")) - VOTE_PERIOD)); // for debugging
		$VoteQuery = "SELECT * FROM `votes` WHERE `votes`.`vote_group` = '".addslashes($ChannelTitle)."' AND `votes`.`vote_added` > '".date("Y-m-d", (mktime(0, 0, 0, date("m"), date("d"), date("Y")) - VOTE_PERIOD))."' ORDER BY `vote_artist_name` ASC";
		$VoteResult = mysql_query($VoteQuery, $RadioDB) or die("Error Radio.VoteQuery: ".mysql_error());
		if (mysql_num_rows($VoteResult)) {
			$out .= "<div class=\"sidebar-vote\">";
			$out .= "<div class=\"vote-channelname\">".$ChannelTitle."</div>";
			if (!empty($GLOBALS['VoteUpdateResult']) and $GLOBALS['VoteUpdateResult']['key'] == $ChannelTitle) {
				$out .= $GLOBALS['VoteUpdateResult']['value'];
				unset($GLOBALS['VoteUpdateResult']);
			}
			$out .= "<form name=\"vote_".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $ChannelTitle)))."\" action=\"\" method=\"post\">";
			$out .= "<input name=\"vote_group\" type=\"hidden\" value=\"".$ChannelTitle."\" />";
			$out .= "<ul>";
			while ($VoteArray = mysql_fetch_array($VoteResult)) {
				if ($VoteCounterSum != 0) {
					$VoteCounterPercent = $VoteArray['vote_counter'] * 100 / $VoteCounterSum;
				}
				else {
					$VoteCounterPercent = 0;
				}
				$ProgressBarWidth = 180 / 100 * $VoteCounterPercent;
				$out .= "<li>";
				$out .= "<input name=\"vote_id\" type=\"radio\" value=\"".$VoteArray['vote_id']."\" onchange=\"javascript: document.forms['vote_".preg_replace("/ /", "", stripslashes(preg_replace("/\//", "_", $ChannelTitle)))."'].submit();\" />".CutString(stripslashes($VoteArray['vote_artist_name']), 21)." <span class=\"vote-percent\">(".ceil($VoteCounterPercent)."&nbsp;%)</span>";
				$out .= "<br />";
				$out .= "<p class=\"progressBar\"><span><em style=\"left:".$ProgressBarWidth."px\">&nbsp;</em></span></p>";
				$out .= "</li>";
				unset ($ProgressBarWidth, $VoteCounterPercent);
			}
			$out .= "</ul>";
			$out .= "</form>";
			$out .= "</div>";
			unset ($VoteArray);
		}
		unset ($VoteResult, $VoteQuery);
		mysql_close($RadioDB);
		
		return $out;
	}
	
	function CapitalizeFirstLetterOnly($string) {
		$string = iconv("UTF-8", "CP1251", $string);
		$string = mb_convert_case(substr($string, 0, 1), MB_CASE_UPPER).mb_convert_case(substr($string, 1, (strlen($string) - 1)), MB_CASE_LOWER);
		$string = iconv("CP1251", "UTF-8", $string);
		return $string;
	}
	
	function CutString($string, $length) {
		if (strlen(iconv("UTF-8", "CP1251", $string)) > $length) {
			$string = iconv("UTF-8", "CP1251", $string);
			$string = trim(substr($string, 0, $length));
			while (substr($string, (strlen($string) - 1), 1) == ".") {
				$string = trim(substr($string, 0, (strlen($string) - 1)));
			}
			$string .= "&hellip;";
			$string = iconv("CP1251", "UTF-8", $string);
		}
		return $string;
	}
	
	function DetectCyrCharset($str) {
		define('LOWERCASE',3);
		define('UPPERCASE',1);

	    $charsets = Array(
    	                  'k' => 0,
        	              'w' => 0,
            	          'd' => 0,
                	      'i' => 0,
                    	  'm' => 0
	                      );
	    for ( $i = 0, $length = strlen($str); $i < $length; $i++ ) {
    	    $char = ord($str[$i]);
        	//non-russian characters
	        if ($char < 128 || $char > 256) continue;
        	
	        //CP866
    	    if (($char > 159 && $char < 176) || ($char > 223 && $char < 242)) 
        	    $charsets['d']+=LOWERCASE;
	        if (($char > 127 && $char < 160)) $charsets['d']+=UPPERCASE;
    	    
        	//KOI8-R
	        if (($char > 191 && $char < 223)) $charsets['k']+=LOWERCASE;
        	if (($char > 222 && $char < 256)) $charsets['k']+=UPPERCASE;
    	    
	        //WIN-1251
    	    if ($char > 223 && $char < 256) $charsets['w']+=LOWERCASE;
        	if ($char > 191 && $char < 224) $charsets['w']+=UPPERCASE;
        	
	        //MAC
    	    if ($char > 221 && $char < 255) $charsets['m']+=LOWERCASE;
        	if ($char > 127 && $char < 160) $charsets['m']+=UPPERCASE;
        	
	        //ISO-8859-5
    	    if ($char > 207 && $char < 240) $charsets['i']+=LOWERCASE;
        	if ($char > 175 && $char < 208) $charsets['i']+=UPPERCASE;        	
		}
    	arsort($charsets);
	    return key($charsets);
	}
	
	function IntToMonth($num, $type, $case) {
		$short = array("янв", "фев", "мар", "апр", "май", "июн", "июл", "авг", "сен", "окт", "ноя", "дек");
		$long  = array("январ", "феврал", "март", "апрел", "ма", "июн", "июл", "август", "сентябр", "октябр", "ноябр", "декабр");

		$nominative = array("ь", "ь", "", "ь", "й", "ь", "ь", "", "ь", "ь", "ь", "ь");
		$genitive = array("я", "я", "а", "я", "я", "я", "я", "а", "я", "я", "я", "я");

		$num = $num - 1;

		switch (mb_convert_case($type, MB_CASE_LOWER)) {
			case "short":
			$var = $short[$num];
			break;

			case "long":
			$var = $long[$num];
			switch (mb_convert_case($case, MB_CASE_LOWER)) {
				case "nominative":
				$var .= $nominative[$num];
				break;

				case "genitive":
				$var .= $genitive[$num];
				break;
			}
			break;
		}

		return $var;
	}
	
	function DatetimeToWords($var, $date_only = true) {
		if ($var == "0000-00-00" or $var == "0000-00-00 00:00:00") {
			return false;
		}
		$date = explode("-", $var);
		$date3 = $date[0];
		$date2 = IntToMonth($date[1], "long", "genitive");
		$date1 = explode(" ", $date[2]);
		$date1 = $date1[0];
	
		if ($date_only == false) {
			$time = explode(" ", $var);
			$time = ", ".$time[1];
		}
		else {
			$time = "";
		}
		
		if ($date1[0] == "0") {
			$date1 = $date1[1];
		}
		return $date1." ".$date2." ".$date3.$time;
		unset ($date, $date1, $date2, $date3, $time);
	}
?>