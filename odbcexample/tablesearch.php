<? function sendtable($Quelle, $Ergebnis, $LinkPage)
{

    $Spaltenzahl = odbc_num_fields($Ergebnis);

    echo "<table border=l> <tr>";

    // Tabellenkopf definieren
    $i = 1;
    while ($i <= $Spaltenzahl) {
        echo "<td>";

        echo "<b>";
        echo odbc_field_name($Ergebnis, $i);
        $Spalte[] = odbc_field_name($Ergebnis, $i);
        echo "</b>";

        echo "</td>";

        $i++;
    }

    echo "</tr>";

    // Daten in Tabelle ausgeben

    // Kontrollieren, ob checkCompleteText definiert ist
    if (isset($_POST['checkCompleteText']) == false) {
        $checkCompleteText = "OFF";
    } else {
        $checkCompleteText = $_POST['checkCompleteText'];
    }
    // Kontrollieren, ob Suchtext definiert ist
    // Wenn nicht, dann wird die komplete Text angezeigt
    if (isset($_POST['Suchtext']) == false) {
        $Suchtext = "xxxxxxxxxxxxxxxxxxxxx";
        $checkCompleteText = 'ON';
    } else {
        $Suchtext = $_POST['Suchtext'];
    }
    // Kontrollieren, ob relevante Suchtext eingegeben ist.
    // Wenn nicht, dann wird die komplete Text angezeigt
    if (strlen($Suchtext) < 1) {
        $Suchtext = "xxxxxxxxxxxxxxxxx";
        $checkCompleteText = 'ON';
    }
    while (odbc_fetch_row($Ergebnis)) {

        $SuchTextAnwesend = 'J';
        $i = 1;

        // Ist der gesuchte Text in irgendeine Spalte vorhanden ?
        $CountSuchtokens = 0;
        $Suchtokenshelper = strtok($Suchtext, " ");
        while (($Suchtokenshelper) && ($SuchTextAnwesend == "J")) {
            $CountSuchtokens++;
            $locSuchTextAnwesend = 'N';
            $i = 1;
            while ($i <= $Spaltenzahl) {
                $MyResult = odbc_result($Ergebnis, $i);
                if (strpos(strtolower($MyResult), strtolower($Suchtokenshelper)) > -1) {
                    $locSuchTextAnwesend = 'J';

                }
                $i++;
            }
            if ($locSuchTextAnwesend == 'N') {
                $SuchTextAnwesend = 'N';
            }
            $Suchtokenshelper = strtok(" ");
        }


        if (($SuchTextAnwesend == 'J') || ($checkCompleteText == 'ON')) {
            echo "<tr>";
        }
        $i = 1;
        $j = 0;
        while ($i <= $Spaltenzahl) {
            if (($SuchTextAnwesend == 'J') || ($checkCompleteText == 'ON')) {
                echo "<td>";
            }
            $MyResult = odbc_result($Ergebnis, $i);

            // gesuchte Text vorhanden ?
            if ((strlen($Suchtext) > 0) && ((strpos(strtolower($MyResult), strtolower($Suchtext)) > -1))
                && ((strpos($MyResult, "www.") > -1) == false)
                && ((strpos($MyResult, "http:") > -1) == false)
            ) {
                echo "<span style=";
                echo '"';
                echo "color : black; background: white";
                echo '">';
                $Pos = strpos(strtolower($MyResult), strtolower($Suchtext));
                $Len = strlen($Suchtext);
                if ($Pos > 0) {
                    echo substr($MyResult, 0, $Pos);
                }
                echo "<span style=";
                echo '"';
                echo "color : black; background: lightgreen";
                echo '">';
                echo "<b>";
                echo substr($MyResult, $Pos, $Len);
                echo "</b>";
                echo "<span style=";
                echo '"';
                echo "color : black; background: white";
                echo '">';
                $LenResult = strlen($MyResult);
                if (($Pos + $Len) < $LenResult) {
                    echo substr($MyResult, $Pos + $Len);
                }
                echo "</span";
                echo "</span";
                echo "</span";
            } else {
                // Gibt es eine Spalte mit dem gesuchten Text oder soll die komplete Text erscheinen ?
                if (($SuchTextAnwesend == 'J') || ($checkCompleteText == 'ON')) {
                    if (strpos($MyResult, "www.") > -1) {
                        echo "<span style=";
                        echo '"';
                        //	echo "color : black; background: yellow";
                        echo "color : black; ";
                        echo '">';
                        echo "<b>";
                        echo '<a target="_blank" href="http://';
                        $Pos = strpos($MyResult, "www.");
                        $Len = strlen($MyResult);
                        $NewResult = shrimpstr(substr($MyResult, $Pos, $Len - $Pos));
                        echo $NewResult;

                        echo '">';
                        echo $MyResult;

                        echo "</a>";
                        echo "</b>";
                        echo "</span";

                    } else {
                        if (strpos($MyResult, "http:") > -1) {
                            echo "<span style=";
                            echo '"';
                            //	echo "color : black; background: yellow";
                            echo "color : black; ";
                            echo '">';
                            echo "<b>";
                            echo '<a target="_blank" href="';
                            $Pos = strpos($MyResult, "http:");
                            $Len = strlen($MyResult);
                            $NewResult = shrimpstr(substr($MyResult, $Pos, $Len - $Pos));
                            echo $NewResult;

                            echo '">';
                            echo $MyResult;
                            echo "</a>";
                            echo "</b>";
                            echo "</span";
                        } else {
                            if (strlen($MyResult) > 0) {
                                // Betrifft es hier ein ID-Feld ?
                                if (strcasecmp($Spalte[$j], "id") == 0) {
                                    echo '<a href="';

                                    echo $LinkPage;
                                    echo "?";
                                    echo "myindex=";
                                    echo $MyResult;
                                    echo '">';
                                    echo $MyResult;

                                    echo "</a>";
                                } else {
                                    echo $MyResult;
                                }
                            } else {
                                echo "  -  ";

                            }
                        }
                    }
                }
            }
            if (($SuchTextAnwesend == 'J') || ($checkCompleteText == 'ON')) {
                echo "</td>";
            }
            $i++;
            $j++;
        }

        if (($SuchTextAnwesend == 'J') || ($checkCompleteText == 'ON')) {
            echo "</tr>";
        }
    }

    echo "</table>";
    return "OK";
}

function sendpdf($MyHeader, $MyWidths, $Quelle, $Ergebnis)
{
    define('FPDF_FONTPATH', 'font/');
    require('mc_table.php');
    $pdf = new PDF_MC_TABLE();
    $pdf->SetHeader($MyHeader);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Open();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 9);
    // $pdf->InitRow();
    $Spaltenzahl = odbc_num_fields($Ergebnis);

    $pdf->SetWidths($MyWidths);
    $Spalten = "";

    // Kontrollieren, ob checkCompleteText definiert ist
    if (isset($_POST['checkCompleteText']) == false) {
        $checkCompleteText = "OFF";
    } else {
        $checkCompleteText = $_POST['checkCompleteText'];
    }
    // Kontrollieren, ob Suchtext definiert ist
    // Wenn nicht, dann wird die komplete Text angezeigt
    if (isset($_POST['Suchtext']) == false) {
        $Suchtext = "xxxxxxxxxxxxxxxxxxxxx";
        $checkCompleteText = 'ON';
    } else {
        $Suchtext = $_POST['Suchtext'];
    }
    // Kontrollieren, ob relevante Suchtext eingegeben ist.

    if (strlen($Suchtext) < 1) {
        $Suchtext = "xxxxxxxxxxxxxxxxx";
        $checkCompleteText = 'ON';
    }
    while (odbc_fetch_row($Ergebnis)) {


        // Ist der gesuchte Text in irgendeine Spalte vorhanden ?
        $SuchTextAnwesend = 'J';
        $i = 1;

        // Ist der gesuchte Text in irgendeine Spalte vorhanden ?
        $CountSuchtokens = 0;
        $Suchtokenshelper = strtok($Suchtext, " ");
        while (($Suchtokenshelper) && ($SuchTextAnwesend == "J")) {
            $CountSuchtokens++;
            $locSuchTextAnwesend = 'N';
            $i = 1;
            while ($i <= $Spaltenzahl) {
                $MyResult = odbc_result($Ergebnis, $i);
                if (strpos(strtolower($MyResult), strtolower($Suchtokenshelper)) > -1) {
                    $locSuchTextAnwesend = 'J';

                }
                $i++;
            }
            if ($locSuchTextAnwesend == 'N') {
                $SuchTextAnwesend = 'N';
            }
            $Suchtokenshelper = strtok(" ");
        }

        if (($SuchTextAnwesend == 'J') || ($checkCompleteText == 'ON')) {
            $i = 1;
            $j = 0;
            $Spalten = "";


            // Spaltentabelle fllen
            while ($i <= $Spaltenzahl) {
                $MyResult = odbc_result($Ergebnis, $i);
                $Spalten[$j] = $MyResult;


                $j++;
                $i++;
            }
            $pdf->Row($Spalten);
        }

    }

    $pdf->Output();
    return "OK";
}

function shrimpstr($StrInput)
{
    $TmpOutput = $StrInput;
    $StrOutput = "";
    for ($n = 0; $n < strlen($TmpOutput); $n++) {
        if (strpos(substr($StrInput, $n, 1), " ") > -1) {
            $n = strlen($TmpOutput);
        } else {
            $StrOutput = $StrOutput . substr($StrInput, $n, 1);
        }
    }
    return $StrOutput;
}

?>
