<?php
/**
 * TinyMail
 * @Creation: 07.04.2020
 * @author Marc Funk | marcfunk IT UG (hb.) | https://marc-funk.de
 * @version 0.1
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
**/



/**
 * mysql connection data
**/
	## host
	$mysqlhost = "localhost";
	
	## port
	$mysqlport = 3306;
	
	## user
	$mysqluser = "";
	
	## password
	$mysqlpass = "";
	
	## database
	$mysqldb = "";
	
	## prefix
	$mysqlpfx = "tm_";
	define("PFX", $mysqlpfx);



/**
 * php mailer -> smtp configuration
**/
	## host
	$mcfg['host'] = "";
	define("MAILHOST", $mcfg['host']);
	
	## smtp port
	$mcfg['port'] = "587";
	define("MAILPORT", $mcfg['port']);
	
	## secure (nothing or ssl)
	$mcfg['sec'] = "";
	define("MAILSEC", $mcfg['sec']);
	
	## user
	$mcfg['user'] = "";
	define("MAILUSER", $mcfg['user']);
	
	## password
	$mcfg['pass'] = "";
	define("MAILPASS", $mcfg['pass']);
	
	## e-mail
	$mcfg['mail'] = "";
	define("MAILMAIL", $mcfg['mail']);
	
	## name
	$mcfg['name'] = "";
	define("MAILNAME", $mcfg['name']);
	
	## maximum passages
	$maxpassages = 10;
	
	## total receivers per round
	$maxreceivers = 75;
	
	## pause (in seconds) between the maximum passages (e. g. 10 minutes sending + 15 minutes pause = 1500 seconds)
	$maxpassagespause = 1500;



/**
 * allowed attachment types
**/
	$atttypes = [
		"pdf",
		"jpg",
		"jpeg"
	];
?>