/**
 * 
 * @param {type} inputobj
 * @param {type} product_id
 * @returns {undefined}
 * This function uses jquery ui datepicker
 * requires
 * jquery.js
 * jquery-ui.js / css
 * jquery-ui.*.css
 */
var productDataCacheMap = {};
var allinputArray = new Array();
var dayNames = new Array("周日", "周一", "周二","周三", "周四", "周五", "周六");

function initPriceCalendar(inputobj, onSelectDate, endDateId) {
    var product_id = parseInt($(inputobj).attr('pdid'));
    var duration = parseInt($(inputobj).attr('pduration'));
    allinputArray[allinputArray.length] = inputobj;
    var aModel;
    if (productDataCacheMap[product_id] === undefined) {
        aModel = new ProductDataModel(product_id);
        productDataCacheMap[product_id] = aModel;
    } else {
        aModel = productDataCacheMap[product_id];
    }
    
    var today = new Date();
    var maxDate = new Date(new Date(today).setMonth(today.getMonth() + 8));
    $(inputobj).datepicker({
        changeMonth: true,
        changeYear: false,
        beforeShowDay: aModel.validateAvailable,
        onChangeMonthYear: aModel.updateMonthlyPrice,
        onSelect:function(dateText, inst) {
            onSelectDate(dateText, inst);
            var date_arr = dateText.split('-');
            var curDate = new Date(parseInt(date_arr[0]), parseInt(date_arr[1])-1, parseInt(date_arr[2]), 0, 0, 0, 0);
            var endDate = new Date(new Date(curDate).setDate(curDate.getDate() + duration));
            
            $('#'+endDateId).html(endDate.getFullYear()+'-'+(endDate.getMonth()+1)+'-'+endDate.getDate()+' '+dayNames[endDate.getDay()]);
        },
        numberOfMonths: 1,
        minDate: today,
        maxDate: maxDate,
        dateFormat: 'yy-mm-dd'
    });
}

function updateMonthlyPrice(year, month, inst) {

}

function ProductDataModel(productid) {
    // private property
    var product_id = productid;
    var scheduleData = {};

    // private constructor 
    var __construct = function() {
        var t = new Date();
//        strs = t.toLocaleDateString().split("/"); //字符分割  IE and Chrome behave differently
        var y = t.getFullYear();
        var m = t.getMonth()+1;
        var dd = t.getDate();

        //initialize this month
        ajaxGetSchedule(product_id, y, m, scheduleData);
    }();

    this.validateAvailable = function(date) {
        var y = date.getFullYear();
        var m = date.getMonth()+1;
        var d = parseInt(date.getDate());
//        alert(scheduleData);
        
        if (scheduleData[y] === undefined || scheduleData[y][m] === undefined) {
            return [false, "date-loading", "正在努力加载"];
        }
        var monthlyData = scheduleData[y][m];
        
//        alert(JSON.stringify(monthlyData));
        if (!monthlyData.result) {
            return [false, "unav-date", "当日不出团"];
        }

        var priceData = monthlyData.content.price;
        if (priceData === undefined || priceData[d - 1] === undefined || priceData[d - 1][0] === -1) {
            return [false, "unav-date", "当日不出团"];
        }

        return [true, "av-date-hightlight", "可选出发日期,最低价 $"+(Math.min(priceData[d - 1][3],priceData[d - 1][2])/100).toFixed(2)];
    };

    this.updateMonthlyPrice = function(year, month, inst) {
        if (scheduleData[month] === undefined) {
            ajaxGetSchedule(product_id, year, month, scheduleData);
        }
    };

    //getter
    this.getProductId = function() {
        return product_id;
    }
}


function ajaxGetSchedule(product_id, y, m, scheduleData) {
//        var mm = length2month(m);
    $.ajax({
        url: "../../../product/query/schedule/" + product_id,
        type: "POST",
        async: true, //block the browser while loading data (should use refresh later on)
        data: {'year': y, 'month': m},
        dataType: "json",
        error: function() {
            //handle non 200 http code
            if(scheduleData[y] === undefined){
                scheduleData[y] = new Array();
            }
            scheduleData[y] = {result:false};
        },
        success: function(data)
        {
//                alert(JSON.stringify(data));
            if(scheduleData[y] === undefined){
                scheduleData[y] = new Array();
            }
            scheduleData[y][m] = data;
//                alert(JSON.stringify(scheduleData[mm]));
        },
        complete: function() {
            refreshAllCalendar();
        }
    });
}
;

function refreshAllCalendar() {
    for (var i = 0; i < allinputArray.length; i++) {
        var thisInput = allinputArray[i];
        $(thisInput).datepicker('refresh');
    }
}

function getScheduleDate(product_id, year, month) {
    var ajaxRes;

    return ajaxRes;
}