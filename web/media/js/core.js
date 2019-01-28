$(function(){
    var loggedIn,
        check = function(){
            $.getJSON('/auth/status', function(data){
                if(undefined === loggedIn) {
                    loggedIn = data.data.loggedin;
                    if(!data.data.loggedin) {
                        clearInterval(checkInt);
                    }
                } else {
                    if(loggedIn != data.data.loggedin) {
                        document.location.href = '/';
                    }
                }
            });
        },
        checkInt = setInterval(check, 30*1000);
});