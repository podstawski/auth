const $=require('jquery'),
        _ajax = require('./ajax');


module.exports = function (server) {
    const ajax=new _ajax(server);
    
    const openWindow = function(url,cb) {
        var w=window.open(url,'googleAuth','width=600,height=500,scrollbars=no,menubar=no,status=no,titlebar=no,toolbar=no');
        var to=function() {
            if (w.window!=null) {
                setTimeout(to,200);
                return;
            }
            if (typeof(cb)=='function') cb();
        }
        setTimeout(to,200);
        return w;
    }
    
    return {
        payment: function(cash,event,cb) {
            var url='/pay/dotpay/'+event+'?amount='+encodeURIComponent(cash);
            var dotpay=openWindow('',cb);
            ajax.get(url,function(d){
                
                
                if (typeof(d.action)!='undefined' && typeof(d.form)!='undefined') {
                    var html='<form method="post" action="'+d.action+'" id="dotpay">';
                    
                    for (var k in d.form) {
                        html+='<input type="hidden" name="'+k+'" value="'+d.form[k]+'"/>';
                    }
                    
                    html+='</form>';
                    html+='<script>document.getElementById("dotpay").submit();</script>';
                    dotpay.document.write(html);
                } else {
                    dotpay.close();
                    // ??? cb();
                }
            });
            
            return true;
        }
        
        
    }
}
