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
 * Create mysqli class and connect to mysql
**/
	$mysqli = new mysqli($mysqlhost, $mysqluser, $mysqlpass, $mysqldb, $mysqlport);
	if($mysqli->connect_error) {
		
		die("Connection to database failed: ".$mysqli->connect_errno.' '.$mysqli->connect_error);
		
	} else {
		
		## set mysqli charset
		mysqli_set_charset($mysqli, "utf8");
		
	}



/**
 * Select-function for mysql
 *
 * Function for simplification the mysql select query
 * @param $thequery, string
**/
	function ms($thequery) {
	
		global $mysqli;
		
		## send query
		$sendtomysql = mysqli_query($mysqli, $thequery) OR die(mysqli_error($mysqli));
		
		## check query
		if($sendtomysql) {
			
			## count datalines
			$myr = mysqli_num_rows($sendtomysql);
			
			## check datalines
			if($myr > 0) {
				
				## process datalines
				for($i = 0; $myf[$i] = mysqli_fetch_assoc($sendtomysql); $i++);
				array_pop($myf);
				
				## return result (total of lines, array of lines)
				return array($myr, $myf);
			} else {
				
				## return 0 datalines
				return array($myr);
				
			}
			
		}
	}



/**
 * Insert/Update/Delete-function for mysql
 *
 * Function for simplification the mysql insert/update/delete query
 * @param $thequery, string
**/
	function mi($thequery) {
		
		global $mysqli;
		
		## send query
		$sendtomysql = mysqli_query($mysqli, $thequery) OR die(mysqli_error($mysqli));
		
		## check query
		if($sendtomysql) {
		
			## id of last insert line
			$mid = mysqli_insert_id($mysqli);
			
			## total affected lines
			$myr = mysqli_affected_rows($mysqli);
			
			## return (total of lines, id of last line)
			return array($myr, $mid);
			
		}
	}



/**
 * Injection-protection for mysql
 *
 * Function for protect mysql-queries in relation to injections
 * @param $thefield, string
**/
	function mres($thefield) {
	
		global $mysqli;
		
		## Use real_escape_string and trim for protection
		$value = mysqli_real_escape_string($mysqli, trim($thefield));
		
		## return protected value
		return $value;
		
	}
?>