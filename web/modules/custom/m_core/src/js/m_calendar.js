(function ($) {

  Drupal.behaviors.m_calendar = {
  attach: function (context, settings) {

    if (!$('body').hasClass('calendar-processed')) {
      let calendarId = 'calendar';
      let $calendarDiv = $('#' + calendarId);
      if ($calendarDiv.length > 0) {
        if (!$calendarDiv.hasClass('processed')) {
          let calendar = initCalendar(calendarId);
          if ($('td.active-date').length === 0) {
            $('.fc-day-top.fc-today').addClass('active-date');
            setTimeout(function () {
              updateViewFilters(getCurrentDate());
            }, 2000);

          }

          $calendarDiv.append($calendarDiv.closest('.field--name-field-vista').siblings('.field--name-field-cta'));
          $calendarDiv.addClass('processed');
        }
      }
      $('body').addClass('calendar-processed');
    }

  }
}

  function initCalendar(calendarId) {
    let calendarEl = document.getElementById(calendarId);
    let calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'dayGrid', 'interaction' ],
      locales: [ 'itLocale', 'enLocale' ],
      locale: 'it',
      timeZone: 'Europe/Rome',
      firstDay: 1,
      left:   '',
      center: 'title',
      right:  'prev,next',
      dateClick: function (info) {
        $('.fc-day-top').removeClass('active-date');
        $('td.fc-day-top[data-date="' + info.dateStr + '"]').addClass('active-date');

        updateViewFilters(info.dateStr);
      }
    });

    calendar.render();
    return calendar;
  }

  function updateViewFilters(date) {
    let $calendarView = $('#views-exposed-form-eventi-block-5');
    $calendarView.find('input[name="da_data"]').prop('type','text').val(date + "\T23:59:59");
    $calendarView.find('input[name="a_data"]').prop('type','text').val(date + "\T00:00:00");
    $calendarView.find('input[type="submit"]').click();

    let $calendarEventService = $('#views-exposed-form-eventi-block-4');
    $calendarEventService.find('input[name="da_data"]').prop('type','text').val(date + "\T23:59:59");
    $calendarEventService.find('input[name="a_data"]').prop('type','text').val(date + "\T00:00:00");
    $calendarEventService.find('input[type="submit"]').click();
  }

  function getCurrentDate() {
    let today = new Date();
    let dd = today.getDate();

    let mm = today.getMonth()+1;
    let yyyy = today.getFullYear();
    if (dd < 10) {
      dd = '0' + dd;
    }

    if (mm < 10) {
      mm = '0' + mm;
    }

    today = yyyy + '-' + mm + '-' + dd;

    return today;
  }

})(jQuery);
