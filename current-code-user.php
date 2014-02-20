<?php
global $user;
	
		# init row variable
		$row = "";
		
		$id_speler = $user->uid;		
		$query = "select max(id) as id from saldo_updates";
		$rs = db_query($query);
		
		# iterate over resultset, will be only one as we call the "max()" function
		foreach ($rs as $rs_item) {
			$row = $rs_item;
		}

		$query = "select * from saldo_updates where id = '".$row->id."'";
		$rs = db_query($query);
		# iterate over saldo_updates item, will be only one as the updateid is a unique primary key
		foreach ($rs as $rs_item) {
			$row = $rs_item;
		}

		$update_id = $row->id;
?>

<h2>Financieel overzicht: update van <?php echo date("d-m-Y", $row->date); ?></h2>
<p>Opmerking: <?php echo $row->remarks; ?></p>

<?php
	$html = "<table class='gentleTable'>\n\t";
	$html.= "<tr>\n\t";
	$html.= "<th align='center'>Datum</th>\n\t";
	$html.= "<th align='right'>Cumul</th>\n\t";
	$html.= "<th align='right'>Bedrag</th>\n\t";
	$html.= "<th align='left'>Beschrijving</th>\n\t";
	$html.= "<th align='left'>Opmerking</th>\n\t";
	$html.= "</tr>\n\t";
	echo $html;

	$query = "select * from saldo_transactions where id_update = '$update_id' and id_speler = '$id_speler' order by datum, id";
	$rs = db_query($query);
	$cumul = 0;
	
	foreach ($rs as $row) {
		$html = "<tr>\n\t";
		$html.= "<td align=\"center\">".date("d-m-Y", $row->datum)."</td>\n\t";
		$html.= "<td align=\"right\">$cumul</td>\n\t";			
		$html.= "<td align=\"right\">$row->bedrag</td>\n\t";
		$html.= "<td align=\"left\">$row->beschrijving</td>\n\t";
		$html.= "<td align=\"left\">$row->opmerking</td>\n";
		$html.= "</tr>\n";
		echo $html;
		$cumul += $row->bedrag;
	}
	
	$html ="</table>\n\t<br /><br />";
	$html.="<strong>HUIDIG SALDO: $cumul</strong>";
	echo $html;
?>
</table>
<h2>Werking</h2>
<p>Gentle heeft zijn eigen bank, beheerd door Quinten Ouvry en Wouter Vandamme. Voor iedereen die lid is of een login heeft op deze website wordt een saldo bijgehouden. Dit saldo wordt minimaal 1 maal per maand geupdated.</p>
<p>Je kan zowel boven al onder nul gaan. Onder nul gaan doe je best niet te lang, want er gelden boetes en in het ergste geval mag je niet mee doen aan toernooien.</p>
<p>Overschrijven kan met de volgende gegevens:</p>
<p><strong>Rekeningnummer:</strong> 363-0070449-67<br/>
<strong>IBAN:</strong> BE93363007044967<br />
<strong>BIC:</strong> BBRUBEBB<br />
<strong>Bank:</strong> ING<br />
<strong>Adres:</strong> Kouter 173, 9000 Gent<br />
<strong>Eigenaar rekening:</strong> Gentle Frisbeeteam</p>