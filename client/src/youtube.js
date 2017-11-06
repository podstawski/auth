const $=require('jquery'),
      moment = require('moment');


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
            return get('/youtube/start/'+id,function(d){
                cb(d);
                if(typeof(d['yt'])!='undefined') setTimeout(function(){
                    get('/youtube/stop/'+id,function(){
                    })
                },6000);            
            });
        },
        date: function (format,date) {
            return moment(date).format(format);
        }
    }
}
