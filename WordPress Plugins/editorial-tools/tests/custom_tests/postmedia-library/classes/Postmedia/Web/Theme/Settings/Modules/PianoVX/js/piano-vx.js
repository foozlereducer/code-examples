// experience.tinypass.com
(function(src){
	var a=document.createElement("script");
	a.type="text/javascript";
	a.async=true;
	a.src=src;
	var b=document.getElementsByTagName( "script" )[0];
	b.parentNode.insertBefore(a,b)
})( get_piano_endpoint( "?aid=" + pn_theme_piano_app_id + "&ver=1.3" ) );

function get_piano_endpoint( query ) {
	if ( typeof postmedia_piano !== 'undefined' && postmedia_piano.endpoint ) {
		return postmedia_piano.endpoint + query;
	}

	return '//experience.tinypass.com/xbuilder/experience/load' + query;
}

function openPianoVXModal( offer_ID, template_ID ){
	tp = window.tp || [];
	tp.push(['init', function(){
		tp.offer.show({
			offerId: offer_ID,
			templateId: template_ID
		});
	}]);
}

function is_ie() {
	return window.navigator.userAgent.indexOf( 'MSIE' ) > -1 || window.navigator.userAgent.indexOf( 'Trident' ) > -1;
}

function precessWhitelistTags(){
	//Paywall whitelists
	var bBreaking = false,
		aMetaTags = document.getElementsByTagName('meta'),
		tagValue = ['not_metered'],
		path = window.location.pathname,
		folders = '';
		
		if( ( path.length > 1 ) && ( path.indexOf('category') == -1 ) && ( path.indexOf('feature') == -1 ) ){
			folders = path.split('/');
			folders.shift();
			folders.pop();
			folders = ',' + folders;
		}

	// check for exemption (Paywall whitelist plugin)
	if (typeof pnMeterExempt !== 'undefined' && !!pnMeterExempt) {
		tagValue = ['not_metered'];
		return tagValue + folders;
	}

	// TEMP: Exclude IE users from paywall ( Sign In Bug )
	if ( typeof postmedia_piano !== 'undefined' && postmedia_piano.paywall_exempt_ie && is_ie() ) {
		return ['not_metered'];
	}

	// Only apply to Long Form
	if (document.getElementsByTagName('body')[0].className.indexOf('single-feature') > -1) {
		tagValue = ['metered'];

		return tagValue;
	}
	
	// Only apply to story pages
	if (document.getElementsByTagName('body')[0].className.indexOf('single-post') > -1) {
		//tp.push(['setCustomVariable', 'category', [ 'news', 'local_news' ]]);
		tagValue = ['metered'];
		// check within keywords
		for (i = 0; i < aMetaTags.length; i += 1) {
			if (aMetaTags[i].name.match(/(keywords)/i)) {
				if (aMetaTags[i].content.match(/(^|,)\s*(editors|sponsored)\s*(,|$)/i)) {
					tagValue = ['not_metered','sponsored_content', 'editors', 'sponsored'];
					return tagValue + folders;
				} else if (aMetaTags[i].content.match(/(premium)/i)) {
					tagValue = ['metered', 'premium_locked', 'premium'];
					return tagValue + folders;
				} else if (aMetaTags[i].content.match(/(^|,)\s*(breaking)\s*(,|$)/i)) {
					bBreaking = true;
				}
			}
		}
		// exclude "breaking" < 24 hours old (from NP/FP)
		if (bBreaking) {
			tagValue = Math.floor((new Date()).getTime() * 0.001) - Math.floor(new Date(npJ('.npDateline > span[property = "dc:created"]').attr('content')).getTime() * 0.001) > 86400 ? ['metered'] : ['not_metered_24'];
			return tagValue + folders;
		}
		return tagValue + folders;
	}
	
	return tagValue;
}

(function(){
	tp = window.tp || [];
	var tagValue = precessWhitelistTags();
	tp.push(['setTags', tagValue]);
})();


tp = window.tp || [];
tp.push( ["addHandler", "startCheckout", function (params) {
    // params object has params.termId
	Postmedia.Analytics.ModelScreen("Piano", "Meter Payment");
}]);

//checkoutComplete
tp.push(["addHandler", "checkoutComplete", function(conversion){ 
   // Your code after successful purchase
   Postmedia.Analytics.ModelScreen("Piano", "Checkout Complete");
}]);

tp.push(["addHandler", "checkoutClose", function( event ){
    // The event parameter contains information about the state of closed modal
    switch (event.state){
        case 'checkoutCompleted':
            // User completed the purchase and now has access
            // Usually it's a good practice to reload the page
			Postmedia.Analytics.ModelScreen("Piano", "Meter Closed After Checkout Completed");
            break;
        case 'alreadyHasAccess':
            // User already has access
            // This state could be a result of user logging in during checkout process
            // Usually it's a good practice to reload the page as well
			Postmedia.Analytics.ModelScreen("Piano", "Meter Closed User Already Has Access");
            break;
        case 'close':
            // User did not complete the purchase and simply closed the modal
			Postmedia.Analytics.ModelScreen("Piano", "Meter Closed Without Purchase");
			var path = window.location.pathname;
			if ( path.indexOf('subscription') == -1 ){ 
				location.href='/subscription';
			} else {
				location.href = path;
			}
    }
}]);

tp.push(["addHandler", "checkoutCustomEvent", function(event){
   switch(event.eventName) {
       case "login":
			// Didn't work during qa
			Postmedia.Analytics.ModelScreen("Piano", "Checkout Custom Event For Login");
			break;
   }
}]);

tp.push(["addHandler", "checkoutError", function(errorData){
	// Didn't work during qa
	Postmedia.Analytics.ModelScreen("Piano", "Checkout Error");
}]);

tp.push(["addHandler", "showOffer", function( offerParams ){
    // Your code after offer has been shown
	Postmedia.Analytics.ModelScreen("Piano", "Meter Authorization");
}]);

tp.push( [ "addHandler", "showTemplate", function ( templateParams ) {
	// Your code after template has been shown
	// Didn't work during qa
	Postmedia.Analytics.ModelScreen("Piano", "Show Template");
}]);

//loginRequired
tp.push(["addHandler", "loginRequired", function(params){
	// Didn't work during qa
	Postmedia.Analytics.ModelScreen("Piano", "Login Required");
}]);

tp.push(["addHandler", "loginSuccess", function(){
	// Any logic required after a successful login
	// Didn't work during qa
	Postmedia.Analytics.ModelScreen("Piano", "Login Success");
}]);

//meterActive
tp.push(["addHandler", "meterActive", function(meterData){
	// Didn't work during qa
	Postmedia.Analytics.ModelScreen("Piano", "Meter Active");
}]);

tp.push(["addHandler", "meterExpired", function(meterData){
	// The logic executed here could differentiate
	// Based on meterData.meterName value
	Postmedia.Analytics.ModelScreen("Piano", "Meter Expired");
}]);
