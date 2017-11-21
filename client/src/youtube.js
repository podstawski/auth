const $=require('jquery'),
      moment = require('moment'),
      _ajax = require('./ajax'),
      datatables = require('datatables.net'),
      dtlang = require('./dt-lang.js');
      
require('datatables.net-dt/css/jquery.dataTables.css');


module.exports = function (server) {
    
    const ajax=new _ajax(server);
    
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
    
    const eventStart = function(button,evid,buttonInsertAfter,buttonInsertText) {
        var self=this;
        var alternateFun=null;
        
        
        if (buttonInsertAfter!=null) {
            var id='but'+buttonInsertAfter.replace('#','');
            var a='<a id="'+id+'" class="'+button+'" href="#">'+buttonInsertText+'</a>';
            $(buttonInsertAfter).parent().append(a);
            button='#'+id;
        }
        
        var win_ch=null;
        var win_yt=null;

        var buttonClick=function(e) {
            if (typeof(alternateFun)=='function') return alternateFun(e);
            
            var b=$(this);
            var w=window.innerWidth;
            var yt_x,yt_y,yt_w,yt_h,ch_x,ch_y,ch_w,ch_h;
            if (w>=640) {
                yt_w=Math.round((w*2)/3);
                if (yt_w>940) yt_w=940;
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
            
            var stopLoop=false;
            yt_h=ch_h=Math.round((yt_w*9)/16)+120;
            
            if (win_yt==null) {   
                win_yt=openWindow('','webkameleon_auth_yt',yt_x,yt_y,yt_w,yt_h,function(){
                    if (win_ch!=null) win_ch.close();
                    win_yt=null;
                    stopLoop=true;
                    ajax.get('/youtube/stop/'+evid);
                });
            } else {
                win_yt.focus();
            }
            
            var tryStart = function(successFun,errorFun) {
                ajax.get('/youtube/start/'+evid,function(d){
                    if (typeof(d.yt)!='undefined') {
                        if (successFun!=null) successFun(d);
                    }                        
                    if (typeof(d.error)!='undefined') {
                        if (errorFun!=null) errorFun(d);
                        
                    }
                    
                });                
            }
            
            var startSuccess = function(d) {
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
                
                    const changeLoop=function() {
                        if (stopLoop) return;
                        ajax.get('/youtube/change/'+evid,function(d){
                            if (typeof(d.hangout)!='undefined' && d.hangout.length>0) {
                                win_yt.location.href=getHOurl(d.hangout);
                                ajax.post('/youtube/change/'+evid,{hangout:''},function(){
                                    setTimeout(changeLoop,1000);
                                });
                            } else if (typeof(d.yt)!='undefined' && d.yt.length>0) {
                                win_yt.location.href=getYTurl(d.yt);
                                ajax.post('/youtube/change/'+evid,{yt:''},function(){
                                    setTimeout(changeLoop,1000);
                                });
                            } else {
                                setTimeout(changeLoop,1000);
                            }
                            
                            
                        });
                    }
                    changeLoop();
                
                } else {
                    win_yt.location.href=yt_url;
                    
                    
                }
                
                setTimeout(function(){
                    ajax.get('/youtube/stop/'+evid,function(){
                    });
                },6000);
                
            }
            
            var startError = function(d) {
                var oldText=b.text();
                var restoreText=function(){
                    b.text(oldText);
                }
                
                if(win_yt!=null) win_yt.close();
                
                switch(d.error.number) {
                    case 9:
                        b.text(d.error.info);
                        alternateFun = function(e) {
                            self.GoogleAuth(function(){
                                restoreText();
                                alternateFun=null;
                                
                                tryStart(null,startError);
                            });
                            
                            return false;
                        }
                        break;
                    
                    case 7:
                        b.text(d.error.info+' '+d.ctx);
                        alternateFun = function(e) {
                            self.DotpayPayment(d.ctx,evid, function(){
                                restoreText();
                                alternateFun=null;
                                tryStart(null,startError);
                            });
                            return false;
                        }
                        break;
                    
                    case 8:
     
                        b.text(d.error.info+' '+moment(d.ctx).format('DD-MM-YYYY HH:mm'));
                        alternateFun = function(e) {
                            restoreText();
                            alternateFun=null;
                            return false;
                        }
                        break;
                }
                
            }
            
            tryStart(startSuccess,startError);            
            return false;
        }
        
        $(button).click(buttonClick);
        
    }
    
    const eventBye = function(id,cb) {
        ajax.get('/youtube/unjoin/'+id,function(d){
            console.log('unjoin',d);
            if (typeof(d.yt)!='undefined') {
                
                ajax.post('/youtube/change/'+id,{yt:d.yt},function(){
                });
                
                if (typeof(cb)=='function') cb();
                return;
            }
            
            setTimeout(eventBye,1000,id,cb);
        });
    }
    
    const eventJoin = function (id,cbIn,cbOut) {
        ajax.get('/youtube/join/'+id,function(d){
            if (typeof(d.hangout)!='undefined') {
                
                ajax.post('/youtube/change/'+id,{hangout:d.hangout},function(){
                });
                
                if (typeof(cbIn)=='function') cbIn();
                setTimeout(eventBye,1000,id,cbOut);
                return;
            }
            
            setTimeout(eventJoin,1000,id,cbIn,cbOut);
        });
    }
    
    var last_guests=JSON.stringify({});
    
    const eventGuests = function(id,cb) {
        ajax.get('/youtube/guests/'+id,function(d){
            if (JSON.stringify(d.guests)!=last_guests ) {
                last_guests=JSON.stringify(d.guests);
                cb(d.guests);
            }
            
            setTimeout(eventGuests,1000,id,cb);
        });
    }
    
    const displayEvents = function(events,tableSelector,name,options,lang,renderRightColumn) {
        
        if (!$(tableSelector).hasClass('dataTable')) {
        
            $(tableSelector).DataTable({
                language: {
                    url: dtlang(lang)
                },
                columns: [{
                    title: name,
                    data: 'title',
                    width: '50%'
                },{
                    title: options,
                    sortable: false,
                    render: renderRightColumn
                }],
                order: []
            });
        }
                
        var datatable = $(tableSelector).dataTable().api();
        var data=[];
        
        for (var k in events) {
            events[k].DT_RowId=k;
            data.push(events[k]);
        }
        
        
        
        datatable.clear();
        datatable.rows.add(data);
        datatable.draw();
        
        return data;
    }
    
    return {
        eventSave: function(id,data,cb) {
            return ajax.post('/youtube/event/'+id,data,cb);
        },
        eventGet: function(id,cb) {
            return ajax.get('/youtube/event/'+id,cb);
        },
        eventStart: eventStart,
        eventJoin: eventJoin,
        eventGuests: eventGuests,
        eventGuestIn:function(id,guest,cb) {
            return ajax.post('/youtube/guests/'+id,{
                user: guest,
                active: 1
            },cb);
        },
        eventGuestOut:function(id,guest,cb) {
            return ajax.post('/youtube/guests/'+id,{
                user: guest,
                active: 0
            },cb);
        },
        displayEvents:displayEvents
        
    }
}
