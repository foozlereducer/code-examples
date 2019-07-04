'use strict';
/**
 * BaseHeadModules ~ Used to extend the modules classes and helps enforce a standard interface
 */
export class BaseHeadModules {

	/**
	 *
	 * @param {string} name ~ the name of the plugin ie. 'indexExchange'
	 * @param {boolean} async ( optional ) ~ can toogle an async script to sync
	 * @param {string} src ( optional ) ~ if set, will become the <script src=...>
	 * @return instance of Baseheadmodules.
	 */
	constructor ( name, async = true, src = false ) {
		this._name = name;
		this._hd = document.head;
		this._hs = document.createElement("script");
		this._inline;
		this._ranInline = false;

		if ( true === async ) {
			this._hs.async = true;
		}

		if ( false !== src ) {
			this._hs.src = src;
		}
	}

	/**
	 * theScript() method ~ must be overriden. This function will contain the JS / CSS / comments that will be loaded.
	 */
	theScript ( params = {} ) {
		this._ranInline = true;
		throw new TypeError("You have to implement theScript() method");
	}

	/**
	 * createScript method ~ converts the inline text into a text node and then appends it to the container
	 * @return void
	 */
	createScript() {
		if ( this._inline ) {
			const inline = document.createTextNode( this._inline );
			this._hs.appendChild( inline );
		}
		this._hd.appendChild( this._hs );
	}

	/**
	 * runt ~ runs the internal methods in sequesnce
	 * @return { string } name ~ the plugin's name
	 */
	run() {
		if ( false === this._ranInline ) {
			return {
				theScript: () => { theScript() },
				createScript: () => { createScript() },
				name: () => { return this.name }
			};
		} else {
			return {
				createScript: () => { createScript() },
				name: () => { return this.name }
			};
		}
	}

}