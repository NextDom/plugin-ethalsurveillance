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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect()) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

		if (init('action') == 'ethGetData') {

			if (init('eqid') == '') {
				//$_GET['eq_id'] = $_SESSION['user']->getOptions('defaultDashboardObject');
				$return['eq'] = array('ethCumulTps' =>'');
				ajax::success($return);	
			}

			$ethalsurveillance = ethalsurveillance::byId(init('eqid'));

			if (!is_object($ethalsurveillance)) {
				throw new Exception(__('Aucun equipement trouvé', __FILE__));
			}
			$date = array(
				'start' => init('dateStart'),
				'end' => init('dateEnd'),
			);

			if ($date['start'] == '') {
				$date['start'] = date('Y-m-d', strtotime('-1 months ' . date('Y-m-d')));
			}
			if ($date['end'] == '') {
				$date['end'] = date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')));
			}

			$cmdEquipement = $ethalsurveillance->getConfiguration('cmdequipement','');
			$eqMasterId = cmd::byString($cmdEquipement)->getEqLogic_id();
			$eqMaster = eqLogic::byId($eqMasterId);

			if (!is_object($eqMaster)) {
				throw new Exception(__('Equipement Master introuvable pour la comamnde: ', __FILE__) . $cmdEquipement);
			}

			if (!is_object($ethalsurveillance)) {
				throw new Exception(__('Equipement ethalsurveillance introuvable : ', __FILE__) . init('eqid'));
			}

			if ($ethalsurveillance->getIsEnable() == 1 && $ethalsurveillance->getEqType_name() == 'ethalsurveillance') {
				$return['eq'] = array('eqName' => $ethalsurveillance->getName(), 'htmlMaster' => $eqMaster->toHtml('dashboard'), 'html' => $ethalsurveillance->toHtml('dashboard'), 'ethCumulTps' => array_values($ethalsurveillance->ethCumulTps($date['start'], $date['end'])));

			}

			ajax::success($return);
		}


    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
?>
