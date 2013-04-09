// setup console logging
var debugging = true;
if (typeof console == "undefined") var console = { log: function() {} };
else if (!debugging || typeof console.log == "undefined") console.log = function() {};

if(!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(needle) {
        for(var i = 0; i < this.length; i++) {
            if(this[i] === needle) {
                return i;
            }
        }
        return -1;
    };
}

function str_repeat (input, multiplier) {
    // Returns the input string repeat mult times  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/str_repeat    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // *     example 1: str_repeat('-=', 10);
    // *     returns 1: '-=-=-=-=-=-=-=-=-=-='
    return new Array(multiplier + 1).join(input);
}

function GUID()
{
    var S4 = function ()
    {
		var str = Math.floor(
                Math.random() * 0x10000 /* 65536 */
            ).toString(16);
       
		while (str.length < 4)
	        str = str + "0";
	    
	    return str;
    };

    return (
            S4() + S4() + "-" +
            S4() + "-" +
            S4() + "-" +
            S4() + "-" +
            S4() + S4() + S4()
        );
}

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