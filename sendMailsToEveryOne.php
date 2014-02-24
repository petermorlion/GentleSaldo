<?php
$saldoFile = "./ledensaldos_drupal.txt";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Load saldo's into DB</title>
<link href="../gentle_style.css" rel="stylesheet" type="text/css" />
</head>

<body class="body">
<?php
function datestring_to_timestamp($datestring){
	$tokens = explode(delimiter_lookup($datestring), $datestring);
	$timestamp = mktime(0,0,0,$tokens[1],$tokens[0],$tokens[2]);
	return $timestamp;
}
function delimiter_lookup($datestring){
	return ((count(explode("-", $datestring)) > 1) ? '-' : '/');
}
$fp_saldo = fopen($saldoFile, 'r');
$buffer = 4096;
$total_line_counter = 0;
$empty_line_counter = 0;
$cntr = 0;
$header_processed = false;
$header = "";
$date_update = NULL;
$id_update = "";
$message_text = "";
$message_html_intro = "";
$message_html_table = "";
$message_temp = "";


$id_speler = "";
$naam_speler = "";
echo "<table align=\"left\" width=\"450\" class=\"tableContent\">";
$speler_processed = true;

//create a boundary string. It must be unique 
//so we use the MD5 algorithm to generate a random hash
$random_hash = md5(date('r', time())); 
//define the headers we want passed. Note that they are separated with \r\n
$headers = "From: info@gentlesite.be\r\nReply-To: info@gentlesite.be";
//add boundary string and mime type specification
$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\""; 
$subject = 'Overzicht saldo Gentle'; 

while(($line = fgets($fp_saldo, $buffer)) ){  //&& $empty_line_counter < 10
	$total_line_counter++;
	$line_stripped = preg_replace("/\t/", "", $line); //alle tabs uit lijn weghalen (voor check op lege lijnen)
	if ($line_stripped != "\n" && $line_stripped != "\r\n"){ // als lijn niet leeg is, lijn processen
		//echo "\n<tr>";
		$tokens = explode("\t", $line);
		if($header_processed == false){ // niets doen
			if (preg_match("/.*naam\tdatum\tcumulatief\tbedrag\tthema\tverwijzing\/opmerking.*/", $line)){
				$header_processed = true; //header is volledig ingelezen, header schrijven naar db
			}
			else {
			}
		}
		else { //header is geprocessed, nu transacties inlezen en mailen
			if ($speler_processed == true){// je bent aan de eerste lijn van een speler, open de mail
				$id_speler = htmlspecialchars(trim($tokens[0]), ENT_QUOTES, "cp1252");
				$naam_speler = htmlspecialchars(trim($tokens[1]), ENT_QUOTES, "cp1252");
				$mailadres_speler = htmlspecialchars(trim($tokens[7]), ENT_QUOTES, "cp1252");
				$speler_processed = false;

				//define the receiver of the email
				$to = $mailadres_speler;
				//define the subject of the email
				$subject = 'Gentle Saldo Update'; 
				//create a boundary string. It must be unique 
				//so we use the MD5 algorithm to generate a random hash
				$random_hash = md5(date('r', time())); 
				//define the headers we want passed. Note that they are separated with \r\n
				$headers = "From: info@gentlesite.be\r\nReply-To: info@gentlesite.be";
				//add boundary string and mime type specification
				$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\""; 
				//define the body of the message.
				//ob_start(); //Turn on output buffering
				
			}
			if (trim($tokens[2]) == "totaal"){
				$speler_processed = true;
				$totaalbedrag = htmlspecialchars(trim(str_replace(",",".",$tokens[3])), ENT_QUOTES, "cp1252");
							?>
<?
			if (!$to ==""){
			 $message_temp = <<<EOD

			  <tr>
				<td>totaal</td>
				<td><div align="center">$totaalbedrag</div></td>
				<td><div align="center">  </div></td>
				<td>  </td>
				<td>  </td>
			  </tr>	
EOD;
			  $message_html_table = $message_html_table.$message_temp; 
//copy current buffer contents into $message variable and delete current output buffer
//$message = ob_get_clean();
				$message_text = <<<EOD
				Beste (ex-)Gentlenaar,
				Beste $naam_speler,
				In deze mail zie je een overzicht van de activiteiten op je saldo (jouw saldo staat op $totaalbedrag). Deze kan je ook steeds nakijken op www.gentlesite.be bij 'Mijn saldo'. Als je niet met de afrekening akkoord gaat is dit dus het moment om te reageren. (Opgelet: Bij een nieuw kalenderjaar wordt je saldo overgedragen en zal je de eerdere activiteiten niet meer kunnen raadplegen)

				Als je negatief staat, gelieve meteen uw saldo terug op te krikken door geld over te schrijven op rekeningnummer 363-0070449-67, met vermelding van je naam. Indien je dit nog niet gedaan hebt, betaal meteen je nieuwe inschrijvingsgeld voor dit jaar. 

				Als je niet akkoord gaat met deze verrekening, gelieve te reageren bij penningmeester Quinten Ouvry via Quinten_Ouvry@hotmail.com
EOD;
				
				$message_html_intro = <<<EOD
				<p>Beste (ex-)Gentlenaar,<br/>
				Beste $naam_speler,</p>
				<p>In deze mail zie je een overzicht van de activiteiten op je saldo (jouw saldo staat op $totaalbedrag). Deze kan je ook steeds nakijken op <a href="http://www.gentlesite.be">www.gentlesite.be</a> bij '<a href="http://www.gentlesite.be/drupal/content/saldo">Mijn saldo</a>'. Als je niet met de afrekening akkoord gaat is dit dus het moment om te reageren. (Opgelet: Bij een nieuw kalenderjaar wordt je saldo overgedragen en zal je de eerdere activiteiten niet meer kunnen raadplegen)</p>
				<p>Als je negatief staat, gelieve meteen uw saldo terug op te krikken door geld over te schrijven op rekeningnummer 363-0070449-67, met vermelding van je naam. Indien je dit nog niet gedaan hebt, betaal meteen je nieuwe inschrijvingsgeld voor dit jaar.</p>
				<p>Als je niet akkoord gaat met deze verrekening, gelieve te reageren bij penningmeester Quinten Ouvry via <a href="mailtoQuinten_Ouvry@hotmail.com">Quinten_Ouvry@hotmail.com</a>.</p>
				<p></p>
				<table width="690" border="1">
				  <tr>
					<th width="76" scope="col">date</th>
					<th width="85" scope="col">cumul</th>
					<th width="81" scope="col">amount</th>
					<th width="94" scope="col">action</th>
					<th width="330" scope="col">comment</th>
				  </tr>
EOD;
$message =  <<<EOD
--PHP-alt-"$random_hash"  
Content-Type: text/plain; charset="iso-8859-1" 
Content-Transfer-Encoding: 7bit

$message_text

--PHP-alt-$random_hash  
Content-Type: text/html; charset="iso-8859-1" 
Content-Transfer-Encoding: 7bit

$message_html_intro
$message_html_table


EOD;

//send the email
$mail_sent = @mail( $to, $subject, $message, $headers );
//if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
echo $mail_sent ? "Mail sent" : "Mail failed";
					echo " to ";
					echo $naam_speler;
					echo " (";
					echo $to;
					echo ") with saldo ";
					echo $totaalbedrag;
					echo "</p>";
					}
$message_text = "";
$message_html_intro = "";
$message_html_table = "";
$message_temp = "";
   			}
			else {
				$datum =($tokens[2]);	
				$cumul = htmlspecialchars(trim(str_replace(",",".",$tokens[3])), ENT_QUOTES, "cp1252");		
				$bedrag = htmlspecialchars(trim(str_replace(",",".",$tokens[4])), ENT_QUOTES, "cp1252");
				$beschrijving = htmlspecialchars(trim($tokens[5]), ENT_QUOTES, "cp1252");
				$opmerking = htmlspecialchars(trim($tokens[6]), ENT_QUOTES, "cp1252");
			 $message_temp = <<<EOD

			  <tr>
				<td>$datum</td>
				<td><div align="center">$cumul</div></td>
				<td><div align="center">$bedrag</div></td>
				<td>$beschrijving</td>
				<td>$opmerking</td>
			  </tr>	
EOD;
			  $message_html_table = $message_html_table.$message_temp; 
			}
		}
	
	}
}
?>
</body>