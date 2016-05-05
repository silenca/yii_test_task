var ExtList = function (params) {
    params = params || {};
    this._className = params.classes || '';
    this._options = params.options || [];
    this._selected = params.selected || null;
    this._name = params.name || '';
    this._attr = params.attr || '';
    this._el = document.createDocumentFragment();
    this._el.appendChild(document.createElement('select'))
    this._el.firstChild.className = this._className;
    this._el.firstChild.setAttribute('data-init-plugin', 'cs-select');
    if (this._name !== '') {
        this._el.firstChild.setAttribute('name', this._name);
    }

    if (this._options && Object.keys(this._options).length) {
        this.setOptions();
    }
    if (this._attr && Object.keys(this._attr).length) {
        this.setAttributes();
    }
};

ExtList.prototype.setOptions = function () {
    //this.clearr();
    var select = this._el.firstChild;
    $(select).empty();
    for (var key in this._options) {
        if (key == this._selected) {
            $(select).append('<option value="' + key + '" selected>' + this._options[key] + '</option>');
        } else {
            $(select).append('<option value="' + key + '">' + this._options[key] + '</option>');
        }

    }
//    $.each(this._options, function (key, value) {
//        $(select).append('<option value="' + key + '">' + value + '</option>');
//    }.bind(this));
//    this._options.forEach(function (value, key) {
//        $(select).append('<option value="' + key + '">' + value + '</option>');
//    }.bind(this));
    return this;
};
ExtList.prototype.setAttributes = function () {
    var select = this._el.firstChild;
    for (var key in this._attr) {
        $(select).attr(key, this._attr[key]);
    }
};

ExtList.prototype.init = function () {
    var select = this._el.firstChild;
    $(select).wrap('<div class="cs-wrapper"></div>');
    new window.SelectFx(select, this._options);
    return this._el.firstChild;
};

function triggerChoise_CsSelect(el, val) {
    $(el).val(val);
    $(el).closest('.cs-wrapper').find('.cs-placeholder').text($(el).find('option:selected').text());
}
//
//ExtList.prototype.clearr = function () {
//    var select = this._el.firstChild;
//    $(select).empty();
//    return this;
//}

(function ($) {
    'use strict';
    $(document).ready(function () {



        // Initializes search overlay plugin.
        // Replace onSearchSubmit() and onKeyEnter() with 
        // your logic to perform a search and display results
        $(".scroll-window-content").scrollbar({
            "onUpdate": function (content) {
                setTimeout(function () {
                    $(content).scrollTop($(content)[0].scrollHeight);
                }, 100);

            }
        });

        $('[data-pages="search"]').search({
            // Bind elements that are included inside search overlay
            searchField: '#overlay-search',
            closeButton: '.overlay-close',
            suggestions: '#overlay-suggestions',
            brand: '.brand',
            // Callback that will be run when you hit ENTER button on search box
            onSearchSubmit: function (searchString) {
                console.log("Search for: " + searchString);
            },
            // Callback that will be run whenever you enter a key into search box. 
            // Perform any live search here.  
            onKeyEnter: function (searchString) {
                console.log("Live search for: " + searchString);
                var searchField = $('#overlay-search');
                var searchResults = $('.search-results');

                /* 
                 Do AJAX call here to get search results
                 and update DOM and use the following block 
                 'searchResults.find('.result-name').each(function() {...}'
                 inside the AJAX callback to update the DOM
                 */

                // Timeout is used for DEMO purpose only to simulate an AJAX call
                clearTimeout($.data(this, 'timer'));
                searchResults.fadeOut("fast"); // hide previously returned results until server returns new results
                var wait = setTimeout(function () {
                    searchResults.find('.result-name').each(function () {
                        if (searchField.val().length != 0) {
                            $(this).html(searchField.val());
                            searchResults.fadeIn("fast"); // reveal updated results
                        }
                    });
                }, 500);
                $(this).data('timer', wait);

            }
        });

        jQuery.validator.addMethod('valueNotZero', function (value, element, arg) {
            return (value != '0');
        }, "");

        jQuery.validator.addMethod('objectAdded', function (value, element, arg) {
            return false;
        }, "");

        jQuery.validator.addMethod("dateFormat", function (value, element) {
            if (value === '') {
                return true
            }
            return value.match(/^(\d{2}).(\d{2}).(\d{4}) (\d{2}):(\d{2})(:(\d{2}))?$/);
        }, "Дата введена не верно");


        setInterval(ping, 120000); // 2 min 
    });


    $('.panel-collapse label').on('click', function (e) {
        e.stopPropagation();
    });

    if ($('#form-login').length) {
        $('#form-login').validate();
    }
})(window.jQuery);

function ping() {
    //$.get('/api/ping');
}

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();