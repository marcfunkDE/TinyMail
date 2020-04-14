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
 * logging
**/
	function logging($logtext = "") {
		
		## logfile
		$logfile = "../log/".date("Ymd").".txt";
		
		## check logtext
		if($logtext !== "") {
			
			## prepare logtext
			$logcontent = "[".date("d.m.Y - H:i:s-").substr(microtime(), 2, 5)."] ".$logtext."\n";
			
			## open log
			$ol = fopen($logfile, "a");
			
			## write log
			fwrite($ol, $logcontent);
			
			## close log
			fclose($ol);
			
		}
		
	}



/**
 * parameter
**/
	## passages file
	$pfile = "../sending/passages";
	
	## time file
	$tfile = "../sending/time";




/**
 * check passages and timestamp
**/
	## check time file
	if(file_exists($tfile)) {
		
		## get timestamp
		$tstamp = file_get_contents($tfile);
		
		## check stamp
		if((time() - $maxpassagespause) > $tstamp) {
			
			## check passages file
			if(file_exists($pfile)) {
				
				## get passages
				$passages = file_get_contents($pfile);
				
				## check passages
				if($passages < 10) {
					
					/**
					 * get mailings with status 1
					**/
						## prepare sqls
						$sqls = "SELECT
									`id`,
									`subject`,
									`content`,
									`attachment`
								FROM
									`".PFX."mailing`
								WHERE
									`status` = '1'
								";
								
						## send query
						$send = ms($sqls);
						
						## check result
						if($send[0] == 1) {
							
							## prepare data
							$d = $send[1][0];
							
							## logging
							$LOG = logging('OKAY: Serienmail mit der ID '.$d['id'].' und dem Betreff '.$d['subject'].' gefunden.');
							
							## get receivers
							$sqls = "SELECT
										`id`,
										`mail`,
										`salutation`,
										`firstname`,
										`lastname`
									FROM
										`".PFX."receiver`
									ORDER BY
										`mail`
									ASC LIMIT ".mres($maxreceivers);
									
							## send query
							$send = ms($sqls);
							
							## logging
							$LOG = logging('INFO: Gelesene Empfänger/innen: '.$send[0].' (Max: '.$maxreceivers.')');
							
							## check result
							if($send[0] > 0) {
								
								## prepare data
								$receiver = $send[1];
								$delarr = "";
								
								## process receivers
								foreach($receiver as $rcv) {
									
									## check mail
									if(filter_var(idn_to_ascii($rcv['mail'], IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), FILTER_VALIDATE_EMAIL)) {
										
										## salutation
										switch($rcv['salutation']) {
														
											case "Herr":
												$salutation = "Sehr geehrter";
												break;
												
											case "Frau":
												$salutation = "Sehr geehrte";
												break;
												
											default :
												$salutation = "";
											
										}
										
										## prepare name
										$receiver_name = ($rcv['firstname'] !== "" && $rcv['lastname'] !== "") ? trim($rcv['firstname'])." ".trim($rcv['lastname']) : $rcv['mail'];
										
										## search values
										$sa = [
											'$SAL',
											'$ANREDE',
											'$VORNAME',
											'$NACHNAME'
										];
										
										## check salutation
										if(strpos($d['content'], '$SAL') == true && $salutation !== "") {
											
											## replace values
											$ra = [
												$salutation,
												trim($rcv['salutation']),
												trim($rcv['firstname']),
												trim($rcv['lastname'])
											];
											
										} else {
											
											## replace values
											$ra = [
												"",
												trim($rcv['salutation']),
												trim($rcv['firstname']),
												trim($rcv['lastname'])
											];
											
										}
										
										## replace search values
										$msgtext = str_replace($sa, $ra, $d['content']);
										
										## check for attachment
										$msgattach = ($d['attachment'] !== "" && file_exists("files/".$d['attachment'])) ? "files/".$d['attachment'] : "";
										
										## send mail
										$mailthemessage = mfmailer($rcv['mail'], $receiver_name, $d['subject'], $msgtext, $msgattach);
										
										## check sending
										if($mailthemessage == 200) {
											
											$LOG = logging('OKAY: Die E-Mail an '.$rcv['mail'].' wurde erfolgreich versendet.');
											
										} else {
											
											$LOG = logging('ERROR: Beim Senden der E-Mail an '.$rcv['mail'].' ist ein Fehler aufgetreten ('.$mailthemessage.')!');
											
										}
										
										## save receiver id to array for deletion
										$delarr .= "'".$rcv['id']."',";
										
									} else {
										
										$LOG = logging('ERROR: E-Mailadresse nicht gültig: '.$rcv['mail']);
										
									}
									
								}
								
								## delete receiver which be used
								$delarr = substr($delarr, 0, strlen($delarr) - 1);
								$sqls = "DELETE FROM
											`".PFX."receiver`
										WHERE
											`id`
										IN
											(".$delarr.")
										";
								$send = mi($sqls);
								if($send[0] > 0) {
									
									$LOG = logging('INFO: Es wurden '.$send[0].' Empfänger/innen aus der Datenbank entfernt.');
									
								} else {
									
									$LOG = logging('ERROR: Beim Entfernen der Empfänger/innen aus der Datenbank ist ein Fehler aufgetreten!');
									
								}
								
							} else {
								
								## set mailing status to 0 (sending finished)
								$sqls = "UPDATE
											`".PFX."mailing`
										SET
											`status` = '0'
										WHERE
											`id` = '".mres($d['id'])."'
										";
										
								## send query
								$send = mi($sqls);
								
								## check result
								if($send[0] == 1) {
									
									$LOG = logging('OKAY: Serienbriefstatus ID '.$d['id'].' auf 0 gesetzt.');
									
								}
								
							}
							
							## update passages file
							$newpassage = $passages + 1;
							$opfile = fopen($pfile, "w+");
							fwrite($opfile, $newpassage);
							fclose($opfile);
							
							
						}
					
				} else {
					
					## set new stamp
					$otfile = fopen($tfile, "w+");
					fwrite($otfile, time());
					fclose($otfile);
					
					## reset passages
					$opfile = fopen($pfile, "w+");
					fwrite($opfile, 1);
					fclose($opfile);
					
				}
				
			}
			
		}
		
	}
?>