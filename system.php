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
 * error reporting
**/
	error_reporting(E_ALL && ~E_NOTICE);



/**
 * include config
**/
	if(file_exists("../system/cfg.php")) {
		
		include("../system/cfg.php");
	
	}


/**
 * include mysql functions
**/
	if(file_exists("../system/mysqli.php")) {
		
		include("../system/mysqli.php");
	
	}



/**
 * include vendor marcfunk
**/
	if(file_exists("../system/vendor/marcfunk.php")) {
		
		include("../system/vendor/marcfunk.php");
	
	}
?>