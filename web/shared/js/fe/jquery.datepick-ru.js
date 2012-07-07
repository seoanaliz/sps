/* http://keith-wood.name/datepick.html
   Russian localisation for jQuery Datepicker.
   Written by Andrew Stromnov (stromnov@gmail.com). */
(function($) {
	$.datepick.regional['ru'] = {
		monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
		'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort: ['янв','фев','мар','апр','май','июн',
		'июл','авг','сен','окт','ноя','дек'],
		dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
		dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
		dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
		dateFormat: 'dd.mm.yyyy', firstDay: 1,
		renderer: $.datepick.defaultRenderer,
		prevText: '',  prevStatus: '',
		prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '',
		nextText: '', nextStatus: '',
		nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '',
		currentText: 'Сегодня', currentStatus: '',
		todayText: 'Сегодня', todayStatus: '',
		clearText: 'Очистить', clearStatus: '',
		closeText: 'Сохранить', closeStatus: '',
		yearStatus: '', monthStatus: '',
		weekText: 'Не', weekStatus: '',
		dayStatus: 'd M, DD', defaultStatus: '',
		isRTL: false
	};
	$.datepick.setDefaults($.datepick.regional['ru']);
})(jQuery);
