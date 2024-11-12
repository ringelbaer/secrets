<?php
session_start();

// Debugging aktivieren (nur für Entwicklungszwecke, nicht in der Produktion)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Einbindung der notwendigen Dateien
require_once("config.php");
require_once("functions.php");

// Aktivieren des Exception-Modus für PDO
try {
    $db = new PDO('sqlite:db/geheimnisse.sqlite3');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Tabelle erstellen, falls nicht vorhanden
    $db->exec("CREATE TABLE IF NOT EXISTS geheimnisse (
        id TEXT PRIMARY KEY,
        geheimnis TEXT,
        time DATETIME DEFAULT CURRENT_TIMESTAMP,
        mehrmals INTEGER DEFAULT 0
    )");
} catch (PDOException $e) {
    die('Datenbankfehler: ' . htmlspecialchars($e->getMessage()));
}

// Initialisierung von Variablen
$ngv = $vm = false;
$errorMessage = '';

// Bereinigung von alten Einträgen (kann in einen Cron-Job ausgelagert werden)
$offset = date('Y-m-d H:i:s', strtotime('-' . $s . ' days'));
$clean = $db->prepare("DELETE FROM geheimnisse WHERE time < :offset");
$clean->bindValue(':offset', $offset, PDO::PARAM_STR);
$clean->execute();

$bereinigt = $clean->rowCount();

?>
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

        <?php if ($bereinigt > 0): ?>
            <div class="minimal cleanup"><?= $bereinigt ?> Datensätze wurden bereinigt.</div>
        <?php endif; ?>

        <?php
        // Verarbeitung von Fehlermeldungen
        if (!empty($errorMessage)) {
            echo '<div class="error">' . htmlspecialchars($errorMessage) . '</div>';
        }

        // Verarbeitung von Formularabsendungen
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['geheimnis'])) {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!verifyCsrfToken($csrf_token)) {
                $errorMessage = 'Ungültiges CSRF Token.';
            } else {
                // Validierung der Eingabe
                $geheimnis = trim($_POST['geheimnis']);
                if (strlen($geheimnis) > $max) {
                    $errorMessage = 'Ihre geheime Zeichenkette ist zu lang.';
                } else {
                    // Generierung von Schlüssel und ID
                    $key = bin2hex(random_bytes(32)); // 64 Zeichen
                    $binaryKey = hex2bin($key);
                    if ($binaryKey === false) {
                        $errorMessage = 'Fehler bei der Schlüsselgenerierung.';
                    } else {
                        $id = generateRandomId();

                        // Verschlüsselung des Geheimnisses
                        $verschluesselt = verschluessele($geheimnis, $binaryKey);

                        // Verarbeitung des Mehrfachabrufs
                        $mehr = isset($_POST['mehrmals']) ? 1 : 0;

                        // Speicherung in der Datenbank
                        try {
                            $stmt = $db->prepare("INSERT INTO geheimnisse (id, geheimnis, mehrmals) VALUES (:id, :geheimnis, :mehrmals)");
                            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
                            $stmt->bindValue(':geheimnis', $verschluesselt, PDO::PARAM_STR);
                            $stmt->bindValue(':mehrmals', $mehr, PDO::PARAM_INT);
                            $stmt->execute();

                            // Ausgabe der URL zum Geheimnis
                            $url = sprintf('%s?id=%s&key=%s', $baseurl, urlencode($id), urlencode($key));
                            echo '<h4>URL zum Geheimnis:</h4>';
                            echo '<input type="text" value="' . htmlspecialchars($url) . '" id="secret" readonly> ';
                            echo '<button onclick="kopiereLink()" id="copy">Kopieren</button>';
                            echo '<div class="minimal">Versenden Sie diese URL statt des Geheimnisses an Ihren Empfänger.</div>';
                            $ngv = true;
                        } catch (PDOException $e) {
                            $errorMessage = 'Fehler beim Speichern des Geheimnisses.';
                        }
                    }
                }
            }
        }
        // Verarbeitung von Geheimnis-Abrufen
        elseif (isset($_GET['id']) && isset($_GET['key'])) {
            $id = $_GET['id'] ?? '';
            $key = $_GET['key'] ?? '';

            // Validierung der ID und des Schlüssels
            if (!ctype_xdigit($id) || strlen($id) !== 32) {
                $errorMessage = 'Ungültige ID.';
            } elseif (!ctype_xdigit($key) || strlen($key) !== 64) {
                $errorMessage = 'Ungültiger Schlüssel.';
            } else {
                $binaryKey = hex2bin($key);
                if ($binaryKey === false) {
                    $errorMessage = 'Ungültiger Schlüssel.';
                } else {
                    try {
                        $stmt = $db->prepare("SELECT * FROM geheimnisse WHERE id = :id");
                        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
                        $stmt->execute();
                        $ergebnis = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (!$ergebnis) {
                            $errorMessage = 'Geheimnis nicht gefunden oder bereits gelöscht.';
                        } else {
                            $datetime = new DateTime($ergebnis["time"]);
                            $datetime->modify('+' . $s . ' days');

                            // Vorschau-Modus
                            if ($vorschaumodus === true && isset($_GET['vorschau']) && $_GET['vorschau'] == $vorschaupass) {
                                $vm = true;
                                $_GET["view"] = "yes";
                                $ngv = true;
                            }

                            // Anzeige der Warnungsseite
                            if (!isset($_GET['view'])) {
                                echo '<h4>Achtung:</h4> Das Geheimnis wird ';
                                echo ($ergebnis["mehrmals"] == 0 ? 'nach dieser Ansicht' : 'am ' . $datetime->format('d.m.Y H:i') . ' Uhr');
                                echo ' gelöscht.<br /><a href="?' . htmlspecialchars($_SERVER['QUERY_STRING']) . '&view=yes" style="margin-top:25px;" class="button green">Geheimnis anzeigen</a>';
                            }
                            // Anzeige des Geheimnisses
                            elseif (isset($_GET['view']) && $_GET['view'] === 'yes') {
                                // Entschlüsselung des Geheimnisses
                                $geheimnis = entschluessele($ergebnis['geheimnis'], $binaryKey);
                                if ($geheimnis === false) {
                                    $errorMessage = 'Entschlüsselung fehlgeschlagen. Schlüssel nicht korrekt.';
                                } else {
                                    echo '<h4>Geheimnis:</h4>';
                                    echo '<div class="flex">';
                                    echo '<textarea id="secret" readonly>' . htmlspecialchars($geheimnis) . '</textarea>';
                                    if (!$vm) {
                                        echo '<button onclick="kopiereLink()" id="copy">Kopieren</button>';
                                    }
                                    echo '</div><div class="minimal">';

                                    // Löschen des Geheimnisses
                                    if (!$vm && (!$ergebnis['mehrmals'] || isset($_GET["delete"]))) {
                                        $loeschStmt = $db->prepare("DELETE FROM geheimnisse WHERE id = :id");
                                        $loeschStmt->bindValue(':id', $id, PDO::PARAM_STR);
                                        $loeschStmt->execute();
                                        echo 'Das Geheimnis wurde vom Server gelöscht.<br />Bitte verwahren Sie das Geheimnis sicher. Der Link hat keine Gültigkeit mehr.';
                                    } else {
                                        echo ($vm ? 'VORSCHAU | ' : '');
                                        echo 'Die Daten stehen bis ' . $datetime->format('d.m.Y H:i') . ' zum Abruf zur Verfügung.';
                                        if (!$vm) {
                                            echo ' <a href="?' . htmlspecialchars($_SERVER['QUERY_STRING']) . '&delete=yes">Löschen</a>';
                                        }
                                    }
                                    echo '</div>';
                                }
                            }
                        }
                    } catch (PDOException $e) {
                        $errorMessage = 'Fehler beim Abrufen des Geheimnisses.';
                    }
                }
            }
        }

        // Anzeige von Fehlermeldungen
        if (!empty($errorMessage)) {
            echo '<div class="error">' . htmlspecialchars($errorMessage) . '</div>';
        }

        // Anzeige des Formulars zur Eingabe eines neuen Geheimnisses
        if ((isset($_GET[$protectstring]) || $protect === false) && !isset($_POST["geheimnis"]) && !isset($_GET["id"]) && !$ngv) {
            $csrf_token = generateCsrfToken();
            ?>
            <form method="post">
                <h4>Geheimnis:</h4>
                <input type="text" name="geheimnis" size="50" maxlength="<?= $max; ?>" autocomplete="off"><br />
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
                <div class="minimal">Erstellt einen Link zum Versand des Geheimnisses
                <?= ($multiple ? '/ Einmalige Ansicht oder: <input type="checkbox" name="mehrmals"> mehrmalige Ansicht' : '') ?>
                / Speicherdauer: max. <?= $s; ?> Tage 
                / max. <?= $max; ?> Zeichen</div>
                <input type="submit" value="Verschlüsseln">
            </form>
            <?php
        }

        if ($ngv) {
            echo '<a href="?' . ($protect ? $protectstring : '') . '" class="button green neu">Neues Geheimnis verschlüsseln</a>';
        }
        ?>
    </div> <!-- Schließende div für container -->
</body>
</html>