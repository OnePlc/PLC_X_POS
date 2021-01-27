function loadUserWorktime() {
    var sMonth = $('select[name="month"]').val();
    console.log('Month: '+sMonth);

    var iEmployee = $('select[name="employee"]').val();
    console.log('Employee: '+iEmployee);

    if(iEmployee != 0) {
        $.post('/pos/worktime/month', {month:sMonth,employee_id:iEmployee}, function (retHTML) {
            $('.worktime-list').html(retHTML);
        });
    }
}

$(function () {
   $('select[name="employee"]').on('change', function () {
       loadUserWorktime();
   });

    $('select[name="month"]').on('change', function () {
        loadUserWorktime();
    })
});

loadUserWorktime();