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
    
    return {
        eventSave: function(id,data,cb) {
            return post('/youtube/event/'+id,data,cb);
        },
        eventGet: function(id,cb) {
            return get('/youtube/event/'+id,cb);
        },
        eventStart: function(id,cb) {
            return get('/youtube/start/'+id,cb);
        }
    }
}
