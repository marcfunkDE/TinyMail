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
 * include system files
**/
	if(file_exists("../system.php")) {
	
		include("../system.php");
	
	}



/**
 * install queries
**/
	## table mailing
	$sqls_mailing = "CREATE TABLE IF NOT EXISTS
						`tm_mailing`
					(
						`id` bigint(20) NOT NULL AUTO_INCREMENT,
						`subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
						`content` longtext COLLATE utf8_unicode_ci NOT NULL,
						`attachment` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
						`datetime` datetime NOT NULL,
						`senddatetime` datetime NOT NULL,
						`status` int(1) NOT NULL,
						PRIMARY KEY (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	
	## table receiver
	$sqls_receiver = "CREATE TABLE IF NOT EXISTS
						`tm_receiver`
					(
						`id` bigint(20) NOT NULL AUTO_INCREMENT,
						`mail` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
						`salutation` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
						`firstname` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
						`lastname` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `mail` (`mail`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";



/**
 * start installation
**/
	if(isset($_POST['start']) && $_POST['start'] == "install") {
		
		## create table mailing
		$send = mi($sqls_mailing);
		
		## create table receiver
		$send = mi($sqls_receiver);
		
		## create install file
		$of = fopen("../sending/install", "w+");
		fclose($of);
		
		## check file
		if(file_exists("../sending/install")) {
			
			header("LOCATION: index.html");
			
		}
		
	}



/**
 * install form
**/
	include("../system/html/install.html5");
?>