**Shimly PHP Class**
Inoffizielle PHP Klasse für das SMI Interface von Shimly.de

**Shimly**
Username: FlareCO
ID: 5032

**Funktionen**

    include path_to_file
	
	$shimly = new Shimly($SMI_ID, $SMI_PW, $MODE); // MODE CAN BE 'shimlys' OR 'boostpoints' DEFAULT: 'shimlys'

    $shimly->hasSuccess() - return true if the last request was successful
    $shimly->validate($sID, $sPW) - validates provided Shimly credentials
    $shimly->validateSimple($sID) - validates provided Shimly credentials
    $shimly->getUserBalance($sID) - returns the balance of the provided Shimly account
    $shimly->payIn($sID, $sPW, $amount, $reference) - debit the provided amount from the provided Shimly account
    $shimly->payOut($sID, $sPW, $amount, $reference) - credit the provided amount to the provided Shimly account
    $shimly->rate($currency) - returns the current rate of the provided currency
    $shimly->smiBalance() - returns the balance of the provided SMI account

Bei jedem aufruf wird ein array mit jeweiligen informationen oder eine exception zurück gegeben.
Hier befindet sich eine liste mit zurück gegebenen werten als beispiel. 
Die Reihe entspricht den oben aufgeführten funktionen.

    1. true/false
    2. { "code": "1001", "username": "FlareCO", "status": "1", "country": "DE" }
    3. { "code": "1001", "username": "FlareCO", "status": "1", "country": "DE" }
    4. { "code": "1001", "smi": "9999999", "user": "100" }
    5. { "code": "1001", "smi": "9999999", "user": "200" }
    6. { "code": "1001", "smi": "9999999", "user": "100" }
    7. does not work
    8. { "code": "1001", "smi": "9999999", "vault": "0" }

Liste aller möglichen exception.

    "1001"  =>  "Erfolgreicher API Zugriff",
    "1002"  =>  "SMI Account existiert nicht",
    "1003"  =>  "SMI Account-Passwort ist falsch",
    "1006"  =>  "Shimly Nutzer existiert nicht",
    "1009"  =>  "Shimly Nutzer SMI-Passwort ist falsch",
    "1050"  =>  "Ungültige IP-Adresse",
    "1095"  =>  "SMI im Wartungsmodus",
    "1097"  =>  "SMI überlastet",
    "1098"  =>  "SMI Account gesperrt",
    "1099"  =>  "Unbekannter Fehler",
