//function CalendarInit() {
//    //c:容器,y:年,m:月,a:出发时间json,f:是否显示双日历,fu:回调调
//    var para = {'c': 'calendarcontainer',
////<<<<<<< HEAD
////            'y': 2014,
////            'm': 8,
////            'a': {
////                'd1': '2014-01-30', //最早时间
////                'd2': '2015-12-31'//最晚时间
////            },
////            'f': 0, //显示双日历用1，单日历用0
////    //		 'clickfu':function (to) {//回调函数，to为点击对象，点击日期是调用的函数,参数to为点击的日期的节点对象，可以把用户选定的日期通过此函数存入服务端或cookies，具体请自行编写
////    //               if(to.id!=""){alert(to.id)}	 
////    //			 },
////    
////            'showFu': function(d,calendardata) {	//回调函数，d为要显示的当前日期，主要用于判断是否要在该日期的格子里显示出指定的内容，在日期格子里额外显示内容的函数,返回值必须为字符串，参数d为显示的日期对象（日期类型）calendardata:该月的价格详情
////                var t = new Date();
////                var strs = new Array(); //定义一数组
////                strs = d.toLocaleDateString().split("/"); //字符分割 
////                var y = strs[0];
////                var day = strs[2];
////                //what is 0 for? don't you need to check if mm is creater than 1 letter?
////                if (strs[1].lenght < 2) {//add check
////                    var m = "0" + strs[1];
////                }
////                var mm = strs[1];
////                var ym = y + m;
////                //alert(day);
////                //alert(calendardata[day-1]); 
//////                if(calendardata!="")
//////                {
//////                    var cd = calendardata;
//////                }else{var cd="";}
////                
////                //TODO, mm? should use day not month?
////               // alert(t.toLocaleDateString()+calendardata[mm-1]);
////                
//////                if (t.toLocaleDateString() == d.toLocaleDateString()) {
//////                        return "<br/>今天";//正常
//////                        //return "<br/>今天"+calendardata[2][1];//calendardata[2]为任意写的，总之这样就是会出现那个undefined
//////                    }
//////                    else {
//////                     return "";	 
//////                    }
////                    
////                  //如果以上可以正常显示，下方去掉注释，日历中的价格就可以显示出来了
////                if(calendardata[day-1]=="-1")
////                {
////                    if (t.toLocaleDateString() == d.toLocaleDateString()) {
////                        return "<br/>今天";
////                    }
////                    else
////                    {
////                        return "";
////                    }
////                }
////                else 
////                {
////                    if (t.toLocaleDateString() == d.toLocaleDateString()) {
////                        return "<br/>今天"+
////                                "<br/>成人："+Math.min(calendardata[day-1][2],calendardata[day-1][3])+
////                                "<br/>儿童：" + calendardata[day-1][4];
////                    }
////                    else
////                    {
////                        return "<br/>成人："+Math.min(calendardata[day-1][2],calendardata[day-1][3])+
////                                "<br/>儿童：" + calendardata[day-1][4];
////                    }
////                }
////              }    
////=======
//        'y': 2014,
//        'm': 8,
//        'a': {
//            'd1': '2014-01-30', //最早时间
//            'd2': '2015-12-31'//最晚时间
//        },
//        'f': 0, //显示双日历用1，单日历用0
//        'clickfu': function(to) {//回调函数，to为点击对象，点击日期是调用的函数,参数to为点击的日期的节点对象，可以把用户选定的日期通过此函数存入服务端或cookies，具体请自行编写
////            if (to.id != "") {
////                alert(to.id);
////            }
//        },
//        'showFu': function(d, calendardata) {	//回调函数，d为要显示的当前日期，主要用于判断是否要在该日期的格子里显示出指定的内容，在日期格子里额外显示内容的函数,返回值必须为字符串，参数d为显示的日期对象（日期类型）calendardata:该月的价格详情
//            var t = new Date();
//            var strs = new Array(); //定义一数组
//            strs = d.toLocaleDateString().split("/"); //字符分割 
//            var y = strs[2];
//            //what is 0 for? don't you need to check if mm is creater than 1 letter?
//            if (strs[0].lenght < 2) {//add check
//                var m = "0" + strs[0];
//            }
//            var mm = strs[0];
//            var dd = parseInt(strs[1]);
//
////            alert(calendardata[dd - 1]);
//            if (calendardata[dd - 1][0] === -1 || d < t)
//            {
//                return "";
//            }
//            else
//            {
//                return "<br>" + (Math.min(calendardata[dd - 1][2], calendardata[dd - 1][3]) / 100).toFixed(2);
//            }
////>>>>>>> origin/before-rm-commented-region
//        }
//    };
//    return para;
//}

function CreateCalendar(product_id, para, paraJsonName) {//c:容器,y:年,m:月,a:出发时间json,f:是否显示双日历,clickfu:点击事件回调函数,showFu:在日历里显示附加内容的回调函数
    var c = para.c;
    var y = para.y;
    var m = para.m;
    var calendardata = "";
    if (arguments.length != 3) {
        var m = para.m;
    }
    else if (arguments[2] == "pre") {
        var m = para.m = para.m - 1;
    }
    else if (arguments[2] == "next") {
        var m = para.m = para.m + 1;
    }
    else {
        var m = para.m;
    }

    var mm = "";
    mm = m < 10 ? "0" + m : m;
    //ajax get calendar information
    $.ajax({
        // + 1 should be replaced with product_id (get it from the hidden input element)

        url: "../../../product/query/schedule/" + product_id,
        type: "POST",
        data: {'date': y + mm},
        dataType: "json",
        error: function() {
            //TODO handle non 200 http code

        },
        success: function(data)
        {
            if (data.result) {
                //{"result":true,"content":{"always_available":true,"start_date":"2014.6.1~2014.9.1\/2014.3.1~2014.5.1","duration":2,"depart_weekday":"0\/0","price":[[10001,9001,8001,7001,0],[-1],[-1],[-1],[-1],[-1],[-1],[-1],[-1],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[10001,9001,8001,7001,0],[-1]]}}
                var content = data.content;
                //var always_available = content.always_available;
                var price = content.price;  //it is a size 30or31 array with price for each date in this month
                calendardata = price;


                var a = para.a;
                var f = para.f;
                var clickfu = para.clickfu;
                var showFu = para.showFu;

                var today = new Date();
                today = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                if (y == 0 || m == 0) {
                    y = today.getFullYear();
                    m = today.getMonth() + 1;
                }
                ;
                //var dmin=Date.parse(a.first().attr('d').replace(/-/g, '/')),dmax =Date.parse(a.last().attr('d').replace(/-/g, '/'));
                var dmin = a.d1.replace(/-/g, "/"), dmax = a.d2.replace(/-/g, "/");

                var i1 = 0, i2 = 0, i3 = 0, d2;
                var d1 = new Date(dmin),
                        today = today.DateToParse();
                if (Date.parse(d1.getFullYear() + '/' + (d1.getMonth() + 1) + '/1') > Date.parse(new Date(y, m - 1, 1))) {
                    y = d1.getFullYear();
                    m = d1.getMonth() + 1;
                }
                $('#' + c).html('');
                //农历
                var ca = new Calendar();
                tmp = '';
                for (var i = 0; i <= f; i++) {
                    d1 = new Date(y, m - 1 + i);
                    y = d1.getFullYear();
                    m = d1.getMonth() + 1;

                    tmp += '<table cellpadding="0">';
                    tmp += '<tr class="month"><th colspan="7"><div class="clearfix"><div class="prevMonth">';
                    if (i == 0) {
                        i1 = Date.parse(y + '/' + m + '/1');
                        d1 = new Date(dmin);
                        if (Date.parse(d1.getFullYear() + '/' + (d1.getMonth() + 1) + '/1') < i1) {
                            d1 = new Date(y, m - 2 - f, 1);
                            tmp += '<a class="prev" href="javascript:;" onclick="CreateCalendar(' + paraJsonName + ',\'' + paraJsonName + '\',\'pre\');" title="上个月">&nbsp;</a>';
                        } else {
                            tmp += '<a class="prev0" href="javascript:;" title="上个月">&nbsp;</a>';
                        }
                    }
                    tmp += '</div>';
                    tmp += '<div class="dates"><em>' + y + '</em>年<em>' + m + '</em>月</div>';
                    tmp += '<div class="nextMonth">';
                    if (i == f) {
                        i1 = Date.parse(y + '/' + m + '/1');
                        d1 = new Date(dmax);
                        i2 = Date.parse(d1.getFullYear() + '/' + (d1.getMonth() + 1) + '/1');
                        if (i2 > i1) {
                            d1 = new Date(y, Date.parse(new Date(y, m + 1, 1)) > i2 ? m - f : m, 1);
                            tmp += '<a class="next" href="javascript:;" onclick="CreateCalendar(' + paraJsonName + ',\'' + paraJsonName + '\',\'next\');" title="下个月">&nbsp;</a>';
                        } else {
                            tmp += '<a class="next0" href="javascript:;" title="下个月">&nbsp;</a>';
                        }
                    }
                    tmp += '</div></div></th></tr>';
                    tmp += '  <tr class="week">';
                    tmp += '    <th class="weekEnd">星期日</th>';
                    tmp += '    <th>星期一</th>';
                    tmp += '    <th>星期二</th>';
                    tmp += '    <th>星期三</th>';
                    tmp += '    <th>星期四</th>';
                    tmp += '    <th>星期五</th>';
                    tmp += '    <th class="weekEnd">星期六</th>';
                    tmp += '  </tr>';
                    var maxdays = (new Date(Date.parse(new Date(y, m, 1)) - 86400000)).getDate();  //当前月的天数
                    d1 = new Date(y, m - 1); //要显示的日期
                    i1 = d1.getDay(); //这个月的第一天是星期几
                    for (var j = 1; j <= 6; j++) {
                        tmp += '<tr>';
                        for (var k = 1; k <= 7; k++) {
                            i2 = (j - 1) * 7 + k - i1;
                            if (i2 < 1 || i2 > maxdays) {
                                tmp += '<td></td>';
                            } else {
                                i3 = Date.parse(new Date(y, m - 1, i2));
                                d1 = new Date(i3);
                                //农历(ll的值为农历)
                                //ca=new Calendar(y,m-1,i2)
                                var ll = ca.getlf(d1);
                                if (ll == '') {
                                    ll = ca.getsf(d1);
                                    if (ll == '') {
                                        ll = ca.getst(d1);
                                        if (ll == '')
                                            ll = ca.getls(d1)[3];
                                    }
                                }
                                tmp += '<td'
                                if (today == i3) {
                                    tmp += ' class="cur"'
                                }
                                ;
                                if (i3 < dmin || i3 > dmax) {
                                    tmp += '><p><em>' + i2 + '</em></td>';
                                } else {
                                    tmp += ' week="' + (k - 1) + '" id="' + y + '-' + m + '-' + i2 + '" title="' + ca.getl(d1, false) + ' ' + ca.getst(d1) + ' ' + ca.getsf(d1) + ' ' + ca.getlf(d1) + '"><p><em>' + i2 + '</em><em class="nl">' + ll + '</em>' + (function(t) {
                                        if ($.isFunction(showFu)) {
                                            return showFu(t, calendardata);
                                        } else {
                                            return ""
                                        }
                                    }(new Date(y, m - 1, i2))) + '</p></td>';

                                }
                            }
                        }
                        tmp += '</tr>';
                    }
                    tmp += '</table>';

                }
                $('#' + c).append(tmp);
                if ($.isFunction(clickfu)) {
                    //fu(this);
                    $('#' + c + ' td').click(function() {
                        clickfu(this);
                    }).hover(function() {
                        $(this).addClass("hover");
                    },
                            function() {
                                $(this).removeClass("hover");
                            }
                    );
                }


            }
            //TODO if result != true?
        }
    });

}

function Calendar() {
    /* ***
     
     该对象用于获取日期对应的节气、节日
     三个方法如下:
     .getl(date,lockNum)  返回日期对应的数字或农历表示
     .getst(date)  获取日期对应的节气,若不是则为空.
     .getlf(date)  获取阴历节日,若不是则为空.
     .getls(date)  获取阴历数组表示 结果["阴历年", "属相", "阴历月", "阴历日"] 
     .getsf(date)  获取阳历节日,若不是则为空.
     */
    var unlockNum = true; //是否开启数字格式值返回 如：2011-12-15返回值为1121 [false则为 冬月廿十一]
    var solarTerm = new Array("小寒", "大寒", "立春", "雨水", "惊蛰", "春分", "清明", "谷雨", "立夏", "小满", "芒种", "夏至", "小暑", "大暑", "立秋", "处暑", "白露", "秋分", "寒露", "霜降", "立冬", "小雪", "大雪", "冬至"); //24节气
    var DifferenceInMonth = new Array(1272060, 1275495, 1281180, 1289445, 1299225, 1310355, 1321560, 1333035, 1342770, 1350855, 1356420, 1359045,
            1358580, 1355055, 1348695, 1340040, 1329630, 1318455, 1306935, 1297380, 1286865, 1277730, 1274550, 1271556); //24节气值
    var SF = {"0101": "元旦", "0214": "情人节", "0305#": "学雷锋纪念日", "0308": "妇女节", "0312#": "植树节", "0315#": "消费者权益日", "0401#": "愚人节", "0501": "劳动节", "0504": "青年节", "0601": "儿童节", "0701": "建党节", "0801": "建军节", "0910": "教师节", "1001": "国庆节", "1224": "平安夜", "1225": "圣诞节"}; //阳历节日
    var LF = {"0101": "春节", "0115": "元宵节", "0505": "端午节", "0815": "中秋节", "0707": "七夕", "0909": "重阳节", "1010#": "感恩节", "1208#": "腊八节", "0100": "除夕"}; //阴历节日
    var CalendarData = new Array(100), madd = new Array(12), tgString = "甲乙丙丁戊己庚辛壬癸", dzString = "子丑寅卯辰巳午未申酉戌亥", numString = "一二三四五六七八九十", monString = "正二三四五六七八九十冬腊", weekString = "日一二三四五六", sx = "鼠牛虎兔龙蛇马羊猴鸡狗猪", cYear, cMonth, cDay, TheDate;
    CalendarData = new Array(0xA4B, 0x5164B, 0x6A5, 0x6D4, 0x415B5, 0x2B6, 0x957, 0x2092F, 0x497, 0x60C96, 0xD4A, 0xEA5, 0x50DA9, 0x5AD, 0x2B6, 0x3126E, 0x92E, 0x7192D, 0xC95, 0xD4A, 0x61B4A, 0xB55, 0x56A, 0x4155B, 0x25D, 0x92D, 0x2192B, 0xA95, 0x71695, 0x6CA, 0xB55, 0x50AB5, 0x4DA, 0xA5B, 0x30A57, 0x52B, 0x8152A, 0xE95, 0x6AA, 0x615AA, 0xAB5, 0x4B6, 0x414AE, 0xA57, 0x526, 0x31D26, 0xD95, 0x70B55, 0x56A, 0x96D, 0x5095D, 0x4AD, 0xA4D, 0x41A4D, 0xD25, 0x81AA5, 0xB54, 0xB6A, 0x612DA, 0x95B, 0x49B, 0x41497, 0xA4B, 0xA164B, 0x6A5, 0x6D4, 0x615B4, 0xAB6, 0x957, 0x5092F, 0x497, 0x64B, 0x30D4A, 0xEA5, 0x80D65, 0x5AC, 0xAB6, 0x5126D, 0x92E, 0xC96, 0x41A95, 0xD4A, 0xDA5, 0x20B55, 0x56A, 0x7155B, 0x25D, 0x92D, 0x5192B, 0xA95, 0xB4A, 0x416AA, 0xAD5, 0x90AB5, 0x4BA, 0xA5B, 0x60A57, 0x52B, 0xA93, 0x40E95);
    madd[0] = 0;
    madd[1] = 31;
    madd[2] = 59;
    madd[3] = 90;
    madd[4] = 120;
    madd[5] = 151;
    madd[6] = 181;
    madd[7] = 212;
    madd[8] = 243;
    madd[9] = 273;
    madd[10] = 304;
    madd[11] = 334;
    function GetBit(m, n) {
        return(m >> n) & 1
    }
    function e2c() {
        TheDate = (arguments.length != 3) ? new Date() : new Date(arguments[0], arguments[1], arguments[2]);
        var total, m, n, k;
        var isEnd = false;
        var tmp = TheDate.getFullYear();
        total = (tmp - 1921) * 365 + Math.floor((tmp - 1921) / 4) + madd[TheDate.getMonth()] + TheDate.getDate() - 38;
        if (TheDate.getYear() % 4 == 0 && TheDate.getMonth() > 1) {
            total++
        }
        for (m = 0; ; m++) {
            k = (CalendarData[m] < 0xfff) ? 11 : 12;
            for (n = k; n >= 0; n--) {
                if (total <= 29 + GetBit(CalendarData[m], n)) {
                    isEnd = true;
                    break
                }
                total = total - 29 - GetBit(CalendarData[m], n)
            }
            if (isEnd)
                break
        }
        cYear = 1921 + m;
        cMonth = k - n + 1;
        cDay = total;
        if (k == 12) {
            if (cMonth == Math.floor(CalendarData[m] / 0x10000) + 1) {
                cMonth = 1 - cMonth
            }
            if (cMonth > Math.floor(CalendarData[m] / 0x10000) + 1) {
                cMonth--
            }
        }
    }
    function GetcDateString() {
        var P = [19416, 19168, 42352, 21717, 53856, 55632, 91476, 22176, 39632, 21970, 19168, 42422, 42192, 53840, 119381, 46400, 54944, 44450, 38320, 84343, 18800, 42160, 46261, 27216, 27968, 109396, 11104, 38256, 21234, 18800, 25958, 54432, 59984, 28309, 23248, 11104, 100067, 37600, 116951, 51536, 54432, 120998, 46416, 22176, 107956, 9680, 37584, 53938, 43344, 46423, 27808, 46416, 86869, 19872, 42448, 83315, 21200, 43432, 59728, 27296, 44710, 43856, 19296, 43748, 42352, 21088, 62051, 55632, 23383, 22176, 38608, 19925, 19152, 42192, 54484, 53840, 54616, 46400, 46496, 103846, 38320, 18864, 43380, 42160, 45690, 27216, 27968, 44870, 43872, 38256, 19189, 18800, 25776, 29859, 59984, 27480, 21952, 43872, 38613, 37600, 51552, 55636, 54432, 55888, 30034, 22176, 43959, 9680, 37584, 51893, 43344, 46240, 47780, 44368, 21977, 19360, 42416, 86390, 21168, 43312, 31060, 27296, 44368, 23378, 19296, 42726, 42208, 53856, 60005, 54576, 23200, 30371, 38608, 19415, 19152, 42192, 118966, 53840, 54560, 56645, 46496, 22224, 21938, 18864, 42359, 42160, 43600, 111189, 27936, 44448];
        var tmp = "";
        if (unlockNum == false) {
            tmp += tgString.charAt((cYear - 4) % 10);
            tmp += dzString.charAt((cYear - 4) % 12);
            tmp += "(";
            tmp += sx.charAt((cYear - 4) % 12);
            tmp += ")年";
        }
        if (cMonth < 1) {
            if (unlockNum == false) {
                tmp += "(闰)";
                tmp += monString.charAt(-cMonth - 1);
            } else {
                //tmp += "(闰)"; //**
                tmp += cMonth < 10 ? "0" + (cMonth - 2) : (cMonth - 2);  //monString.charAt(-cMonth - 1);//**        
            }
        } else {
            if (unlockNum == false) {
                tmp += monString.charAt(cMonth - 1);
            } else {
                tmp += cMonth < 10 ? "0" + cMonth : cMonth;  //monString.charAt(cMonth - 1);//**
            }
        }
        if (unlockNum == false) {
            tmp += "月";
            tmp += (cDay < 11) ? "初" : ((cDay < 20) ? "十" : ((cDay < 30) ? "廿" : "三十"));
            if (cDay % 10 != 0 || cDay == 10) {
                tmp += numString.charAt((cDay - 1) % 10);
            }
        } else {
            tmp += (cDay < 10 ? "0" + cDay : cDay);
            if (cMonth == 12 && cDay == ((P[cYear - 1900] & (65536 >> 12)) ? 30 : 29)) {
                tmp = "0100";
            }
        }
        return tmp;
    }
    //获取阴历	
    function GetLunarDay(solarYear, solarMonth, solarDay) {
        if (solarYear < 1921 || solarYear > 2020) {
            return""
        } else {
            solarMonth = (parseInt(solarMonth) > 0) ? (solarMonth - 1) : 11;
            e2c(solarYear, solarMonth, solarDay);
            return GetcDateString()
        }
    }

    //获取节气
    this.getst = function(date) {
        var DifferenceInYear = 31556926;
        var BeginTime = new Date(1901 / 1 / 1);
        BeginTime.setTime(947120460000);
        for (; date.getFullYear() < BeginTime.getFullYear(); ) {
            BeginTime.setTime(BeginTime.getTime() - DifferenceInYear * 1000);
        }
        for (; date.getFullYear() > BeginTime.getFullYear(); ) {
            BeginTime.setTime(BeginTime.getTime() + DifferenceInYear * 1000);
        }
        for (var M = 0; date.getMonth() > BeginTime.getMonth(); M++) {
            BeginTime.setTime(BeginTime.getTime() + DifferenceInMonth[M] * 1000);
        }
        if (date.getDate() > BeginTime.getDate()) {
            BeginTime.setTime(BeginTime.getTime() + DifferenceInMonth[M] * 1000);
            M++;
        }
        if (date.getDate() > BeginTime.getDate()) {
            BeginTime.setTime(BeginTime.getTime() + DifferenceInMonth[M] * 1000);
            M == 23 ? M = 0 : M++;
        }
        var JQ = "";
        if (date.getDate() == BeginTime.getDate()) {
            JQ += solarTerm[M];
        }
        return JQ;
    }
    //获取阳历节日
    this.getsf = function() {
        var m, d;
        if (arguments.length == 2) {
            m = arguments[0];
            d = arguments[1];
        } else {
            m = arguments[0].getMonth() + 1;
            d = arguments[0].getDate();
        }
        m = SF[(m < 10 ? "0" + m : m.toString()) + (d < 10 ? "0" + d : d.toString())];
        return m ? m : '';
    }
    //获取阴历 D当前日期 lockNum是否开启数字格式值返回 //var D = new Date();
    this.getl = function(D, lockNum) {
        unlockNum = lockNum;
        if (lockNum == false || lockNum == "false") {
            numString = "一二三四五六七八九十";
            monString = "正二三四五六七八九十冬腊";
        }
        var yy = D.getFullYear();
        var mm = D.getMonth() + 1;
        var dd = D.getDate();
        var ww = D.getDay();
        var ss = parseInt(D.getTime() / 1000);
        if (yy < 100)
            yy = "19" + yy;
        return GetLunarDay(yy, mm, dd);
    }
    //获取阴历节日
    this.getlf = function(D) {
        var dayT = LF[this.getl(D, true)];
        return dayT ? dayT : "";
    }
    //获取阴历数组
    this.getls = function(D) {
        var tmp = this.getl(D, false);
        var t = ['', '', '', ''];
        var s = tmp.indexOf('年');
        if (s != -1) {
            t[0] = tmp.substring(0, 2);
            t[1] = tmp.substring(3, 4);
            tmp = tmp.substring(6);
            s = tmp.indexOf('月');
            t[2] = tmp.substring(0, s);
            t[3] = tmp.substring(s + 1);
        }
        return t;
    }
}

Date.prototype.dateDiff = function(interval, objDate2) {
    var d = this, i = {}, t = d.getTime(), t2 = objDate2.getTime();
    i['y'] = objDate2.getFullYear() - d.getFullYear();
    i['q'] = i['y'] * 4 + Math.floor(objDate2.getMonth() / 4) - Math.floor(d.getMonth() / 4);
    i['m'] = i['y'] * 12 + objDate2.getMonth() - d.getMonth();
    i['ms'] = objDate2.getTime() - d.getTime();
    i['w'] = Math.floor((t2 + 345600000) / (604800000)) - Math.floor((t + 345600000) / (604800000));
    i['d'] = Math.floor(t2 / 86400000) - Math.floor(t / 86400000);
    i['h'] = Math.floor(t2 / 3600000) - Math.floor(t / 3600000);
    i['n'] = Math.floor(t2 / 60000) - Math.floor(t / 60000);
    i['s'] = Math.floor(t2 / 1000) - Math.floor(t / 1000);
    return i[interval];
}

Date.prototype.DateAdd = function(strInterval, Number) {
    var dtTmp = this;
    switch (strInterval) {
        case's':
            return new Date(Date.parse(dtTmp) + (1000 * Number));
        case'n':
            return new Date(Date.parse(dtTmp) + (60000 * Number));
        case'h':
            return new Date(Date.parse(dtTmp) + (3600000 * Number));
        case'd':
            return new Date(Date.parse(dtTmp) + (86400000 * Number));
        case'w':
            return new Date(Date.parse(dtTmp) + ((86400000 * 7) * Number));
        case'q':
            return new Date(dtTmp.getFullYear(), (dtTmp.getMonth()) + Number * 3, dtTmp.getDate(), dtTmp.getHours(), dtTmp.getMinutes(), dtTmp.getSeconds());
        case'm':
            return new Date(dtTmp.getFullYear(), (dtTmp.getMonth()) + Number, dtTmp.getDate(), dtTmp.getHours(), dtTmp.getMinutes(), dtTmp.getSeconds());
        case'y':
            return new Date((dtTmp.getFullYear() + Number), dtTmp.getMonth(), dtTmp.getDate(), dtTmp.getHours(), dtTmp.getMinutes(), dtTmp.getSeconds());
    }
}

Date.prototype.DateToParse = function() {
    var d = this;
    return Date.parse(d.getFullYear() + '/' + (d.getMonth() + 1) + '/' + d.getDate());
}