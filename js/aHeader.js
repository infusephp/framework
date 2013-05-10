// setup console logging
var debugging = true;
if (typeof console == "undefined") var console = { log: function() {} };
else if (!debugging || typeof console.log == "undefined") console.log = function() {};

(function($) {
	$.get = function(url, data, success) {
		$.ajax({
			url: url,
			type: "GET",
			data: data,
			dataType: "json",
			contentType: "application/json; charset=utf-8",
			success: success
		});
	};

	$.post = function(url, data, success) {
		$.ajax({
			url: url,
			type: "POST",
			data: data,
			dataType: "json",
			contentType: "application/json; charset=utf-8",
			success: success
		});
	};
	
	$.put = function(url, data, success) {
		$.ajax({
			url: url,
			type: "PUT",
			data: data,
			dataType: "json",
			contentType: "application/json; charset=utf-8",
			success: success
		});
	};

	$.delete = function(url, success) {
		$.ajax({
			url: url,
			type: "DELETE",
			dataType: "json",
			contentType: "application/json; charset=utf-8",
			success: success
		});
	};
})(jQuery);