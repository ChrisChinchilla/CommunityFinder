<?php	 
session_start();

// Bootstrap Config

if ($_SERVER['HTTP_HOST'] == 'theveggiebox.org' || $_SERVER['HTTP_HOST'] == 'www.theveggiebox.org') {
	$SITE = array();
	$SITE['GOOGLE_KEY'] = "ABQIAAAAiflk3RC59GlSewXxvZDrIhSKjjOOwOf6Deakv_c0W5fk5OntFRTpe1ELMDcNTMf2mODmqKh0DXLlcw";
	$SITE['DOMAIN'] = "www.theveggiebox.org";
	$SITE['DB_HOST'] = 'localhost';
	$SITE['DB_USERNAME'] = 'lukasber_tvb';
	$SITE['DB_PW'] = '';
	$SITE['DB_NAME'] = 'lukasber_agcdev';
}
else if ($_SERVER['HTTP_HOST'] == 'agrowingcommunity.org' || $_SERVER['HTTP_HOST'] == 'www.agrowingcommunity.org') {
	$SITE = array();	
	$SITE['GOOGLE_KEY'] = "ABQIAAAA0DfdGjlGDDT7I6yi_1JrlxSoo8lXEzg-M7_Ch5nwoLCndX9HaxQAlTeW_OlkcleAuMgU6afSPkQCfA";
	$SITE['DOMAIN'] = "www.agrowingcommunity.org";
	$SITE['DB_HOST'] = 'localhost';
	$SITE['DB_USERNAME'] = 'lukasber_tvb';
	$SITE['DB_PW'] = '';
	$SITE['DB_NAME'] = 'lukasber_agc';
}
else if ($_SERVER['HTTP_HOST'] == 'victoriamycommunity.org' || $_SERVER['HTTP_HOST'] == 'www.victoriamycommunity.org') {
	$SITE = array();
	$SITE['GOOGLE_KEY'] = "ABQIAAAA0DfdGjlGDDT7I6yi_1JrlxReUrB34zyg3_Uqv9k3eVayiHXfOxSGkSj-044iWfJlJhN16dph6bLYgg";
	$SITE['DOMAIN'] = "www.victoriamycommunity.org";
	$SITE['DB_HOST'] = 'localhost';
	$SITE['DB_USERNAME'] = 'lukasber_tvb';
	$SITE['DB_PW'] = '';
	$SITE['DB_NAME'] = 'lukasber_agc';
}
/* Change this file to reflect your local settings */
else{
	$SITE = array();
	$SITE['GOOGLE_KEY'] = "";
	$SITE['DOMAIN'] = "communityfinder.net";
	$SITE['DB_HOST'] = 'localhost';
	$SITE['DB_USERNAME'] = 'communityfinder';
	$SITE['DB_PW'] = 'communityfinder';
	$SITE['DB_NAME'] = 'communityfinder';
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
}
define('ANONYMOUS_USER', 133);
?>
