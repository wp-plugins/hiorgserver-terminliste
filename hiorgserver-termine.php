<?php
/*
  Plugin Name: HiOrg-Server Termine
  Plugin URI: http://www.klebsattel.de
  Description: Termine Ihres HiOrg-Server in einem Widget darstellen.
  Version: 0.6
  Author: Jörg Klebsattel
  Author URI: http://www.klebsattel.de
  License: GPL
 */

// TODO: Code umschreiben, damit er die Daten per JSON abruft - dann wird das Parsen des iCal unnötig

require 'class.iCalReader.php';

add_action('plugins_loaded', 'hiorgservertermine_init');

function hiorgservertermine_init() {
    register_sidebar_widget('HiOrg-Server Termine', 'hiorg_termine');
    register_widget_control('HiOrg-Server Termine', 'hiorg_termine_control', 280, 260);
}

function hiorg_termine() {
    $titel = 'Termine';
    $account = get_option("hiorg_account");
    $anzahl = get_option("hiorg_anzahl");
    $monate = get_option("hiorg_monate");

    echo '<div class="sidebox">';
    echo '<h3 class="sidetitl">' . $titel . '</h3>';

    if (empty($account)) {
        echo "Bitte zuerst das Organisations-Kürzel in der Widget-Konfiguration eingeben";
    } else {

        $url = 'https://www.hiorg-server.de/termine.php?ical=1&ov=' . $account;
        if(is_numeric($anzahl)) {
            $url .= "&anzahl=" . $anzahl;
        }
        if(is_numeric($monate)) {
            $url .= "&monate=" . $monate;
        }
        $ical = new ICal($url);
        $events = $ical->events();
        $date = $events[0]['DTSTART'];

        foreach ($events as $event) {
			date_default_timezone_set('UTC');
            $date = $ical->iCalDateToUnixTimestamp($event['DTSTART']);
            $date_ende = $ical->iCalDateToUnixTimestamp($event['DTEND']);
			date_default_timezone_set('Europe/Berlin');
            $hiorg_date = date("d.m.Y", $date);
            $hiorg_starttime = date("H:i", $date);
            $hiorg_endetime = date("H:i", $date_ende);
            echo '<div class="textwidget">';
            echo '<p>';
            echo '<small>' . $hiorg_date . ' | ' . $hiorg_starttime . '-' . $hiorg_endetime . '</small><br/>';
            echo '<b>' . stripslashes($event['SUMMARY']) . '</b><br/>';
            echo '<small>' . stripslashes($event['LOCATION']) . '</small><br/>';
            echo '</p>';
            echo '</div>';
        }
    }
    echo '</div>';
}

function hiorg_termine_control() {
    if ($_POST['hiorg-submit']) {
        $account = trim($_POST['hiorg-account']);
        $anzahl = trim($_POST['hiorg-anzahl']);
        $monate = trim($_POST['hiorg-monate']);
        update_option("hiorg_account", $account);
        update_option("hiorg_anzahl", $anzahl);
        update_option("hiorg_monate", $monate);
    }
    ?>
    <p>
        <label for="hiorg-account">Organisations-Kürzel:</label>
        <input type="text" id="hiorg-account" name="hiorg-account" value="<?= $account ?>" style="width:250px" />
        <br />
        Weitere Parameter: <small>(optional)</small><br />
        <label for="hiorg-anzahl">Anzahl der Termine:</label>
        <input type="text" id="hiorg-anzahl" name="hiorg-anzahl" value="<?= $anzahl ?>" style="width:250px" />
        <br />
        <label for="hiorg-monate">Zeitraum:</label>
        <input type="text" id="hiorg-monate" name="hiorg-monate" value="<?= $monate ?>" style="width:250px" /> Monate
        <input type="hidden" id="hiorg-submit" name="hiorg-submit" value="1" />
    </p>
    <?php
}