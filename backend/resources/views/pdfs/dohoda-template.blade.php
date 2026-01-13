<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Dohoda o odbornej praxi študenta</title>
    <style>
        @page {
            margin: 2cm 2cm 2cm 2cm;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.15;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .text-center {
            text-align: center;
        } 
        
        .bold {
            font-weight: bold;
        }

        .indent {
            text-indent: 2em;
        }

        .indent-1 {
            padding-left: 40px;
            text-indent: -20px;
        }

        .indent-2 {
            padding-left: 80px;
            text-indent: -30px;
        }

        .indent-3 {
            padding-left: 120px;
            text-indent: -30px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            border: none;
        }

        p {
            margin: 0 0 0 0;
        }

    </style>
</head>
<body>
<p class="text-center"><strong>DOHODA O ODBORNEJ PRAXI ŠTUDENTA</strong></p>
<p class="text-center">uzatvorená v zmysle § 51 Občianskeho zákonníka a Zákona č. 131/2002 Z.z. o vysokých školách</p>
<br>
<p><strong>Univerzita Konštantína Filozofa v Nitre</strong></p>
<p class="indent">Fakulta prírodných vied a informatiky</p>
<p class="indent">Trieda A. Hlinku 1, 949 01 Nitra</p>
<p class="indent">v zastúpení Dr. h. c. prof. RNDr. František Petrovič, PhD., MBA - dekan fakulty</p>
<p class="indent">e-mail: dfpvai@ukf.sk tel.   037/6408 555</p>
<br>
<p><strong>Poskytovateľ odbornej praxe (organizácia, resp. inštitúcia)</strong></p>
<p class="indent">Plný názov a adresa {{ $company->company_name }}@if($company->address), {{ $company->address->street }} {{ $company->address->street_number }}, {{ $company->address->postal_code }} {{ $company->address->city }}@endif</p>
<p class="indent">v zastúpení {{ $company->contact_person_name ?? '.................................................................' }} (meno, pozícia)</p>
<br>
<p><strong>Študent:</strong></p>
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="width: 50%; padding-left: 2em; border: none;">Meno a priezvisko:</td>
        <td style="width: 50%; border: none;">{{ $student->first_name }} {{ $student->last_name }}</td>
    </tr>
    <tr>
        <td style="padding-left: 2em; border: none;">Adresa trvalého bydliska:</td>
        <td style="border: none;">@if($student->address){{ $student->address->street }} {{ $student->address->street_number }}, {{ $student->address->postal_code }} {{ $student->address->city }}@else.........................................@endif</td>
    </tr>
    <tr>
        <td style="padding-left: 2em; border: none;">Kontakt študenta FPVaI UKF v Nitre:</td>
        <td style="border: none;">{{ $student->student_email ?? $student->email }}@if($student->phone_number), {{ $student->phone_number }}@endif</td>
    </tr>
    <tr>
        <td style="padding-left: 2em; border: none;">Študijný program:</td>
        <td style="border: none;">{{ $student->studyField->study_field_name ?? 'aplikovaná informatika' }}</td>
    </tr>
</table>
<br>
<p>uzatvárajú túto dohodu o odbornej praxi študenta.</p>
<br>
<p class="text-center"><strong>I. Predmet dohody</strong></p>
<br>
<p>Predmetom tejto dohody je vykonanie odbornej praxe študenta v rozsahu 150 hodín, v termíne od {{ \Carbon\Carbon::parse($internship->date_start)->format('d.m.Y') }} do {{ \Carbon\Carbon::parse($internship->date_end)->format('d.m.Y') }} bezodplatne.</p>
<br>
<p class="text-center"><strong>II. Práva a povinnosti účastníkov dohody</strong></p>
<br>
<p><strong>1. Fakulta prírodných vied a informatiky Univerzity Konštantína Filozofa v Nitre:</strong></p>
<p></p>
<p class="indent-1">1.1 Poverí svojho zamestnanca: Mgr. Dominik Halvoník, PhD. (ďalej garant odbornej praxe) garanciou odbornej praxe.</p>
<p class="indent-1">1.2 Prostredníctvom garanta odbornej praxe:</p>
<p class="indent-2">a) poskytne študentovi:</p>
<p class="indent-3">- informácie o organizácii praxe, o podmienkach dojednania dohody o odbornej praxi, o obsahovom zameraní odbornej praxe a o požiadavkách na obsahovú náplň správy z odbornej praxe,</p>
<p class="indent-3">- návrh dohody o odbornej praxi študenta,</p>
<p class="indent-2">b) rozhodne o udelení hodnotenia „ABS" (absolvoval) študentovi na základe dokladu „Výkaz o vykonanej odbornej praxi", vydaného poskytovateľom odbornej praxe a na základe študentom vypracovanej správy o odbornej praxi, ktorej súčasťou je verejná obhajoba výsledkov odbornej praxe,</p>
<p class="indent-2">c) spravuje vyplnenú a účastníkmi podpísanú dohodu o odbornej praxi.</p>
<br>
<br>
<p><strong>2. Poskytovateľ odbornej praxe:</strong></p>
<p class="indent-1">2.1 poverí svojho zamestnanca (tútor - zodpovedný za odbornú prax v organizácii) {{ $company->contact_person_name ?? '.................................................................' }}, ktorý bude dohliadať na dodržiavanie dohody o odbornej praxi, plnenie obsahovej náplne odbornej praxe a bude nápomocný pri získavaní potrebných údajov pre vypracovanie správy z odbornej praxe,</p>
<p class="indent-1">2.2 na začiatku praxe vykoná poučenie o bezpečnosti a ochrane zdravia pri práci v zmysle platných predpisov,</p>
<p class="indent-1">2.3 vzniknuté organizačné problémy súvisiace s plnením dohody rieši spolu s garantom odbornej praxe,</p>
<p class="indent-1">2.4 po ukončení odbornej praxe vydá študentovi „Výkaz o vykonanej odbornej praxi", ktorý obsahuje popis vykonávaných činností a stručné hodnotenie študenta a je jedným z predpokladov úspešného ukončenia predmetu Odborná prax,</p>
<p class="indent-1">2.5 umožní garantovi odbornej praxe a garantovi študijného predmetu kontrolu študentom plnených úloh.</p>
<br>
<p><strong>3. Študent FPVaI UKF v Nitre:</strong></p>
<p class="indent-1">3.1 osobne zabezpečí podpísanie tejto dohody o odbornej praxi študenta,</p>
<p class="indent-1">3.2 zodpovedne vykonáva činnosti pridelené tútorom odbornej praxe,</p>
<p class="indent-1">3.3 zabezpečí doručenie dokladu „Výkaz o vykonanej odbornej praxi" najneskôr v termínoch predpísaných garantom pre daný semester,</p>
<p class="indent-1">3.4 okamžite, bez zbytočného odkladu informuje garanta odbornej praxe o problémoch, ktoré bránia plneniu odbornej praxe.</p>
<br>
<p class="text-center"><strong>III. Všeobecné a záverečné ustanovenia</strong></p>
<p class="indent-1">1. Dohoda sa uzatvára na dobu určitú. Dohoda nadobúda platnosť a účinnosť dňom podpísania obidvomi zmluvnými stranami. Obsah dohody sa môže meniť písomne len po súhlase jej zmluvných strán.</p>
<p class="indent-1">2. Diela vytvorené študentom sa spravujú režimom zamestnaneckého diela podľa § 90 zákona č. 185/2015 Z. z. (Autorský zákon). V prípade, že sa dielo stane školským dielom podľa § 93 citovaného zákona, Fakulta prírodných vied a informatiky Univerzity Konštantína Filozofa v Nitre týmto udeľuje Poskytovateľovi odbornej praxe výhradnú, časovo a teritoriálne neobmedzenú, bezodplatnú licenciu na akékoľvek použitie alebo sublicenciu diel vytvorených študentom počas trvania odbornej praxe.</p>
<p class="indent-1">3. Dohoda sa uzatvára v 3 vyhotoveniach, každá zmluvná strana obdrží jedno vyhotovenie dohody.</p>
<br>
<br>

<table class="signature-table">
    <tr>
        <td>
            <p>V Nitre, dňa {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
        </td>
        <td>
            <p>V ........., dňa ....................</p>
        </td>
    </tr>
</table>

<br>
<br>
<br>

<table class="signature-table">
    <tr>
        <td>
            <p>...................................................................</p>
            <p>Dr. h. c. prof. RNDr. František Petrovič, PhD., MBA</p>
            <p>dekan FPVaI UKF v Nitre</p>
        </td>
        <td>
            <p>...................................................................</p>
            <p>{{ $company->contact_person_name ?? '.........................................' }}</p>
            <p>štatutárny zástupca pracoviska odb. praxe</p>
        </td>
    </tr>
</table>

<br>
<br>
<br>

<table class="signature-table">
    <tr>
        <td>
            <!-- Empty left side -->
        </td>
        <td>
            <p>...................................................................</p>
            <p>{{ $student->first_name }} {{ $student->last_name }}</p>
        </td>
    </tr>
</table>

</body>
</html>