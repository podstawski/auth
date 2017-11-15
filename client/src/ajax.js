const $=require('jquery');

module.exports = function (server) {
    return {
        get: function(url,cb) {
            $.ajax({
                url:server+url,
                xhrFields: {
                    withCredentials: true
                }
            }).done(cb);
            return true;
        },
        post: function (url,data,cb) {
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
    }

}