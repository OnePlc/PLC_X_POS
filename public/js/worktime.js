function loadCurrentWorktimeWidget() {
    $.post('/pos/worktime/current', {}, function (retHTML) {
        $('.current-worktime-widget').html(retHTML);
    });
}

loadCurrentWorktimeWidget();