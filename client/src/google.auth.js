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
    
    const post = function (url,data,cb) {
        $.ajax({
            type: "POST",
            data: data,
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
    
    const openWindow = function(url,cb) {
        var w=window.open(url,'googleAuth','width=600,height=500,scrollbars=no,menubar=no,status=no,titlebar=no,toolbar=no');
        var to=function() {
            if (w.window!=null) {
                setTimeout(to,200);
                return;
            }
            if (typeof(cb)!='function') return;
            getUser(cb);
        }
        setTimeout(to,200);    
    }
    
    return {
        user: getUser,
        authorize:function(cb) {
            openWindow(server+'/google?redirect='+encodeURI(server+'/close.html'),cb);
            return true;
        } ,
        logout: function(cb) {
            return get('/google/logout',cb);
        },
        gettoken: function(service,cb) {
            openWindow(server+'/google/scope/'+service+'?redirect='+encodeURI(server+'/close.html'),cb);
            return true;
        },
        init: function(options,cb) {
            var url='/google/init';
            return post(url,options,function(res){
                if (typeof(cb)=='function') cb(res.result);
            });
        }
        
    }
}
