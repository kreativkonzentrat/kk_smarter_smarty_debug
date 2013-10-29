{literal}
<style type="text/css">
	.kk-row h4{
		display: block;
		padding: 5px 0;
	}
	.kk-row li{
		list-style: disc inside;
	}
	.kk-row i{
		font-weight: bold;
	}
</style>
{/literal}
<div class="kk-row">
	<h4>Allgemeine Hinweise</h4>
    <ul>
	    <li>Sie k&ouml;nnen den Debug-Output permanent verf&uuml;gbar machen oder per Option das Vorhandensein eines GET-Parameters voraussetzen.</li>
		<li>Falls Sie die Option <i>"Nur bei GET-Parameter aktivieren?"</i> auf "Ja" gestellt haben, wird der Debug-Output nur angezeigt, wenn in der URL der im Feld <i>"Name des GET-Parameters"</i> anzugebenen Parameter vorhanden ist. Ein Beispiel w&auml;re <i>http://example.com/mein-produkt?kk-debug</i></li>
	    <li>In dem Fall k&ouml;nnen Sie zus&auml;tzlich die Option <i>In Session speichern?</i> aktivieren, damit Sie den Parameter nur einmalig anh&auml;ngen m&uuml;ssen und anschlie&szlig;end &uuml;ber Ihre Session die Ausgabe aktiviert wird.</li>
	    <li>Optional k&ouml;nnen Sie auch PHP-Fehler in Smarter Smarty Debug anzeigen. Aktivieren Sie dazu einfach die entsprechende Option in den Plugin-Einstellungen.</li>
	    <li>Das Aktivieren der Option <i>Anzeige aktiver Hooks aktivieren?</i> gibt Ihnen Auskunft &uuml;ber aktive Hooks von installierten Plugins und deren Reihenfolge.</li>
	    <p>Klicken auf einen Eintrag markiert automatisch den Pfad ganz rechts. Sie brauchen nur noch STRG+C zu dr&uuml;cken, um den Pfad der Variablen zu kopieren und k&ouml;nnen ihn z.B. direkt in Ihr Template einf&uuml;gen</p>
    </ul>
    <h4>Anzeigen des Debug-Outputs</h4>
    <ul>
        <li>Dr&uuml;cken Sie STRG+Enter um den Debug-Output anzuzeigen</li>
	    <li>Erneutes Dr&uuml;cken dieser Kombination fokussiert das Suchfeld</li>
        <li>Escape schlie&szlig;t die Ausgabe</li>
	    <li>Alternativ k&ouml;nnen Sie in den Plugin-Optionen einen Textlink zum Anzeigen/Ausblenden des Debuggers aktivieren. <br />Dies ist z.B. n&uuml;tzlich beim Debuggen auf mobilen Ger&auml;ten, die keine Keyboard-Shortcuts unterst&uuml;tzen.</li>
    </ul>
	<h4>Suchoptionen</h4>
	<ul>
		<li>Verwenden Sie <i>$</i> um nach Variablennamen zu durchsuchen. Sie k&ouml;nnen dabei auch nur Wortteile verwenden, beispielsweise <i>$einstel box</i> w&uuml;rde auch den Knoten <i>BoxenEinstellungen.Boxen</i> finden.</li>
		<li>Um nach genau einem Begriff zu suchen, schlie&szlig;en Sie ihn in Anf&uuml;hrungszeichen ein. <i>"Einstellungen"</i> findet wirklich nur Knoten mit der Bezeichnung oder dem Wert "Einstellungen", nicht z.B. "Boxeneinstellungen".</li>
		<li>Nach Werten k&ouml;nnen Sie per <i>="meinWert"</i> suchen. <i>="Y"</i> gibt z.B. alle Knoten aus, die den Wert <i>Y</i> haben.</li>
		<li>Ohne die Anf&uuml;hrungszeichen werden auch Teilstrings gefunden. <i>=123</i> findet Knoten mit dem Wert 123, aber z.B. auch 12345.</li>
	</ul>
	<h4>Eigene Inhalte debuggen</h4>
	<ul>
		<li>Um eigene Variablen zum Debug-Output hinzuzuf&uuml;gen, k&ouml;nnen Sie die folgende Funktion nutzen: <i>$GLOBALS['dbg']->dump($myvar, 'myvar-name')</i>. <i>$myvar</i> entspricht der auszugebenen Variablen. Optional k&ouml;nnen Sie auch noch einen Namen angeben.</li>
		<li>Der Wert erscheint anschlie&szlig;end in einem eigenen Abschnitt der Debug-Ausgabe.</li>
	</ul>
</div>