'use strict';
/**
 * BaseBodyModules ~ Used to extend the modules classes and helps enforce a standard interface
 */
export class BaseBodyModules {
	/**
	 *
	 * @param {string} name ~ the name of the plugin ie. 'indexExchange'
	 * @param {boolean} async ( optional ) ~ can toogle an async script to sync
	 * @param {string} src ( optional ) ~ if set, will become the <script src=...>
	 * @return instance of Baseheadmodules.
	 */
	constructor ( name, id = false, async = true, src = false ) {
		this._name = name;
		this._id = id;

		// choose the main container to drop the script
		if ( false === id ) {
			this._bd = document.body;
		} else {
			if ( 'string' === typeof id ) {
				this._bd = document.getElementById( id );
				this._bd.innerHTML = "";
			}
		}

		this._bs = document.createElement("script");
		this._inline;

		if ( true === async ) {
			this._bs.async = async;
		}

		if ( false !== src ) {
			this._hs.src = src;
		}
	}

	/**
	 * theScript() method ~ This must be overriden and will contain the script as a string.
	 * It must be called prior to the instance of the plugin being registered.
	 * @param {array} params (optional) ~ can pass parameters to your script
	 */
	theScript ( params = [] ) {
		throw new TypeError("You have to implement theScript() method");
	}

	/**
	 * createScript method ~ converts the inline text into a text node and then appends it to the container
	 * @return void
	 */
	createScript() {
		const inline = document.createTextNode( this._inline );
		this._bs.appendChild( inline );
		this._bd.appendChild( this._bs );
	}

	/**
	 * runt ~ only runs the createScript() method. This means theScript() method must be called before
	 * registering the plugin.
	 * @return { string } name ~ the plugin's name
	 */
	run() {
		return {
			createScript: () => { createScript() },
			name: () => { return this.name }
		};
	}

}