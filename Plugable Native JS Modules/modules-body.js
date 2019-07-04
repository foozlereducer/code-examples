import { BaseBodyModules } from './BaseBodyModules.js';

export class LeaderboardAd extends BaseBodyModules {
	theScript( params = false ) {

		if ( params ) {
			this.params = params;
			console.log( this._name + params );
		}

		this._inline =
		`googletag.cmd.push(function () {
			googletag.display('` + this._id + `');
		});
		console.log('dynamically loaded the gpt-leaderboard');`;
	}
}

export class BigboxtopAd extends BaseBodyModules {
	theScript() {
		this._inline =
		`googletag.cmd.push(function () {
			googletag.display('gpt-bigboxtop');
		});
		console.log('dynamically loaded the gpt-bigboxtop');`;
	}
}

export class BigboxmidAd extends BaseBodyModules {
	theScript() {
		this._inline =
		`googletag.cmd.push(function() {googletag.display('gpt-bigboxmid');});
		console.log('dynamically loaded the gpt-bigboxmid');`;
	}
}
