# Archivierung
Das Plugin erlaubt die leichtere Archivierung von Themen, indem es einen Button hinter den Thementitel anzeigt. Mit einem Klick auf diesen kann man das Thema - ohne die Nutzung des MyBB eigenen Verschiebungstool - in das Standardarchiv verschieben. Das Standardarchiv kann für jede Kategorie einzeln im AdimCP bestimmt werden. Falls man einen Inplaybereich hat, ist es möglich Themen sofort richtig einsortieren zu lassen. 

## Funktionen
__allgemeine Funktionen__
* Verschiebung von Themen in den vorbestimmten Archivbereich (User können nur eigene verschieben)
* Bestätigungsseite fragt nach, ob dieses Archiv das richtige ist und verschiebt erst nach Zustimmung

falls Inplaybereich:
* jeder Szenenteilnehmer kann die Inplayszene archivieren

__Funktionen für Admins__
* Festlegung der Archivbereiches über das AdminCP

## Voraussetzungen
* FontAwesome muss eingebunden sein, andernfalls muss man die Icons in dem Template _archivingButton_ ersetzen
* [Enhanced Account Switcher](http://doylecc.altervista.org/bb/downloads.php?dlid=26&cat=2) muss installiert sein 
* [Inplaytracker 2.0](https://github.com/its-sparks-fly/Inplaytracker-2.0) muss installiert sein

## Template-Änderungen
__Neue globale Templates:__
* archivingButton
* archivingSubmitSite

__Veränderte Templates:__
* forumdisplay_thread (wird um die Variable $archivingButton erweitert)

## Anmerkung Inplayarchiv
Ein Inplayarchiv ist in diesem Fall wie folgt definiert: Eine Forum enthält für jeden Monat ein eigenes Unterforum, welches nach dem Schema "Monatsname YYYY" definiert ist. Nur wenn diese Schreibweise eingehalten wurde, kann das Plugin automatisch zuweisen. Wenn eine alternative Schreibweise bevorzugt wird, müssen Änderungen in der PHP-Datei vorgenommen werden.

![Archivierung Beispiel](https://beforestorm.de/imageUpload/plugins/archiving_example.png)

## Vorschaubilder
__Einstellungen in der Foren-Editieren-Seite__
![Archivierung Einstellungen](https://beforestorm.de/imageUpload/plugins/archiving_settings.png)

__Ansicht im Forum__
![Archivierung Forum](https://beforestorm.de/imageUpload/plugins/archiving_forum.png)
