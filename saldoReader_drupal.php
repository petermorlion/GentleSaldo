<?php
include("dbconnectie_drupal.inc");
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

$id_speler = "";
$naam_speler = "";
echo "<table align=\"left\" width=\"450\" class=\"tableContent\">";
$speler_processed = true;


while(($line = fgets($fp_saldo, $buffer)) ){  //&& $empty_line_counter < 10
	$total_line_counter++;
	$line_stripped = preg_replace("/\t/", "", $line); //alle tabs uit lijn weghalen (voor check op lege lijnen)
	if ($line_stripped != "\n" && $line_stripped != "\r\n"){ // als lijn niet leeg is, lijn processen
		//echo "\n<tr>";
		$tokens = explode("\t", $line);
		if($header_processed == false){ // header processen en opslaan in db
			if (preg_match("/\tlaatste update:\t/", $line)){
				echo "\n<tr>\n\t<td class=\"tableHeader\">\n\t\tdate of update: ";
				$date_update = datestring_to_timestamp($tokens[2]);
				echo date("d-m-Y",$date_update);
				echo "\n\t</td>\n</tr>";				
			}
			elseif (preg_match("/.*naam\tdatum\tcumulatief\tbedrag\tthema\tverwijzing\/opmerking.*/", $line)){
				$header_processed = true; //header is volledig ingelezen, header schrijven naar db
				echo "\n<tr>\n\t<td class=\"tableHeader\">\n\t\tHeader:\n\t</td>\n</tr>\n<tr>\n\t<td>\n\t\t&nbsp;\n\t</td>\n</tr>\n<tr>\n\t<td class=\"tableHeader\">\n\t\t".$header."\n\t</td>\n</tr>\n<tr>\n\t<td>\n\t\t&nbsp;\n\t</td>\n</tr>\n<tr>\n\t<td>\n\t\t&nbsp;\n\t</td>\n</tr>";
				$query = "insert into saldo_updates (date, remarks) values ('$date_update', '$header')";
				$rs = mysql_query($query) or die("MYSQL_Error: Het creëren van de nieuwe saldo update is niet gelukt...");
				$query = "select LAST_INSERT_ID() as last_id";
				$rs = mysql_query($query);
				$row = mysql_fetch_object($rs);
				$id_update = $row->last_id;
			}
			else {
				if (!preg_match("/^spelers saldos$/", trim($line_stripped))){
					$header.= htmlspecialchars(trim($line, " \t"),ENT_QUOTES, "cp1252");
				}
			}
		}
		else { //header is geprocessed, nu transacties inlezen en schrijven naar db


			if (trim($tokens[2]) == "totaal"){
				$speler_processed = true;
			}
			else {
				if ($speler_processed == true){
					$id_speler = htmlspecialchars(trim($tokens[0]), ENT_QUOTES, "cp1252");
					$naam_speler = htmlspecialchars(trim($tokens[1]), ENT_QUOTES, "cp1252");
					$speler_processed = false;
				}
				//echo "\n\t<td>\n\t\t";
				$datum = datestring_to_timestamp($tokens[2]);
				//echo "\n\t</td>";				
				$bedrag = htmlspecialchars(trim(str_replace(",",".",$tokens[4])), ENT_QUOTES, "cp1252");
				//echo "\n\t<td>\n\t\t".$naam_speler."\n\t</td>\n\t<td>\n\t\t".$bedrag."\n\t<td>";
				$beschrijving = htmlspecialchars(trim($tokens[5]), ENT_QUOTES, "cp1252");
				$opmerking = htmlspecialchars(trim($tokens[6]), ENT_QUOTES, "cp1252");
				$query = "insert into saldo_transactions (bedrag, beschrijving, datum, id_speler, id_update, naam, opmerking) 
						  values('$bedrag', '$beschrijving', '$datum', '$id_speler', '$id_update', '$naam_speler', '$opmerking')";
				//echo $query."<br>";
				$rs = mysql_query($query) or die("MYSQL_Error: Fout tijdens het toevoegen van lijn:<br>$line");	
				$cntr++;			
			}
		}
		//echo "\n</tr>";
	}
	else $empty_line_counter++;
}
fclose($fp_saldo);
echo "\n</table>\n";
?>
&nbsp;
<table class="tableHeader">
<tr><td>&raquo;Statistics:</td></tr>
<tr>
<td>
	<table class="tableContent">
	<tr>
		<td>Totaal aantal lijnen:</td>
		<td><?php echo $total_line_counter; ?></td>
	</tr>
	<tr>
		<td>Totaal aantal lege lijnen:</td>
		<td><?php echo $empty_line_counter; ?></td>
	</tr>
	<tr>
		<td>Aantal toegevoegde records:</td>
		<td><?php echo $cntr; ?></td>
	</tr>
	</table>
</td>
</tr>
</table>
</body>
</html>