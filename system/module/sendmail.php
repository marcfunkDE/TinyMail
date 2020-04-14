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
 * prepare vars
**/
	$errmsg = "";



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
		
		echo '<p class="w">Achtung: Es läut aktuell noch eine Versendung! Es kann deshalb kein zweiter Vorgang gestartet werden!</p>';
		
	} else {

		/**
		 * start mail generation
		**/
			## complete sending started
			if(isset($_POST['completesend']) && $_POST['completesend'] == "yes" && ctype_digit($_POST['mailingid'])){
				
				## update mailing: set status to 1 for sending
				$sqls = "UPDATE
							`".PFX."mailing`
						SET
							`status` = '1',
							`senddatetime` = '".date("Y-m-d H:i:s")."'
						WHERE
							`id` = '".mres($_POST['mailingid'])."'
						";
						
				## send query
				$send = mi($sqls);
				
				## check result
				if($send[0] == 1) {
					
					echo '<p class="o">Der Versand wurde erfolgreich gestartet!</p>';
					
				} else {
					
					echo '<p class="e">Beim Starten des Versands ist ein Fehler aufgetreten!</p>';
					
				}
				
			}
			
			
			## check action
			if(isset($_POST['go']) && $_POST['go'] == "yes") {
				
				## check for testing
				if(isset($_POST['testsend']) && $_POST['testsend'] == "yes") {
					
					## check mail
					if(!filter_var(idn_to_ascii($_POST['mail'], IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_EMAIL)) {
						
						$errmsg = '<p class="e">Die E-Mailadresse ist nicht gültig!</p>';
						
					} else {
						
						## check mailing
						if(ctype_digit($_POST['mailing'])) {
							
							## prepare mailing id
							$mailingid = $_POST['mailing'];
							
							## get mailing
							$sqls = "SELECT
										`id`,
										`subject`,
										`content`,
										`attachment`
									FROM
										`".PFX."mailing`
									WHERE
										`id` = '".mres(trim($mailingid))."'
									";
							
							## send query
							$send = ms($sqls);
							
							## check result
							if($send[0] == 1) {
								
								## prepare data
								$d = $send[1][0];
								$receiver_name = ($_POST['firstname'] !== "" && $_POST['lastname'] !== "") ? trim($_POST['firstname'])." ".trim($_POST['lastname']) : $_POST['mail'];
								switch($_POST['salutation']) {
									
									case "Herr":
										$salutation = "Sehr geehrter";
										break;
										
									case "Frau":
										$salutation = "Sehr geehrte";
										break;
										
									default :
										$salutation = "";
									
								}
								
								## search values
								$sa = [
									'$SAL',
									'$ANREDE',
									'$VORNAME',
									'$NACHNAME'
								];
								
								## replace values
								$ra = [
									$salutation,
									trim($_POST['salutation']),
									trim($_POST['firstname']),
									trim($_POST['lastname'])
								];
								
								## replace search values
								$msgtext = str_replace($sa, $ra, $d['content']);
								
								## check for attachment
								$msgattach = ($d['attachment'] !== "" && file_exists("files/".$d['attachment'])) ? "files/".$d['attachment'] : "";
								
								## send mail
								$mailthemessage = mfmailer($_POST['mail'], $receiver_name, $d['subject'], $msgtext, $msgattach);
								
								## check sending
								if($mailthemessage == 200) {
									
									$errmsg = '<p class="o">Die Testsendung wurde versendet.</p>';
									
								} else {
									
									$errmsg = '<p class="e">Beim Senden der Testsendung ist ein Fehler aufgetreten ('.$mailthemessage.')!</p>';
									
								}
								
							} else {
								
								$errmsg = '<p class="e">Beim Abrufen des Mailings ist ein Fehler aufgetreten!</p>';
								
							}
									
						} else {
							
							$errmsg = '<p class="e">Parameterfehler!!</p>';
							
						}
						
					}
					
				} else {
					
					## count receivers
					$tr = mi("SELECT `id` FROM `".PFX."receiver`");
					
					## check receivers
					if($tr[0] > 0) {
					
						## mailing id
						$mailingid = (ctype_digit($_POST['mailing'])) ? $_POST['mailing'] : "";
						
						## second time question
						include($MP."sendquestion.html5");
						
					} else {
						
						$errmsg = '<p class="e">Es sind keine Empfänger/innen vorhanden!</p>';
						
					}
					
				}
				
			}



		/**
		 * output content
		**/
			
			## check complete send
			if(!isset($_POST['startsend'])) {
			
				## get mailings
				$sqls = "SELECT
							`id`,
							`subject`,
							`datetime`
						FROM
							`".PFX."mailing`
						ORDER BY
							`subject`
						ASC
						";
				
				## send query
				$send = ms($sqls);
				
				## check result
				if($send[0] > 0) {
					
					## preparing data
					$data = $send[1];
					$option = "";
					
					## template file
					$tplfile = $MP."tpl_option.html5";
					
					## template
					$tpl = file_get_contents($tplfile);
					
					## process data
					foreach($data as $d) {
						
						## prepare vars
						$id = $d['id'];
						$subject = $d['subject'];
						
						## search values
						$sa = [
							'$ID',
							'$SUBJECT'
						];
						
						## replace values
						$ra = [
							$id,
							$subject." (".date("d.m.Y", strtotime($d['datetime'])).")"
						];
						
						## replace search values
						$option .= str_replace($sa, $ra, $tpl);
						
					}
					
					## output form
					include($MP.$sic.".html5");
					
				} else {
					
					echo '<p class="n">Es sind keine Serienmails für die Versendung vorhanden.</p>';
					
				}
				
			}
			
	}
?>