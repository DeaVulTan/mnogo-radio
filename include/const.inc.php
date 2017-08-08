<?php

        // LANG - Start
        function CurrentURL() {
            $currentURL = $_SERVER['HTTP_REFERER'];

            $currentURL = explode("?", $currentURL);
            $currentURL = $currentURL[0];

            return $currentURL;
        }
        
        function mb_ucfirst($string) {
            $string = mb_ereg_replace("^[\ ]+","", $string);
            $string = mb_strtoupper(mb_substr($string, 0, 1, "UTF-8"), "UTF-8").mb_substr($string, 1, mb_strlen($string), "UTF-8" );
            return $string;
        }
        
        $SiteLanguages = array(
            'uz' => array(
                'ru' => 'Узбекский',
                'uz' => 'O`zbekcha',
            ),
            'ru' => array(
                'ru' => 'Русский',
                'uz' => 'Ruscha',
            ),
        );

        if (isset($_GET['lang'])) {
            if (strlen($_GET['lang']) == 2) {
                $SetLang = mb_convert_case($_GET['lang'], MB_CASE_LOWER);
                if (in_array($SetLang, array_keys($SiteLanguages))) {
                    setcookie("LANG", $SetLang, time()+259200, "/");
                }
            }    
            header("Location: ".CurrentURL());
        }
       
        switch ($_COOKIE['LANG']) {
            case "ru":
            case "uz":
                break;

            default:
                setcookie("LANG", "ru", time()+259200, "/");
                $_COOKIE['LANG'] = "ru";
                break;
        }
        
        define ("SelectedLanguage", $_COOKIE['LANG']);
        
        define("SitePath", "/var/www/radio.stream.uz");
        define("LangDir", "/include/lang");
        define("LangPath", SitePath.LangDir);
        
        if (file_exists(LangPath."/language-".SelectedLanguage.".json")) {
            $lang = json_decode(file_get_contents(LangPath."/language-".SelectedLanguage.".json"), true);
        }
        else {
            $lang = json_decode(file_get_contents(LangPath."/language-ru.json"), true);
        }
        
        // LANG - End 

	define('MEDIA_PATH', '/var/www/radio.stream.uz/media/');
	define('PLAYLIST_PATH', '/var/www/radio.stream.uz/streams/');

	define('RADIO_DBHOST', '192.168.140.108');
	define('RADIO_DBUSER', 'radiostream');
	define('RADIO_DBPASS', '38z2sjQG44nemSC8');
	define('RADIO_DBBASE', 'radiostream');

	define('MUSIC_DBHOST', '192.168.140.108');
	define('MUSIC_DBUSER', 'radiomusic');
	define('MUSIC_DBPASS', 'uUpGPwU9Dyx6NSHM');
	define('MUSIC_DBBASE', 'radiomusic');
        
	$VotePeriod = 86400 * 7; // days
	define('VOTE_PERIOD', $VotePeriod);
	unset ($VotePeriod);

	$ChannelCellColorCurrent = "CE";
	$ChannelCellColorIncrement = 9;

	$Channels = array ("Electronic" => array("53", "206", "148", "32", "26", "36", "19"),
                           "Rock" => array("41", "21", "190", "182", "2", "92", "80", "48", "184", "198", "160", "93", "122", "18", "79", "96", "142", "166", "171", "201", "57", "203", "188", "145", "150", "10", "163", "23", "181", "214"),
                           "Pop / Dance" => array("133", "4", "172", "179", "14", "55", "197"),
                           "Jazz / Folk" => array("9", "81", "1", "30", "75", "82", "31", "84", "210", "209", "211", "212"));

	
	$VoteArtists = 10;
	$VoteWinners = 6;

	$VoteExceptions = array("VA%", "%Сборник%", "%Trance Around the World", "%A State of Trance", "%Ministry of Sound%");

	$PlaylistMaxTracks = 400;
	$PlaylistMaxTrackTime = 7; // minutes
?>
