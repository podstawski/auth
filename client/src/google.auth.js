const $=require('jquery');


module.exports = function (server) {
    
    const getUser = function(cb) {
        $.ajax({
            url:server+'/google/auth',
            xhrFields: {
                withCredentials: true
            }
        }).done(cb);
        return true;
    }
    
    return {
        user: getUser,
        authorize:function(cb) {
            var w=window.open(server+'/google?redirect='+encodeURI(server+'/close.html'),'googleAuth','width=600,height=500,scrollbars=no,menubar=no,status=no,titlebar=no,toolbar=no');
            var to=function() {
                if (w.window!=null) {
                    setTimeout(to,200);
                    return;
                }
                if (typeof(cb)!='function') return;
                getUser(cb);
            }
            setTimeout(to,200);
            return true;
        } ,
        logout: function(cb) {
            $.ajax({
                url:server+'/google/logout',
                xhrFields: {
                    withCredentials: true
                }
            }).done(cb);
            return true;
        }
    }
}
