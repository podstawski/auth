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
    
    const openWindow = function(url,name,x,y,w,h,cb) {
        var win=window.open(url,name,'left='+x+',top='+y+',width='+w+',height='+h+',scrollbars=no,menubar=no,status=no,titlebar=no,toolbar=no,location=no');
        var to=function() {
            if (win.window!=null) {
                setTimeout(to,200);
                return;
            }
            cb();
        }
        
        if (win!=null && typeof(cb)=='function') setTimeout(to,200);
		return win;
    }
    
    const getYTurl = function(id) {
        return 'https://www.youtube.com/watch?v='+id+'?autoplay=1';
    }
    const getHOurl = function(id) {
        return 'https://hangouts.google.com/hangouts/_/ytl/'+id;
    }
    
    const eventStart = function(button,evid) {
        var self=this;
        var alternateFun=null;
        var buttonClick=function(e) {
            if (typeof(alternateFun)=='function') return alternateFun(e);
            
            var b=$(this);
            var w=window.innerWidth;
            var yt_x,yt_y,yt_w,yt_h,ch_x,ch_y,ch_w,ch_h;
            if (w>=640) {
                yt_w=Math.round((w*2)/3);
                yt_x=20;
                yt_y=20;
                ch_x=yt_x+yt_w;
                ch_y=yt_y;
                ch_w=Math.round((w)/4);;
                
            } else {
                yt_w=w;
                yt_x=0;
                yt_y=0;
                ch_x=0;
            }
            
            yt_h=ch_h=Math.round((yt_w*9)/16)+120;
            var win_ch=null;
            var win_yt=openWindow('','webkameleon_auth_yt',yt_x,yt_y,yt_w,yt_h,function(){
                if (win_ch!=null) win_ch.close();
            });
            
            
            get('/youtube/start/'+evid,function(d){
                
                if (typeof(d.yt)!='undefined') {
                    var yt_url=getYTurl(d.yt);
                    if (typeof(d.hangout)!='undefined')
                        yt_url=getHOurl(d.hangout);
                    if (d.chat && ch_x>0) {
                        var fname='top_'+Math.random();
                        fname=fname.replace('\.','_');
                        window[fname] = function () {
                            win_ch=openWindow('','webkameleon_auth_ch',ch_x,ch_y,ch_w,ch_h);
                            
                            win_yt.location.href=yt_url;
                            win_ch.location.href=server+'/youtube/chat/'+evid;
                        }
                        win_yt.document.write('<div align="center"><img id="yt" onclick="top.opener.'+fname+'()" src="http://auth.webkameleon.com/img/yt.jpg" width="99%" style="cursor:pointer"/></div>')
                    } else {
                        win_yt.location.href=yt_url;
                        
                    }
                    
                    setTimeout(function(){
                        get('/youtube/stop/'+evid,function(){
                        });
                    },6000);
                }

                if (typeof(d.error)!='undefined') {
                    win_yt.close();
                    var oldText=b.text();
                    var restoreText=function(){
                        b.text(oldText);
                    }
                    
                    switch(d.error.number) {
                        case 9:
                            b.text(d.error.info);
                            alternateFun = function(e) {
                                self.GoogleAuth(restoreText);
                                alternateFun=null;
                            }
                            break;
                        
                        case 7:
                            b.text(d.error.info+' '+d.ctx);
                            alternateFun = function(e) {
                                restoreText();
                                alternateFun=null;
                            }
                            break;
                        
                        case 8:
                            b.text(d.error.info+' '+moment(d.ctx).format('DD-MM-YYYY HH:mm'));
                            alternateFun = function(e) {
                                restoreText();
                                alternateFun=null;
                            }
                            break;
                    }
                }
                
            });
            
        
        }
        
        $(button).click(buttonClick);
        
    }
    
    const eventJoin = function (id,cb) {
        get('/youtube/join/'+id,function(d){
            if (typeof(d.hangout)!='undefined') {
                
                var id='a'+Date.now();
                $('body').append('<a href="'+getHOurl(d.hangout)+'" id="'+id+'" target="webkameleon_auth_yt">a</a>');
                $('#'+id).click();
                if (typeof(cb)=='function') cb();
                return;
            }
            
            setTimeout(eventJoin,1000,id,cb);
        });
    }
    
    return {
        eventSave: function(id,data,cb) {
            return post('/youtube/event/'+id,data,cb);
        },
        eventGet: function(id,cb) {
            return get('/youtube/event/'+id,cb);
        },
        eventStart: eventStart,
        eventJoin: eventJoin
    }
}
