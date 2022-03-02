function convertDate(dateString){
    if(dateString!=''){
        var p = dateString.split(/\D/g);
        return [p[2],p[1],p[0]].join("/");
    }
}

function getDateNow(){
    var currentTime = new Date();
    var day = ('0' + currentTime.getDate()).slice(-2);
    var month = ('0' + (currentTime.getMonth()+1)).slice(-2);
    var year = currentTime.getFullYear();
    return (day + "/" + month + "/" + year);
}

function getDateByDateRange(dateRange){
    var dateReturn = new Array();
    var diffMonthQuarter;
    var diffWeekDay = Date.today().getDay();
    
    switch (dateRange){
        case "Today":
            dateReturn[0] = Date.today();
            dateReturn[1] = Date.today();
            break;
        case "Yesterday":
            dateReturn[0] = Date.today().addDays(-1);
            dateReturn[1] = Date.today().addDays(-1);
            break;
        case 'This Week':
            dateReturn[0] = Date.today().addDays(-diffWeekDay);
            dateReturn[1] = Date.today().addDays(-diffWeekDay+6);
            break;
        case 'This Week-to-date':
            dateReturn[0] = Date.today().addDays(-diffWeekDay);
            dateReturn[1] = Date.today();
            break;
        case 'This Month':
            dateReturn[0] = Date.today().moveToFirstDayOfMonth();
            dateReturn[1] = Date.today().moveToLastDayOfMonth();
            break;
        case 'This Month-to-date':
            dateReturn[0] = Date.today().moveToFirstDayOfMonth();
            dateReturn[1] = Date.today();
            break;
        case 'This Quarter':
            diffMonthQuarter = Date.today().getMonth()% 3;
            dateReturn[0] = Date.today().addMonths(-diffMonthQuarter).moveToFirstDayOfMonth();
            dateReturn[1] = Date.today().addMonths((-diffMonthQuarter +2)).moveToLastDayOfMonth();
            break;
        case 'This Quarter-to-date':
            diffMonthQuarter = Date.today().getMonth()% 3;
            dateReturn[0] = Date.today().addMonths(-diffMonthQuarter).moveToFirstDayOfMonth();
            dateReturn[1] = Date.today();
            break;
        case 'This Year':
            dateReturn[0] = Date.today().set({
                day: 1,
                month: 0
            });
            dateReturn[1] = Date.today().set({
                day: 31,
                month: 11
            });
            break;
        case 'This Year-to-date':
            dateReturn[0] = Date.today().set({
                day: 1,
                month: 0
            });
            dateReturn[1] = Date.today();
            break;
        case 'Last 30 days':
            dateReturn[0] = Date.today().addDays(-30);
            dateReturn[1] = Date.today();
            break;
        case 'Last 365 days':
            dateReturn[0] = Date.today().addDays(-365);
            dateReturn[1] = Date.today();
            break;
        case 'Last Week':
            dateReturn[0] = Date.today().addDays(-diffWeekDay).addWeeks(-1);
            dateReturn[1] = Date.today().addDays(-diffWeekDay+6).addWeeks(-1);
            break;
        case 'Last Week-to-date':
            dateReturn[0] = Date.today().addDays(-diffWeekDay).addWeeks(-1);
            dateReturn[1] = Date.today();
            break;
        case 'Last Month':
            dateReturn[0] = Date.today().addMonths(-1).moveToFirstDayOfMonth();
            dateReturn[1] = Date.today().addMonths(-1).moveToLastDayOfMonth();
            break;
        case 'Last Month-to-date':
            dateReturn[0] = Date.today().addMonths(-1).moveToFirstDayOfMonth();
            dateReturn[1] = Date.today();
            break;
        case 'Last Quarter':
            diffMonthQuarter = Date.today().getMonth()% 3;
            dateReturn[0] = Date.today().addMonths(-diffMonthQuarter-3).moveToFirstDayOfMonth();
            dateReturn[1] = Date.today().addMonths((-diffMonthQuarter -1)).moveToLastDayOfMonth();
            break;
        case 'Last Quarter-to-date':
            diffMonthQuarter = Date.today().getMonth()% 3;
            dateReturn[0] = Date.today().addMonths(-diffMonthQuarter-3).moveToFirstDayOfMonth();
            dateReturn[1] = Date.today();
            break;
        case 'Last Year':
            dateReturn[0] = Date.today().set({
                day: 1,
                month: 0
            }).addYears(-1) ;
            dateReturn[1] = Date.today().set({
                day: 31,
                month: 11
            }).addYears(-1) ;
            break;
        case 'Last Year-to-date':
            dateReturn[0] = Date.today().set({
                day: 1,
                month: 0
            }).addYears(-1) ;
            dateReturn[1] = Date.today();
            break;
        case 'Next 30 days':
            dateReturn[0] = Date.today() ;
            dateReturn[1] = Date.today().addDays(30);
            break;
        case 'Next 365 days':
            dateReturn[0] = Date.today() ;
            dateReturn[1] = Date.today().addDays(365);
            break;
        case 'Next Week':
            dateReturn[0] = Date.today().addDays(-diffWeekDay).addWeeks(1);
            dateReturn[1] = Date.today().addDays(-diffWeekDay+6).addWeeks(1);
            break;
        case 'Next 4 Weeks':
            dateReturn[0] = Date.today().addDays(-diffWeekDay).addWeeks(4);
            dateReturn[1] = Date.today().addDays(-diffWeekDay+6).addWeeks(4);
            break;
        case 'Next Month':
            dateReturn[0] = Date.today().addMonths(1).moveToFirstDayOfMonth();
            dateReturn[1] = Date.today().addMonths(1).moveToLastDayOfMonth();
            break;
        case 'Next Quarter':
            diffMonthQuarter = Date.today().getMonth()% 3;
            dateReturn[0] = Date.today().addMonths(-diffMonthQuarter+3).moveToFirstDayOfMonth();
            dateReturn[1] = Date.today().addMonths(-diffMonthQuarter+5).moveToLastDayOfMonth();
            break;
        case 'Next Year':
            diffMonthQuarter = Date.today().getMonth()% 3;
            dateReturn[0] = Date.today().set({
                day: 1,
                month: 0
            }).addYears(1);
            dateReturn[1] = Date.today().set({
                day: 31,
                month: 11
            }).addYears(1);
            break;
    }
    return dateReturn;
}

function loadAutoCompleteOff(){
    $('.interger').attr('autocomplete','off');
    $('.float').attr('autocomplete','off');
    $('.floatQty').attr('autocomplete','off');
    $('.qty').attr('autocomplete','off');
    $('.floatQtyAdjUom').attr('autocomplete','off');
}

function loadSpecialCharaterOff(data){
    var inputString = data,
    outputString = inputString.replace(/([~!@#$%^&*()_+=`{}\[\]\|\\:;'<>,.\/? ])+/g, '-').replace(/^(-)+|(-)+$/g,'');
    return console.log(outputString);
}