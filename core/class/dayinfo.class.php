<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
if (!class_exists('ICal')) { require_once dirname(__FILE__) . '/../../core/php/class.iCalReader.php'; }
if (!class_exists('MoonPhase')) { require_once dirname(__FILE__) . '/../../core/php/class.MoonPhase.php'; }
if (!class_exists('Season')) { require_once dirname(__FILE__) . '/../../core/php/class.Season.php'; }

class dayinfo extends eqLogic {


    public static function cronDaily() {
        foreach (eqLogic::byType('dayinfo', true) as $dayinfo) {
            $dayinfo->getInformations();
        }
    }

    public static function start() {
        foreach (eqLogic::byType('dayinfo', true) as $dayinfo) {
            $dayinfo->getInformations();
        }
    }

    public function getInformations() {
        //call methods to compute and update
        if ($this->getConfiguration('type') == 'bankdays') {
            $this->isNotWorkable();
            $this->getDifftoNextHoliday();
        }
        if ($this->getConfiguration('type') == 'calendar') {
            $this->calendarEvent();
        }
        if ($this->getConfiguration('type') == 'holidays') {
            $this->whatHolidays();
        }
        if ($this->getConfiguration('type') == 'moon') {
            $this->whatMoon();
        }
        if ($this->getConfiguration('type') == 'various') {
            $this->isTodayWeekend();
            $this->numberDay();
            $this->whatSeason();
        }
        if ($this->getConfiguration('type') == 'global') {
            $this->whatMoon();
            $this->whatHolidays();
            $this->isNotWorkable();
            $this->getDifftoNextHoliday();
            $this->isTodayWeekend();
            $this->numberDay();
            $this->whatSeason();
        }

    }

    public function postAjax() {
        $this->loadCmdFromConf($this->getConfiguration('type'));
        $this->getInformations();
    }

    public function loadCmdFromConf($type) {
        $content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
        $return = json_decode($content, true);
        if (!is_array($return) || !isset($return['commands'])) {
            return true;
        }
        $this->import($return);
    }

    public function isNotWorkable(){
        if ($this->getConfiguration('geoloc') == "jeedom") {
            $postal = config::byKey('info::postalCode');
            $region = $postal[0] . $postal[1];
            $stateCode = strtolower(config::byKey('info::stateCode'));
            if ($staeCode == 'fr') {
                $country = "france";
            } else if ($staeCode == 'be') {
                $country = "belgique";
            } else if ($staeCode == 'ch') {
                $country = "suisse";
            } else if ($staeCode == 'CA') {
                $country = "canada";
            } else {
                $country = $stateCode;
            }
          } else {
            $region = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
            $country = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:country')->execCmd();
          }
        $timestamp = strtotime("today");
        $year = date("Y", $timestamp);
        $holidays = dayinfo::getHolidays($country,$region,$year);
        $return = (in_array($timestamp, $holidays)) ? 1 : 0;
        $this->checkAndUpdateCmd('bankdays:bankday', $return);
        log::add('dayinfo', 'debug', 'Redday ' . $return);
    }

    /*
    * Cette fonction retourne un tableau de timestamp correspondant
    * aux jours fériés en France pour une année donnée.
    */
    public function getHolidays($country,$region, $year=null)		{
        if ($year === null) $year = date("Y");

        $easterDate  = easter_date($year);
        $easterDay   = date("j", $easterDate);
        $easterMonth = date("n", $easterDate);
        $easterYear   = date("Y", $easterDate);

        $holidays = array();

        if ($country == "france") {
            // Dates fixes
            $holidays[] = mktime(0, 0, 0, 1,  1,  $year);  // 1er janvier
            $holidays[] = mktime(0, 0, 0, 5,  1,  $year);  // Fête du travail
            $holidays[] = mktime(0, 0, 0, 5,  8,  $year);  // Victoire des alliés 1945
            $holidays[] = mktime(0, 0, 0, 7,  14, $year);  // Fête nationale
            $holidays[] = mktime(0, 0, 0, 8,  15, $year);  // Assomption
            $holidays[] = mktime(0, 0, 0, 11, 1,  $year);  // Toussaint
            $holidays[] = mktime(0, 0, 0, 11, 11, $year);  // Armistice 1918
            $holidays[] = mktime(0, 0, 0, 12, 25, $year);  // Noel

            // Dates variables
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear); // Lundi de Paques
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear); // Ascension
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear); // Lundi Pentecote

            if ($region == "57" || $region == "67" || $region == "68" || $region == "90") {
                $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay - 2,  $easterYear); // Vendredi Saint
                $holidays[] = mktime(0, 0, 0, 12, 26, $year);  // Saint Etienne
            } else if ($region == "972") {
                $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay - 2,  $easterYear); // Vendredi Saint
                $holidays[] = mktime(0, 0, 0, 5, 22, $year);  // Abolition esclavage
            } else if ($region == "971") {
                $holidays[] = mktime(0, 0, 0, 5, 27, $year);  // Abolition esclavage
                $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay - 47,  $easterYear); // Mardi Gras
                $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay - 46,  $easterYear); // Mercredi Gras
                $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay - 2,  $easterYear); // Vendredi Saint
                $holidays[] = mktime(0, 0, 0, 11, 2, $year);  // Fete des Morts
            } else if ($region == "973") {
                $holidays[] = mktime(0, 0, 0, 6, 10, $year);  // Abolition esclavage
            } else if ($region == "974") {
                $holidays[] = mktime(0, 0, 0, 12, 20, $year);  // Abolition esclavage
            }
        }
        else if ($country == "belgique")
        {
            // Dates fixes
            $holidays[] = mktime(0, 0, 0, 1,  1,  $year);  // 1er janvier
            $holidays[] = mktime(0, 0, 0, 5,  1,  $year);  // Fête du travail
            $holidays[] = mktime(0, 0, 0, 7,  21, $year);  // Fête nationale
            $holidays[] = mktime(0, 0, 0, 8,  15, $year);  // Assomption
            $holidays[] = mktime(0, 0, 0, 11, 1,  $year);  // Toussaint
            $holidays[] = mktime(0, 0, 0, 11, 11, $year);  // Armistice 1918
            $holidays[] = mktime(0, 0, 0, 12, 25, $year);  // Noel

            // Dates variables
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear); // Lundi de Paques
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear); // Ascension
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear); // Lundi Pentecote
        }
        else if ($country == "suisse")
        {
            // Dates fixes
            $holidays[] = mktime(0, 0, 0, 1,  1,  $year);  // 1er janvier
            $holidays[] = mktime(0, 0, 0, 8,  1, $year);  // Fête nationale
            $holidays[] = mktime(0, 0, 0, 12, 25, $year);  // Noel

            // Dates variables
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear); // Lundi de Paques
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear); // Ascension
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear); // Lundi Pentecote

        }
        else if ($country == "canada")
        {
            // Dates fixes
            $holidays[] = mktime(0, 0, 0, 1,  1,  $year);  // 1er janvier
            $holidays[] = strtotime("previous monday", mktime(0, 0, 0, 5, 25,  $year)); // Lundi avant la Fete de la Reine
            $holidays[] = mktime(0, 0, 0, 7,  1,  $year);  // Fête du Canada
            $holidays[] = strtotime("first monday of september", mktime(0, 0, 0, 1,  1,  $year)); // Fete du Travail
            $holidays[] = strtotime("second monday of october", mktime(0, 0, 0, 1,  1,  $year)); // Action de Grace
            $holidays[] = mktime(0, 0, 0, 12, 25, $year);  // Noel

            // Dates variables
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay - 2,  $easterYear); // Vendredi Saint
            $holidays[] = $easterDate;
            $holidays[] = mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear); // Lundi de Paques
        }
        sort($holidays);
        return $holidays;
    }

    public function nextHoliday($country,$region,$year=null)	{
        $now = time();
        $next_holiday = null;
        $holidays = dayinfo::getHolidays($country,$region,$year);
        foreach($holidays as $key => $holiday_timestamp) {
            if ($holiday_timestamp >= $now) {
                $next_holiday = $holiday_timestamp;
                break;
            }
        }
        return $next_holiday;
    }

    public function getNextHoliday($country,$region)		{
        $next_holiday = dayinfo::nextHoliday($country,$region); // get first next holiday for current year
        if ($next_holiday === null) { // no holiday for current year
            $next_year = date("Y") + 1; // next year
            $next_holiday = dayinfo::nextHoliday($country,$region,$next_year); // get first next holiday for next year
        }
        return $next_holiday;
    }

    public function getDifftoNextHoliday($format="%a")		{
        if ($this->getConfiguration('geoloc') == "jeedom") {
            $postal = config::byKey('info::postalCode');
            $region = $postal[0] . $postal[1];
            $stateCode = strtolower(config::byKey('info::stateCode'));
            if ($staeCode == 'fr') {
                $country = "france";
            } else if ($staeCode == 'be') {
                $country = "belgique";
            } else if ($staeCode == 'ch') {
                $country = "suisse";
            } else if ($staeCode == 'CA') {
                $country = "canada";
            } else {
                $country = $stateCode;
            }
          } else {
            $region = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
            $country = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:country')->execCmd();
          }
        $next_holiday = dayinfo::getNextHoliday($country,$region);
        $date_next_holiday = new DateTime(date('Y-m-d', $next_holiday));
        $current_date = new DateTime('today');
        $interval = $current_date->diff($date_next_holiday);
        $this->checkAndUpdateCmd('bankdays:nextdays', $interval->format($format));
        log::add('dayinfo', 'debug', 'Nredday ' . $interval->format($format));
    }

    // For the current date
    public function isTodayWeekend() {
        $currentDate = new DateTime("now");
        $weekend = ($currentDate->format('N') >= 6) ? 1 : 0;
        $this->checkAndUpdateCmd('various:weekend', $weekend);
        log::add('dayinfo', 'debug', 'Weekend ' . $weekend);
    }

    //Day of the year
    public function numberDay() {
        $daynumber = date('z');
        $this->checkAndUpdateCmd('various:daynumber', $daynumber);
        log::add('dayinfo', 'debug', 'Weekend ' . $daynumber);
    }

    // Season
    public function whatSeason() {
        $season = new Season();
        $aseason = $season->getSeason();
        $nseason = $season->getNextSeasonNbDays();
        log::add('dayinfo', 'debug', 'Season ' . $aseason);
        log::add('dayinfo', 'debug', 'Next Season ' . $nseason);
        $this->checkAndUpdateCmd('various:season', $aseason);
        $this->checkAndUpdateCmd('various:nextseason', $nseason);
    }

    // Moon
    public function whatMoon() {
        $moon = new Solaris\MoonPhase();
        $age = round($moon->age(),1); // age de la lune en jour
        $phase = round($moon->phase(),2); //0 et 1 nouvelle lune, 0,5 pleine lune
        $illumination = round($moon->illumination(),2);
        $distance = round($moon->distance(),2);
        $name = $moon->phase_name();
        log::add('dayinfo', 'debug', 'Phase Lune ' . $phase);
        log::add('dayinfo', 'debug', 'Age Lune ' . $age);
        $this->checkAndUpdateCmd('moon:phase', $phase);
        $this->checkAndUpdateCmd('moon:age', $age);
        $this->checkAndUpdateCmd('moon:illumination', $illumination);
        $this->checkAndUpdateCmd('moon:distance', $distance);
        $this->checkAndUpdateCmd('moon:name', $name);
    }

    // Vacances scolaires
    public function whatHolidays() {
        if ($this->getConfiguration('geoloc') == "jeedom") {
            $stateCode = strtolower(config::byKey('info::stateCode'));
            if ($staeCode == 'fr') {
                $country = "france";
            } else if ($staeCode == 'be') {
                $country = "belgique";
            } else if ($staeCode == 'ch') {
                $country = "suisse";
            } else if ($staeCode == 'CA') {
                $country = "canada";
            } else {
                $country = $stateCode;
            }
          } else {
            $country = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:country')->execCmd();
          }
        $holiday = '0';
        $nholiday = '-';
        $nextlabel = '-';
        //build calendar ID
        if ($country == 'france') {
            if ($this->getConfiguration('geoloc') == "jeedom") {
            $postal = config::byKey('info::postalCode');
            $departement = $postal[0] . $postal[1];
          } else {
            $departement = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
          }
            if (strpos($departement,'97') == true) {
                log::add('dayinfo', 'error', 'Calendrier des DOM TOM non pris en charge');
                return;
            }
            $devAddr = dirname(__FILE__) . '/../../resources/academies.csv';
            $devResult = fopen($devAddr, "r");
            while ( ($data = fgetcsv($devResult,1000,",") ) !== FALSE ) {
                $num = count($data);
                if ($data[3] == $departement) {
                    $explode = explode(' ',$data[2]);
                    $calendarid = $country . $explode[1];
                }
            }
            fclose($devResult);
        } else {
            $calendarid = $country;
        }
        $icaddr = dirname(__FILE__) . '/../../resources/' . $calendarid . '.ics';
        $ical   = new ICal($icaddr);
        log::add('dayinfo', 'debug', 'Ical ' . $icaddr);

        $events = $ical->events();
        //$timestamp = strtotime("12/30/2015");
        $timestamp = strtotime("today");
        $datetoday = date_create("today");
        $diffday = 365;
        $diffend = 365;
        $finete = date_create(mktime(0, 0, 0, 1,  1));

        foreach ($events as $event) {
            if (isset($event['DTEND'])) {
                $datehol = date_create($event['DTSTART']);
                if ($datetoday < $datehol) {
                    //calcul du début prochaines vacances
                    $diff = date_diff($datetoday, $datehol);
                    if ($diff->format('%a') < $diffday && $diff->format('%a') > 0) {
                        $diffday = $diff->format('%a');
                        $nextlabel = $event['SUMMARY'];
                    }
                }
                $datefin = date_create($event['DTEND']);
                if ($datetoday < $datefin) {
                    //calcul de la fin des prochaines vacances
                    $diff = date_diff($datetoday, $datefin);
                    if ($diff->format('%a') < $diffend && $diff->format('%a') > 0) {
                        $diffend = $diff->format('%a');
                    }
                }
                if ($datehol <= $datetoday && $datetoday < $datefin)
                {
                    $holiday = '1';
                    $nholiday = $event['SUMMARY'];
                }
            } else {
                if (strpos($event['DESCRIPTION'],'été') !== false) {
                    //post debut vacances d'été (label vacances, date supérieure et on est bien sur l'année en cours)
                    $datehol = date_create($event['DTSTART']);
                    if (date_format($datetoday,'Y') === date_format($datehol,'Y') ) {
                        $debutete = $datehol;
                        //log::add('dayinfo', 'debug', 'Debut ' . $debutete);
                    }
                }
                if ($event['DESCRIPTION'] == "Rentrée scolaire des élèves") {
                    //post reprise (label rentrée, date supérieure)
                    $datehol = date_create($event['DTSTART']);
                    //log::add('dayinfo', 'debug', 'Fin ' . date_format($datetoday,'Y') . ' ' . date_format($datehol,'Y'));
                    if (date_format($datetoday,'Y') === date_format($datehol,'Y') ) {
                        $finete = $datehol;
                        //log::add('dayinfo', 'debug', 'Fin ' . $finete);
                    }
                }
            }
        }

        if ($datetoday < $debutete) {
            $diff = date_diff($datetoday, $debutete);
            if ($diff->format('%a') < $diffday && $diff->format('%a') > 0) {
                $diffday = $diff->format('%a');
                $nextlabel = "Vacances d'été";
            }
        }

        if ($datetoday < $finete) {
            $diff = date_diff($datetoday, $finete);
            if ($diff->format('%a') < $diffend && $diff->format('%a') > 0) {
                $diffend = $diff->format('%a');
            }
        }

        if ($debutete <= $datetoday && $datetoday < $finete)
        {
            $holiday = '1';
            $nholiday = "Vacances d'été";
        }
        log::add('dayinfo', 'debug', 'Holiday ' . $holiday);
        log::add('dayinfo', 'debug', 'Label ' . $nholiday);
        log::add('dayinfo', 'debug', 'Next Holiday ' . $diffday);
        log::add('dayinfo', 'debug', 'Next End Holiday ' . $diffend);
        $this->checkAndUpdateCmd('holidays:day', $holiday);
        $this->checkAndUpdateCmd('holidays:daylabel', $nholiday);
        $this->checkAndUpdateCmd('holidays:nextbegin', $diffday);
        $this->checkAndUpdateCmd('holidays:nextend', $diffend);
        $this->checkAndUpdateCmd('holidays:nextlabel', $nextlabel);
    }

    public function calendarEvent() {
        $eventn = '0';
        $label = '-';
        $nextlabel = '-';

        $icaddr = $this->getConfiguration('calendar');
        $ical   = new ICal($icaddr);
        log::add('dayinfo', 'debug', 'Ical ' . $icaddr);

        $events = $ical->events();
        //$timestamp = strtotime("12/30/2015");
        $timestamp = strtotime("today");
        $datetoday = date_create("today");
        $diffday = 365;
        $diffend = 365;

        foreach ($events as $event) {
            if (isset($event['DTEND'])) {
                $datehol = date_create($event['DTSTART']);
                if ($datetoday < $datehol) {
                    //calcul du début prochaines vacances
                    $diff = date_diff($datetoday, $datehol);
                    if ($diff->format('%a') < $diffday && $diff->format('%a') > 0) {
                        $diffday = $diff->format('%a');
                        $nextlabel = $event['SUMMARY'];
                    }
                }
                $datefin = date_create($event['DTEND']);
                if ($datetoday < $datefin) {
                    //calcul de la fin des prochaines vacances
                    $diff = date_diff($datetoday, $datefin);
                    if ($diff->format('%a') < $diffend && $diff->format('%a') > 0) {
                        $diffend = $diff->format('%a');
                    }
                }
                if ($datehol <= $datetoday && $datetoday < $datefin)
                {
                    $eventn = '1';
                    $label = $event['SUMMARY'];
                }
            }
        }

        $this->checkAndUpdateCmd('calendar:event', $eventn);
        $this->checkAndUpdateCmd('calendar:eventlabel', $label);
        $this->checkAndUpdateCmd('calendar:nextbegin', $diffday);
        $this->checkAndUpdateCmd('calendar:nextend', $diffend);
        $this->checkAndUpdateCmd('calendar:nextlabel', $nextlabel);
    }

}

class dayinfoCmd extends cmd {
    public function execute($_options = null) {
      if ($this->getLogicalId() == 'refresh') {
        $this->getEqLogic()->getInformations();
      }
    }

}

?>
