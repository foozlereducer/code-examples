/**
 * The Script Mediator ~ use to register, unregister and bulk process JS plugins
 */
class ScriptMediator {
	constructor() {
		this._scripts = [];
	}

	/**
	 * Register method ~ Register a JavaScript Plugin
	 * @param {object} scriptObj ~ The plugin that will be registered
	 */
	register( scriptObj ) {
		if( this.isValidScriptObj( scriptObj ) ) {
			this._scripts.push( scriptObj );
			return this;
		}
		let error = new Error( 'Attempt to register invalid script object' );
		error.scriptObj = scriptObj;
		throw error;
	}

	/**
	 * Is Function Utility ~ checks if an object is a funciton
	 * @param {object} obj
	 */
	isFunction( obj ) {
		return !!(obj && obj.constructor && obj.call && obj.apply);
	}

	/**
	 * Is Valid Object ~ validates objects and adds the functions to the processing array
	 * @param {object} scriptObj
	 */
	isValidScriptObj( scriptObj ) {
		let functions = [];
		functions.push( this.isFunction( scriptObj.run ) );
		functions.push( this.isFunction( scriptObj.createScript ) );
		functions.push( this.isFunction( scriptObj.theScript ) );
		if ( functions.indexOf( false ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Unregister method ~ will remove the Javascript from processing array
	 * @param {object} scriptObj ~ the plugin to be deregisterd
	 * @TODO refactor this to get it working
	 */
	unRegister( scriptObj ) {
		delete this._scripts[ scriptObj._name ];
	}

	/**
	 * LoadAllScripts ~ parses each of the plugins and run their methods that set and create the scripts.
	 */
	loadAllScripts() {
		this._scripts.forEach( function( scriptPlugin ) {
			if ( false === scriptPlugin._ranInline ) {
				scriptPlugin.theScript();
			}
			scriptPlugin.createScript();
		});
	}
}

const SM = new ScriptMediator();

/**
 * Ad Scripts
 * @description The async script loader. If the 'uap' parameter is true you can set flags defined
 * in the loaders urlParams conditionals and control what scrips load.
 */

let urlParams = new URLSearchParams(window.location.search);
(async () => {
    const modulesHead = './modules-head.js';
	const moduleHead = await import(modulesHead);
	const modulesBody = './modules-body.js';
	const moduleBody = await import( modulesBody );

	if ( 'true' === urlParams.get('uap') ) {
		// load plugins via url parameters

		/****************************************/
		/************ <HEAD> MODULES ************/
		/****************************************/
		let plugin;
		// Google Tag
		if ( 'true' === urlParams.get('gt') ) {
			plugin = new moduleHead.Google('Google', true, 'https://www.googletagservices.com/tag/js/gpt.js' );
			SM.register( plugin );

			plugin = new moduleHead.GoogTag('GoogTag');
			SM.register( plugin );
		}

		// Index Exchange
		if ( 'true' === urlParams.get('ix')) {
			plugin = new moduleHead.IndexExchange('IndexExchange');
			SM.register( plugin );
		}

		// Load the Ad slots and GoogTag configuration such as lazyloading and SRA
		if ( 'true' === urlParams.get('slots')) {
			plugin = new moduleHead.LeaderboardSlot('LeaderboardSlot');
			let config = {
				slot: '/3081/vancouverprovince.com',
				sizes: '[[728, 90]]',
				targeting: [ ['loc', 'top'] ]
			}
			plugin.theScript( config );
			SM.register( plugin );

			plugin = new moduleHead.BigboxtopSlot('BigboxtopSlot');
			plugin.theScript();
			SM.register( plugin );

			plugin = new moduleHead.BigboxmidSlot('BigboxmidSlot');
			plugin.theScript();
			SM.register( plugin );
		}

		/****************************************/
		/************ <BODY> MODULES ************/
		/****************************************/

		if ( 'true' === urlParams.get('ads')) {
			plugin = new moduleBody.LeaderboardAd( 'LeaderboardAd', 'gpt-leaderboard');
			plugin.theScript();
			SM.register( plugin );

			plugin = new moduleBody.BigboxtopAd( 'BigboxtopAd', 'gpt-bigboxtop');
			plugin.theScript();
			SM.register( plugin );

			plugin = new moduleBody.BigboxmidAd( 'BigboxmidAd', 'gpt-bigboxmid');
			plugin.theScript();
			SM.register( plugin );
		}

	} else if ( 'undefined' !== config ) {
		// This section could load config data from a file, api or object
	} else {
		// Load all the plugins.

		let plugin;
		// Google Tag
		plugin = new module.GoogTag('GoogTag');
		SM.register( plugin );
		// Index Exchange
		plugin = new module.IndexExchange('IndexExchange');
		SM.register( plugin );
		plugin = new module.LeaderboardSlot('LeaderboardSlot');
		SM.register( plugin );
	}
	SM.loadAllScripts();
  })();



