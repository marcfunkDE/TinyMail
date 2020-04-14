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
 * sending check
**/
	## prepare sql
	$sqls = "SELECT
					`id`
			FROM
				`".PFX."mailing`
			WHERE
				`status` = '1'
			";
			
	## send query
	$send = ms($sqls);
	
	## check result
	if($send[0] > 0) {
		
		echo '<p class="w">Achtung: Es läut aktuell noch eine Versendung! Bitte nehmen Sie aktuelle keine Änderungen vor!!</p>';
		
	}



/**
 * listing
**/
	if(!isset($_GET['a'])) {
		
		## prepare sql
		$sqls = "SELECT
					`id`,
					`mail`,
					`firstname`,
					`lastname`
				FROM
					`".PFX.$sic."`
				ORDER BY
					`mail`
				ASC
				";
				
		## send query
		$send = ms($sqls);
		
		## total lines
		$tl = $send[0];
		
		## check if data lines exists
		if($tl > 0) {
			
			## preparing data
			$data = $send[1];
			
			## preparing vars
			$lc = 0;
			
			## process data
			foreach($data as $d) {
				
				## linecounter
				$lc++;
				
				## prepare vars
				$id = $d['id'];
				$mail = $d['mail'];
				$firstname = $d['firstname'];
				$lastname = $d['lastname'];
				
				## output
				include($MP."list.html5");
				
			}
			
		} else {
			
			## output nolines template
			include($MP."list-nolines.html5");
			
		}
		
	}



/**
 * adding
**/
	if(isset($_GET['a']) && $_GET['a'] == "a") {
		
		## set vars
		$errmsg = "";
		
		## form check
		if(isset($_POST['save'.$sic]) && $_POST['save'.$sic] == "yes") {
			
			## check for file
			$sfile = "";
			if(!empty($_FILES['file']['name'])) {
				
				$filetype = explode(".", substr($_FILES['file']['name'], -4, 4));
				if(isset($filetype[1]) && in_array(strtolower($filetype[1]), array("csv"))) {
					
					## filename
					$newfile = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($_FILES['file']['name'])).".".strtolower($filetype[1]);
					
					## check filename
					if(file_exists("files/".$newfile)) {
						
						## change name with random number if file with same name exist
						$newfile = rand(10, 100).$newfile;
						
					}
					
					## move file
					$copyfile = move_uploaded_file($_FILES['file']['tmp_name'], "files/".$newfile);
					
					## check moving file
					if(!$copyfile) {
						
						$errmsg = '<p class="e">Beim Upload der Datei ist ein Fehler aufgetreten (Copy error)!</p>';
						
					} else {
						
						## file path
						$csvfile = "files/".$newfile;
						
						## check file
						if(file_exists($csvfile)) {
							
							## get file content
							$csv = file_get_contents($csvfile);
							
							## split lines
							$lines = explode("\n", $csv);
							
							## count lines and subtract last line feed
							$tl = count($lines) - 1;
							
							## prepare sql
							$sqls = "INSERT INTO
										`".PFX.$sic."`
									VALUES 
									";
							
							## process lines
							for($l = 0; $l < $tl; $l++) {
								
								## split fields
								$fields = explode(";", $lines[$l]);
								
								## count fields
								$fc = count($fields);
								
								## check field counter (must be 4 fields)
								if($fc == 4) {
									
									## prepare values
									$mail = strtolower(trim($fields[0]));
									$salutation = ucfirst(strtolower(trim(preg_replace('/[^a-zA-Z-]\s+/', '', $fields[1]))));
									$firstname = str_replace("\"", "", trim($fields[2]));
									$lastname = str_replace("\"", "", trim($fields[3]));
									
									## check mail
									if(!filter_var(idn_to_ascii($mail, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_EMAIL)) {
										
										$errmsg .= '<p class="e">Zeile '.($l+1).': Die E-Mailadresse '.$mail.' ist nicht gültig!</p>';
										
									} else {
										
										## prepare sql
										$sqls .= "(
													'',
													'".mres($mail)."',
													'".mres($salutation)."',
													'".mres($firstname)."',
													'".mres($lastname)."'
												),";
										
									}
									
								} else {
									
									$errmsg = '<p class="e">Es gibt mind. eine Zeile, die zu viele Felder besitzt!</p>';
									break;
									
								}
								
							}
							
							## sql preparation
							if(strlen($sqls) > 65) {
								
								$sqls = substr($sqls, 0, strlen($sqls) - 1);
								$sqls .= " ON DUPLICATE KEY UPDATE `mail` = VALUES(`mail`)";
								
								## send query
								$send = mi($sqls);
								
								## check result
								$ti = $send[0];
								if($ti > 0) {
									
									$errmsg = '<p class="o">Von insgesamt '.$tl.' Zeilen wurden '.$ti.' importiert.</p>';
									unlink($csvfile);
									
								} else {
									
									$errmsg = '<p class="e">Es wurden keine Daten importiert!</p>';
									
								}
								
							}
							
						} else {
							
							$errmsg = '<p class="e">Beim Upload der Datei ist ein Fehler aufgetreten (File not found)!</p>';
							
						}
						
					}
					
				} else {
					
					$errmsg = '<p class="e">Dieser Dateityp ist nicht erlaubt!</p>';
					
				}
				
			} else {
				
				$errmsg = '<p class="e">Bitte wählen Sie eine Datei aus!</p>';
				
			}
			
		}
		
		
		## show adding form
		include($MP."import.html5");
		
	}



/**
 * editing
**/
	if(isset($_GET['a']) && $_GET['a'] == "e" && isset($_GET['id']) && ctype_digit($_GET['id'])) {
		
		## prepare vars
		$errmsg = "";
		
		## prepare id
		$id = trim($_GET['id']);
		
		
		## saving changes
		if(isset($_POST['save'.$sic]) && $_POST['save'.$sic] == "yes") {
			
			## set status field
			$sf = 200;
			
			## check subject
			if($_POST['mail'] == "" || !filter_var(idn_to_ascii($_POST['mail'], IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_EMAIL)) {
				
				$errmsg = '<p class="e">Die E-Mailadresse ist nicht gültig!</p>';
				$sf = 400;
				
			## saving receiver
			} else {
				
				## prepare vars
				$smail = trim($_POST['mail']);
				$ssalutation = trim($_POST['salutation']);
				$sfirstname = trim($_POST['firstname']);
				$slastname = trim($_POST['lastname']);
				
				## check status
				if($sf == 200) {
					
					## prepare sql
					$sqls = "UPDATE
								`".PFX.$sic."`
							SET
								`mail`='".mres($smail)."',
								`salutation`='".mres($ssalutation)."',
								`firstname`='".mres($sfirstname)."',
								`lastname`='".mres($slastname)."'
							WHERE
								`id` = '".mres($id)."'
							AND
								`id` = '".mres($_POST['receiverid'])."'
							";
							
					## send query
					$send = mi($sqls);
					
					## check query
					if($send[0] == 1) {
						
						$errmsg = '<p class="o">Die/Der Empfänger/in wurde erfolgreich bearbeitet.</p>';
						unset($_POST);
						
					} else {
						
						$errmsg = '<p class="e">Beim Speichern der/des Empfängerin/Empfängers ist ein Fehler aufgetreten!</p>';
						
					}
					
				}
				
			}
			
		}
		
		
		## get details
		$sqls = "SELECT
					`id`,
					`mail`,
					`salutation`,
					`firstname`,
					`lastname`
				FROM
					`".PFX.$sic."`
				WHERE
					`id` = '".mres($id)."'
				";
				
		## send query
		$send = ms($sqls);
		
		## check result
		if($send[0] == 1) {
			
			## prepare data
			$d = $send[1][0];
			
			## prepare values
			$pmail = $d['mail'];
			$psalutation = $d['salutation'];
			$pfirstname = $d['firstname'];
			$plastname = $d['lastname'];
			
			## output
			include($MP."edit.html5");
			
		} else {
			
			echo '<p class="e">Diese/r Empfänger/in kann nicht aufgerufen werden!</p>';
			
		}
		
	}



/**
 * deletion
**/
	if(isset($_GET['a']) && $_GET['a'] == "d" && isset($_GET['id']) && ctype_digit($_GET['id'])) {
		
		## prepare vars
		$errmsg = "";
		
		## prepare id
		$id = trim($_GET['id']);
		
		## deletion check
		if(isset($_POST['del'.$sic]) && $_POST['del'.$sic] == "yes") {
			
			## prepare sql
			$sqls = "DELETE FROM
						`".PFX.$sic."`
					WHERE
						`id` = '".mres($id)."'
					AND
						`id` = '".mres($_POST[$sic.'id'])."'
					";
					
			## send query
			$send = mi($sqls);
			
			## check deletion
			if($send[0] == 1) {
				
				echo '<p class="o">Die/Der Empfänger/in wurde gelöscht.</p>';
				
			} else {
				
				echo '<p class="e">Beim Löschen der/des Empfängerin/Empfängers ist ein Fehler aufgetreten!</p>';
				
			}
			
		} else {
		
			## get details
			$sqls = "SELECT
						`id`,
						`mail`
					FROM
						`".PFX.$sic."`
					WHERE
						`id` = '".mres($id)."'
					";
					
			## send query
			$send = ms($sqls);
			
			## check result
			if($send[0] == 1) {
				
				## prepare data
				$d = $send[1][0];
				
				## prepare vars
				$id = $d['id'];
				$delname = $d['mail'];
				
				## delete question
				include($MP."del.html5");
				
			} else {
				
				echo '<p class="e">Diese/r Empänger/in kann nicht aufgerufen werden!</p>';
				
			}
			
		}
		
	}
?>