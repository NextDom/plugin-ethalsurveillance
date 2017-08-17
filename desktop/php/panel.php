<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('ethalsurveillance');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

$date = array(
    'start' => init('startDate', date('Y-m-d', strtotime('-1 month ' . date('Y-m-d')))),
    'end' => init('endDate', date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')))),
);

?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <li class="nav-header"><i class="fa fa-bar-chart"></i> {{Surv. Equipement}}</li>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach ($eqLogics as $eqLogic) {
                    $eqEnabled = $eqLogic->getIsEnable();
                    $opacity = ($eqEnabled) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
                    if ($eqEnabled) {
                        echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"  style="' . $opacity . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
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
