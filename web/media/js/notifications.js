 $(function(){
     //var socket = io.connect('wss://dopomogaplus.silencatech.com:8005');

     /*
      * message: text
      * position: top | bottom | top-left | top-right | bottom-left | bottom-right
      * type: info | warning | success | danger | default
      * style: bar | flip | circle | simple
      */
     function showNotification(selector, message, position, type, style, timeout) {
         if (!timeout) timeout = 0;
         $(selector).pgNotification({
             style: style,
             message: message,
             position: position,
             timeout: timeout,
             type: type
         }).show();
     }

     function hideNotifications(selector) {
         selector.find('.pgn').remove();
     }
});