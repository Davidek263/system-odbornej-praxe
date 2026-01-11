<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Dohoda o odbornej praxi študenta</title>
    <style>
        @page {
            margin: 2cm 2.5cm;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }
        
        .text-center {
            text-align: center;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .mb-1 {
            margin-bottom: 0.3em;
        }
        
        .mb-2 {
            margin-bottom: 0.8em;
        }
        
        .mb-3 {
            margin-bottom: 1.2em;
        }
        
        h1 {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin: 1em 0 0.5em 0;
            text-transform: uppercase;
        }
        
        .subtitle {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 1.5em;
            line-height: 1.3;
        }
        
        h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 1.2em 0 0.6em 0;
            text-align: center;
        }
        
        h3 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0.8em 0 0.3em 0;
        }
        
        .section {
            margin-bottom: 1em;
        }
        
        .party-info {
            margin-bottom: 1.2em;
        }
        
        .party-info p {
            margin: 0.15em 0;
            line-height: 1.3;
        }
        
        .student-table {
            margin-left: 2em;
            margin-top: 0.5em;
        }
        
        .student-table .row {
            display: table;
            width: 100%;
            margin-bottom: 0.2em;
        }
        
        .student-table .label {
            display: table-cell;
            width: 280px;
            padding-right: 1em;
        }
        
        .student-table .value {
            display: table-cell;
        }
        
        .indent {
            margin-left: 2em;
        }
        
        .indent-2 {
            margin-left: 3em;
        }
        
        ul {
            margin: 0.2em 0;
            padding-left: 0;
            list-style: none;
        }
        
        li {
            margin: 0.2em 0;
            padding-left: 1.5em;
            text-indent: -0.7em;
        }
        
        li:before {
            content: "- ";
        }
        
        .dotted-line {
            display: inline-block;
            border-bottom: 1px dotted #000;
            min-width: 150px;
        }
        
        .signature-section {
            margin-top: 2em;
        }
        
        .signature-row {
            display: table;
            width: 100%;
            margin-bottom: 0.3em;
        }
        
        .signature-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .signature-col.left {
            padding-right: 1em;
        }
        
        .signature-col.right {
            padding-left: 1em;
        }
        
        .signature-line {
            border-bottom: 1px dotted #000;
            margin-top: 1.5em;
            margin-bottom: 0.2em;
        }
        
        .signature-student {
            margin-top: 1.5em;
            text-align: center;
        }
        
        .signature-student .signature-line {
            max-width: 450px;
            margin: 1.5em auto 0.2em auto;
        }
    </style>
</head>
<body>
    <h1>Dohoda o odbornej praxi študenta</h1>
    
    <p class="subtitle">
        uzatvorená v zmysle § 51 Občianskeho zákonníka a Zákona č. 131/2002 Z.z. o vysokých školách
    </p>
    
    <div class="party-info">
        <h3>Univerzita Konštantína Filozofa v Nitre</h3>
        <p class="indent">Fakulta prírodných vied a informatiky</p>
        <p class="indent">Trieda A. Hlinku 1, 949 01 Nitra</p>
        <p class="indent mb-1">v zastúpení Dr. h. c. prof. RNDr. František Petrovič, PhD., MBA – dekan fakulty</p>
        <p class="indent">e-mail: dfpvai@ukf.sk&nbsp;&nbsp;&nbsp;&nbsp;tel. 037/6408 555</p>
    </div>
    
    <div class="party-info">
        <h3>Poskytovateľ odbornej praxe (organizácia, resp. inštitúcia)</h3>
        <p class="indent mb-1">
            Plný názov a adresa {{ $company->company_name }}@if($company->address), {{ $company->address->street }} {{ $company->address->street_number }}, {{ $company->address->postal_code }} {{ $company->address->city }}@endif
        </p>
        <p class="indent mb-1">
            v zastúpení {{ $company->contact_person_name ?? '.................................................................' }} (meno, pozícia)
        </p>
    </div>
    
    <div class="party-info">
        <h3>Študent:</h3>
        <div class="student-table">
            <div class="row">
                <div class="label">Meno a priezvisko:</div>
                <div class="value">{{ $student->first_name }} {{ $student->last_name }}</div>
            </div>
            <div class="row">
                <div class="label">Adresa trvalého bydliska:</div>
                <div class="value">@if($student->address){{ $student->address->street }} {{ $student->address->street_number }}, {{ $student->address->postal_code }} {{ $student->address->city }}@else.........................................@endif</div>
            </div>
            <div class="row">
                <div class="label">Kontakt študenta FPVaI UKF v Nitre:</div>
                <div class="value">{{ $student->student_email ?? $student->email }}@if($student->phone_number), {{ $student->phone_number }}@endif</div>
            </div>
            <div class="row">
                <div class="label">Študijný program:</div>
                <div class="value">{{ $student->studyField->study_field_name ?? 'aplikovaná informatika' }}</div>
            </div>
        </div>
    </div>
    
    <p class="text-center bold mb-3">uzatvárajú túto dohodu o odbornej praxi študenta.</p>
    
    <div class="section">
        <h2>I. Predmet dohody</h2>
        <p>
            Predmetom tejto dohody je vykonanie odbornej praxe študenta v rozsahu 150 hodín, v termíne od {{ \Carbon\Carbon::parse($internship->date_start)->format('d.m.Y') }} do {{ \Carbon\Carbon::parse($internship->date_end)->format('d.m.Y') }} bezodplatne.
        </p>
    </div>
    
    <div class="section">
        <h2>II. Práva a povinnosti účastníkov dohody</h2>
        
        <p class="bold mb-1">1. Fakulta prírodných vied a informatiky Univerzity Konštantína Filozofa v Nitre:</p>
        
        <p class="mb-1">1.1 Poverí svojho zamestnanca: Mgr. Dominik Halvoník, PhD. (ďalej garant odbornej praxe) garanciou odbornej praxe.</p>
        
        <p class="mb-1">1.2 Prostredníctvom garanta odbornej praxe:</p>
        
        <p class="indent mb-1">a) poskytne študentovi:</p>
        <ul class="indent-2">
            <li>informácie o organizácii praxe, o podmienkach dojednania dohody o odbornej praxi, o obsahovom zameraní odbornej praxe a o požiadavkách na obsahovú náplň správy z odbornej praxe,</li>
            <li>návrh dohody o odbornej praxi študenta,</li>
        </ul>
        
        <p class="indent mb-1">
            b) rozhodne o udelení hodnotenia „ABS" (absolvoval) študentovi na základe dokladu „Výkaz o vykonanej odbornej praxi", vydaného poskytovateľom odbornej praxe a na základe študentom vypracovanej správy o odbornej praxi, ktorej súčasťou je verejná obhajoba výsledkov odbornej praxe,
        </p>
        
        <p class="indent mb-2">c) spravuje vyplnenú a účastníkmi podpísanú dohodu o odbornej praxi.</p>
        
        <p class="bold mb-1">2. Poskytovateľ odbornej praxe:</p>
        
        <p class="mb-1">
            2.1 poverí svojho zamestnanca (tútor - zodpovedný za odbornú prax v organizácii) {{ $company->contact_person_name ?? '.................................................................' }}, ktorý bude dohliadať na dodržiavanie dohody o odbornej praxi, plnenie obsahovej náplne odbornej praxe a bude nápomocný pri získavaní potrebných údajov pre vypracovanie správy z odbornej praxe,
        </p>
        
        <p class="mb-1">2.2 na začiatku praxe vykoná poučenie o bezpečnosti a ochrane zdravia pri práci v zmysle platných predpisov,</p>
        
        <p class="mb-1">2.3 vzniknuté organizačné problémy súvisiace s plnením dohody rieši spolu s garantom odbornej praxe,</p>
        
        <p class="mb-1">
            2.4 po ukončení odbornej praxe vydá študentovi „Výkaz o vykonanej odbornej praxi", ktorý obsahuje popis vykonávaných činností a stručné hodnotenie študenta a je jedným z predpokladov úspešného ukončenia predmetu Odborná prax,
        </p>
        
        <p class="mb-2">2.5 umožní garantovi odbornej praxe a garantovi študijného predmetu kontrolu študentom plnených úloh.</p>
        
        <p class="bold mb-1">3. Študent FPVaI UKF v Nitre:</p>
        
        <p class="mb-1">3.1 osobne zabezpečí podpísanie tejto dohody o odbornej praxi študenta,</p>
        
        <p class="mb-1">3.2 zodpovedne vykonáva činnosti pridelené tútorom odbornej praxe,</p>
        
        <p class="mb-1">3.3 zabezpečí doručenie dokladu „Výkaz o vykonanej odbornej praxi" najneskôr v termínoch predpísaných garantom pre daný semester,</p>
        
        <p class="mb-2">3.4 okamžite, bez zbytočného odkladu informuje garanta odbornej praxe o problémoch, ktoré bránia plneniu odbornej praxe.</p>
    </div>
    
    <div class="section">
        <h2>III. Všeobecné a záverečné ustanovenia</h2>
        
        <p class="mb-1">
            1. Dohoda sa uzatvára na dobu určitú. Dohoda nadobúda platnosť a účinnosť dňom podpísania obidvomi zmluvnými stranami. Obsah dohody sa môže meniť písomne len po súhlase jej zmluvných strán.
        </p>
        
        <p class="mb-1">
            2. Diela vytvorené študentom sa spravujú režimom zamestnaneckého diela podľa § 90 zákona č. 185/2015 Z. z. (Autorský zákon). V prípade, že sa dielo stane školským dielom podľa § 93 citovaného zákona, Fakulta prírodných vied a informatiky Univerzity Konštantína Filozofa v Nitre týmto udeľuje Poskytovateľovi odbornej praxe výhradnú, časovo a teritoriálne neobmedzenú, bezodplatnú licenciu na akékoľvek použitie alebo sublicenciu diel vytvorených študentom počas trvania odbornej praxe.
        </p>
        
        <p class="mb-3">3. Dohoda sa uzatvára v 3 vyhotoveniach, každá zmluvná strana obdrží jedno vyhotovenie dohody.</p>
    </div>
    
    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-col left">
                <p>V Nitre, dňa {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
            </div>
            <div class="signature-col right">
                <p>V .........., dňa ....................</p>
            </div>
        </div>
        
        <div class="signature-row">
            <div class="signature-col left">
                <div class="signature-line"></div>
                <p class="text-center">Dr. h. c. prof. RNDr. František Petrovič, PhD., MBA</p>
                <p class="text-center">dekan FPVaI UKF v Nitre</p>
            </div>
            <div class="signature-col right">
                <div class="signature-line"></div>
                <p class="text-center">{{ $company->contact_person_name ?? '.........................................' }}</p>
                <p class="text-center">štatutárny zástupca pracoviska odb. praxe</p>
            </div>
        </div>
    </div>
    
    <div class="signature-student">
        <div class="signature-line"></div>
        <p>{{ $student->first_name }} {{ $student->last_name }}</p>
        <p>meno a priezvisko študenta</p>
    </div>
</body>
</html>