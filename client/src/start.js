const google_auth_obj = require('./google.auth.js'),
    youtube_obj = require('./youtube.js'),
    dotpay_obj = require('./dotpay.js'),
    jquery=require('jquery');

let WebKameleonAuthObj = function(w,d) {
    var server;
    var s=d.getElementsByTagName('script');
    for(var i=s.length-1;i>=0;i--) {
        if (s[i].src&&s[i].src.indexOf('webkameleon-auth-client.js')>0) {
            server=s[i].src.replace(/\/webkameleon-auth-client\.js.*/,'');
            
            var callback_i=s[i].src.indexOf('callback=');
            if (callback_i>0) {
                var callback=s[i].src.substr(callback_i+9);
                if (callback.indexOf('&')>0) {
                    callback=callback.substr(0,callback.indexOf('&'));
                }
                if (typeof(window[callback])=='function') setTimeout(window[callback],10);
                
            }
            break;
        }
    }
    const google_auth = google_auth_obj(server),
            youtube = youtube_obj(server),
            dotpay = dotpay_obj(server);
    
    
    
    return {
        GoogleAuth: google_auth.authorize,
        GoogleUser: google_auth.user,
        GoogleLogout: google_auth.logout,
        GoogleToken: google_auth.gettoken,
        GoogleInit: google_auth.init,
        
        YoutubeSaveEvent: youtube.eventSave,
        YoutubeGetEvent: youtube.eventGet,
        YoutubeStartEvent: youtube.eventStart,
        YoutubeJoinEvent: youtube.eventJoin,
        YoutubeEventGuests: youtube.eventGuests,
        YoutubeEventGuestIn: youtube.eventGuestIn,
        YoutubeEventGuestOut: youtube.eventGuestOut,
        
        DotpayPayment: dotpay.payment,
        
        jquery: jquery
    }
    
}

window.WebKameleonAuth = new WebKameleonAuthObj(window,window.document);

