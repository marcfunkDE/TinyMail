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
	include("../system.php");



/**
 * check install file
**/
	## filename
	$ifile = "../sending/install";
	
	## check if file exist
	if(!file_exists($ifile)) {
		
		header("LOCATION: install.php");
		
	}



/**
 * content generator
**/
	## default parameter
	$addcss = "";
	$sic = "index";

	## check parameter
	if(isset($_GET['s']) && $_GET['s'] !== "") {
		
		## parameter protection (only letters, numbers, hyphen and underline character allowed)
		$siccheck = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['s']);

		## check if requested page file exists
		if(file_exists("../system/module/".$siccheck.".php")) {
			
			## prepare include var
			$sic = $siccheck;
			
			## prepare include file
			$includefile = "../system/module/".$sic.".php";
			
			## prepare vars
			$MP = "../system/module/".$sic."/";
			
		}
		
	} else {
		
		## prepare include file
		$includefile = "../system/module/".$sic.".php";
		
		## prepare vars
		$MP = "../system/module/".$sic."/";
		
	}
	
	## check css file
	if(file_exists("css/".$sic.".css")) {
		
		$addcss = '<link rel="stylesheet" type="text/css" href="css/'.$sic.'.css">'."\n";
		
	}



/**
 * processing content
 */	
	## start site buffering
	ob_start();
	
	## include content file
	include($includefile);
	
	## prepare content
	$sitecontent = ob_get_contents();

	## flush buffer
	ob_end_clean();
	
	## include page template
	include("../system/html/page.html5");
?>