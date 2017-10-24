
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


//$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_ethalEqAction").sortable({axis: "y", cursor: "move", items: ".ethalEqAction", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});


/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */


$('body').on( 'click','.bt_selectCmdExpression', function() {
    var el = $(this).closest('.input-group').find('.eqLogicAttr');
    jeedom.cmd.getSelectModal({cmd: {type: ''},eqLogic: {eqType_name : ''}}, function (result) {
         el.value(result.human);
    });
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=cmdequipementtype]').on('change', function () {
    $('.cmdequipementtype').hide();
    $('.cmdequipementtype.' + $(this).value()).show();

    if ($(this).value() == 'analogique') {
        if ($('.eqLogicAttr[data-l1key=configuration][data-l2key=general]').value()==1) {
            $('.cmdequipementtype.logique.not_general').hide();            
            $('.cmdequipementtype.' + $(this).value()+'.not_general').hide();
            $('.cmdequipementtype.' + $(this).value()+'.general').show();
        } else {
            $('.cmdequipementtype.' + $(this).value()+'.general').hide();
            $('.cmdequipementtype.logique.not_general').show();            
            $('.cmdequipementtype.' + $(this).value()+'.not_general').show();
        } 
    }
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=general]').on('change',function () {
    $('.cmdequipementtype').hide();    
    $('.cmdequipementtype.' + $('.eqLogicAttr[data-l1key=configuration][data-l2key=cmdequipementtype]').value()).show();
    
    if(this.checked) {
        $('.cmdequipementtype.logique.not_general').hide();            
        $('.cmdequipementtype.' + $('.eqLogicAttr[data-l1key=configuration][data-l2key=cmdequipementtype]').value()+'.not_general').hide();
        $('.cmdequipementtype.' + $('.eqLogicAttr[data-l1key=configuration][data-l2key=cmdequipementtype]').value()+'.general').show();
    } else {
        $('.cmdequipementtype.' + $('.eqLogicAttr[data-l1key=configuration][data-l2key=cmdequipementtype]').value()+'.general').hide();
        $('.cmdequipementtype.logique.not_general').show();            
        $('.cmdequipementtype.' + $('.eqLogicAttr[data-l1key=configuration][data-l2key=cmdequipementtype]').value()+'.not_general').show();
    }
});


$('#btn_addethalEqAction').on('click', function () {
    addAction({}, 'ethalEqAction', '{{Action}}');
});


 /**************** Commun ***********/
 $("body").delegate(".listCmdAction", 'click', function () {
    var type = $(this).attr('data-type');
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
        el.value(result.human);
        jeedom.cmd.displayActionOption(el.value(), '', function (html) {
            el.closest('.' + type).find('.actionOptions').html(html);
            taAutosize();
        });
    });
});

$("body").delegate(".listAction", 'click', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.getSelectActionModal({}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      taAutosize();
  });
});
});

 $("body").delegate('.bt_removeAction', 'click', function () {
    var type = $(this).attr('data-type');
    $(this).closest('.' + type).remove();
});

 $('body').delegate('.cmdAction.expressionAttr[data-l1key=cmd]', 'focusout', function (event) {
    var type = $(this).attr('data-type')
    var expression = $(this).closest('.' + type).getValues('.expressionAttr');
    var el = $(this);
    jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
        el.closest('.' + type).find('.actionOptions').html(html);
        taAutosize();
    })
});

 $('.nav-tabs li a').on('click',function(){
   setTimeout(function(){ 
    taAutosize();
}, 50);
})

/**************** End Commun ***********/

function saveEqLogic(_eqLogic) {
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {};
    }
    _eqLogic.configuration.ethalEqAction = $('#div_ethalEqAction .ethalEqAction').getValues('.expressionAttr');
    return _eqLogic;
}


function printEqLogic(_eqLogic) {
    actionOptions = [];
    $('#div_ethalEqAction').empty();
    if (isset(_eqLogic.configuration.ethalEqAction)) {
        for (var i in _eqLogic.configuration.ethalEqAction) {
            addAction(_eqLogic.configuration.ethalEqAction[i], 'ethalEqAction', '{{Action}}');
        }
    }
    jeedom.cmd.displayActionsOption({
        params : actionOptions,
        async : false,
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success : function(data){
            for(var i in data){
                $('#'+data[i].id).append(data[i].html.html);
            }
            taAutosize();
        }
    });
}

function addAction(_action, _type, _name, _el) {
    if (!isset(_action)) {
        _action = {};
    }
    if (!isset(_action.options)) {
        _action.options = {};
    }
    var input = '';
    var div = '<div class="' + _type + '">';
    div += '<div class="form-group ">';
    div += '<label class="col-sm-1 control-label">' + _name + '</label>';
    div += '<div class="col-sm-2">';
    div += '<i class="fa fa-arrows-v pull-left" style="margin-top : 9px; margin-right: 10px; "></i>';
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour desactiver l\'action}}" />';
    //div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher pour que la commande s\'éxecute en parrallele des autres actions}}" />';
    div += '<select class="expressionAttr form-control input-sm" data-l1key="actionType" style="width:calc(100% - 50px);display:inline-block">';
    div += '<option value="etat">{{Etat}}</option>';
    div += '<option value="alarme">{{Alarme}}</option>';
    div += '</select>';
    div += '</div>';
    div += '<div class="col-sm-1">';
    div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="actionSens"/>{{Inverser}}</label>';
    div += '</div>';
    div += '<div class="col-sm-3">';
    div += '<div class="input-group">';
    div += '<span class="input-group-btn">';

    div += '<a class="btn btn-default bt_removeAction btn-sm" data-type="' + _type + '"><i class="fa fa-minus-circle"></i></a>';
    div += '</span>';
    div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-default btn-sm listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fa fa-tasks"></i></a>';
    div += '<a class="btn btn-default btn-sm listCmdAction" data-type="' + _type + '"><i class="fa fa-list-alt"></i></a>';
    div += '</span>';
    div += '</div>';
    div += '</div>';
    var actionOption_id = uniqId();
    div += '<div class="col-sm-5 actionOptions" id="'+actionOption_id+'">';
    div += '</div>';
    div += '</div>';
    if (isset(_el)) {
        _el.find('.div_' + _type).append(div);
        _el.find('.' + _type + ':last').setValues(_action, '.expressionAttr');
    } else {
        $('#div_' + _type).append(div);
        $('#div_' + _type + ' .' + _type + ':last').setValues(_action, '.expressionAttr');
    }
    actionOptions.push({
        expression : init(_action.cmd, ''),
        options : _action.options,
        id : actionOption_id
    });
}


function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<span class="cmdAttr" data-l1key="name"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="type"></span>';
    tr += '<br/>';
    tr += '<span class="cmdAttr" data-l1key="subType"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
    if (_cmd.subType == 'numeric') {
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
    }
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
