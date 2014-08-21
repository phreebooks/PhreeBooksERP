<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/phreedom/language/en_us/admin.php
//

// Überschriften
define ('TEXT_MY_COMPANY', 'My Company');
define ('TEXT_CONFIGURATION', 'Einstellungen');
define ('TEXT_DEFAULT_GL_ACCOUNTS', 'Default GL-Konten');
define ('TEXT_EMAIL_PREFERENCES', 'E-Mail-Einstellungen');
define ('TEXT_LOCAL', 'Lokale');
define ('TEXT_DEBUG_AND_TROUBLESHOOTING', 'Debug und Fehlerbehebung');
define ('TEXT_DOWNLOAD', 'Download');
define ('TEXT_LEGEND', 'Legend');
define ('TEXT_REQUIRED', 'required');
define ('TEXT_MODULE_DATA_IMPORT_OR_EXPORT', 'Modul Data Import oder Export');
define ('TEXT_IMPORT_OR_EXPORT_AND_BEGINNING_BALANCES', 'Import / Export und Anfang Balances');
// Definiert für Login-Bildschirm
define ('TEXT_PHREEBOOKS_LOGIN', 'PhreeBooks Login');
define ('TEXT_LOGIN_NAME', 'Benutzername:');
define ('TEXT_LOGIN_PASS', 'Passwort');
define ('TEXT_LOGIN_COMPANY', 'Select Company:');
define ('TEXT_SELECT_LANGUAGE', 'Select Language');
define ('TEXT_SELECT_THEME', 'Wählen Thema:');
define ('TEXT_RESEND_PASSWORD', 'Passwort zusenden');
define ('TEXT_LOGIN', 'Login');
define ('TEXT_FORM_PLEASE_WAIT', 'Bitte warten ... Wenn Sie ein Upgrade, das kann eine Weile dauern.');
define ('TEXT_COPYRIGHT_NOTICE', 'Dieses Programm ist freie Software: Sie können es weiterverbreiten und / oder
modifizieren unter den Bedingungen der GNU General Public License
veröffentlicht von der Free Software Foundation, entweder gemäß Version 3
der Lizenz oder jeder späteren Version. Dieses Programm wird verteilt
in der Hoffnung, dass es nützlich ist, aber OHNE JEDE GARANTIE;
sogar ohne die implizite Garantie der MARKTREIFE oder
FÜR EINEN BESTIMMTEN ZWECK. Lesen Sie die GNU General Public License
weitere Details. Die Lizenz, die mit diesem Paket gebündelt ist
gelegen %s . ');

// Allgemeine
define ('TEXT_DEFAULT_STORE', 'Default Store');
define ('TEXT_DEFAULT_CASH_ACCOUNT', 'Default Cash Konto ');
define ('TEXT_RESTRICT_STORE', 'Filterung der Einträge nach diesem Shop?');
define ('TEXT_DEFAULT_AR_ACCOUNT', 'Default Forderungskonto');
define ('TEXT_DEFAULT_PAYABLES_ACCOUNT', 'Default Verbindlichkeiten Konto');
define ('TEXT_RESTRICT_PERIOD', 'einschränken Beiträge des aktuellen Berichtszeitraums?');
define ('TEXT_AUDIT_DB_DATA_BACKUP', 'Audit-Log-Datenbank Tabelle Backed Up');
define ('TEXT_AUDIT_DB_DATA_CLEAN', 'Audit-Log-Datenbank Tabelle Gereinigt');
define ('TEXT_PHREEBOOKS_SQL_ERROR_TRACE_INFORMATION', 'PhreeBooks SQL-Fehler Trace Information');
define ('TEXT_CRASH_INFORMATION', 'PhreeBooks hat ein unerwarteter Fehler aufgetreten Klicken Sie auf den untenstehenden Button zum Download der Debug-Trace-Datei Informationen an die PhreeBooks Development Team für Unterstützung bei der Fehlerbehebung zu senden..');
define ('TEXT_DOWNLOAD_DEBUG_INFORMATION', 'Download Debug-Informationen');
define ('TEXT_CONFIGURATION_VALUES_HAVE_BEEN_SAVED', 'haben Configuration Werte gespeichert.');

define ('ERROR_CANNOT_CREATE_MODULE_DIR', 'Fehler beim Erstellen des Verzeichnis: %s  Überprüfen Sie Ihre Berechtigungen');
define ('ERROR_CANNOT_REMOVE_MODULE_DIR', 'Fehler beim Entfernen Verzeichnis: %s . Das Verzeichnis ist nicht vorhanden oder kann nicht leer sein Es muss von Hand entfernt werden!.');
define ('TEXT_BACKUP_OR_CLEAN_AUDIT_LOGS', 'Backup / Clean Audit Logs');
define ('GEN_ADM_TOOLS_CLEAN_LOG_DESC', 'Dieser Vorgang erstellt eine Sicherungskopie Ihrer heruntergeladen Audit-Log-Datenbank-Datei. Dies wird dazu beitragen, dass die Größe der Datenbank ab und reduzieren Unternehmen Backup-Dateien. Sichern Sie dieses Protokoll ist vor der Reinigung aus, um Geschichte zu bewahren PhreeBooks Transaktion empfohlen. <br /> INFORMATIONEN: Reinigen Sie die Audit-Log werden die aktuellen Zeiten Daten in der Datenbank Tisch verlassen und entfernen Sie alle anderen Aufzeichnungen ');
define ('TEXT_BACKUP_AUDIT_LOG', 'Backup-Audit-Log');
define ('TEXT_CLEAN_OUT_AUDIT_LOG', 'Clean Out-Audit-Log');
define ('GEN_ADM_TOOLS_BTN_CLEAN_CONFIRM', 'Sind Sie sicher, dass Sie diese Log-Einträge löschen?');
define ('TEXT_BACKUP_NOW', 'Jetzt sichern');
define ('TEXT_CLEAN_NOW', 'Clean Now');
define ('GL_HEADING_BEGINNING_BALANCES', 'Kontenplan - Beginn Balances');
define ('TEXT_IMPORT_BEGINNING_BALANCES', 'Import Anfang Balances');
define ('GL_BTN_IMP_BEG_BALANCES', 'Import Inventory, Kreditorenbuchhaltung, Debitorenbuchhaltung Anfang Balances');
define ('TEXT_GENERAL_JOURNAL_BEGINNING_BALANCES', 'General Journal Beginn Balances');
define ('GL_UTIL_BEG_BAL_TEXT', 'Für die erste Set-ups und Transfers aus einem anderen Abrechnungssystem.');
define ('TEXT_ENTER_BEGINNING_BALANCES', 'Geben Anfang Balances');
define ('TEXT_IMPORT_JOURNAL_ENTRIES', 'Import Journal Entries');
define ('TEXT_IMPORT_INVENTORY', 'Import Inventory');
define ('TEXT_IMPORT_ACCOUNTS_PAYABLE', 'Import Kreditorenbuchhaltung');
define ('TEXT_IMPORT_ACCOUNTS_RECEIVABLE', 'Import Debitoren');
define ('TEXT_IMPORT_SALES_ORDERS', 'Import Kundenauftrag');
define ('TEXT_IMPORT_PURCHASE_ORDERS', 'Import von Bestellungen');
define ('TEXT_REFER_TO_THE_HELP_FILE_FOR_FORMAT_REQUIREMENTS', 'finden Sie in der Hilfedatei für Format Anforderungen.');
define ('TEXT_IMPORT_OR_EXPORT_DATABASE_TABLES', 'Import / Export von Datenbanktabellen');
define ('TEXT_IMPORT_EXPORT_INFO', 'Information Table');
define ('GEN_IMPORT_EXPORT_MESSAGE', '. Importieren können über XML-oder CSV-Format werden auf die Probe Klicken Sie die Schaltfläche, um eine Probe-Datei für die Formatierung zu verwenden herunterladen.');
define ('TEXT_SAMPLE_XML', 'Beispiel für eine XML');
define ('TEXT_SAMPLE_CSV', 'Sample CSV');
define ('GEN_IMPORT_MESSAGE', 'Die folgende Liste zeigt die Tabellen für den Import Wählen Sie ein Format, eine Datei hochladen und drücken Sie die Schaltfläche "Importieren", um fortzufahren..');
define ('TEXT_SELECT_A_FORMAT_AND_PRESS_THE_EXPORT_BUTTON_TO_CONTINUE', 'Wählen Sie ein Format und drücken Sie die Schaltfläche "Exportieren", um fortzufahren.');
define ('TEXT_TABLES_AVAILABLE_TO', 'Tabellen zur Verfügung, um');
/************************** (General) ******************** ***************************/
define ('TEXT_MINIMUM_LENGTH_OF_PASSWORD', 'Minimum Länge des Passwort');
define ('TEXT_MAXIMUM_NUMBER_OF_SEARCH_RESULTS_RETURNED_PER_PAGE', 'Maximale Anzahl der Suchergebnisse pro Seite zurück');
define ('CD_08_03_DESC', 'Automatisch nach Aktualisierungen suchen Programm bei der Anmeldung zu PhreeBooks.');
define ('CD_08_05_DESC', 'Versteckt Meldungen über erfolgreiche Operationen Nur Vorsicht und Fehlermeldungen angezeigt werden..');
define ('CD_08_07_DESC', '. Updates der Wechselkurs geladen Währungen bei jedem Login <br /> Wenn deaktiviert, kann Währungen manuell in das Setup => Währungen Menü aktualisiert werden.');
define ('CD_08_10_DESC', 'Begrenzt die Länge der Geschichte Werte in Debitor / Kreditor Konten für Käufe / Verkäufe gezeigt.');
define ('CD_15_01_DESC', 'Session-Timeout - Geben Sie die Zeit in Sekunden (default = 3600) Beispiel: 3600 = 1 Stunde <br /> Hinweis:.. Zu wenige Sekunden Timeout Probleme ergeben können beim Hinzufügen / Bearbeiten von Produkten');
define ('CD_15_05_DESC', 'Wenn aktiviert, wird diese Option verwenden Ajax aktualisieren Sie die Session Timer alle 5 Minuten Verhinderung der Sitzung vor dem Verfall und dem Abmelden des Benutzers. Diese Funktion verhindert, dass sank Beiträge, wenn PhreeBooks inaktiv war und eine post-Ergebnisse in einen Login-Bildschirm');
define ('CD_20_99_DESC', 'Enable Trace Generation für Debugging-Zwecke verwendet Wenn Ja ausgewählt ist, einem zusätzlichen Menü auf "Extras"-Menü hinzugefügt werden zum Download der Trace-Informationen zum Debuggen Entsendung Probleme..');
define ('CD_09_01_DESC', 'Gibt den Export bevorzugt beim Exportieren von Berichten und Formularen. Local wird sie in der / my_files / reports Verzeichnis des Webservers speichern zur Verwendung mit allen Unternehmen. Download wird die Datei herunterladen, Ihren Browser zu speichern / drucken auf Ihrem lokalen Rechner. ');
define ('CD_00_01_DESC', 'Legt das Anzeigeformat für angezeigt und eingegeben Daten (Standard m / d / Y), m - Monat, d - Tag, Y - vierstellige Jahreszahl finden Sie in der php.net Funktion <b> Datum. </ b> für Format Anforderungen ').
define ('CD_00_02_DESC', 'Gibt das Trennzeichen zur Trennung von Daten (default /) Dies muss das Trennzeichen Einsatz im Datumsformat oben übereinstimmen..');
define ('CD_00_03_DESC', 'Legt das Anzeigeformat für die formale mit der Zeit (default m / d / Y h: i: sa) finden Sie in der php.net Datums-Funktion für Format-Optionen..');
/************************** (My Company) ******************* ****************************/
define ('TEXT_THE_NAME_OF_MY_COMPANY', 'Der Name meiner Firma');
define ('TEXT_THE_DEFAULT_NAME_OR_IDENTIFIER_TO_USE_FOR_ALL_RECEIVABLE_OPERATIONS', 'Der Standard-Name oder Kennung für alle Forderungen Operationen verwenden.');
define ('TEXT_THE_DEFAULT_NAME_OR_IDENTIFIER_TO_USE_FOR_ALL_PAYABLE_OPERATIONS', 'Der Standard-Name oder Kennung für alle zahlenden Operationen verwenden.');
define ('TEXT_FIRST_ADDRESS_LINE', 'Erste Adresse Zeile');
define ('TEXT_SECOND_ADDRESS_LINE', 'zweite Adresszeile');
define ('TEXT_THE_CITY_OR_TOWN_WHERE_THIS_COMPANY_IS_LOCATED', 'Die Stadt oder Gemeinde, wo dieses Unternehmen befindet');
define ('TEXT_THE_STATE_OR_REGION_WHERE_THIS_COMPANY_IS_LOCATED', 'Der Staat oder die Region, in der dieses Unternehmen befindet');
define ('TEXT_POSTAL_OR_ZIP_CODE_WHERE_THIS_COMPANY_IS_LOCATED', 'Postal oder PLZ wo dieses Unternehmen befindet');
define ('TEXT_THE_COUNTRY_THIS_COMPANY_IS_LOCATED', 'Das Land befindet sich dieses Unternehmen');
define ('TEXT_ENTER_THE_COMPANYS_PRIMARY_TELEPHONE_NUMBER', 'Geben Sie den\'s primäre Rufnummer ');
define ('TEXT_SECONDARY_TELEPHONE_NUMBER', 'Secondary Telefonnummer (auch gebührenfreie Nummer sein)');
define ('TEXT_ENTER_THE_COMPANYS_FAX_NUMBER', 'Geben Sie den\'s Faxnummer ');
define ('TEXT_ENTER_THE_GENERAL_COMPANY_EMAIL_ADDRESS', 'Geben Sie die allgemeine Firma E-Mail Adresse');
define ('TEXT_THE_COMPANY_WEBSITE_WITHOUT_THE_HTTP', 'Geben Sie die Homepage der Website des Unternehmens (ohne http://)');
define ('TEXT_ENTER_THE_COMPANYS_FEDERAL_TAX_ID_NUMBER', 'Geben Sie den\'s (Bundes-) Steuer-ID-Nummer ');
define ('CD_01_16_DESC', 'Geben Sie den ID-Nummer Diese Nummer wird verwendet, um Transaktionen lokal versus importiert / exportiert Umsätzen zu erkennen..');
define ('CD_01_18_DESC', '. Aktivieren mehrerer Niederlassung Funktionalität <br /> Wenn Nein ausgewählt ist, nur einen Standort des Unternehmens angenommen werden.');
define ('CD_01_19_DESC', 'Aktivieren mehrerer Währungen im User-Eingabemasken <br /> Wenn Nein ausgewählt ist, nur die Standard-Währung wil verwendet werden..');
define ('CD_01_20_DESC', 'wird automatisch in der Sprache \' s Währung, wenn es geändert wird ');
define ('CD_01_25_DESC', 'Ob der Versand-Funktionen und Versand Felder zu ermöglichen.');
define ('TEXT_ALLOW_STORAGE_OF_ENCRYPTED_FIELDS', 'Ob erlauben Speicherung der verschlüsselten Felder aus.');
/************************** E-Mail-Einstellungen ******************* ****************************/
define ('CD_12_01_DESC', 'Definiert die Methode zum Senden von Mail. <br /> <strong> PHP </ strong> ist der Standard und nutzt integrierte PHP-Wrapper für die Verarbeitung. <br /> Server unter Windows und MacOS Diese Einstellung sollte auf <strong> SMTP </ strong> zu ändern. <br /> <strong> smtpauth </ strong> nur verwendet werden sollten, wenn Ihr Server erfordert SMTP-Autorisierung, um Nachrichten zu senden. Darüber hinaus müssen Sie Ihre smtpauth Einstellungen in den entsprechenden Felder in diesem Abschnitt admin. <br /> <strong> sendmail </ strong> ist für Linux / Unix-Hosts mit dem sendmail-Programm auf dem Server <br /> <strong> "sendmail-f" </ strong> ist nur für Server, die die Verwendung der Parameter-f verlangen, schicken Sie eine Mail. Dies ist eine Sicherheitseinstellung oft verwendet, um Spoofing zu verhindern. Will Fehler verursachen, wenn Ihr Host Mailserver nicht konfiguriert ist, es zu benutzen. <br /> <strong> Qmail </ strong > ist für Linux / Unix Hosts mit Qmail als sendmail-Wrapper in / var / qmail / bin / sendmail benutzt ').
define ('TEXT_DEFINES_THE_CHARACTER_SEQUENCE_USED_TO_SEPARATE_MAIL_HEADERS', 'Definiert die Zeichenfolge verwendet, um separate Mail-Header.');
define ('TEXT_SEND_E-MAILS_IN_HTML_FORMAT', 'E-Mails im HTML-Format');
define ('CD_12_10_DESC', '. E-Mail-Adresse des Shop-Besitzer als Gebrauchtwagen "nur Anzeige", wenn die Information der Verbraucher darüber, wie Sie zu erreichen.');
define ('CD_12_11_DESC', 'Adresse, unter der E-Mails "geschickt" werden standardmäßig kann über-geritten komponieren-Zeit im Admin-Module..');
define ('TEXT_PLEASE_SELECT_THE_ADMIN_EXTRA_EMAIL_FORMAT', 'Bitte wählen Sie die Admin extra E-Mail-Format');
define ('CD_12_70_DESC', 'Geben Sie die Mailbox Account-Namen (me@mydomain.com) geliefert von Ihrem Gastgeber Dies ist der Kontoname, Ihr Gastgeber für SMTP-Authentifizierung erfordert (Nur erforderlich, wenn Sie SMTP-Authentifizierung für E-Mail)..');
define ('CD_12_71_DESC', '. Geben Sie das Passwort für Ihren SMTP-Postfach (nur bei Verwendung der SMTP-Authentifizierung für E-Mail erforderlich)');
define ('CD_12_72_DESC', 'Geben Sie den DNS-Namen Ihres SMTP-Mailserver, dh mail.mydomain.com oder 55.66.77.88 (nur bei Verwendung der SMTP-Authentifizierung für E-Mail) erforderlich.');
define ('CD_12_73_DESC', 'Geben Sie die IP Port-Nummer, Ihren SMTP-Mailserver auf operiert (nur bei Verwendung der SMTP-Authentifizierung für E-Mail) erforderlich.');
define ('CD_12_74_DESC', 'Was Währungsumrechnungen benötigen Sie für Text-E-Mails (Default = &pound;, £:? &euro;, €)');
/************************** Währungen Einstellungen ********************* **************************/
define ('TEXT_CURRENCIES', 'Währungen');
define ('TEXT_CURRENCY', 'Währung');
define ('TEXT_CURRENCY_CODE', 'Währung Code');
define ('TEXT_UPDATE_EXCHANGE_RATE', 'Update Exchange Rate');
define ('SETUP_CURR_EDIT_INTRO', 'Bitte führen Sie alle notwendigen Änderungen durch');
define ('TEXT_TITLE', 'Titel:');
define ('TEXT_SYMBOL_LEFT', 'Symbol Linke');
define ('TEXT_SYMBOL_RIGHT', 'Symbol rechts');
define ('TEXT_DECIMAL_POINT', 'Dezimalpunkt');
define ('TEXT_THOUSANDS_POINT', 'Tausende Point');
define ('TEXT_DECIMAL_PLACES', 'Nachkommastellen');
define ('SETUP_INFO_CURRENCY_DECIMAL_PRECISE', 'Nachkommastellen: Für den Einsatz mit Einheit Preise und Mengen bei einer höheren Präzision als Währung Werte Dieser Wert ist in der Regel auf die Anzahl der Dezimalstellen palces gesetzt.');
define ('TEXT_VALUE', 'Preis');
define ('TEXT_PLEASE_ENTER_THE_NEW_CURRENCY_WITH_ITS_RELATED_DATA', 'Bitte geben Sie die neue Währung mit allen relevanten Daten');
define ('SETUP_CURR_DELETE_INTRO', 'Sind Sie sicher, dass Sie diese Währung wirklich löschen?');
define ('SETUP_INFO_HEADING_NEW_CURRENCY', 'Neue Währung');
define ('SETUP_INFO_HEADING_EDIT_CURRENCY', 'Edit Währung');
define ('SETUP_SET_DEFAULT', 'Als Standard');
define ('SETUP_INFO_SET_AS_DEFAULT', SETUP_SET_DEFAULT . "(erfordert ein manuelles Update der Währung Werte)");
define ('SETUP_INFO_CURRENCY_UPDATED', 'Der Wechselkurs für %s  ( %s ) wurde erfolgreich via %s aktualisiert .');
define ('SETUP_ERROR_CANNOT_CHANGE_DEFAULT', 'Die Standard-Währung kann nicht geändert werden, sobald Einträge in der System eingegeben worden sein!');
define ('SETUP_ERROR_CURRENCY_INVALID', 'Fehler: Der Wechselkurs für %s  ( %s ) wurde nicht über %s  aktualisiert Ist es ein gültiges Zahlungsmittel Code?');
define ('SETUP_WARN_PRIMARY_SERVER_FAILED', 'Warnung: Das primäre Wechselkurs Server ( %s ) fehlgeschlagen für %s  ( %s ) - versuchen den sekundären Wechselkurs-Server.');
define ('TEXT_DISPLAY_NUMBER_OF_CURRENCIES', TEXT_DISPLAY_NUMBER . "Währungen");
define ('SETUP_LOG_CURRENCY', 'Währungen -');
// Verschlüsselung definiert
define ('TEXT_ENTER_ENCRYPTION_KEY', 'Geben Encryption Key');
define ('BOX_HEADING_ENCRYPTION', 'Data Encryption Services ');
define ('GEN_ENCRYPTION_GEN_INFO', 'Encryption Dienste sind abhängig von einer Taste verwendet, um Daten in der Datenbank zu verschlüsseln NICHT den Schlüssel verlieren, sonst können nicht entschlüsselt werden.');
define ('TEXT_ENTER_THE_ENCRYPTION_KEY_USED_TO_STORE_SECURE_DATA', 'Geben Sie den Chiffrierschlüssel verwendet werden, um sichere Daten zu speichern.');
define ('TEXT_ENCRYPTION_KEY', 'Encryption key');
define ('TEXT_ENCRYPTION_KEY_CONFIRM', 'Re-Enter-Taste');
define ('TEXT_ERROR_ENCRYPTION_KEY_MATCH', 'Die Schlüssel stimmen nicht überein!');
define ('TEXT_ERROR_WRONG_ENCRYPTION_KEY', 'Sie haben einen Schlüssel, aber es stimmt nicht mit dem gespeicherten Wert.');
define ('TEXT_THE_ENCRYPTION_KEY_IS_SET', 'Der Schlüssel gesetzt wurde.');
define ('TEXT_THE_ENCRYPTION_KEY_IS_CHANGED', 'Der Schlüssel wurde geändert.');
define ('TEXT_CREATE_OR_CHANGE_ENCRYPTION_KEY', 'Erstellen / Ändern Encryption Key');
define ('GEN_ADM_TOOLS_SET_ENCRYPTION_PW_DESC', 'Stellen Sie den Schlüssel zu benutzen, wenn \' Verschlüsselung aktiviert \'eingeschaltet ist Wenn Einstellung zum ersten Mal, wird der alte Schlüssel leer..');
define ('TEXT_OLD_ENCRYPTION_KEY', 'Old Encryption Key');
define ('GEN_ADM_TOOLS_ENCRYPT_PW', 'Neue Encryption Key');
define ('TEXT_RE-ENTER_NEW_ENCRYPTION_KEY', 'Re-enter New Encryption Key');
define ('TEXT_THE_CURRENT_ENCRYPTED_KEY_DOES_NOT_MATCH_THE_STORED_VALUE', 'Die aktuelle verschlüsselten Schlüssel stimmt nicht mit dem gespeicherten Wert');
// Backup definiert
define ('TEXT_COMPANY_RESTORE', 'Gesellschaft Restore');
define ('TEXT_START_BACKUP', 'Start Backup ');
define ('GEN_BACKUP_GEN_INFO', 'Bitte wählen Sie die Backup-Komprimierung und-Optionen aus.');
define ('TEXT_COMPRESSION_TYPE', 'Komprimierungstyp:');
define ('GEN_COMP_BZ2', 'bz2 (Linux)');
define ('TEXT_COMPRESSION_ZIP', 'Zip');
define ('GEN_COMP_NONE', 'Keine (Database Only)');
define ('TEXT_DATABASE_ONLY', 'Database Only');
define ('TEXT_DATABASE_AND_COMPANY_DATA_FILES', 'Datenbank-und Data Files Company');
define ('GEN_BACKUP_SAVE_LOCAL', 'Speichern einer lokalen Kopie in Webserver (my_files / Backups) Verzeichnis');
define ('GEN_BACKUP_WARNING', 'Warnung Diese Operation wird löschen und neu schreiben der Datenbank sind Sie sicher, dass Sie weitermachen!.?');
define ('GEN_BACKUP_NO_ZIP_CLASS', 'Die ZIP-Klasse kann nicht gefunden PHP muss die Zip-Bibliothek installiert haben, um wieder mit ZIP-Komprimierung werden..');
define ('TEXT_ERROR_ZIP_FILE', 'Die ZIP-Datei kann nicht erstellt werden Überprüfen Sie die Berechtigungen für das Verzeichnis:.');
define ('TEXT_THE_DOWNLOAD_FILE_DOES_NOT_CONTAIN_ANY_DATA', 'Die Download-Datei enthält keine Daten!');
// Betriebsleiter
define ('SETUP_CO_MGR_COPY_CO', 'Copy Company');
define ('TEXT_DELETE_COMPANY', 'Lösche Gesellschaft');
define ('TEXT_BASIC_DATA', 'Grunddaten');
define ('TEXT_ALL_DATA', 'Alle Daten');
define ('TEXT_DEMO_DATA', 'Demo-Daten');
define ('SETUP_CO_MGR_COPY_HDR', 'Geben Sie den Datenbank-Informationen für das neue Unternehmen. (Muss entsprechen Namenskonventionen, in der Regel 8-12 alphanumerische Zeichen) Dieser Name wird als der Name der Datenbank MySQL verwendet und wird dem my_files Verzeichnis hinzugefügt werden, um Unternehmen zu halten . Daten der Datenbank vorhanden sein müssen, vor der Schaffung des Unternehmens');
define ('TEXT_DATABASE_SERVER', 'Datenbank-Server');
define ('TEXT_DATABASE_NAME', 'Datenbank-Name');
define ('TEXT_DATABASE_USER_NAME', 'Datenbank-Benutzername');
define ('TEXT_DATABASE_PASSWORD', 'Datenbank-Passwort');
define ('TEXT_COMPANY_FULL_NAME', 'Gesellschaft Vollständiger Name');
define ('SETUP_CO_MGR_MOD_SELECT', 'Bitte wählen Sie die Module zu kopieren / erstellen und die Daten zu kopieren:');
define ('SETUP_CO_MGR_ERROR_EMPTY_FIELD', 'Datenbank-Name und der Firmenname darf nicht leer sein!');
define ('SETUP_CO_MGR_DUP_DB_NAME', 'Fehler - Der Name der Datenbank kann nicht der gleiche wie der aktuelle Name der Datenbank werden!');
define ('SETUP_CO_MGR_CANNOT_CONNECT', 'Fehler beim Verbinden mit der neuen Datenbank Überprüfen Sie Benutzernamen und Passwort ein..');
define ('TEXT_ERROR_CREATING_DATABASE_TABLES', 'Fehler beim Erstellen der Datenbank-Tabellen.');
define ('TEXT_SUCCESSFULY_CREATED_NEW_COMPANY', 'successfuly erstellt neue Gesellschaft');
define ('SETUP_CO_MGR_DELETE_SUCCESS', 'Das Unternehmen wurde erfolgreich gelöscht');
define ('SETUP_CO_MGR_LOG', 'Company Manager -');
define ('TEXT_SELECT_THE_COMPANY_TO_DELETE', 'Wählen Sie die Firma zu löschen');
define ('SETUP_CO_MGR_DELETE_CONFIRM', 'WARNUNG: Dies löscht die Datenbank und alle UNTERNEHMEN Bestimmte Dateien alle Daten verloren sein!');
define ('SETUP_CO_MGR_JS_DELETE_CONFIRM', 'Sind Sie sicher, dass Sie dieses Unternehmen wirklich löschen?');
define ('TEXT_NO_COMPANY_WAS_SELECTED_TO_DELETE', 'Kein Unternehmen wurde ausgewählt, um zu löschen');

// Audit-Log-Nachrichten
define ('TEXT_USER_LOGIN', 'Benutzer-Login');
define ('GEN_LOG_LOGIN_FAILED', 'Keine Benutzer-Login - id ->');
define ('TEXT_USER_LOGOFF', 'Benutzer abmelden');
define ('TEXT_RE-SENT_PASSWORD_TO_EMAIL', 'Re-Passwort per E-Mail geschickt');

?>