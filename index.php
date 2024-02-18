<!DOCTYPE html>
<html lang="de">
  <head>
    <meta name="robots" content="noindex, nofollow"> 
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geheimnis</title>
    <link rel="stylesheet" href="assets/style.css" />
	<script src="assets/script.js"></script>
  </head>
  <body>
	<div class="container">
	<img src="assets/logo.png" class="logo"><br />
	<?php
	
	require_once("config.php");
	require_once("functions.php");
	
	// Stelle sicher, dass die SQLite-Datei vorhanden ist oder erstellen Sie sie
	$db = new PDO('sqlite:db/geheimnisse.sqlite3');
	$db->exec("CREATE TABLE IF NOT EXISTS geheimnisse (id INTEGER PRIMARY KEY, geheimnis TEXT, time DATETIME DEFAULT CURRENT_TIMESTAMP, mehrmals BOOLEAN DEFAULT FALSE)");
	
	$ngv = $vm = false;

	// Cleanup
	$offset = date('Y-m-d H:i:s', strtotime('-'.$s.' days'));
	
	$clean = $db->prepare("DELETE FROM geheimnisse WHERE time < :offset");
	$clean->bindValue(':offset', $offset, PDO::PARAM_STR);
	$clean->execute();
	
	if($clean->rowCount() > 0)
		echo '<div class="minimal cleanup">' . $clean->rowCount() . " Datensätze wurden bereinigt.</div>";

	// Überprüfen, ob das Formular gesendet wurde
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['geheimnis'])) {
		
		// Setze die Datenbankrechte korrekt
		chmod("db/geheimnisse.sqlite3", 0600);
		
		// Prüfe Länge der Zeichenfolge
		if(strlen($_POST['geheimnis']) > $max) 
			die('Ihre geheime Zeichenkette ist zu lang.');
		
		$geheimnis = htmlspecialchars($_POST['geheimnis']); 	// Konvertiert spezielle Zeichen
		$key = bin2hex(random_bytes(16)); 						// Generiere Schlüssel
		$ver = verschluessele($geheimnis, $key);				// Verschlüssele Zeichenkette
		
		$mehr = (isset($_POST['mehrmals']) ? true : false);
		
		// Generiere eine zufällige einzigartige ID
		do {
			$id = generateRandomId();
		} while (!isIdUnique($db, $id));
		
		$stmt = $db->prepare("INSERT INTO geheimnisse (id, geheimnis, mehrmals) VALUES (:id, :geheimnis, :mehrmals)");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->bindValue(':geheimnis', $ver, PDO::PARAM_STR);
		$stmt->bindValue(':mehrmals', $mehr, PDO::PARAM_BOOL);

		$stmt->execute();
		
		// Generierung der URL
		echo '<h4>URL zum Geheimnis:</h4><input type="text" value="'.$baseurl.'?id='.$id.'&key='.$key.'" id="secret" readonly> <button onclick="kopiereLink()" id="copy">Kopieren</button><div class="minimal">Versenden Sie diese URL statt des Geheimnisses an Ihren Empfänger.</div>';
		$ngv = true;
	}
	else
		// Überprüfen, ob eine Anfrage zur Entschlüsselung gemacht wurde
		if (isset($_GET['id']) && isset($_GET['key'])) {
			$id = $_GET['id'];
			$key = $_GET['key'];
			
			// Prüfe Schlüssel
			if(strlen($key) != 32) 
				die('Die URL scheint unvollständig zu sein.'); 
			
			// Datenbank-Zugriff
			$stmt = $db->prepare("SELECT * FROM geheimnisse WHERE id = :id");
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$ergebnis = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$datetime = new DateTime($ergebnis["time"]);
			$datetime->modify('+'.$s.' days');
			
			if($ergebnis === false) 
				die('Geheimnis nicht gefunden oder bereits gelöscht.');
				
			// Prüfe Vorschau-Modus
			if($vorschaumodus === true && isset($_GET['vorschau']) && $_GET['vorschau'] == $vorschaupass) {
				$vm = true;
				$_GET["view"] = "yes";
				$ngv = true;
			}		
					
			// Anzeigen einer Warnungsseite
			if (!isset($_GET['view'])) {
				echo '<h4>Achtung:</h4> Das Geheimnis wird ';
				echo ($ergebnis["mehrmals"] == 0 ? 'nach dieser Ansicht' : 'am ' . $datetime->format('d.m.Y H:i') . ' Uhr');
				echo ' gelöscht.<br /><a href="?'.$_SERVER['QUERY_STRING'].'&view=yes" style="margin-top:25px;" class="button green">Geheimnis anzeigen</a>';
			}
			else
				// Holen und Löschen des Geheimnisses, wenn bestätigt
				if (isset($_GET['view']) && $_GET['view'] == 'yes') {
					
					// Entschlüsseln des Geheimnisses
					$geheimnis = htmlspecialchars_decode(entschluessele($ergebnis['geheimnis'], $key));
					if(empty($geheimnis)) 
						die('Entschlüsselung fehlgeschlagen. Schlüssel nicht korrekt.');					
					
					echo '<h4>Geheimnis:</h4><div class="flex"><textarea id="secret" readonly rows="'.ceil(strlen($geheimnis)/50).'" >'.$geheimnis.'</textarea> ';
					echo (!$vm ? '<button onclick="kopiereLink()" id="copy">Kopieren</button>' : null);
					echo '</div><div class="minimal">';
						
					// Löschen des Geheimnisses aus der Datenbank
					if (!$vm && (!$ergebnis['mehrmals'] || isset($_GET["delete"]))) {
						$loeschStmt = $db->prepare("DELETE FROM geheimnisse WHERE id = :id");
						$loeschStmt->bindValue(':id', $id, PDO::PARAM_INT);
						$loeschStmt->execute();
						echo 'Das Geheimnis wurde vom Server gelöscht.<br />Bitte verwahren Sie das Geheimnis sicher. Der Link hat keine Gültigkeit mehr.';
					} 
					else {
						// Hinweis zum Löschzeitpunkt und Löschmöglichkeit
						echo ($vm ? 'VORSCHAU | ' : null);
						echo 'Die Daten stehen bis '. $datetime->format('d.m.Y H:i') .' zum Abruf zur Verfügung.';
						echo (!$vm ? ' <a href="?'.$_SERVER['QUERY_STRING'].'&delete=yes" >Löschen</a>' : null);
					}
					
					echo '</div>';
					
				}
		}
	
	if((isset($_GET[$protectstring]) || $protect === false) && !isset($_POST["geheimnis"]) && !isset($_GET["id"])) {
	?>
	<form method="post">
		<h4>Geheimnis:</h4><input type="text" name="geheimnis" size="50" maxlength="<?php echo $max; ?>" autocomplete="off"><br /><div class="minimal">Erstellt einen Link zum Versand des Geheimnisses / Einmalige Ansicht oder: <input type="checkbox" name="mehrmals"> mehrmalige Ansicht / Speicherdauer: max. <?php echo $s; ?> Tage / max. <?php echo $max; ?> Zeichen</div>
		<input type="submit" value="Verschlüsseln">
	</form>
	<?php
	}
	echo '</div>';
	echo ($ngv ? '<a href="?neu" class="button green neu">Neues Geheimnis verschlüsseln</a>' : null);
	?>
  </body>
</html>
