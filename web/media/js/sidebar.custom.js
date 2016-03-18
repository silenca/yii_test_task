(function($) {
  "use strict";

  $.fn.sidebarCustom = function(){
    return $(this).each(function(i){
      var $this = $(this),
          $switcher = $this.find('.js-switch-sidebar');

      $switcher.on('click', function(){
        $this.toggleClass('open-custom-sidebar');
      });

    });
  }

  $('[data-pages="sidebar-custom"]').sidebarCustom();

})(window.jQuery);
