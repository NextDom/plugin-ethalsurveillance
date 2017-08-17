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
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
foreach ($eqLogics as $eqLogic) {
    $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"  style="' . $opacity . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
           </ul>
       </div>
   </div>

   <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend>{{Mes equipements à surveiller}}</legend>
  <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <i class="fa fa-plus-circle" style="font-size : 6em;color:#94ca02;"></i>
        <br>
        <span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#94ca02">{{Ajouter}}</span>
    </div>
    <?php
foreach ($eqLogics as $eqLogic) {
    $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="text-align: center; background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
    echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95" />';
    echo "<br>";
    echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;">' . $eqLogic->getHumanName(true, true) . '</span>';
    echo '</div>';
}
?>
  </div>
</div>

<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
	<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
  <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
  <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
  <ul class="nav nav-tabs" role="tablist" id='etheqtab'>
    <li role="presentation"><a class="eqLogicAction cursor" aria-controls="tab" role="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="tab" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
    <form class="form-horizontal">
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
                <div class="col-sm-3">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-3">
                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                        <option value="">{{Aucun}}</option>
                        <?php
foreach (object::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
                   </select>
               </div>
           </div>
	           <div class="form-group">
                <label class="col-sm-3 control-label">{{Catégorie}}</label>
                <div class="col-sm-8">
                    <?php
                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                    }
                    ?>
               </div>
           </div>

  <div class="form-group">
		<label class="col-sm-3 control-label"></label>
		<div class="col-sm-9">
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" />{{Activer}}</label>
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" />{{Visible}}</label>
		</div>
	</div>
  <hr>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Type de commande}}</label>
            <div class="col-sm-4">
                <select class="eqLogicAttr form-control" data-l1key='configuration' data-l2key='cmdequipementtype'>
                    <option value='logique'>Logique</option>
                    <option value='analogique' >Analogique</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Commande équipement}}</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="cmdequipement"/>
                    <span class="input-group-btn">
                        <a class="btn btn-default bt_selectCmdExpression"><i class="fa fa-list-alt"></i></a>
                    </span>
                </div>
            </div>
            <div class="cmdequipementtype analogique" style="display: none;">
                <div class="col-sm-4" >
                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="general" />{{Compteur Général}}</label>
                </div> 
            </div> 
        </div>
        <div class="cmdequipementtype analogique general" style="display: none;">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Heure de surveillance prévue +/- 2 min (HHMM)}}<span> (1<upper>*</upper>)</label>
                <div class="col-sm-2">
                    <div class="input-group">
                        <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="debutheure" />
                    </div>
                </div>
                <label class="col-sm-3 control-label">{{Valeur surveillance active}}</label>
                <div class="col-sm-2">
                    <div class="input-group">
                        <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="puissance" />
                    </div>
                </div>
            </div>
        </div>
        <div class="cmdequipementtype analogique not_general" style="display: none;">
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Valeur surveillance inactive}}</label>
                <div class="col-sm-1">
                    <div class="input-group">
                        <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="minpuissance" />
                    </div>
                </div>
                <label class="col-sm-2 control-label">{{Délai valeur surveillance inactive (min)}}</label>
                <div class="col-sm-1">
                    <div class="input-group">
                        <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="minpuissancedelai" />
                    </div>
                </div>
                <label class="col-sm-2 control-label">{{Valeur surveillance active}}</label>
                <div class="col-sm-1">
                    <div class="input-group">
                        <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="maxpuissance" />
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div>
            <ul class="nav nav-tabs" role="tablist">
                <li role="defaut" class="active"><a href="#defaut" role="tab" aria-controls="defaut" aria-expanded="true" data-toggle="tab" >{{Défaut}}</a></li>
                <li role="lundi"><a href="#lundi" role="tab" aria-controls="lundi" aria-expanded="false" data-toggle="tab" >{{Lundi}}</a></li>
                <li role="mardi"><a href="#mardi" aria-controls="mardi" aria-expanded="false" data-toggle="tab" >{{Mardi}}</a></li>
                <li role="mercredi"><a href="#mercredi" aria-controls="mercredi" aria-expanded="false" data-toggle="tab" >{{Mercredi}}</a></li>
                <li role="jeudi"><a href="#jeudi" aria-controls="jeudi" aria-expanded="false" data-toggle="tab" >{{Jeudi}}</a></li>
                <li role="vendredi"><a href="#vendredi" aria-controls="vendredi" aria-expanded="false" data-toggle="tab" >{{Vendredi}}</a></li>
                <li role="samedi"><a href="#samedi" aria-controls="samedi" aria-expanded="false" data-toggle="tab" >{{Samedi}}</a></li>
                <li role="dimanche"><a href="#dimanche" aria-controls="dimanche" aria-expanded="false" data-toggle="tab" >{{Dimanche}}</a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="defaut" aria-labelledby="defaut-tab">
                    <br>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps mini surveillance active (min)}}<span> (2<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="tempsmini" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps max surveillance active (min)}}<span> (4<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="tempsmax" />
                            </div>
                        </div>
                    </div>
                    <div class="cmdequipementtype logique not_general" style="display: none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance inactive (HHMM)}}<span> (8<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="expectedstoppedtime" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance active (HHMM)}}(16<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="expectedstartedtime" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Valeur compteur haut}}<span> (32<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="cptalarmehaute" />
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="lundi" aria-labelledby="lundi-tab">
                    <br>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps mini surveillance active (min)}}<span> (2<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="1tempsmini" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps max surveillance active (min)}}<span> (4<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="1tempsmax" />
                            </div>
                        </div>
                    </div>
                    <div class="cmdequipementtype logique not_general" style="display: none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance inactive (HHMM)}}<span> (8<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="1expectedstoppedtime" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance active (HHMM)}}(16<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="1expectedstartedtime" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Valeur compteur haut}}<span> (32<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="1cptalarmehaute" />
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="mardi" aria-labelledby="mardi-tab">
                    <br>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps mini surveillance active (min)}}<span> (2<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="2tempsmini" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps max surveillance active (min)}}<span> (4<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="2tempsmax" />
                            </div>
                        </div>
                    </div>
                    <div class="cmdequipementtype logique not_general" style="display: none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance inactive (HHMM)}}<span> (8<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="2expectedstoppedtime" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance active (HHMM)}}(16<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="2expectedstartedtime" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Valeur compteur haut}}<span> (32<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="2cptalarmehaute" />
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="mercredi" aria-labelledby="mercredi-tab">
                    <br>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps mini surveillance active (min)}}<span> (2<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="3tempsmini" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps max surveillance active (min)}}<span> (4<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="3tempsmax" />
                            </div>
                        </div>
                    </div>
                    <div class="cmdequipementtype logique not_general" style="display: none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance inactive (HHMM)}}<span> (8<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="3expectedstoppedtime" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance active (HHMM)}}(16<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="3expectedstartedtime" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Valeur compteur haut}}<span> (32<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="3cptalarmehaute" />
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="jeudi" aria-labelledby="jeudi-tab">
                    <br>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps mini surveillance active (min)}}<span> (2<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="4tempsmini" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps max surveillance active (min)}}<span> (4<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="4tempsmax" />
                            </div>
                        </div>
                    </div>
                    <div class="cmdequipementtype logique not_general" style="display: none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance inactive (HHMM)}}<span> (8<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="4expectedstoppedtime" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance active (HHMM)}}(16<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="4expectedstartedtime" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Valeur compteur haut}}<span> (32<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="4cptalarmehaute" />
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="vendredi" aria-labelledby="vendredi-tab">
                    <br>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps mini surveillance active (min)}}<span> (2<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="5tempsmini" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps max surveillance active (min)}}<span> (4<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="5tempsmax" />
                            </div>
                        </div>
                    </div>
                    <div class="cmdequipementtype logique not_general" style="display: none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance inactive (HHMM)}}<span> (8<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="5expectedstoppedtime" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance active (HHMM)}}(16<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="5expectedstartedtime" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Valeur compteur haut}}<span> (32<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="5cptalarmehaute" />
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="samedi" aria-labelledby="samedi-tab">
                    <br>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps mini surveillance active (min)}}<span> (2<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="6tempsmini" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps max surveillance active (min)}}<span> (4<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="6tempsmax" />
                            </div>
                        </div>
                    </div>
                    <div class="cmdequipementtype logique not_general" style="display: none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance inactive (HHMM)}}<span> (8<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="6expectedstoppedtime" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance active (HHMM)}}(16<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="6expectedstartedtime" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Valeur compteur haut}}<span> (32<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="6cptalarmehaute" />
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="dimanche" aria-labelledby="dimanche-tab">
                    <br>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps mini surveillance active (min)}}<span> (2<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="7tempsmini" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Temps max surveillance active (min)}}<span> (4<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="7tempsmax" />
                            </div>
                        </div>
                    </div>
                    <div class="cmdequipementtype logique not_general" style="display: none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance inactive (HHMM)}}<span> (8<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="7expectedstoppedtime" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Heure prévue surveillance active (HHMM)}}(16<upper>*</upper>)</label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="7expectedstartedtime" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Valeur compteur haut}}<span> (32<upper>*</upper>)</label>
                        <div class="col-sm-5">
                            <div class="input-group">
                            <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="7cptalarmehaute" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</fieldset>
</form>
<div>(*) {{Code d'alarme}}</div>
</div>
<div role="tabpanel" class="tab-pane" id="commandtab">
    <br/>
<table id="table_cmd" class="table table-bordered table-condensed">
    <thead>
        <tr>
            <th>{{Nom}}</th><th>{{Type}}</th><th>{{Options}}</th><th></th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
</div>

</div>

</div>


</div>

</div>


<?php include_file('desktop', 'ethalsurveillance', 'js', 'ethalsurveillance');?>
<?php include_file('core', 'plugin.template', 'js');?>
