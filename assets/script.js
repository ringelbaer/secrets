function kopiereLink() {
	// Holen des input-Feldes
	var linkFeld = document.getElementById('secret');
	var copyA = document.getElementById('copy');
	// Ausw�hlen des Textes im input-Feld
	linkFeld.select();
	linkFeld.setSelectionRange(0, 99999); // F�r mobile Ger�te
	copyA.style.backgroundColor = '#7FAAD9';
	copyA.innerText = "Kopiert!";

	// Kopieren des Textes in die Zwischenablage
	document.execCommand('copy');
}