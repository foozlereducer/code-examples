(function() { 
	if ( -1 == document.cookie.indexOf( 'ppUser' ) ) {
		var ARTICLE_URL = window.location.href; 
		var CONTENT_ID = 'everything'; 
		document.write( '<scr'+'ipt '+ 'src="//survey.g.doubleclick.net/survey?site=_e5l5xofbmo5jayrc6g3njdpnci'+ '&url='+encodeURIComponent(ARTICLE_URL)+ (CONTENT_ID ? '&cid='+encodeURIComponent(CONTENT_ID) : '')+ '&random='+(new Date).getTime()+ '" type="text/javascript">'+'\x3C/scr'+'ipt>'); 
	}
})();