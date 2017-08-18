<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

$date = array(
    'start' => init('startDate', date('Y-m-d', strtotime('-1 month ' . date('Y-m-d')))),
    'end' => init('endDate', date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')))),
);

if (init('object_id') == '') {
    $object = object::byId($_SESSION['user']->getOptions('defaultDashboardObject'));
} else {
    $object = object::byId(init('object_id'));
}
if (!is_object($object)) {
    $object = object::rootObject();
}
if (!is_object($object)) {
    throw new Exception('{{Aucun objet racine trouvé. Pour en créer un, allez dans Générale -> Objet.<br/> Si vous ne savez pas quoi faire ou que c\'est la premiere fois que vous utilisez Jeedom n\'hésitez pas a consulter cette <a href="http://jeedom.fr/premier_pas.php" target="_blank">page</a>}}');
}

sendVarToJs('object_id', init('object_id'));

?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <li class="nav-header"><i class="fa fa-bar-chart"></i> {{Surv. Equipement}}</li>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                $allObject = object::buildTree();
                foreach ($allObject as $object_li) {
                    if ($object_li->getIsVisible() == 1 && count($object_li->getEqLogic(true, true, 'ethalsurveillance')) > 0) {
                        $margin = 15 * $object_li->parentNumber();
                        if ($object_li->getId() == init('object_id')) {
                            echo '<li class="cursor li_object active" ><a href="index.php?v=d&m=ethalsurveillance&p=panel&object_id=' . $object_li->getId() . '" style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</a></li>';
                        }else{
                            echo '<li class="cursor li_object" ><a href="index.php?v=d&m=ethalsurveillance&p=panel&object_id=' . $object_li->getId() . '" style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</a></li>';
                        }
                    }
                }
                ?>               
           </ul>
       </div>
    </div>
    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <div class="row">
            <legend>{{Information de Surveillance}}</legend>
            <div class="row">
            <div class="col-lg-12">          
                <input id="in_startDate" class="form-control input-sm in_datepicker" style="display : inline-block; width: 150px;" value="<?php echo $date['start'] ?>"/>
                <input id="in_endDate" class="form-control input-sm in_datepicker" style="display : inline-block; width: 150px;" value="<?php echo $date['end'] ?>"/>
                <a class="btn btn-success btn-sm" id='bt_validChangeDate' title="{{Attention une trop grande plage de dates peut mettre très longtemps à être calculée ou même ne pas s'afficher}}">{{Ok}}</a>

                <select class="form-control pull-right" id="sel_groupingType" style="width: 200px;">
                    <option value="cumulday">{{Cumul par jour}}</option>
                    <option value="cumulweek">{{Cumul par semaine}}</option>
                    <option value="cumulmonth">{{Cumul par mois}}</option>
                </select>
            </div>
            </div>
            <br/>
            <div class="row">
                <div class="col-lg-3" id="div_displayEquipement"></div>
                <div class="col-lg-9" id="div_graphic_tpsfct"></div>
            </div>
        </div>
        <div class="row">
            </br>
            <legend>{{Equipement surveillé}}</legend>
            <div class="col-lg-12" id="div_displayEquipementMaster"></div>
        </div>
    </div>

</div>

<?php include_file('desktop', 'cumul', 'js', 'ethalsurveillance');?>
<?php include_file('core', 'plugin.template', 'js');?>
