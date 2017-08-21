
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


$('.in_datepicker').datepicker();

$('#bt_validChangeDate').on('click', function () {
    ethGetDataAndDrawCurve($('#in_startDate').value(), $('#in_endDate').value(),$('#sel_groupingType').value());
});


$('a[data-toggle="tab"]').on('shown.bs.tab',function (e) {
    if (e.target.getAttribute('href') == "#cumultab") {
        $('#sel_groupingType').value = 'cumulday';
        ethGetDataAndDrawCurve($('#in_startDate').value(), $('#in_endDate').value(),$('#sel_groupingType').value());
    }
});

$('#sel_groupingType').change(function () {
    ethGetDataAndDrawCurve($('#in_startDate').value(), $('#in_endDate').value(),$('#sel_groupingType').value());
});



function ethGetDataAndDrawCurve(_dateStart,_dateEnd,_grouping) {
    $.ajax({
        type: 'POST',
        url: 'plugins/ethalsurveillance/core/ajax/ethalsurveillance.ajax.php',
        data: {
            action: 'ethGetData',
            eqid: $('#ul_eqLogic .li_eqLogic.active').attr('data-eqLogic_id'),
            dateStart : _dateStart,
            dateEnd : _dateEnd,
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_graphics_tpsfct').empty();
            $('#div_graphics_cpt').empty();
            el_tpsfct ='div_graphic_tpsfct'
            el_cpt = 'div_graphic_cpt'
            tooltip_tpsfct = {pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y} {{heure(s)}}</b><br/>',
                valueDecimals: 1,
            }
            tooltip_cpt = {pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
                valueDecimals: 0,
            }


            var series_tpsfct = []
            var series_cpt = []
            dataGrouping = {}
            if (_grouping == 'cumulweek') {
                dataGrouping =  {
                    approximation: "sum",
                    enabled: true,
                    forced: true,
                    units: [['week',[1]]]
                }
            }
            if (_grouping == 'cumulmonth') {
                dataGrouping =  {
                    approximation: "sum",
                    enabled: true,
                    forced: true,
                    units: [['month',[1]]]
                }
            }
            //console.log(data.result.eq.ethCumulData[0].tpsFct)
            series_tpsfct.push({
                dataGrouping : dataGrouping,
                name: data.result.eq.ethCumulData[0].tpsFctName,
                data: data.result.eq.ethCumulData[0].tpsFct,
                type: 'column',
            });
            series_cpt.push({
                dataGrouping : dataGrouping,
                name: data.result.eq.ethCumulData[0].cptName,
                data: data.result.eq.ethCumulData[0].cpt,
                type: 'column',
            });

            drawCurve(el_tpsfct, series_tpsfct,tooltip_tpsfct);
            //drawCurve(el_cpt, series_cpt,tooltip_cpt);
        }
    });
}

function drawCurve(_el, _serie,_tooltip) {
    var legend = {
        enabled: true,
        borderColor: 'black',
        borderWidth: 2,
        shadow: true
    };

    new Highcharts.StockChart({
        chart: {
            zoomType: 'x',
            renderTo: _el,
            height: 350,
            spacingTop: 0,
            spacingLeft: 0,
            spacingRight: 0,
            spacingBottom: 0
        },
        credits: {
            text: 'Ethal',
            href: 'http://ethal.fr',
        },
        navigator: {
            enabled: false
        },
        rangeSelector: {
            buttons: [{
                type: 'minute',
                count: 30,
                text: '30m'
            }, {
                type: 'hour',
                count: 1,
                text: 'H'
            }, {
                type: 'day',
                count: 1,
                text: 'J'
            }, {
                type: 'week',
                count: 1,
                text: 'S'
            }, {
                type: 'month',
                count: 1,
                text: 'M'
            }, {
                type: 'year',
                count: 1,
                text: 'A'
            }, {
                type: 'all',
                count: 1,
                text: 'Tous'
            }],
            selected: 6,
            inputEnabled: false
        },
        legend: legend,
        tooltip: _tooltip,
        yAxis: {
            format: '{value}',
            showEmpty: false,
            showLastLabel: true,
            min: 0,
            labels: {
                align: 'right',
                x: -5
            }
        },
        scrollbar: {
            barBackgroundColor: 'gray',
            barBorderRadius: 7,
            barBorderWidth: 0,
            buttonBackgroundColor: 'gray',
            buttonBorderWidth: 0,
            buttonBorderRadius: 7,
            trackBackgroundColor: 'none', trackBorderWidth: 1,
            trackBorderRadius: 8,
            trackBorderColor: '#CCC'
        },
        series: _serie
    });
}


