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

class ethalsurveillance extends eqLogic {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom */
    public static function cron() {
      foreach (eqLogic::byType('ethalsurveillance', true) as $eq) {
        $equipementType = '';
        $etat = $eq->ethGetValue('etat');
        $pGeneral = $eq->getConfiguration('general','');
        $configCmdEquipement = $eq->getConfiguration('cmdequipement','');

        $cmdEquipement = cmd::byString($configCmdEquipement);
        if (is_object($cmdEquipement)) {
          if ($cmdEquipement->getSubType() == 'numeric') {
            $equipementType = 'numeric';
          }  elseif ($cmdEquipement->getSubType() == 'binary') {
            $equipementType = 'binary';
          }     
        }        
        $_option = array('equipement_id' => $eq->getId());
        if ($etat == 1 and $equipementType == 'numeric' and $pGeneral != '1') {
          self::checkequipement($_option);
        }
      }
    }
    
    /*
    * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom */
    public static function cron5() {
      foreach (eqLogic::byType('ethalsurveillance', true) as $ethalsurveillance) {         
        $currentTime = time();
        $expectedStoppedTime = -1;
        $expectedStartedTime = -1;
        $expectedStartedTimeMin = -1;
        $expectedStartedTimeMax = -1;
        $expectedStoppedTimeMin = -1;
        $expectedStoppedTimeMax = -1;
 
        $configDebutheure = $ethalsurveillance->getConfiguration('debutheure','');

        $configExpectedStoppedTime = $ethalsurveillance->ethGetDayValue($currentTime,'expectedstoppedtime','');
        $configExpectedStartedTime = $ethalsurveillance->ethGetDayValue($currentTime,'expectedstartedtime','');
        $configTempsMini = $ethalsurveillance->ethGetDayValue($currentTime,'tempsmini',0);
        $configTempsMax = $ethalsurveillance->ethGetDayValue($currentTime,'tempsmax',0);

        log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : min temps set to->' . $configTempsMini.
          'max temps set to->' . $configTempsMax);

        /* verification debut heure */
        if ($configDebutheure != ''){
          $debutHeure = DateTime::createFromFormat('Gi', $configDebutheure)->getTimestamp();
          $debutHeureMin = $debutHeure-120;
          $debutHeureMax = $debutHeure+120;          
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : debut heures set to->' .date('H:i:s',$debutHeureMin).'/' .date('H:i:s',$debutHeure).'/'.date('H:i:s',$debutHeureMax));
        } else {
          $debutHeure = $currentTime;
          $debutHeureMin = $debutHeure;
          $debutHeureMax = $debutHeure;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : debut heure default set to->' .date('H:i:s',$debutHeureMin).'/' .date('H:i:s',$debutHeure).'/'.date('H:i:s',$debutHeureMax));
        }

        if ($configExpectedStoppedTime == '') {
          $expectedStoppedTime = -1;
          $expectedStoppedTimeMin = -1;
          $expectedStoppedTimeMax = -1;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : Arret prévu set to->' . $expectedStoppedTime);
        } else {
          $expectedStoppedTime = DateTime::createFromFormat('Gi', $configExpectedStoppedTime)->getTimestamp();
          $expectedStoppedTimeMin = $expectedStoppedTime;
          $expectedStoppedTimeMax = $expectedStoppedTime+310;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : Arret prévu entre-> '.date('H:i:s',$expectedStoppedTimeMin).' et '.date('H:i:s',$expectedStoppedTimeMax));
        }

        if ($configExpectedStartedTime == '') {
          $expectedStartedTime = -1;
          $expectedStartedTimeMin = -1;
          $expectedStartedTimeMax = -1;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : Marche prévu set to->' . $expectedStartedTime);
        } else {
          $expectedStartedTime = DateTime::createFromFormat('Gi', $configExpectedStartedTime)->getTimestamp();
          $expectedStartedTimeMin = $expectedStartedTime;
          $expectedStartedTimeMax = $expectedStartedTime+310;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : Marche prévu entre-> ' .date('H:i:s',$expectedStartedTimeMin).' et '.date('H:i:s',$expectedStartedTimeMax));
        }

        $etat = $ethalsurveillance->ethGetValue('etat');
        $alarme = $ethalsurveillance->ethGetValue('alarme');
        $currentTempsFct = $currentTime- $ethalsurveillance->getConfiguration('startedtime');
        $currentTempsFctTotal = $ethalsurveillance->getConfiguration('previoustpsfct') + $currentTempsFct;


        /*mise à jour des commandes de mesure de temps si l'équiepment est actif */ 
        if ($etat == 1) {                
          $fmtCurrentTempsFct = $ethalsurveillance->ethFormatTpsFct($currentTempsFct);
          $fmtCurrentTempsFctTotal = $ethalsurveillance->ethFormatTpsFct($currentTempsFctTotal);
        
          $ethalsurveillance->checkAndUpdateCmd('tempsfct',$currentTempsFct);
          $ethalsurveillance->checkAndUpdateCmd('tempsfct_hms',$fmtCurrentTempsFct);
          $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal',$currentTempsFctTotal);
          $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal_hms', $fmtCurrentTempsFctTotal);
        }
        
        /* Alarme code 1 si pas demarré a l'heure prevu + temps mini de fonctionnement et debut heure non vide */
        if ($currentTime >= ($debutHeure+($configTempsMini*60)) and $etat == 0 and $configTempsMini !=0) {
          $ethalsurveillance->ethAlarmeCode(1);
		      if ($alarme ==0){
				    $alarme = 1;
				    $ethalsurveillance->checkAndUpdateCmd('alarme',1);
				    self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
				    log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : Alarme debut heure->' . date('H:i:s',$debutHeure));
          }
        }
        /* alarme code 4*/
        if ($currentTempsFct >= ($configTempsMax*60) and $etat == 1 and $configTempsMax !=0) {
          $ethalsurveillance->ethAlarmeCode(4);
          if ($alarme == 0){
				    $alarme = 1;
				    $ethalsurveillance->checkAndUpdateCmd('alarme',1);
				    self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
				    log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : Alarme Temps Max->' . $currentTempsFct);
          }
        }

        /* alarme code 8*/
        if ($currentTime >= $expectedStoppedTimeMin and $currentTime <= $expectedStoppedTimeMax and $etat == 1 and $expectedStoppedTime !=-1) {
          $ethalsurveillance->ethAlarmeCode(8);
          if ($alarme == 0){
				    $alarme = 1;
				    $ethalsurveillance->checkAndUpdateCmd('alarme',1);
				    self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
				    log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : Alarme Expected Stopped Time->' .date('H:i:s',$expectedStoppedTimeMin).'<' .date('H:i:s',$currentTime) .'>'.date('H:i:s',$expectedStoppedTimeMax));
          }
        }

        /* alarme code 16*/
        if ($currentTime >= $expectedStartedTimeMin and $currentTime <= $expectedStartedTimeMax and $etat == 0 and $expectedStartedTime !=-1) {
          $ethalsurveillance->ethAlarmeCode(16);
          if ($alarme == 0){
            $alarme = 1;
            $ethalsurveillance->checkAndUpdateCmd('alarme',1);
            self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
            log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : cron5 : Alarme Expected Started Time->' .date('H:i:s',$expectedStartedTimeMin).'<' .date('H:i:s',$currentTime) .'>'.date('H:i:s',$expectedStartedTimeMax));
          }
        }
      }
    }
    
    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
    */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
    public static function cronDayly() {

    }
    */

    public static function deadCmd() {
      $return = array();
      foreach (eqLogic::byType('ethalsurveillance') as $ethalsurveillance){
        preg_match_all("/#([0-9]*)#/", $ethalsurveillance->getConfiguration('cmdequipement',''), $matches);
        foreach ($matches[1] as $cmd_id) {  
          if (!cmd::byId(str_replace('#','',$cmd_id))){
              $return[]= array('detail' => 'Ethal Surveillance ' . $ethalsurveillance->getHumanName(),'help' => 'Type de commande','who'=>'#' . $cmd_id . '#');
          }
        }
      }
      return $return;
    }
 

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {

    }

    public function postSave() {

    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
      $this->ethCreateCmd('ethalsurveillance');
    }

    public function preRemove() {
      $listener = listener::byClassAndFunction('ethalsurveillance', 'checkequipement', array('equipement_id' => $this->getId()));
      if (is_object($listener)) {
        log::add('ethalsurveillance', 'debug', 'Suppression du listener->checkequipement');        
        $listener->remove();
      }
    }            

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /* public Ethal Surveillance plugin function */
    public function checkequipement($_option) {

      log::add('ethalsurveillance', 'debug', 'checkequipement started');

      $ethalsurveillance = ethalsurveillance::byId($_option['equipement_id']);
      if (is_object($ethalsurveillance) and $ethalsurveillance->getIsEnable() == 1) {
        
        log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName(). ' : checkequipement : equipement trouvé et actif');

        $etat = 0;
        $alarme = 0;
        $equipementType = '';
        $currentTime = time();
        $memoCurrentTimeStatus = 0;
        $minPuissanceDelaiReach = 0;

        $expectedStoppedTime = -1;
        $expectedStartedTime = -1;
        $expectedStartedTimeMin = -1;
        $expectedStartedTimeMax = -1;
        $expectedStoppedTimeMin = -1;
        $expectedStoppedTimeMax = -1;

        $configCmdEquipement = $ethalsurveillance->getConfiguration('cmdequipement','');
        $puissance = $ethalsurveillance->getConfiguration('puissance',-100000);
        $minPuissance = $ethalsurveillance->getConfiguration('minpuissance',0);
        $maxPuissance = $ethalsurveillance->getConfiguration('maxpuissance',-10000);
        $pGeneral = $ethalsurveillance->getConfiguration('general','');
        $memoPuissance = $ethalsurveillance->getConfiguration('memopuissance',''); // Feature used
        $configDebutheure = $ethalsurveillance->getConfiguration('debutheure','');

        $minPuissanceDelai = $ethalsurveillance->getConfiguration('minpuissancedelai',0);
        $memoCurrentTime =   $ethalsurveillance->getConfiguration('memocurrenttime',0);    
        $memoCurrentTimeStatus = $ethalsurveillance->getConfiguration('memocurrenttimestatus',0);

        $configExpectedStoppedTime = $ethalsurveillance->ethGetDayValue($currentTime,'expectedstoppedtime','');
        $configExpectedStartedTime = $ethalsurveillance->ethGetDayValue($currentTime,'expectedstartedtime','');
        $configTempsMini = $ethalsurveillance->ethGetDayValue($currentTime,'tempsmini',0);
        $configTempsMax = $ethalsurveillance->ethGetDayValue($currentTime,'tempsmax',0);
        $configCptAlarmeHaute = $ethalsurveillance->ethGetDayValue($currentTime,'cptalarmehaute',0);

        log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : min temps set to ->' . $configTempsMini.
          ' max temps set to ->' . $configTempsMax.
          ' min puissance set to->' .$minPuissance.
          ' max puissance set to->' . $maxPuissance.
          ' puissance set to ->' . $puissance);
     
        /* verification debut heure */
        if ($configDebutheure != ''){
          $debutHeure = DateTime::createFromFormat('Gi', $configDebutheure)->getTimestamp();
          $debutHeureMin = $debutHeure-120;
          $debutHeureMax = $debutHeure+120;          
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : debut heures set to->' .date('H:i:s',$debutHeureMin).'/' .date('H:i:s',$debutHeure).'/'.date('H:i:s',$debutHeureMax));
        } else {
          $debutHeure = $currentTime;
          $debutHeureMin = $debutHeure;
          $debutHeureMax = $debutHeure;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : debut heure default set to->' .date('H:i:s',$debutHeureMin).'/' .date('H:i:s',$debutHeure).'/'.date('H:i:s',$debutHeureMax));
        }
        
        if ($configExpectedStoppedTime == '') {
          $expectedStoppedTime = -1;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Arret prévu set to->' . $expectedStoppedTime);
        } else {
          $expectedStoppedTime = DateTime::createFromFormat('Gi', $configExpectedStoppedTime)->getTimestamp();
          $expectedStoppedTimeMin = $expectedStoppedTime;
          $expectedStoppedTimeMax = $expectedStoppedTime+310;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Arret prévu entre ->'.date('H:i:s',$expectedStoppedTimeMin).' et '.date('H:i:s',$expectedStoppedTimeMax));
        }

        if ($configExpectedStartedTime == '') {
          $expectedStartedTime = -1;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Marche prévu set to->' .$expectedStartedTime);
        } else {
          $expectedStartedTime = DateTime::createFromFormat('Gi', $configExpectedStartedTime)->getTimestamp();
          $expectedStartedTimeMin = $expectedStartedTime;
          $expectedStartedTimeMax = $expectedStartedTime+310;
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Marche prévu entre ->'.date('H:i:s',$expectedStartedTimeMin).' et '.date('H:i:s',$expectedStartedTimeMax));
        }

        /* Verification de la commande de mesure de l'equipement*/
        $cmdEquipement = cmd::byString($configCmdEquipement);
        if (is_object($cmdEquipement)) {
          if ($cmdEquipement->getSubType() == 'numeric') {
            $equipementType = 'numeric';
            log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Numeric : Power measurement name->' . $cmdEquipement->getHumanName() .' Power measurement value->'.$cmdEquipement->execCmd());
          }  elseif ($cmdEquipement->getSubType() == 'binary') {
            $equipementType = 'binary';
            log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Binary : Equipement Cmd name->' . $cmdEquipement->getHumanName() .' Cmd equipement state->'.$cmdEquipement->execCmd());
          } else {
            log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : NOT Binary/Numeric : Equipement Cmd name->' . $cmdEquipement->getHumanName());            
          }     
        } else {          
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Equipment cmd not found');
        }
        
        $etat = $ethalsurveillance->ethGetValue('etat');
        $alarme = $ethalsurveillance->ethGetValue('alarme');
                
        $cmdValue = $cmdEquipement->execCmd();
        $compteur =  $ethalsurveillance->getCmd(null,'count')->execCmd();           

        if ($pGeneral == '1' and $equipementType == 'numeric') {
          $cmdValue = $cmdValue - $puissance;
          $minPuissance = 0;
          $maxPuissance = 0;
          $minPuissanceDelai = 0;          
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : cpt général =1, min/max cmd set to->0');
        }

        if ($equipementType == 'binary') {
          $minPuissance = 0;
          $maxPuissance = 1;
          $minPuissanceDelai = 0;
          $inverse = $ethalsurveillance->getConfiguration('inverse','0');
          if ($inverse == '0') {
            $cmdValue = $cmdValue;
          }
          if ($inverse == '1') {
            $cmdValue = !$cmdValue;
          }
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : binary cmd, max cmd set to->1 min cmd set to->0 inverse->'.$inverse);
        }
        log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : value etat->'.$etat. ' value cmd ->'.$cmdValue. ' min cmd->'.$minPuissance . ' max cmd->'.$maxPuissance);        
        /*
            0903 < 2330 < 0907
            2327 < 2330 < 2333 
        */

        if ($equipementType == 'numeric' or $equipementType == 'binary') {
        
          if ($cmdValue >= $maxPuissance and $etat == 0 and  (($currentTime >= $debutHeureMin and $currentTime <= $debutHeureMax) or $debutHeureMin == $debutHeure))  {

            $etat = 1;
            $ethalsurveillance->checkAndUpdateCmd('etat',$etat);

            $ethalsurveillance->checkAndUpdateCmd('startedtime',date('H:i:s',$currentTime));
            $ethalsurveillance->checkAndUpdateCmd('stoppedtime','-'); 

            $ethalsurveillance->checkAndUpdateCmd('count',$compteur+1);


            $ethalsurveillance->setConfiguration('startedtime',$currentTime);
            $ethalsurveillance->setConfiguration('memopuissance',$cmdValue);
            $ethalsurveillance->save();

            $alCode32 = $ethalsurveillance->getCmd(null,'code_alarme')->getConfiguration('ethalarmecode32');
            $alarme = $ethalsurveillance->ethGetValue('alarme');
            if ($alarme == 1) {
				      self::ethResetAlarme($ethalsurveillance);
            }
            $alarme = 0;

            if (($compteur+1) >= $configCptAlarmeHaute and $configCptAlarmeHaute != 0) {
              $ethalsurveillance->ethAlarmeCode(32);
				      if ($alarme == 0) {
                $alarme = 1;
                $ethalsurveillance->checkAndUpdateCmd('alarme',1);
                self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
                log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Valeur compteur haute->' . $ethalsurveillance->getCmd(null,'count')->execCmd());
              }
              log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : value change etat->'.$etat. ' compteur->'.$ethalsurveillance->getCmd(null,'count')->execCmd());
            }
            self::doAction('ethalEqAction','etat',0,$ethalsurveillance);
          }


          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : value memoCurrentTimeStatus->'.$memoCurrentTimeStatus. ' value minPuissanceDelaiReach->'.$minPuissanceDelaiReach. ' value memoCurrentTime+minPuissanceDelai->'.($memoCurrentTime+($minPuissanceDelai*60)));
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : value change etat->'.$etat. ' compteur->'.$ethalsurveillance->getCmd(null,'count')->execCmd());
          
          /* gestion du delai sur la puissance mini*/
          if ($cmdValue <= $minPuissance and $etat == 1 and $equipementType == 'numeric' and $pGeneral != '1' and $memoCurrentTimeStatus == 0) {
            $memoCurrentTime = $currentTime;
            $memoCurrentTimeStatus = 1;
            $ethalsurveillance->setConfiguration('memocurrenttime',$memoCurrentTime);
            $ethalsurveillance->setConfiguration('memocurrenttimestatus',$memoCurrentTimeStatus);
            $ethalsurveillance->save();
          }

          if ($cmdValue >= $minPuissance and $etat == 1 and $equipementType == 'numeric' and $pGeneral != '1' and $memoCurrentTimeStatus == 1) {
            $memoCurrentTime = $currentTime;
            $memoCurrentTimeStatus = 0;
            $ethalsurveillance->setConfiguration('memocurrenttime',$memoCurrentTime);
            $ethalsurveillance->setConfiguration('memocurrenttimestatus',$memoCurrentTimeStatus);
            $ethalsurveillance->save();
          }
          /* End gestion du delai sur la puissance mini*/
          
          if ($currentTime >= ($memoCurrentTime+($minPuissanceDelai*60)) and $memoCurrentTimeStatus == 1 ) {
            $minPuissanceDelaiReach = 1; 
          }
          
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : value memoCurrentTimeStatus->'.$memoCurrentTimeStatus. ' value minPuissanceDelaiReach->'.$minPuissanceDelaiReach. ' value memoCurrentTime+minPuissanceDelai->'.($memoCurrentTime+($minPuissanceDelai*60)));
          log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : value change etat->'.$etat. ' compteur->'.$ethalsurveillance->getCmd(null,'count')->execCmd());


          if (($cmdValue <= $minPuissance and ($equipementType == 'binary'  or $pGeneral == '1')  and $etat == 1) or ($minPuissanceDelaiReach == 1 and $etat == 1)) {

            $etat = 0;
            $minPuissanceDelaiReach = 0;

            $ethalsurveillance->setConfiguration('memocurrenttime',0);
            $ethalsurveillance->setConfiguration('memocurrenttimestatus',0);

            $ethalsurveillance->checkAndUpdateCmd('etat',$etat);
            $ethalsurveillance->checkAndUpdateCmd('stoppedtime',date('H:i:s',$currentTime)); 
            
            $ethalsurveillance->setConfiguration('stoppedtime',$currentTime);
            $ethalsurveillance->setConfiguration('memopuissance',0);
            
            $currentTempsFct = $currentTime - $ethalsurveillance->getConfiguration('startedtime');
            $currentTempsFctTotal = $ethalsurveillance->getConfiguration('previoustpsfct') + $currentTempsFct;

            $ethalsurveillance->setConfiguration('previoustpsfct',$currentTempsFctTotal);
            $ethalsurveillance->save();

            $fmtCurrentTempsFct = $ethalsurveillance->ethFormatTpsFct($currentTempsFct);
            $fmtCurrentTempsFctTotal = $ethalsurveillance->ethFormatTpsFct($currentTempsFctTotal);

            $ethalsurveillance->checkAndUpdateCmd('tempsfct',$currentTempsFct);
            $ethalsurveillance->checkAndUpdateCmd('tempsfct_hms', $fmtCurrentTempsFct);
            $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal',$currentTempsFctTotal);
            $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal_hms', $fmtCurrentTempsFctTotal);

            /* Alarme Code 2 */
            if ($currentTempsFct <= ($configTempsMini*60) and $configTempsMini !=0) {
				$ethalsurveillance->ethAlarmeCode(2);
				if ($alarme == 0){
					$alarme = 1;
					$ethalsurveillance->checkAndUpdateCmd('alarme',1);
					self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
					log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Temps min, alarme set to->1');
				}
            }

            if ($currentTempsFct >= ($configTempsMax*60) and $configTempsMax !=0) {
				$ethalsurveillance->ethAlarmeCode(4);
				if ($alarme == 0){
					$alarme = 1;
					$ethalsurveillance->checkAndUpdateCmd('alarme',1);
					self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
					log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Temps max, alarme set to->1');
				}
            }
			
			self::doAction('ethalEqAction','etat',1,$ethalsurveillance);
			
            log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Started Time->'.$ethalsurveillance->getConfiguration('startedtime').' Stopped Time->'. $currentTime);
            log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : value Temps mini(sec)->'.($configTempsMini*60).' Valeur Current Temps de fct->'. $currentTempsFct);
            log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : value Temps max(sec)->'.($configTempsMax*60).' Valeur Current Temps de fct->'. $currentTempsFct);
            log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : value change etat->'.$etat);
          }
        }

        $currentTempsFct = $currentTime- $ethalsurveillance->getConfiguration('startedtime');
        $currentTempsFctTotal = $ethalsurveillance->getConfiguration('previoustpsfct') + $currentTempsFct;

        /* Alarme si pas demarré a l'heure prevu + temps mini de fonctionnement et debut heure non vide */
        if ($currentTime >= ($debutHeure+($configTempsMini*60)) and $etat == 0 and $configTempsMini !=0) {
			$ethalsurveillance->ethAlarmeCode(1);
			if ($alarme == 0){
				$alarme = 1;
				$ethalsurveillance->checkAndUpdateCmd('alarme',1);
				self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
				log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Alarme debut heure->' . $debutHeure);
			}
        }
        if ($currentTempsFct >= ($configTempsMax*60) and $etat == 1 and $configTempsMax !=0) {
			$ethalsurveillance->ethAlarmeCode(4);
			if ($alarme == 0){
				$alarme = 1;
				$ethalsurveillance->checkAndUpdateCmd('alarme',1);
				self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
				log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Alarme Temps max->' . $currentTempsFct);
			}
        }

        if ($currentTime >= $expectedStoppedTimeMin and $currentTime <= $expectedStoppedTimeMax and $etat ==1  and $expectedStoppedTime !=-1) {
			$ethalsurveillance->ethAlarmeCode(8);
			if ($alarme == 0){
				$alarme = 1;
				$ethalsurveillance->checkAndUpdateCmd('alarme',1);
				self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
				log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Alarme Expected Stopped Time->' .date('H:i:s',$expectedStoppedTimeMin).'<' .date('H:i:s',$currentTime) .'>'.date('H:i:s',$expectedStoppedTimeMax));
			}
        }

        if ($currentTime >= $expectedStartedTimeMin and $currentTime <= $expectedStartedTimeMax and $etat == 0 and $expectedStartedTime !=-1) {
			$ethalsurveillance->ethAlarmeCode(16);
			if ($alarme == 0){
				$alarme = 1;
				$ethalsurveillance->checkAndUpdateCmd('alarme',1);
				self::doAction('ethalEqAction','alarme',0,$ethalsurveillance);
				log::add('ethalsurveillance', 'debug', $ethalsurveillance->getName().' : checkequipement : Alarme Expected Started Time->' .date('H:i:s',$expectedStartedTimeMin).'<' .date('H:i:s',$currentTime) .'>'.date('H:i:s',$expectedStartedTimeMax));
			}
        }

        if ($etat == 1) {
          $fmtCurrentTempsFct = $ethalsurveillance->ethFormatTpsFct($currentTempsFct);
          $fmtCurrentTempsFctTotal = $ethalsurveillance->ethFormatTpsFct($currentTempsFctTotal);

          $ethalsurveillance->checkAndUpdateCmd('tempsfct',$currentTempsFct);
          $ethalsurveillance->checkAndUpdateCmd('tempsfct_hms',$fmtCurrentTempsFct);
          $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal',$currentTempsFctTotal);
          $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal_hms', $fmtCurrentTempsFctTotal);
        }           
      
      }
    }

    /* plugin private static function */

    private static function ethResetAlarme($eq) {
      
        $eq->checkAndUpdateCmd('alarme',0);
        $eq->checkAndUpdateCmd('code_alarme',0);
        $eq->getCmd(null,'code_alarme')->setConfiguration('ethalarmecode1',0);
        $eq->getCmd(null,'code_alarme')->setConfiguration('ethalarmecode2',0);
        $eq->getCmd(null,'code_alarme')->setConfiguration('ethalarmecode4',0);
        $eq->getCmd(null,'code_alarme')->setConfiguration('ethalarmecode8',0);
        $eq->getCmd(null,'code_alarme')->setConfiguration('ethalarmecode16',0);
        $eq->getCmd(null,'code_alarme')->setConfiguration('ethalarmecode32',0);
 
		$eq->getCmd(null,'code_alarme')->save();
		self::doAction('ethalEqAction','alarme',1,$eq);
		log::add('ethalsurveillance', 'debug', $eq->getName().' : ethResetAlarme : Alarme Reset');

    }
    
    private function ethAlarmeCode($code) {

      //$alCode = $eq->getConfiguration('alarmecode'.$code);
      $alCode = $this->getCmd(null,'code_alarme')->getConfiguration('ethalarmecode'.$code);
      log::add('ethalsurveillance', 'debug', $this->getName().' : ethAlarmeCode : check Alarme Code '.$code .' current value->'.$alCode);

      if ($alCode != 1) {
        $this->checkAndUpdateCmd('code_alarme',$this->getCmd(null,'code_alarme')->execCmd()+$code);
        $this->getCmd(null,'code_alarme')->setConfiguration('ethalarmecode'.$code,1);
        $this->getCmd(null,'code_alarme')->save();          
        log::add('ethalsurveillance', 'debug', $this->getName().' : ethAlarmeCode : Alarme Code set to->'.$code);
      }
    }  

    private function ethFormatTpsFct($val) {
      
      $return = '';
      if ((floor($val / (3600*24))) == 0) {
        $return = gmdate('H:i:s',$val);
      } else {
        $return = strval(floor($val / (3600*24))).'j '.gmdate('H:i:s',$val);
      }
      log::add('ethalsurveillance', 'debug', 'Function : ethFormatTpsFct : Temps Fct->' .$return);
      return $return;
    }

    private function ethGetValue($name) {
      $value = $this->getCmd(null,$name)->execCmd();
      if ($value === null or !is_int($value)) {
        $this->checkAndUpdateCmd($name,0);
        $value = 0; 
        log::add('ethalsurveillance', 'debug', $this->getName().' : ethGetValue : '.$name. ' current Type value->' . gettype($value).' return init value->' . $value);
      } else {
        log::add('ethalsurveillance', 'debug', $this->getName().' : ethGetValue : '.$name. ' return value->' . $value);
      }
      return $value;      
    }

    private function ethGetDayValue($currentTime,$key,$default) {
      $dayConfig = $this->getConfiguration(date('N',$currentTime).$key,$default);        
      $return = $this->getConfiguration($key,$default);
      if ($dayConfig != $default){
          $return = $dayConfig;
      }          
      return $return;
    }    
    

    private static function doAction($_action, $_type, $_sens,$eq) {

      foreach ($eq->getConfiguration($_action) as $action) {
        $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
		    log::add('ethalsurveillance', 'debug', 'Liste Action->'.$action['cmd']. ' type->'.$action['actionType'].'/'.$_type. ' Sens->'.$action['actionSens'].'/'.$_sens);
	      /* A revoir pas tres clair
        if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
			   log::add('ethalsurveillance', 'debug', 'Action-> Oups Cmd probleme');
			   continue;
        }
        */
      // IF a revoir pas terrible  
		  if ($action['actionSens'] == $_sens &&  $action['actionType'] == $_type) {
        try {
          $options = array();
          if (isset($action['options'])) {
            $options = $action['options'];
          }
          log::add('ethalsurveillance', 'debug', 'Done Action->'.$action['cmd']. ' type->'.$action['actionType'].'/'.$_type. ' Sens->'.$action['actionSens'].'/'.$_sens);
          scenarioExpression::createAndExec('action', $action['cmd'], $options);
          } catch (Exception $e) {
            log::add('ethalsurveillance', 'error', __('Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
        }
		  }
	   }
    }
        
    private function ethCreateCmd($type) {
      /* commande alarme code fonctionnement 
        debut heure : 1
        Temps mini : 2
        Temps maxi : 4
        Arret prevu : 8
        Marche prevu : 16
        Compteur haut : 32
      */
      if (!is_file(dirname(__FILE__) . '/../config/devices/' . $type . '.json')) {
        log::add('ethalsurveillance', 'error', 'fichier commande pas trouvé');
        return;
      }
      $content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
      if (!is_json($content)) {
        log::add('ethalsurveillance', 'error', 'fichier commande impossible à lire');
        return;
      }
      $device = json_decode($content, true);
      if (!is_array($device) || !isset($device['commands'])) {
        log::add('ethalsurveillance', 'error', 'format fichier commande json mauvais');
        return;
      }
      log::add('ethalsurveillance', 'debug', 'fichier commande ok');
      /*$this->import($device);*/
      foreach ($device['commands'] as $command) {
        $cmd = null;
        foreach ($this->getCmd() as $liste_cmd) {
          if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])) {
            $cmd = $liste_cmd;
            break;
          }
        }
        if ($cmd === null or !is_object($cmd)) {
          $cmd = new ethalsurveillanceCmd();
          $cmd->setEqLogic_id($this->getId());
          utils::a2o($cmd, $command);
          $cmd->save();
          log::add('ethalsurveillance', 'debug', 'Creation de la commande->'.$command['logicalId']);
        }else{
          $cmd = ethalsurveillanceCmd::byEqLogicIdAndLogicalId($this->getId(),$command['logicalId']);
          utils::a2o($cmd, $command);
          $cmd->save();
          log::add('ethalsurveillance', 'debug', 'Mise à jour de la commande->'.$command['logicalId']);
        }
      }

      /* listener de la mesure de puissance our de la commande d'etat*/
      if ($this->getIsEnable() == 1 and $this->getConfiguration('cmdequipement') != null) {
        $listener = listener::byClassAndFunction('ethalsurveillance', 'checkequipement', array('equipement_id' => $this->getId()));
        if (!is_object($listener)) {
          log::add('ethalsurveillance', 'debug', 'Création du listener->checkequipement');        
          $listener = new listener();
        }
        $listener->setClass('ethalsurveillance');
        $listener->setFunction('checkequipement');
        $listener->setOption(array('equipement_id' => $this->getId()));
        $listener->emptyEvent();
        $listener->addEvent($this->getConfiguration('cmdequipement'));
        
        $listener->save();
        log::add('ethalsurveillance', 'debug', 'Mise à jour du listener->checkequipement');
      }
    }      

    /* public plugin function */
 
    public function ethCumulTps($_startDate = null, $_endDate = null) {
      $etatCmd = $this->getCmd(null, 'etat');
      if (!is_object($etatCmd)) {
        return array();
      }
      $return = array();
      $prevValue = 0;
      $prevDatetime = 0;
      $day = null;
      foreach ($etatCmd->getHistory($_startDate, $_endDate) as $history) {
        if (date('Y-m-d', strtotime($history->getDatetime())) != $day && $prevValue == 1 && $day != null) {
          if (strtotime($day . ' 23:59:59') > $prevDatetime) {
            $return[$day][1] += (strtotime($day . ' 23:59:59') - $prevDatetime) / 3600;
          }
          $prevDatetime = strtotime(date('Y-m-d 00:00:00', strtotime($history->getDatetime())));
        }
        $day = date('Y-m-d', strtotime($history->getDatetime()));
        if (!isset($return[$day])) {
          $return[$day] = array(strtotime($day . ' 00:00:00 UTC') * 1000, 0);
        }
        if ($history->getValue() == 1 && $prevValue == 0) {
          $prevDatetime = strtotime($history->getDatetime());
          $prevValue = 1;
        }
        if ($history->getValue() == 0 && $prevValue == 1) {
          if ($prevDatetime > 0 && strtotime($history->getDatetime()) > $prevDatetime) {
            $return[$day][1] += (strtotime($history->getDatetime()) - $prevDatetime) / 3600;
          }
          $prevValue = 0;
        }
      }
      
      return $return;
    }

    // Not used , work in progress
    public function ethCumulCpt($_startDate = null, $_endDate = null) {
      $cptCmd = $this->getCmd(null, 'count');
      if (!is_object($cptCmd)) {
        return array();
      }
      $return = array();
      $prevValue = 0;
      $prevDatetime = 0;
      $day = null;
      foreach ($ctpCmd->getHistory($_startDate, $_endDate) as $history) {
        if (date('Y-m-d', strtotime($history->getDatetime())) != $day && $prevValue == 1 && $day != null) {
          if (strtotime($day . ' 23:59:59') > $prevDatetime) {
            $return[$day][1] += (strtotime($day . ' 23:59:59') - $prevDatetime) / 3600;
          }
          $prevDatetime = strtotime(date('Y-m-d 00:00:00', strtotime($history->getDatetime())));
        }
        $day = date('Y-m-d', strtotime($history->getDatetime()));
        if (!isset($return[$day])) {
          $return[$day] = array(strtotime($day . ' 00:00:00 UTC') * 1000, 0);
        }
        if ($history->getValue() == 1 && $prevValue == 0) {
          $prevDatetime = strtotime($history->getDatetime());
          $prevValue = 1;
        }
        if ($history->getValue() == 0 && $prevValue == 1) {
          if ($prevDatetime > 0 && strtotime($history->getDatetime()) > $prevDatetime) {
            $return[$day][1] += (strtotime($history->getDatetime()) - $prevDatetime) / 3600;
          }
          $prevValue = 0;
        }
      }
      
      return $return;
    }

    /*     * **********************Getteur Setteur*************************** */

}

class ethalsurveillanceCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
      $ethalsurveillance = $this->getEqLogic();
      $val = date('d/m/Y H:i:s',time());
	  
      log::add('ethalsurveillance', 'debug', 'action->' . $this->getLogicalId());
      
      if ($this->getLogicalId() == 'setcountplus') {
        $compteur =  $ethalsurveillance->getCmd(null,'count')->execCmd();
        $ethalsurveillance->checkAndUpdateCmd('count',$compteur+1);
        log::add('ethalsurveillance', 'debug', 'Set Compteurs plus 1');
      }
      if ($this->getLogicalId() == 'setcountmoins') {
        $compteur =  $ethalsurveillance->getCmd(null,'count')->execCmd();
        $ethalsurveillance->checkAndUpdateCmd('count',$compteur-1);
        log::add('ethalsurveillance', 'debug', 'Set Compteurs moins 1');
      }
      if ($this->getLogicalId() == 'razcount') {
        $ethalsurveillance->checkAndUpdateCmd('count',0);
		$ethalsurveillance->checkAndUpdateCmd('tpsrazcpt',$val);		
        log::add('ethalsurveillance', 'debug', 'RAZ Compteurs');

      }        
      if ($this->getLogicalId() == 'raztempsfcttotal') {
        $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal',0);
        $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal_hms','-');
        $ethalsurveillance->setConfiguration('previoustpsfct',0);
	$ethalsurveillance->checkAndUpdateCmd('tpsraztps',$val);
        $ethalsurveillance->save();
        log::add('ethalsurveillance', 'debug', 'RAZ Temps Fct Total');
      }
      if ($this->getLogicalId() == 'razall') {	
        $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal',0);
        $ethalsurveillance->checkAndUpdateCmd('tempsfcttotal_hms','-');
        $ethalsurveillance->setConfiguration('previoustpsfct',0);
	$ethalsurveillance->checkAndUpdateCmd('count',0);
	$ethalsurveillance->checkAndUpdateCmd('tpsraztps',$val);
	$ethalsurveillance->checkAndUpdateCmd('tpsrazcpt',$val);		
        $ethalsurveillance->save();
        log::add('ethalsurveillance', 'debug', 'RAZ All');
      }

    }

    /*     * **********************Getteur Setteur*************************** */
}

?>
