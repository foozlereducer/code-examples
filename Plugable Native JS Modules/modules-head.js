import { BaseHeadModules } from './BaseHeadModules.js';

// This builds a googletag.js script link with src
export class Google extends BaseHeadModules {
	theScript() {}
}

export class GoogTag extends BaseHeadModules {
	theScript() {
		this._inline =
		` var googletag = googletag || {};
		googletag.cmd = googletag.cmd || [];

		// Configure SRA
		googletag.pubads().enableSingleRequest();

		googletag.pubads().enableLazyLoad({
			fetchMarginPercent: 250,  // Fetch slots within 5 viewports.
			renderMarginPercent: 150,  // Render slots within 2 viewports.
			mobileScaling: 2.0  // Double the above values on mobile.
		});

		googletag.enableServices();`;
	}
}

export class IndexExchange extends BaseHeadModules {

	theScript() {
		this._inline =
		`if(  "undefined" != typeof I10C ) {
	if ( "undefined" != I10C.Morph ) {
		if ( null != I10C.Morph ) {
			if ( 1 == I10C.Morph ) {
				googletag.pubads().setTargeting( "shield" , "1" );
			}
		}
	}
}`;
	}
}

export class LeaderboardSlot extends BaseHeadModules {
	theScript( params = {} ) {

		this._inline =
		`googletag.cmd.push(function() {
	var gpt_leaderboard = googletag.defineSlot('` + params.slot + `', ` + params['sizes'] + `, 'gpt-leaderboard');
	gpt_leaderboard.addService( googletag.pubads())` +
	this.setTargeting( params.targeting ) +
	`googletag.pubads().enableSingleRequest();
	googletag.enableServices();
});
googletag.cmd.push( function() { googletag.display('gpt-leaderboard'); });
console.log('dynamically loaded the leaderboard slot');`;

		this._ranInline = true;
	}

	setTargeting( targeting ) {
		let theTargeting = '';
		targeting.forEach( targets => {
			let i = 1;
			theTargeting += ".setTargeting(";
			targets.forEach( val => {
				theTargeting += "'" + val + "'";
				if ( i < targets.length ) {
					theTargeting += ', ';
				}
				i++;
			})
			theTargeting += "); \n\t";
		});

		return theTargeting;
	}

}

export class BigboxtopSlot extends BaseHeadModules {

	theScript() {
		this._inline =
		`googletag.cmd.push(function () {
			gpt_bigboxtop = googletag.defineSlot('/3081/vp_ind.com/index',[ [ 300,250 ], [ 300,251 ], [ 300,600 ], [ 300,601 ], [ 300,1050 ] ], 'gpt-bigboxtop')
		.addService(googletag.pubads())
		.setTargeting('loc', 'top');

	});`

		this._ranInline = true;
	}
}

export class BigboxmidSlot extends BaseHeadModules {

	theScript() {
		this._inline =
		`googletag.cmd.push(function () {
			gpt_bigboxmid = googletag.defineSlot('/3081/vp_ind.com/index',[ [ 300,250 ], [ 300,252 ] ], 'gpt-bigboxmid')
		.addService(googletag.pubads())
		.setTargeting('loc', 'top');

	});`

		this._ranInline = true;
	}
}
