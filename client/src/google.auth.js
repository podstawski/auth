const $=require('jquery');


module.exports = function (server) {
    
    const get =function(url,cb) {
        $.ajax({
            url:server+url,
            xhrFields: {
                withCredentials: true
            }
        }).done(cb);
        return true;
    }
    
    const getUser = function(cb) {
        return get('/google/auth',cb);
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
            return get('/google/logout',cb);
        }
    }
}
