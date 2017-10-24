(function(w,d,u,a,s){
        if (typeof(w['WebKameleonAuth'])!='undefined') return;        
        a=d.createElement('script'),s=d.getElementsByTagName('script');
        for(var i=s.length-1;i>=0;i--) {
            if (s[i].src&&s[i].src.indexOf('client.js')>0) {
                a.async=1,a.src=s[i].src.replace('client.js',u);
                s[0].parentNode.insertBefore(a,s[0]);break;
            }
        }
})(window,document,'webkameleon-auth-client.js');