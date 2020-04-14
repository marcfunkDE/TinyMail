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
				`".PFX.$sic."`
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
					`subject`,
					`datetime`,
					`senddatetime`,
					`status`
				FROM
					`".PFX.$sic."`
				ORDER BY
					`datetime`
				DESC
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
				$datetime = date("d.m.Y", strtotime($d['datetime']));
				$senddatetime = ($d['senddatetime'] !== "0000-00-00 00:00:00") ? date("d.m.Y H:i", strtotime($d['senddatetime'])).' Uhr' : "";
				$subject = $d['subject'];
				
				## output
				include($MP."list.html5");
				
			}
			
		} else {
			
			## output nolines template
			include($MP."list-nolines.html5");
			
		}
		
	}



/**
 * showing
**/
	if(isset($_GET['a']) && $_GET['a'] == "s" && isset($_GET['id']) && ctype_digit($_GET['id'])) {
		
		## prepare id
		$id = trim($_GET['id']);
		
		## get details
		$sqls = "SELECT
					`id`,
					`subject`,
					`content`,
					`attachment`,
					`datetime`,
					`senddatetime`,
					`status`
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
			$datetime = date("d.m.Y", strtotime($d['datetime']));
			$senddatetime = ($d['senddatetime'] !== "0000-00-00 00:00:00") ? date("d.m.Y", strtotime($d['senddatetime'])).' Uhr' : "";
			$subject = $d['subject'];
			$content = $d['content'];
			$attachment = ($d['attachment'] !== "") ? $d['attachment'] : "";
			
			## output
			include($MP."show.html5");
			
		} else {
			
			echo '<p class="e">Diese Serienmail kann nicht aufgerufen werden!</p>';
			
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
			
			## set status field
			$sf = 200;
			
			## check subject
			if($_POST['subject'] == "" || !ctype_print($_POST['subject'])) {
				
				$errmsg = '<p class="e">Der Betreff ist leer oder enthält nicht erlaubte Zeichen!</p>';
				$sf = 400;
				
			## check message
			} elseif($_POST['editor'] == "") {
				
				$errmsg = '<p class="e">Bitte geben Sie eine E-Mail Nachricht ein!</p>';
				$sf = 400;
			
			## saving mailing
			} else {
				
				## prepare vars
				$ssubject = trim($_POST['subject']);
				$seditor = trim($_POST['editor']);
				
				## check for file
				$sfile = "";
				if(!empty($_FILES['file']['name'])) {
					
					$filetype = explode(".", substr($_FILES['file']['name'], -4, 4));
					if(isset($filetype[1]) && in_array(strtolower($filetype[1]), $atttypes)) {
						
						## filename
						$newfile = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($_FILES['file']['name'])).".".strtolower($filetype[1]);
						
						## check filename
						if(file_exists("files/".$newfile)) {
							
							$newfile = rand(10, 100).$newfile;
							
						}
						
						## move file
						$copyfile = move_uploaded_file($_FILES['file']['tmp_name'], "files/".$newfile);
						
						## check moving file
						if(!$copyfile) {
							
							$errmsg = '<p class="e">Beim Upload der Datei ist ein Fehler aufgetreten!</p>';
							$sf = 400;
							
						} else {
							
							## set file to sql var
							$sfile = $newfile;
							
						}
						
					} else {
						
						$errmsg = '<p class="e">Dieser Dateityp ist nicht erlaubt!</p>';
						$sf = 400;
						
					}
					
				}
				
				## check status
				if($sf == 200) {
					
					## prepare sql
					$sqls = "INSERT INTO
								`".PFX.$sic."`
							VALUES (
								'',
								'".mres($ssubject)."',
								'".mres($seditor)."',
								'".mres($sfile)."',
								'".date("Y-m-d H:i:s")."',
								'0000-00-00 00:00:00',
								'0'
							)";
							
					## send query
					$send = mi($sqls);
					
					## check query
					if($send[0] == 1) {
						
						$errmsg = '<p class="o">Die Serienmail wurde erfolgreich angelegt.</p>';
						unset($_POST);
						
					} else {
						
						$errmsg = '<p class="e">Beim Speichern der Serienmail ist ein Fehler aufgetreten!</p>';
						
					}
					
				}
				
			}
			
		}
		
		
		## prepare values
		$psubject = (isset($_POST['subject'])) ? trim($_POST['subject']) : "";
		$peditor = (isset($_POST['editor'])) ? trim($_POST['editor']) : "";
		
		## show adding form
		include($MP."add.html5");
		
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
			if($_POST['subject'] == "" || !ctype_print($_POST['subject'])) {
				
				$errmsg = '<p class="e">Der Betreff ist leer oder enthält nicht erlaubte Zeichen!</p>';
				$sf = 400;
				
			## check message
			} elseif($_POST['editor'] == "") {
				
				$errmsg = '<p class="e">Bitte geben Sie eine E-Mail Nachricht ein!</p>';
				$sf = 400;
			
			## saving mailing
			} else {
				
				## prepare vars
				$ssubject = trim($_POST['subject']);
				$seditor = trim($_POST['editor']);
				
				## check for file
				$sfile = "";
				if(!empty($_FILES['file']['name'])) {
					
					$filetype = explode(".", substr($_FILES['file']['name'], -4, 4));
					if(isset($filetype[1]) && in_array(strtolower($filetype[1]), $atttypes)) {
						
						## filename
						$newfile = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($_FILES['file']['name'])).".".strtolower($filetype[1]);
						
						## check filename
						if(file_exists("files/".$newfile)) {
							
							$newfile = rand(10, 100).$newfile;
							
						}
						
						## move file
						$copyfile = move_uploaded_file($_FILES['file']['tmp_name'], "files/".$newfile);
						
						## check moving file
						if(!$copyfile) {
							
							$errmsg = '<p class="e">Beim Upload der Datei ist ein Fehler aufgetreten!</p>';
							$sf = 400;
							
						} else {
							
							## set file to sql var
							$sfile = $newfile;
							
						}
						
					} else {
						
						$errmsg = '<p class="e">Dieser Dateityp ist nicht erlaubt!</p>';
						$sf = 400;
						
					}
					
				}
				
				## check status
				if($sf == 200) {
					
					## prepare sql
					$sqls = "UPDATE
								`".PFX.$sic."`
							SET
								`subject`='".mres($ssubject)."',
								`content`='".mres($seditor)."',
								`attachment`='".mres($sfile)."'
							WHERE
								`id` = '".mres($id)."'
							AND
								`id` = '".mres($_POST[$sic.'id'])."'
							";
							
					## send query
					$send = mi($sqls);
					
					## check query
					if($send[0] == 1) {
						
						$errmsg = '<p class="o">Die Serienmail wurde erfolgreich bearbeitet.</p>';
						unset($_POST);
						
					} else {
						
						$errmsg = '<p class="e">Beim Speichern der Serienmail ist ein Fehler aufgetreten!</p>';
						
					}
					
				}
				
			}
			
		}
		
		
		## get details
		$sqls = "SELECT
					`id`,
					`subject`,
					`content`,
					`attachment`
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
			
			
			## delete attachment
			if(isset($_GET['da']) && $d['attachment'] !== "") {
				
				## delete file
				unlink("files/".$d['attachment']);
				
				## update entry
				$send = mi("UPDATE `".PFX.$sic."` SET `attachment` = '' WHERE `id` = '".mres($id)."'");
				if($send[0] == 1) {
					
					## replace var
					$d['attachment'] = "";
					
					echo '<p class="o">Der Anhang wurde entfernt.</p>';
					
				} else {
					
					echo '<p class="e">Beim Entfernen des Anhangs ist ein Fehler aufgetreten!</p>';
					
				}
				
			}
			
			
			## prepare values
			$psubject = $d['subject'];
			$peditor = $d['content'];
			$attachment = ($d['attachment'] !== "") ? $d['attachment'] : "";
			
			## output
			include($MP."edit.html5");
			
		} else {
			
			echo '<p class="e">Diese Serienmail kann nicht aufgerufen werden!</p>';
			
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
				
				## check attachment
				if($_POST['attach'] !== "") {
					
					## delete attachment
					unlink("files/".$_POST['attach']);
					
				}
				
				echo '<p class="o">Die Serienmail wurde gelöscht.</p>';
				
			} else {
				
				echo '<p class="e">Beim Löschen der Serienmail ist ein Fehler aufgetreten!</p>';
				
			}
			
		} else {
		
			## get details
			$sqls = "SELECT
						`id`,
						`subject`,
						`attachment`
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
				$delname = $d['subject'];
				$attachment = $d['attachment'];
				
				## delete question
				include($MP."del.html5");
				
			} else {
				
				echo '<p class="e">Diese Serienmail kann nicht aufgerufen werden!</p>';
				
			}
			
		}
		
	}
?>