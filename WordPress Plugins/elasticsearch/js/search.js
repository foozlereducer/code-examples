jQuery(document).ready(function () {

	var DRV = DRV || {}; // Sudo Namespace

	/* Titles, Headers, Intro text use block comment format */
	// Used for element comments

	/*
	 * This named-spaced IIFE ( Immediately-invoked Function Expression ) is invoked immediately after it has been defined
	 * Only functions, variables and objects defined within this context may only be accessed inside of it. So function
	 * invocation provides privacy
	 *
	 * Anoymous functions defined within can be accessed via DRV.somePublicFunction and variables like DRV.myVariable
	 * Methods are private
	 *
	 * All methods and class methods defined in the object literal, properties, and variables are private to the namespaced IIFE
	 */
	(function (DRV, undefined) {
		var archive_search = jQuery('em.run-archive-search'),
			ie_path = jQuery(location).attr('href'),
			p_terms = getURLParameter('p_terms', ie_path),
			count,
			terms_obj = jQuery('#terms'),
			default_text_obj = jQuery('.default-text'),
			compare_page = jQuery('input[name=compare_page]'),
			is_mobile = Postmedia.Properties.isMobile,
			page_title = ( is_mobile ) ? null : document.title;

		/*
		 * On Page Load
		 *
		 * Check if the document is a archive search page and perform a call that will run the default archive search
		 *
		 * When the URL contains bookmarked or browser history-based search parameters then check if it is a mobile
		 * device and if true, apply the 'load more' functionality to rebuild the users results.
		 *
		 * Otherwise, check if there are any url parameters that can autoload a saved search on the search-results page
		 *
		 * This code block will run only when the page loads; subsequent search calls & filtering are requested using AJAX.
		 */

		if( compare_page.size() > 0 ) {
			var callback = function () {
					if( !is_mobile) {
						jQuery('.compare-search-results').stop().animate({ opacity: 0.3 },500);
					}
					var filters = getCompareFilters();
					callSearch(compare_page, filters);
				}

			jQuery('.filter-set').drvFilters(true, callback);

			getCallBack(compare_page);

		}
		else if (archive_search.length) {
			var filters = getAllFiltersFromURL();
			callSearch(archive_search, filters);

		} else { // Auto-search if search parameter exist in the URL ( bookmark for example )
			ie_path = jQuery(location).attr('href'); // get IE capable path

			if (isSetURLParameter('from_page', ie_path) & null != isSetURLParameter('p_terms', ie_path)) {
				terms_obj.val(getURLParameter('p_terms', ie_path)) // Set the <input id='terms' to the users original term(s)
				from_page = getURLParameter('from_page', ie_path);

				if (Postmedia.Properties.isMobile && from_page > 1) { // Get the isMobile global value from )
					count = parseInt(from_page);

					/*if ( ! isSetURLParameter( 'p_terms' ) ) {
						p_terms = getTerms();
					}*/

					if (isSetURLParameter('a_hit', ie_path)) { // Return to from link
						for (o = 1; o <= count; o++) { // loop from first to last page ( count );
							pushFromPageToUrlMobile(from_page = o); // cycle through and update the URL starting from the counter
							callSearch(this, 'load_more'); // call the search with the 'load_more' action so that it appends the results together
						}
					} else { // bookmark
						for (o = count; o >= 1; o--) { // inverse loop
							pushFromPageToUrlMobile(from_page = o); // cycle through and update the URL starting from the counter
							callSearch(this, 'load_more'); // call the search with the 'load_more' action so that it appends the results together
						}
						pushFromPageToUrlMobile(count) // reset from_page to highest value;
					}

				} else { // load the desktop / table results with the bookmarked or return from a selected link
					terms_obj.val(getURLParameter('p_terms', ie_path)) // Set the <input id='terms' to the users original term(s)
					var filters = getAllFiltersFromURL();
					callSearch(this, filters);
				}
			} else if (isSetURLParameter('terms', ie_path)) { // Search initiated from a traditional Search $_POST form/action; non-search page
				terms_obj.val(getURLParameter('terms', ie_path));
				callSearch(this);
			}
		}

		/* Event Listeners*/

		if( archive_search.length ) {
			/*
			 * Autocomplete Keypress
			 *
			 * @uses:	pushParamsToUrl()
			 * @uses:	callSearch()
			 */
			jQuery(".autocomplete").keypress(function (event) {
				var terms;
				var from_page;
				var filter_parameters;

				if (event.which === 13) {
					terms = this.value;
					from_page = 1;
					filter_parameters = new Array();
					pushParamsToUrl(from_page, terms, filter_parameters);
					callSearch(this);
				}

			});

			jQuery('.page.search-result-page a').click(function (event) {
				if (Postmedia.Properties.isMobile) {
					pushClickedALinkToUrl();
				}
			});

			jQuery(document).on("click", "ul.ui-autocomplete li a", function (event) {
				terms = jQuery(this).text();
				from_page = 1;
				filter_parameters = new Array();
				pushParamsToUrl(from_page, terms, filter_parameters);
				callSearch(this);
			});

			jQuery('#header-search .search-button, #footer-search .search-button').click(function (event) {
				event.preventDefault();
				if (0 != jQuery('.run-archive-search').length) {
					clearResults(true);
				} else {
					clearResults();
				}
				callSearch(this);
			});

			jQuery('.search-button-post').click(function (event) {
				terms = getSearchTerm();
				clearResults();
				jQuery(location).attr("href", "/search/?terms=" + terms);
			});

			jQuery('body').on("click", '.search_these_terms_instead', function (event) {
				terms_obj.val(jQuery(this).text());
				callSearch(this);
			});

			jQuery('ul.filter-by li').click(function (event) {
				event.preventDefault();
				callSearch(this, 'filter_by');
			});

			jQuery('ul.all-media li').click(function (event) {
				event.preventDefault();
				callSearch(this, 'all_media_filter');
			});

			jQuery('button.news_filter').click(function (event) {
				event.preventDefault();
				callSearch(this, 'news_filter');
			});

			jQuery('button.reviews_filter').click(function (event) {
				event.preventDefault();
				callSearch(this, 'reviews_filter');
			});

			jQuery('div.filter-tag button').click(function (event) {
				event.preventDefault();
				callSearch(this, 'model_filters');
			});

			jQuery('ul.sort-by li').click(function (event) {
				event.preventDefault();
				callSearch(this, 'sort_by');
			});

			jQuery('ul.year-filter li').click(function (event) {
				event.preventDefault();
				callSearch(this, 'year_filter');
			});

			jQuery('ul.body-style-filter li').click(function (event) {
				event.preventDefault();
				callSearch(this, 'body_style_filter');
			});

			jQuery('ul.auto-shows-filter li').click(function (event) {
				event.preventDefault();
				callSearch(this, 'auto_shows_filter');
			});

			jQuery('ul.filter_makes li').click(function (event) {
				event.preventDefault();
				var make = jQuery(this).data('slug');
				var filter_params = {};

				if (jQuery('ul.filter_makes li').first().hasClass('selected')) {
					filter_params = {
						'mpvf': ''
					};
					callSearch(this, 'make_filter', filter_params);
				} else {
					callSearch(this, 'make_filter');
				}

				if (0 < jQuery('#pv_models').length) {
					loadModels(this, make);
				}

			});

			jQuery('body').on('click', 'ul.filter_models li', function (event) {
				event.preventDefault();
				jQuery(this).parent().parent().find('button').html(this.innerText + '<span></span>');
				callSearch(this, 'model_pv_filter');
				jQuery('.filter_models').hide();
			});

			jQuery('ul.filter_authors li').click(function (event) {
				event.preventDefault();
				callSearch(this, 'author_filter');
			});

			jQuery('button.run-archive-search').click(function (event) {
				event.preventDefault();
				clearUrlParameters();
				callSearch(this);
			});

		}

		jQuery(".compare-page").on("click", ".paging a", function (event) {
			event.preventDefault();
			if( ! jQuery(this).hasClass('active') ) {
				jQuery('.compare-search-results').stop().animate({ opacity: 0.3 },500);
				var filters = getCompareFilters();
				callSearch(this, filters);
			}
		});

		jQuery('.mob .more-results').click(function (event) {
			event.preventDefault();
			var is_inactive = jQuery('.more-results').hasClass('inactive');
			if (!is_inactive) {
				var filters = getCompareFilters();
				callSearch(this, 'load_more', filters);
			}
		});

		/*
		 * Default text events
		 *
		 * by appending a class = 'default-text' to a <input> and setting the title attribute to whatever you want as the
		 * default text then this code will put the default text in the input until focus or if the value of the input is
		 * not empty.
		 */
		default_text_obj.focus(function (src) {
			if (jQuery(this).val() == jQuery(this)[0].title) {
				jQuery(this).removeClass("default-text-active");
				jQuery(this).val("");
			}
		});

		default_text_obj.blur(function () {
			if (jQuery(this).val() == "") {
				jQuery(this).addClass("default-text-active");
				jQuery(this).val(jQuery(this)[0].title);
			}
		});


		/*
		 * If a user removes the term run a search to return the default archive search content
		 */
		jQuery("#archive_terms").keyup(function () {
			var archive_search = jQuery('em.run-archive-search');

			if (jQuery(this).val() == "") {
				if (archive_search.length) {
					clearUrlParameters();
					callSearch(archive_search);
				}
			}
		});

		/*
		 * Search box: If a user removes the term then clear url parameters
		 */
		jQuery("#terms").keyup(function () {
			if (jQuery(this).val() == "") {
				clearUrlParameters();
			}
		});

		/*
		 * Searchbox with autocomplete
		 */
		if (terms_obj.hasClass('autocomplete')) {

			_apiServCall = "http://app.canada.com";
			_num = 5;
			_minLength = 3;
			_defaultText = "Search Driving.ca";
			_index = "/essearch/search.svc/autocomplete/drvtaxonomy";
			_afield = "display.autocomplete";
			_site = "driving";

			jQuery(".autocomplete").autocomplete({
				source: function (request, response) {
					return jQuery.ajax({
						url: _apiServCall + _index,
						dataType: "jsonp",
						data: {
							q: request.term,
							afield: _afield,
							format: "json",
							num: _num,
							site: _site
						},
						success: function (data) {
							return response(jQuery.map(data.Terms, function (item) {
								return {
									label: item,
									value: item
								};
							}));
						}
					});
				},
				minLength: _minLength
			});
		}

		/*
		 * Get 'from_page' value || Special need to use this instead of getURLParameter()
		 *
		 * When the AJAX call is doSearch() the clicked link adds a 'from_page' page parameter. This is not
		 * seen in the URL as it is handled on the AJAX call and does not refress the page / URL
		 *
		 * @uses:	indexOf()
		 * @uses: 	split()
		 * @uses: 	replace()
		 * @uses:	isSetURLParameter()
		 *
		 * @param:	_this		- (this) the current selected element that callSearch() the 'this' object is required
		 * @param:	p_terms		- (string) the p_terms are the term(s) set in the p_terms url param or is set from the #terms input
		 */
		function getCurrentResultPage(_this, p_terms) {
			var from_page;
			var url = location.href;
			var terms_contains_default_search_text;
			var ie_path = jQuery(location).attr('href'); // get IE capable path

			if ('undefined' !== _this.href) {
				url = _this.href;
			}

			if (null != p_terms) {
				if (-1 != p_terms.indexOf('Search')) {
					terms_contains_default_search_text = true;
				} else {
					terms_contains_default_search_text = false;
				}
			} else {
				terms_contains_default_search_text = false;
			}

			if (url === undefined & terms_contains_default_search_text) {
				from_page = null;
			} else if (url === undefined & !terms_contains_default_search_text) {
				from_page = 1;
			} else {
				if (isSetURLParameter('from_page', ie_path)) {
					from_page = url.split('?')[1]; // get all the url parameters

					var matches = from_page.match(/from_page=([^&]*)/);

					if ('string' == typeof from_page && 0 != from_page.replace(/from_page=/, '')) {
						//from_page = from_page.replace(/from_page=/, '');
						from_page = matches[1];
						return parseInt(from_page);
					}
					from_page = from_page.split('&'); // split url parameters into an array
					from_page_count = from_page.length - 1;
					if (0 == from_page_count) {
						return 1;
					}

					for (i = 0; i <= from_page_count; i++) { // iterate to see if a parameter has from_page variable
						if (i == from_page[i].indexOf('from_page')) {
							from_page = from_page[i]; // convert array to index that has from_page variable
						}
					}

					from_page = from_page.replace(/from_page=/, ''); // strip out the 'from_page=' and leave just the number
				} else {
					from_page = 1;
				}
			}

			return parseInt(from_page);
		}

		/*
		 * Do AJAX Search
		 *
		 * @uses: 	getCurrentResultPage()
		 * @uses: 	getFormattedData( data )
		 * @uses: 	pushTotalPagesToUrl( total_pages ) for 'load-more' functionality for mobiles
		 * @uses: 	isSetURLParameter()
		 * @uses: 	getURLParameter()
		 * @uses: 	jQuery.ajax()
		 * @uses: 	jQuery.fadeIn()
		 * @uses: 	jQuery.hide()
		 * @uses: 	jQuery.html()
		 * @uses: 	jQuery.attr()
		 * @uses: 	jQuery.append()
		 * @uses: 	Math.ceil()
		 * @uses: 	Pagination object Run Ajax search that calls the do-search.php which is hooked
		 * 		  		into the Elastica library
		 * @uses: 	Pagination.paginate()
		 *
		 * @param:	_this		- (this) The current button, link or <li> that was selected
		 * @param:	from_page	- (string or integer) The from_page passed to ElasticSearch as 'from'
		 * @param:	p_terms		- (string) The terms set in the p_terms URL parameter or set to the '#terms' input value
		 * @param:	filters		- (array) An array of any filters applied to the search
		 * @param:	load_more	- (string) If this flag is set then the code will use search mobile functionality
		 */

		function doSearch(_this, from_page, p_terms, filters, load_more) { 
			var archive_search = jQuery('em.run-archive-search');
			var obj = {};
			var terms;
			var original_terms;
			var hit_count;
			var number_of_records_per_page;
			var mobile_limit;
			var total_pages;
			var current_page;
			var parameters_string;
			var show_this_many_pagination_links;
			var objPagination;
			var links;
			var results_start;
			var results_end;
			var results_x_to_y_of_z_text;
			var css_classes;
			var es_index;
			var ie_path = jQuery(location).attr('href'); // get IE capable path

			obj.page = 'search';
			obj.results = jQuery('.results');
			obj.moreresults = jQuery('.more-results');
			obj.paging = jQuery('.paging');
			obj.status = jQuery('.result-status');
			obj.totalitems = jQuery('.total-items');

			if (undefined === p_terms) {
				p_terms = '';
			}

			from_page = parseInt(from_page);

			if (isSetURLParameter('hits', ie_path)) {
				hit_count = getURLParameter('hits', ie_path);
			}

			es_index = (jQuery('input[name=es_index]').size() > 0) ? jQuery('input[name=es_index]').val() : null;

			jQuery.ajax({
				type: "POST",
				url: Postmedia.Urls.home_url + '/wp-admin/admin-ajax.php',
				data: {
					action: 'do_search',
					terms: p_terms,
					from_page: from_page,
					hits: hit_count,
					filters: filters,
					es_index: es_index
				},
				success: function (data) {

					if( es_index != null ) {
						obj.page = 'compare';
						obj.results = jQuery('.compare-search-results');
					}
					if (null != data) {

						if (data.original_terms) { // A suggested search has occured so this is the original terms a user provided
							original_terms = data.original_terms;
						} else if (data.terms) {
							original_terms = data.terms;
						}

						getArchiveSearchData( data, obj, archive_search );

						getPagination( data, obj, from_page, ie_path, load_more, archive_search );

						if ( data.facets && ( ! jQuery.isEmptyObject( data.facets ) ||  data.facets.length > 0 ) ) {
							$( document ).trigger( "facets::changed", data.facets );
						}
						$( document ).trigger( "ajax::loaded", '.compare-search-results' );
					} // end data is null wrapper
				},
				error: function (xhr, ajaxOptions, thrownError) { // log errors to the console
					console.log(xhr.status);
					console.log(thrownError);
				}
			});
		}

		/*
		 * Get a specific URL parameter
		 *
		 * @uses: 	decodeURIComponent()
		 * @uses: 	RegEXP()
		 * @uses: 	exec()
		 * @uses: 	replace()
		 *
		 * @param: 	name		- (string) the name of a URL paramater ie) if the the Url has ?from_page=1&p_terms='Tesla'&sb=date 'sb'
		 *                    		would be the name and 'date' would be returned
		 * @param: 	ie_path	- (string) a jQuery href.location string that contains all url parameters
		 */
		function getURLParameter(name, ie_path) {
			if (ie_path === undefined || ie_path == 'undefined' || ie_path == null) {
				return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)', 'i').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null;
			} else {
				return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)', 'i').exec(ie_path) || [, ""])[1].replace(/\+/g, '%20')) || null;
			}
		}

		/*
		 * Check if a specific URL parameter has a value
		 *
		 * @uses: 	RegEXP()
		 * @uses: 	exec()
		 *
		 * @param: 	name		- (string) the name of a URL paramater ie) if the the Url has ?from_page=1&p_terms='Tesla'&sb=date 'sb'
		 *                    			   would be the name and true would be returned as the value is not null
		 */
		function isSetURLParameter(name, ie_path) {
			if (ie_path === undefined || ie_path == 'undefined' || ie_path == null) {
				return (new RegExp('[?|&]' + name + '(?:[=|&|#|;|]|$)', 'i').exec(location.search) !== null);
			} else {
				return (new RegExp('[?|&]' + name + '(?:[=|&|#|;|]|$)', 'i').exec(ie_path) !== null);
			}
		}

		/*
		 * Returns a valid url parameter value
		 *
		 * When filter; a button, or list item is selected the binded event plus the match_this_string is passed through
		 * to this fucntion.
		 *
		 * @uses: 	toLowerCase()
		 * @uses: 	jQuery.text()
		 * @uses: 	getURLParameter()
		 *
		 * @param: 	_this 					- (this) the object a user has selected or the page ( on page load )
		 * @param: 	selected_filter_val 	- (string) filter value passed in during the click, load, or focus event
		 * @param: 	match_this_string 		- (string) set as the valid filter to be called this string will match the controller
		 * 							      			   code in the pm-es-search.php do_search_callback() method
		 * @param: 	param_name 				- (string) the querystring parameter key that matches _this filter
		 * @param: 	return_name 			- (string) returns _this's text
		 *
		 */
		function parseParam(_this, selected_filter_val, match_this_string, param_name, return_name) {
			var text;
			var data_slug;
			var is_number;
			var ie_path = jQuery(location).attr('href'); // get IE capable path

			if (1 == return_name & selected_filter_val == match_this_string) { // Return the element's text and not the data-slug
				text = jQuery(_this).text();
				return text;
			} else if (selected_filter_val == match_this_string) { // Return value by data-slug; Filter By term
				data_slug = jQuery(_this).data('slug');
				if ('number' === typeof (data_slug + 0)) {
					return data_slug;
				} else {
					return data_slug.toLowerCase();
				}
			} else { // User has not chosen a Filter By term but the URL has bookmarked Filter By term
				return getURLParameter(param_name, ie_path);
			}
		}

		/*
		 * Set the 'load more' filter state
		 *
		 * For 'load more' if a filter is added or changed then the existing
		 * results ( that are already filtered ) need to be cleared and then the new filter ordered
		 * results will be added.
		 *
		 * If a filter already exists and is not changed then don't clear the results and
		 * append the load more results with the same filter
		 *
		 * @param: filter		- (string) the filter that is either added or changed
		 * @param: param_string	- (string) the URL parameter name ie: 'sb'
		 */
		function mobileSetLoadMoreFilterState(filter, param_string) {
			var ie_path = jQuery(location).attr('href'); // get IE capable path
			var filter_param_state;

			filter_param_state = isSetURLParameter(param_string, ie_path);

			if (filter) {
				switch (filter_param_state) {
				case true:
					if (filter != getURLParameter(param_string, ie_path)) { // the new sort filter doesn't match the old sort filter
						clearResults();
					}
					break;
				case false: // Sort is set but not existing sort filter exists, so clear the results so the sorted content can be loaded
					clearResults();
					break;
				}
			}
		}

		/*
		 * Get the current terms by filtering for default content and looking for
		 * terms either in the URL p_terms variable or in the <input id='terms'
		 *
		 * @uses: 	getURLParameter()
		 * @uses: 	clearResults()
		 *
		 */
		function getTerms() {
			var ie_path = jQuery(location).attr('href'); // get IE capable path
			var terms;
			var p_terms;
			var terms;
			var terms_contains_default_text;
			var archive_terms_contains_default_text;

			/*
			 * IE handles the query string processing at a later event stage so even though the parameter p_terms is updated
			 * in the URL IE doesn't see it in time for us to just look for the value of p_terms; therefore we set a path
			 * that jQuery finds ( ie_path )that includes the up-to-date query parameter and we pass this to use in getURLParameter()
			 */
			p_terms = getURLParameter('p_terms', ie_path);
			terms = getSearchTerm();
			archive_terms = jQuery('#archive_terms').val();

			if (p_terms) {
				if (-1 != p_terms.indexOf('Search')) {
					p_terms = '';
				} else {
					if (undefined !== terms || '' != terms && terms != p_terms) {
						return terms;
					} else {
						return p_terms;
					}
				}
			}



			if (undefined !== terms) {
				if (-1 != terms.indexOf('Search')) {
					terms_contains_default_text = true;
					p_terms = "";
				} else {
					terms_contains_default_text = false;
				}
			} else {
				terms_contains_default_text = false;
			}

			if (undefined === archive_terms | "" == archive_terms) {
				archive_terms_contains_default_text = true;
			} else {
				if (-1 != archive_terms.indexOf('Search')) {
					archive_terms = '';
				}
				archive_terms_contains_default_text = false;
			}

			if (terms_contains_default_text && 0 < jQuery('#archive_terms').length) {
				clearResults(true);
				p_terms = archive_terms;
			} else if (!terms_contains_default_text && 0 < jQuery('#archive_terms').length) {
				p_terms = archive_terms;
			} else if (null == p_terms && terms_contains_default_text && null == terms && true == archive_terms_contains_default_text) {
				clearResults()
				return;
			} else if (p_terms !== null && terms_contains_default_text) {
				terms_obj.val(p_terms); // set the search input to the param val
			} else if (terms != p_terms && false == terms_contains_default_text) {
				clearResults()
				p_terms = terms; // set param p_terms value to the search input value
			}


			return p_terms;
		}

		/*
		 * Get the current from_page value
		 *
		 * @uses: 	getCurrentResultPage()
		 * @uses: 	getURLParameter()
		 * @uses: 	clearResults()
		 * @uses: 	parseInt()
		 *
		 * @param: 	_this		- (this) current element or page load event as 'this'
		 * @param: 	p_terms		- the terms URL (and sometimes the term <input) value
		 */
		function getFrom(_this, p_terms, filter_or_sort, is_mobile, load_more) {
			var from_page;

			var ie_path = jQuery(location).attr('href'); // get IE capable path

			from_page = getCurrentResultPage(_this, p_terms);

			if (undefined === from_page) {
				//clearResults();
				//return;
			}

			if (is_mobile) {
				if ('sort_by' == filter_or_sort || 'all_media_filter' == filter_or_sort) {
					from_page = from_page;
				} else {
					if (load_more) {
						from_page++;
					} else if (1 == from_page) {
						from_page = from_page;
					} else {
						from_page++;
					}
				}
			}

			return from_page;
		}

		function getFilter(_this, filter_or_sort, by_filter_or_sort_val) {
			var nullify_terms = [];
			var ul_and_link_based_filters = {};
			var return_by_text = true;
			var param_value;
			var param_key;
			var count;
			var i = 0;

			ul_and_link_based_filters = {
				"sort_by": ['sb'],
				"author_filter": ['auf'],
				"all_media_filter": ['amf'],
				"year_filter": ['yf', return_by_text],
				"make_filter": ['mkf', return_by_text],
				"model_pv_filter": ['mpvf', return_by_text],
				"body_style_filter": ['bsf', return_by_text],
				"special_section_filter": ['ssf', return_by_text],
				"auto_shows_filter": ['asf', return_by_text],
				"model_filters": ['mf']
			};



			/*
			 * Array of default text values that are set as the first <option> or <li> item in the drop-down search filters
			 * Any value that is used for these needs to be included in this list otherwise the search will set the search
			 * parameter to the default value and the search will return 0 results.
			 */
			nullify_terms = [
				'All Vehicle Years'
				, 'All Years'
				, 'Years'
				, 'All Auto Shows'
				, 'Auto Shows'
				, 'All Media'
				, 'all_media'
				, 'Media'
				, 'All Galleries'
				, 'Car Brands'
				, 'All Brands'
				, 'Makes'
				, 'All Models'
				, 'MODELS'
				, 'relevance'
				, 'Authors'
				, 'All Authors'
				, 'All Body Styles'
				, 'BODY STYLES'
			];

			/*year_filter = parseParam( _this, filter_or_sort, 'year_filter'		,  'yf',  return_name );
			year_filter = nullifyDefaultValues( year_filter , nullify_terms );*/

			switch (filter_or_sort) {
			case 'sort_by':
			case 'author_filter':
			case 'all_media_filter':
			case 'body_style_filter':
			case 'special_section_filter':
			case 'year_filter':
			case 'make_filter':
			case 'model_pv_filter':
			case 'auto_shows_filter':
			case 'model_filters':
			case undefined:
				for (var key in ul_and_link_based_filters) {
					if (key == by_filter_or_sort_val) {
						if (ul_and_link_based_filters.hasOwnProperty(key)) {
							param_key = ul_and_link_based_filters[key][0];

							count = ul_and_link_based_filters[key].length;

							if (count == 1) {
								param_value = parseParam(_this, key, filter_or_sort, ul_and_link_based_filters[key][0]);
							} else {
								param_value = parseParam(_this, key, filter_or_sort, ul_and_link_based_filters[key][0], ul_and_link_based_filters[key][1]);
							}

							param_value = nullifyDefaultValues(param_value, nullify_terms);

						}
					}
					i++;
				}
				break;
			}

			return param_value;
		}

		/*
		 * Call search
		 *
		 * Check URL parameters and setups search depenencies i.e if filter by or sort by are set before calling search.
		 *
		 * @uses: 	getURLParameter()
		 * @uses: 	isSetURLParameter()
		 * @uses: 	clearResults()
		 * @uses: 	parseParam()
		 * @uses: 	nullifyDefaultValues()
		 * @uses: 	mobileSetLoadMoreFilterState()
		 * @uses: 	getCurrentResultPage()
		 * @uses: 	parseInt()
		 * @uses: 	pushParamsToUrl()
		 *
		 * @param: 	_this			- (this) the object a user has selected or the page ( on page load )
		 * @param: 	filter_or_sort	- (string) the current filter or sort that is passed in the click event of _this
		 */
		function callSearch(_this, filter_or_sort, filter_params) {
			var terms = terms_obj.val(); // set the terms variable
			var from_page;
			var ie_path = jQuery(location).attr('href'); // get IE capable path
			var archive_search;
			var p_terms;
			var nullify_terms;
			var sort_by;
			var all_media_filter;
			var category;
			var subcategory;
			var tags;
			var taxonomy_or_category;
			var archive_filter_within;
			var archive_search_terms;
			var archive_model;
			var return_name;
			var nullify_terms;
			var model_filters;
			var model_pv_filter;
			var model_param_values;
			var split_model_filters;
			var current_removed_model_filter;
			var make_filter;
			var sort_by;
			var review_filter;
			var bn_news_filter;
			var news_filter;
			var year_filter;
			var body_style_filter;
			var special_section_filter;
			var sub_special_section_filter;
			var auto_show_filter;
			var all_media_filter;
			var make_filter;
			var author_filter;
			var photo_filter;
			var video_filter;
			var filter_params;
			var is_mobile;
			var match_all_query = 1;

			p_terms = getTerms();

			is_mobile = Postmedia.Properties.isMobile; // Get the isMobile global value from Postmedia variable set in the page head

			if (!filter_params)
				filter_params = {};

			archive_search = jQuery('.run-archive-search').length // set target to test if 'this' is the archive search

			if (is_mobile) { // Set mobile filters

				/*
				 * Array of default text values that are set
				 *
				 * The first <option> or <li> item in the drop-down searchfilters
				 *
				 * All default values need to be included in this list, otherwise the search will
				 * set the search parameter to the default value and the search will return 0 results.
				 */
				nullify_terms = new Array(
					'relevance', 'all_media'
				);


				sort_by = getFilter(_this, filter_or_sort, 'sort_by');
				if ('sort_by' == filter_or_sort) {
					sort_by = getFilter(_this, filter_or_sort, 'sort_by');
				} else if (isSetURLParameter('sb', ie_path)) { // If sort is already set then keep it in the current search call
					sort_by = getURLParameter('sb', ie_path);
				}

				if (isSetURLParameter('amf', ie_path)) { // If media type is already set then keep it in the current search call
					all_media_filter = getURLParameter('amf', ie_path);
				} else {
					all_media_filter = getFilter(_this, filter_or_sort, 'all_media_filter'); // See if sort by filter has be selected by the user
				}

				if ('load_more' === filter_or_sort) {
					from_page = getFrom(_this, p_terms, filter_or_sort, is_mobile, filter_or_sort);
				} else {
					from_page = getFrom(_this, p_terms, filter_or_sort, is_mobile);
				}

			} else { // Set desktop filters

				from_page = getFrom(_this, p_terms);
				if (0 < archive_search) {
					taxonomy_or_category = jQuery('#taxonomy_or_category').val();
					archive_filter_within = jQuery('#archive_filter_within').val();

					switch (taxonomy_or_category) {
					case 'Category':
						category = taxonomy_obj.parent;
						if (taxonomy_obj.child) {
							subcategory = taxonomy_obj.child;
						}
						break;

					case 'Photos':
					case 'Video':
						category = cat_scat.category;
						break;

					case 'Make':
						make_filter = taxonomy_obj.parent;
						break;

					case 'Model':
						model_pv_filter = taxonomy_obj.child;
						break;

					case 'Bodystyle':
						body_style_filter = taxonomy_obj.parent;
						break;

					case 'Specialsection':
						special_section_filter = taxonomy_obj.parent;
						if (taxonomy_obj.child) {
							sub_special_section_filter = taxonomy_obj.child;
						}
						break;

					}

					archive_model = jQuery('#archive_model_term').val();
				}


				/*
				 * Array of default text values that are set
				 *
				 * The first <option> or <li> item in the drop-down searchfilters
				 *
				 * All default values need to be included in this list, otherwise the search will
				 * set the search parameter to the default value and the search will return 0 results.
				 */
				nullify_terms = new Array(
					'All Vehicle Years', 'All Years', 'Years', 'All Auto Shows', 'Auto Shows', 'All Media', 'all_media', 'Media', 'All Galleries', 'Car Brands', 'All Brands', 'Makes', 'relevance', 'Authors', 'All Authors', 'All Body Styles', 'BODY STYLES'
				);


				/* Sort filter */
				sort_by = getFilter(_this, filter_or_sort, 'sort_by');
				return_name = true; // flag set so when parseParam() is called using this param it should return the clicked objects text and not its' data-slug

				/* Multi model filter(s) */
				if (!archive_model) { // if it is not an archive search on the models page
					if ('model_filters' == filter_or_sort) { // Concatenate filters
						if (jQuery(_this).hasClass('active')) {
							model_filters = getURLParameter('mf', ie_path);
							if (model_filters) { // When a model filter exists in the URL then build a model filter comma delimited string
								if (model_filters != parseParam(_this, filter_or_sort, 'model_filters', 'mf', return_name)) {
									model_filters = model_filters + "," + parseParam(_this, filter_or_sort, 'model_filters', 'mf', return_name);
								}
							} else {
								model_filters = parseParam(_this, filter_or_sort, 'model_filters', 'mf', return_name);
							}
						} else { // Remove Parameters
							model_filters = '';
							model_param_values = getURLParameter('mf', ie_path); // mf values can be a concatenated string like &mf=Mazda5,Mazda3,MX-5
							if (model_param_values != null) {
								split_model_filters = model_param_values.split(','); // convert parameter to array by splitting on the comma
								if (0 != split_model_filters.length) { // get the current value that was just clicked and should be removed
									current_removed_model_filter = parseParam(_this, filter_or_sort, 'model_filters', 'mf', return_name);
									for (j = 0; j < split_model_filters.length; j++) {
										if (split_model_filters[j] != current_removed_model_filter) {
											if (model_filters == '') {
												model_filters = split_model_filters[j];
											} else {
												model_filters = model_filters + ',' + split_model_filters[j];
											}
										}
									}
									if (model_filters == '') {
										model_filters = null;
									}
								}
							}
						}
					} else if (undefined === model_filters) {
						model_filters = getURLParameter('mf', ie_path);
					}
				} else {
					model_filters = archive_model;
					p_terms = p_terms + ' ' + archive_filter_within + ' ' + archive_model;
				}
				/* Review button filter */
				if (jQuery('button.reviews_filter').hasClass("active")) {
					review_filter = parseParam(_this, filter_or_sort, 'reviews_filter', 'rf');
				}

				/* News button filter */
				bn_news_filter = document.querySelector(".news_filter");
				if (jQuery('button.news_filter').hasClass("active")) {
					news_filter = parseParam(_this, filter_or_sort, 'news_filter', 'nf');
				}

				// Year list filter */
				year_filter = getFilter(_this, filter_or_sort, 'year_filter');

				/* Body style list filter */
				if (!body_style_filter) {
					body_style_filter = getFilter(_this, filter_or_sort, 'body_style_filter');
				}

				/* Special Section list filter */
				if (!special_section_filter) {
					special_section_filter = getFilter(_this, filter_or_sort, 'special_section_filter');
				}

				/* Sub Special Section list filter */
				if (!sub_special_section_filter) {
					sub_special_section_filter = getFilter(_this, filter_or_sort, 'sub_special_section_filter');
				}

				/* Auto-show list filter */
				auto_show_filter = getFilter(_this, filter_or_sort, 'auto_shows_filter');

				/* All media filter */
				if (!all_media_filter) {
					all_media_filter = getFilter(_this, filter_or_sort, 'all_media_filter');
				}

				/* Make filter */
				if (!make_filter) {
					make_filter = getFilter(_this, filter_or_sort, 'make_filter');
				}

				/* Model photo, video and bodystyle filter */
				model_pv_filter = getFilter(_this, filter_or_sort, 'model_pv_filter');

				/* Author filter */
				author_filter = getFilter(_this, filter_or_sort, 'author_filter');

				/* Used for photo page; sets the filter  */
				if ('photos' == p_terms) {
					photo_filter = 'PhotoGallery';
				}

				/* Used for video page; sets the filter */
				if ('videos' == p_terms) {
					video_filter = 'Videos';
				}

				if (!category) {
					category = getFilter(_this, filter_or_sort, 'category');
				}

				if (!subcategory) {
					subcategory = getFilter(_this, filter_or_sort, 'category');
				}
			}

			/*
			 * Shared code between the mobile & desktop
			 */
			if (filter_or_sort === Object(filter_or_sort)) {
				filter_params = filter_or_sort;
			}

			if (!filter_params.sb && undefined !== sort_by)
				filter_params.sb = sort_by;

			if (!filter_params.rf && undefined !== review_filter)
				filter_params.rf = review_filter;

			if (!filter_params.yf && undefined !== year_filter)
				filter_params.yf = year_filter;

			if (!filter_params.nf && undefined !== news_filter)
				filter_params.nf = news_filter;

			if (!filter_params.asf && undefined !== auto_show_filter)
				filter_params.asf = auto_show_filter;

			if (!filter_params.amf && undefined !== all_media_filter)
				filter_params.amf = all_media_filter;

			if (!filter_params.mkf && undefined !== make_filter)
				filter_params.mkf = make_filter;

			if (!filter_params.mf && undefined !== model_filters)
				filter_params.mf = model_filters;

			/* filter_parms.mpvf being null means that model filter is set but car brands
			 * has been set to default. This parameter set to null removes it from query parameters
			 */
			if (!filter_params.mpvf && undefined !== model_pv_filter) {
				filter_params.mpvf = model_pv_filter;
			} else if ('' == filter_params.mpvf) {
				filter_params.mpvf = null;
			}


			if (!filter_params.auf && undefined !== author_filter)
				filter_params.auf = author_filter;

			if (!filter_params.bsf && undefined !== body_style_filter)
				filter_params.bsf = body_style_filter;

			if (!filter_params.ssf && undefined !== special_section_filter)
				filter_params.ssf = special_section_filter;

			if (!filter_params.sssf && undefined !== sub_special_section_filter)
				filter_params.sssf = sub_special_section_filter;

			if (!filter_params.vf && undefined !== video_filter)
				filter_params.vf = video_filter;

			if (!filter_params.pf && undefined !== photo_filter)
				filter_params.pf = photo_filter;

			if (!filter_params.cat && undefined !== category)
				filter_params.cat = category;

			if (!filter_params.scat && undefined !== subcategory)
				filter_params.scat = subcategory;

			if (!filter_params.tag && undefined !== tags)
				filter_params.tag = tags;


			/*filter_params = { // All possible filters stored in a object literal

					'sb'	: sort_by
					, 'rf'  : review_filter
					, 'yf'  : year_filter
					, 'nf'  : news_filter
					, 'asf' : auto_show_filter
					, 'amf' : all_media_filter
					, 'mkf' : make_filter
					, 'mf'  : model_filters
					, 'mpvf': model_pv_filter
					, 'auf' : author_filter
					, 'bsf' : body_style_filter
					, 'vf'  : video_filter
					, 'pf'  : photo_filter
			};*/

			/*
			 * The initial search page is loaded and a user has typed in term(s). This runs also when a user has selected
			 * a paging link
			 */

			pushParamsToUrl(from_page, p_terms, filter_params); // push values to the url params and browser history

			filter_params.maq = match_all_query;

			if ('load_more' == filter_or_sort) { // if mobile 'load_more' action
				from_page = getFrom(_this, p_terms);
				doSearch(this, from_page, p_terms, filter_params, 'load_more');
			} else {
				doSearch(this, from_page, p_terms, filter_params);
			}

		}

		/*
		 * Returns null on match values
		 *
		 * Null is returned if the  value matches any default value in the default_string_array
		 * Null needs to be set for any default values. On search any nullified params will be removed from the URL
		 *
		 * @param: 	value					- (string or integer) value to be checked if it is null
		 * @param: 	default_string_array	- (array) array that contains the default values for the different filters
		 */
		function nullifyDefaultValues(value, default_string_array) {
			var compare_value;
			var default_string;

			if (null == value) {
				return null;
			}

			if ('number' == typeof (value + 0)) {
				compare_value = value;
			} else {
				compare_value = value.toLowerCase();
			}

			default_string = '';

			for (i = 0; i < default_string_array.length; i++) {
				default_string = default_string_array[i].toLowerCase();
				if (compare_value == default_string) {
					return null;
				}
			}
			return value; // made it here so value is not null
		}

		/*
		 * Push URL Parameter
		 *
		 * Configure the URL with the appropriate number and type of parameters from_page = define from what page to start
		 * p_terms = term(s) filter_parameters = object of all possible filter_parameters
		 *
		 * @param: 	from_page			- (string or integer) the current from page value
		 * @param: 	terms				- (string) the search terms
		 * @param: 	filter_parameters 	- (array) array of filters
		 */
		function pushParamsToUrl(from_page, terms, filter_parameters) {
			var active_filters = {};

			for (j in filter_parameters) {
				if (filter_parameters.hasOwnProperty(j)) { // use non-prototype properties only
					if (filter_parameters[j] != null && typeof (filter_parameters[j]) != 'object') {
						active_filters[j] = filter_parameters[j];
					}
				}
			}

			updateUrl(from_page, terms, active_filters);
		}

		/*
		 * Update the URL and Browser History
		 *
		 * @uses: 	History object (native-history.js)
		 * @uses: 	History.Adapter
		 * @uses: 	History.getState()
		 * @uses: 	History.pushState()
		 * @uses: 	getURLParameter()
		 *
		 * @param: 	from_page			- (string or integer) the current from page value
		 * @param: 	terms				- (string) the search terms
		 * @param: 	filter_parameters 	- (array) array of active filters
		 */
		function updateUrl(from_page, p_terms, active_filters) {
			var State;
			var push_string;
			var ie_path = jQuery(location).attr('href'); // get IE capable path
			var hits = getURLParameter('hits', ie_path);
			var hits_string;
			var param_val;

			History.Adapter.bind(window, 'statechange', function () { // Bind to StateChange event
				State = History.getState();
			});

			var check_hash = ie_path.indexOf('#');
			var check_question = ie_path.indexOf('?');
			if( check_hash < check_question ) {
				ie_path = ie_path.substr( 0, check_hash ) + ie_path.substr( check_question );
			}
			else {
				ie_path = ie_path.substr( 0, check_hash );
			}

			var has_page_id = getURLParameter('page_id', ie_path);

			var is_set_from_page = isSetURLParameter( 'from_page', ie_path );
			var is_set_p_terms = isSetURLParameter( 'p_terms', ie_path );
			var is_set_hits = isSetURLParameter( 'hits', ie_path );
			var is_set_page_id = isSetURLParameter( 'page_id', ie_path );

			var query_string = '';

			from_page = parseInt( from_page );

			if( is_set_from_page ) {
				ie_path = ie_path.replace(/(from_page=)[^\&]+/, '$1' + from_page );
			}
			else {
				query_string += '&from_page=1';
			}

			if( is_set_p_terms ) {
				ie_path = ie_path.replace(/(p_terms=)[^\&]+/, '$1' + p_terms);
			}
			else {
				query_string += '&p_terms=';
			}

			if( is_set_hits ) {
				ie_path = ie_path.replace(/(hits=)[^\&]+/, '$1' + hits);
			}
			else if( hits != null ) {
				query_string += '&hits=' + hits;
			}

			if( is_set_page_id ) {
				ie_path = ie_path.replace(/(page_id=)[^\&]+/, '$1' + has_page_id);
			}
			else if( has_page_id != null ) {
				query_string += '&page_id=' + has_page_id;
			}

			if( ie_path.indexOf('?') != -1 ) {
				query_string = ie_path + query_string;
			}
			else {
				query_string = ie_path + '?' + query_string;
			}

			query_string = query_string.replace( '?&', '?');

			/*
			 * Add required parameters from_page and p_terms to the url string
			 */
			if (undefined !== p_terms && from_page) {

				push_string = query_string;

				for (k in active_filters) {
					if (active_filters.hasOwnProperty(k)) { // use non-prototype properties only
						// k is the key / parameter name and active_filters[k] is the corresponding value
						// append as many parameters as housed in active_filters
						param_val = active_filters[k];
						push_string += '&' + k + '=' + param_val;
					}
				}


				History.pushState(null, page_title, push_string); // Update the history and URL
			}
		}

		/*
		 * Push Total Pages to the URL
		 *
		 * @uses: 	History object (native-history.js)
		 * @uses: 	History.hash
		 * @uses: 	History.getState()
		 * @uses: 	History.pushState()
		 *
		 * @param: 	count	- (integer) page count
		 */
		function pushHitsToUrl(count) {
			var State;
			var push_string;

			State = History.getState();
			push_string = State.hash + '&hits=' + count;
			History.pushState(null, null, push_string); // Update the history and URL
		}

		function pushClickedALinkToUrl() {
			var State;
			var push_string;

			State = History.getState();
			push_string = State.hash + '&a_hit=1';
			History.pushState(null, null, push_string); // Update the history and URL
		}

		/*
		 * Bookmarked or Returned-to links exclusively make use of this function
		 *
		 * Called iteratively to update the the URL path with the current filters and term
		 * but from_page is based on an iterative value
		 *
		 * @uses: 	History object (native-history.js)
		 * @uses: 	History.pushState()
		 *
		 * @param: 	from_page	- (integer) from_page value based on an interated value
		 */
		function pushFromPageToUrlMobile(from_page) {
			var State;
			var push_string;
			var filter_params;
			var ie_path = jQuery(location).attr('href'); // get IE capable path
			var sort_by;
			var all_media_filter;

			State = History.getState();

			if (isSetURLParameter('sb', ie_path)) {
				sort_by = getURLParameter('sb', ie_path);
			} else {
				sort_by = null;
			}

			if (isSetURLParameter('amf', ie_path)) {
				all_media_filter = getURLParameter('amf', ie_path);
			} else {
				all_media_filter = null;
			}

			filter_params = { // All possible mobile filters
				'sb': sort_by,
				'amf': all_media_filter
			};

			push_string = "?from_page=" + from_page + '&p_terms=' + p_terms;

			for (k in filter_params) {
				if (filter_params.hasOwnProperty(k)) { // use non-prototype properties only
					// k is the key / parameter name and active_filters[k] is the corresponding value
					// append as many parameters as housed in active_filters
					push_string += '&' + k + '=' + filter_params[k];
				}
			}

			History.pushState(null, null, push_string); // Update the history and URL
		}
		/*
		 *
		 */
		/*
		 * Clear URL paramaters
		 *
		 * @uses: 	History object (native-history.js)
		 * @uses: 	History.Adapter
		 * @uses: 	History.getState()
		 * @uses: 	History.pushState()
		 *
		 * @param: 	from_page	- (integer) from_page value based on an interated value
		 */
		function clearUrlParameters() {
			var path = location.href;
			var State;

			var pos = path.indexOf('?');
			if (-1 < pos) {
				path = path.substr(0, pos);
			}

			History.Adapter.bind(window, 'statechange', function () { // Bind to StateChange event
				State = History.getState(); // get the current state
			});

			if (-1 < path.indexOf('#./')) {
				path = path.replace('#.', '');
			}
			History.pushState(null, null, path); // Update the history and URL
		}

		/*
		 * Get all the url parameters and return as an object literal ( array )
		 *
		 * @uses:	substring();
		 * @uses:	split();
		 * @uses:	decodeURIComponent()
		 *
		 * returns:	object literal ( array )
		 */
		function getAllFiltersFromURL() {
			var url = location.href;
			var data_slug;
			var parent;
			var models;

			var filters = {};
			var pairs = url.substring(url.indexOf('?') + 1).split('&');
			if (1 != pairs.length) { // Only run if there are parameters
				for (var i = 0; i < pairs.length; i++) {
					var pair = pairs[i].split('=');
					switch (decodeURIComponent(pair[0])) {
					case 'p_terms':
					case 'from_page':
						break
					default:
						filters[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
						if ('mf' == decodeURIComponent(pair[0])) { // model(s)
							models = decodeURIComponent(pair[1]);
							models = convertToLowercaseWords(models);
							data_slug = '[data-slug="' + models + '"]';
							jQuery(data_slug).addClass('active');
						} else {
							data_slug = '[data-slug="' + decodeURIComponent(pair[1]) + '"]';
							parent = jQuery(data_slug).parent();
							jQuery(parent).children().removeClass('selected');
							jQuery(data_slug).addClass('selected');
							jQuery(parent).prev().html(jQuery(data_slug).text() + '<span></span>');
						}
					}
				}

				return filters;

			} else {
				return null;
			}
		}

		/*
		 * Object objPagination
		 *
		 * @param: 	_total_pages 	- (string or integer) the total number of pages to be paginated param: _current_page
		 * @param: 	_current_page	- (string or integer) the current page. Usually grabed from the 'from_page=4' url
		 *                                                parameter param: _range - show a maximum of this many
		 *                                                links at one time ie: 5 would give 1,2,3,4,5...435 or
		 *                                                1...3,4,5,6,7...435 param: _css_classes - an array of class
		 * @param: 	_range			- (string or integer) the total number of pagination links displayed
		 * 												  (discluding 1.. and ..563 as well as 1,2,3,4,5,6 or 1,2 )
		 * @param: 	_css_classes	- (array)			  parameters used to style the links:
		 * 												   		var css_classes = new Array();
		 * 														css_classes['active'] = 'active';
		 * 														css_classes['inner_class'] = 'prev-next';
		 * 														css_classes['a'] = 'pagination_link';
		 * @param: 	_parameter_string - (string) 		  parameter string that holds the page number value like 'from_page='
		 * 												  ( The driving site uses the 'from_page=' parameter string as a dependancy )
		 */
		function Pagination(_total_pages, _current_page, _range, _css_classes, _parameters_string) {

			/* Property declarations */

			/* private properties */
			var self = this;
			var _base_url = location.pathname;
			var _current = parseInt(_current_page);


			/* public properties */
			self.show_prev_and_next;
			self.prev_html;
			self.next_html;
			this.paging_target;
			self.container;
			self.results_per_page;

			this.paginate = function () { // public method to return the private methods data
				return _paginate();
			}

			if (_current < 1 || _current > _total_pages) { // don't allow boundaries to be exceeded
				_current = 1;
			}

			/* private code */
			function _hasValue(variable) {
				if (typeof variable == 'undefined') {
					return false;
				}

				if (variable == false) {
					return false;
				}

				return true;
			}

			/*
			 * ahref container method. Uses data and builds links using <h4> and <a> tags
			 */
			function _ahref(data) {
				var ahref = ' '; // Build up the list. Checks are done to see if classes, and/or id are set
				var cleaned_link;

				if (_hasValue(data['end_points']['prev'])) { // If previous link exists then add it

					ahref += '<li class="first"><a ';

					if (_hasValue(_css_classes['a'])) { // Add class value to the link
						ahref += 'class="' + _css_classes['a'] + '" ';
					}

					ahref += "href=" + _base_url + '?' + _parameters_string + (_current - 1) + '>' + self.prev_html + '</a></li>';
				}

				cleaned_link = ''; // will store a cleaned link

				for (i = 0; i <= data['paging'].length - 1; i++) { // build the links

					if( i == data['paging'].length - 1 ) {
						ahref += '<li class="dots-wrapper"><span class="page-numbers dots">..</span></li>';
					}

					ahref += '<li><a class="';

					if (_hasValue(_css_classes['a'])) {
						ahref += _css_classes['a']; // Add class value to the link
					}

					if (data['paging'][i] == _current) {
						ahref += ' active current '; // set the current (clicked) link to have the 'active class'
					}

					ahref += '" ';

					if (typeof (data['paging'][i] == 'string')) { // Clean the '..' off of paging links that are either 1.. or ..p_terms

						cleaned_link = data['paging'][i];

						cleaned_link = String(cleaned_link);

						cleaned_link = cleaned_link.replace('..', '');

						cleaned_link = parseInt(cleaned_link); // This cleaned link is used for the url parameter

					} else {
						cleaned_link = data['paging'][i]; // This cleaned link is used for the url parameter
					}

					if( data['paging'][i] == _current ) {
						ahref += '>' + data['paging'][i] + '</a></li>';
					}
					else {
						ahref += 'href=' + _base_url + '?' + _parameters_string + cleaned_link + '>' + data['paging'][i] + '</a></li>';
					}
				}

				if (_hasValue(data['end_points']['next'])) { // If previous link exists then add it
					ahref += '<li class="last"><a ';

					if (_hasValue(_css_classes['a'])) { // Add class value to the link
						ahref += 'class="' + _css_classes['a'] + '" ';
					}

					ahref += "href=" + _base_url + '?' + _parameters_string + (_current + 1) + '>' + self.next_html + '</a></li>';
				}

				return ahref;
			}

			/*
			 * list container method. Uses data and builds links <ul> elements
			 */
			function _list(data) {
				var list = '<ul '; // Build up the list. Checks are done to see if classes, and/or id are set
				var cleaned_link = '';

				if (_hasValue(_css_classes['inner_id'])) { // Style the <ul> element any ids or classes will be added to the element here
					list += 'id="' + _css_classes['inner_id'] + '"';
				}

				if (_hasValue(_css_classes['inner_class'])) {
					list += 'class="' + _css_classes['inner_class'] + '"';
				}

				list += '>';

				if (_hasValue(data['end_points']['prev'])) { // If previous link exists then add it

					list += '<li><a ';

					if (_hasValue(_css_classes['a'])) { // Add class value to the link
						list += 'class="' + _css_classes['a'] + '" ';
					}

					list += "href=" + _base_url + '?' + _parameters_string + (_current - 1) + '>' + self.prev_html + '</a></li>';
				}



				for (i = 0; i <= data['paging'].length - 1; i++) { // build the links

					list += '<li><a ';

					if (_hasValue(_css_classes['a'])) { // Add class value to the link
						list += 'class="' + _css_classes['a'];
					}

					if (data['paging'][i] == _current) { // Add the active link to the current page
						list += ' active ';
					}

					list += '" ';

					if (typeof (data['paging'][i] == 'string')) { // Clean the '..' off of paging links that are either 1.. or ..p_terms
						cleaned_link = data['paging'][i];
						cleaned_link = String(cleaned_link);
						cleaned_link = cleaned_link.replace('..', '');
						cleaned_link = parseInt(cleaned_link); // This cleaned link is used for the url parameter
					} else {
						cleaned_link = data['paging'][i]; // This cleaned link is used for the url parameter
					}

					list += "href=" + _base_url + '?' + _parameters_string + cleaned_link + '>' + data['paging'][i] + '</a></li>';
				}


				if (_hasValue(data['end_points']['next'])) { // If previous link exists then add it

					list += '<li><a ';

					if (_hasValue(_css_classes['a'])) { // Add class value to the link
						list += 'class="' + _css_classes['a'] + '" ';
					}

					list += "href=" + _base_url + '?' + _parameters_string + (_current + 1) + '>' + self.next_html + '</a></li>';
				}

				list += '</ul>';

				return list;
			}

			/*
			 * links widget router method determines the type of html widget
			 */
			function _links(data) {
				switch (self.container) {
				case 'list':
					return _list(data);
				case 'ahref':
					return _ahref(data);

				case 'div': // TODO
					return 0;
				case 'table': // TODO
					return 0;
				default:
					return _list(data);
				}
			}

			function _paginate() {
				var page_data = new Array();
				var paging = new Array();
				var end_points = new Array();
				var end = _total_pages;
				var begin = '1</a></li><li class="dots-wrapper"><span class="page-numbers dots">..</span></li>';

				if (_total_pages == 0) {
					return '';
				}

				if (_total_pages % self.results_per_page > 0) {
					_total_pages = _total_pages;
				} else {
					_total_pages = _total_pages - 1;
				}

				if (_total_pages == 1) { // If there is only one page then don't build links; return empty pagination

					page_data['paging'] = new Array();

					page_data['end_points'] = new Array();

					return _links(page_data);
				}

				if (_current > _total_pages) { // Adjust boundary to not exceed the total number of pages
					_current = _total_pages;
				}

				if (!_hasValue(_range)) { // Default range is set to 5 if range is not set in the constructor
					_range = 5;
				}

				if (!_hasValue(self.prev_html)) {
					self.prev_html = '&#8592; Newer'; // Default previous link html can be overidden by setting this public property
				}

				if (!_hasValue(self.next_html)) {
					self.next_html = 'Older  &#8594;'; // Default next link html can be overidden by setting this public property
				}

				/* set previous and next links */
				if (_current != 1) {
					end_points['prev'] = self.prev_html;
				}

				if (_current != _total_pages) {
					end_points['next'] = self.prev_html;
				}

				/* end ranges */

				/* if c is 1 or 2 then don't show beginning
				 * if c is p_terms-1 or p_terms then don't show end
				 */
				if (_current == 1 | _current == 2) {
					if (_current + _range <= _total_pages) { // paging range does not exceed total
						for (i = 1; i <= _current + (_range - 1); i++) {
							paging.push(i);
						}
					} else if (_current + _range > _total_pages) { // paging range exceeds total
						if (_total_pages - _range < 1) {
							for (i = 1; i <= _total_pages; i++) {
								paging.push(i);
							}
						} else {
							for (i = _total_pages - _range; i <= _total_pages; i++) {
								paging.push(i);
							}
						}
					}

					/* if c + r <= p_terms - 1
					 * either push ..p_terms or p_terms to the end of the paging links
					 */
					if (_current + _range <= _total_pages - 1) {
						paging.push(end);
					} else if (_current + _range <= _total_pages) {
						paging.push(_total_pages);
					}

				} else if (_current > 2 & _current + _range <= _total_pages) { // show both begin and end

					paging.push(begin);

					if (_current == 3) { // if 3 then do 1..3 and not 1..23
						var second_paging_value = _current;
					} else { // do 1..456789 where the current value for example is 5
						var second_paging_value = _current - 1;
					}

					for (i = second_paging_value; i <= _current + _range - 1; i++) {
						paging.push(i);
					}

					if (_current + _range <= _total_pages - 1) {
						paging.push(end);
					} else {
						paging.push(_total_pages);
					}

				} else if (_current > 2 & _current + _range > _total_pages) { // range exceeds total. rage goes from_page
					if (_total_pages - _range < 1) { // backing-up puts the range into negative numbers so start from_page 1
						for (i = 1; i <= _total_pages; i++) {
							paging.push(i);
						}
					} else {
						if (_total_pages - _range > 2) {
							paging.push(begin);
						}

						for (i = _total_pages - _range; i <= _total_pages; i++) {
							paging.push(i);
						}

					}
				}

				page_data['paging'] = paging;
				page_data['end_points'] = end_points;

				return _links(page_data);
			}

		}

		/*
		 * Return formatted Driving data
		 *
		 * The styling and html is specific to the driving application.
		 *
		 * @param: 	data	- The returned JSON data after an Elastic Search result set has been returned
		 */
		function getFormattedData( data, obj ) {
			var results = '';
			switch( obj.page ){
				case 'compare':
					results = getCompareResults( data, obj );
					break;
				default:
					results = getSearchResults( data, obj );
					break;
			}

			return results;
		}

		/*
		* Setup pagination dependencies
		*
		* - Determine the number of pages required based onthe search count.
		* - Round down for non whole numbers
		* - Need to process this before testing if data is returned as total_pages - and current_page
		*   are required to be processed to catch if a user has - manually adjusted the 'from_page=' parameter
		*   to a greater value then the total number of pages
		*/
		function getPagination( data, obj, from_page, ie_path, load_more, archive_search ){

		       total_pages = Math.ceil( data.count / data.number_of_records_per_page);
		       current_page = from_page; // current page number
		       is_mobile = data.is_mobile;

		       if (!is_mobile) { // Is desktop
			       if (null != data['results']) { // data is returned
				       obj.results.html( getFormattedData( data, obj ) );

				       parameters_string = 'from_page='; // parameter required for the pagination class ( @TODO hard-coded in place to be cleaned at a later date )
				       show_this_many_pagination_links = 3; // set how many links are visible at one time.

				       /* Create and add css classes to be passed into the pagination object */
				       css_classes = [];
				       css_classes['active'] = 'active current';
				       css_classes['inner_class'] = 'prev-next';
				       css_classes['a'] = 'pagination_link';

				       objPagination = new Pagination(total_pages, current_page, show_this_many_pagination_links, css_classes, parameters_string);

				       // Configure the Pagination Instance
				       objPagination.show_prev_and_next = true; // Show the next and previous buttons
				       objPagination.prev_html = '<span id="prev-page">&laquo; Newer </span>'; // HTML to use for the previous																																			// link
				       objPagination.next_html = '<span id="next-page"> Older &raquo;</span>'; // HTML to use for the next link
				       objPagination.container = 'ahref'; // type of container; this determines the HTML that will wrap the objPagination links
				       objPagination.results_per_page = data.number_of_records_per_page; // display this number of results per page

				       links = objPagination.paginate(); // Get the objPagination links for the current page of results

				       obj.paging.html(links); // Load links on the page

				       // Logic to determine how to display the end and start of the current page of results
				       results_start = data['results_start'];
				       results_end = data['results_end'];


				       // Adjust if the last page of results does not have a full number of records per page
				       if (results_start + data.number_of_records_per_page > data.count) {
					       results_end = (results_start + data.number_of_records_per_page) - (total_pages % data.number_of_records_per_page);
				       }

				       // If a user manually ajusts the 'from_page=' parameter to a number larger than the result set set
				       // to the largest possible result
				       if (results_end > data.count) {
					       results_end = data.count;
				       }

				       results_x_to_y_of_z_text = results_start + ' - ' + results_end + ' of ' + data.count + ' items';

				       if (archive_search.length) {
					       obj.status.html(results_x_to_y_of_z_text);
					       obj.totalitems.html(results_x_to_y_of_z_text);
				       } else {
					       obj.totalitems.html(results_x_to_y_of_z_text);
				       }

			       } else {
				       obj.totalitems.html('');
				       obj.results.html( getFormattedData( data, obj ) );
				       obj.status.html('');
				       obj.paging.html('');
				       jQuery('.total-models').html('0 models');
			       }
		       } else { // Is mobile
			       clear(); // clear only the results to reload loadmore when filters are applied
			       if (!isSetURLParameter('hits', ie_path)) {
				       pushHitsToUrl(data.count); // set total pages to determine when 'load more' actions should be blocked
			       }

			       var inactive = (data.count <= 0) ? obj.moreresults.addClass('inactive default-cursor') : obj.moreresults.removeClass('inactive');

			       if (1 == from_page && !load_more) {
				       obj.results.html( getFormattedData( data, obj ) ); // first page of results injected like the desktop version
			       } else {
				       var t = getURLParameter('from_page', ie_path);
				       //t = parseInt(t) + 1;
				       t = t * data.mobile_limit;
				       if (t <= data.count) {
					       obj.results.html( getFormattedData( data, obj ) );
				       } else { // append the last page and block 'load more' actions
					       obj.results.html( getFormattedData( data, obj ) );
						obj.moreresults.addClass('inactive default-cursor'); // set the inactive flag and default cursor ( default-cursor is inside the plugins css/pm_es_pagination.css )
				       }
			       }
		       }

		    var slots=[];

			for( i in adSlots ) {
				slots.push( adSlots[i] )
			}

			var check_googletag = setInterval( function() {

			if( typeof googletag !== 'undefined' && typeof googletag.pubads !== 'undefined' ) {

				first_result = obj.results.find(jQuery('.sub-row:first .sub-left:first input[type=checkbox]'));
				if( first_result.length > 0 ) {
					make = first_result.attr('data-make');
					model = first_result.attr('data-model');
					bodystyle = first_result.attr('data-body-type');
					googletag.pubads().setTargeting('make', make.toLowerCase()).setTargeting('model', model.toLowerCase()).setTargeting('bt', bodystyle.toLowerCase());
				}
				googletag.pubads().refresh( slots );
				clearInterval(check_googletag);
			}
			}, 500 );

			if( typeof pnAd_gpt_wallpaper !== undefined && slots.length > 0 ) {
				delete adSlots[1];
			}
		}

		function getCompareResults( data, obj ) {

			var compare_arr = [];
			var compare = '';
			if( Driving.common.utilGetJSONfromSessionStorage('comparebarData') != null ){

				compare = Driving.common.utilGetJSONfromSessionStorage('comparebarData');

			} else if( Driving.common.utilGetJSONfromLocalStorage('comparebarData') != null ) {

				compare = Driving.common.utilGetJSONfromLocalStorage('comparebarData');
			}
			else {
				compare = { cars:{}, timestamp:Date.now() };
			}

			if( compare != null ) {
				for( var key in compare.cars ) {
					compare_arr.push(key);
				}
			}

			var array_of_result_hits = data['results'];
			var search_body = jQuery('<div>');
			var count = 0;
			var first_one = 0;
			var template_rul = jQuery('input[name=template_url]').attr('value');
			jQuery('.total-models').html(data.count + ' models');
			var products = new Array();

			var getCompareCarWidget = function(year,make,model,bodystyle,msrp,hwy,city,title,trim,style_id,img_url,checked,active){

				var widget, figure, img, upper_btn_mob, chkbox_wrap_mob, chkbox_mob, lbl_mob, text_component, btm, h5gas,
					item_des, upper_btn_des, chkbox_wrap_des, chkbox_des, lbl_des, lower_btn_des, lower_btn_mob;
				var img_blank = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAADCAQAAAAe/WZNAAAADklEQVR42mNkgAJGDAYAAFEABCaLYqoAAAAASUVORK5CYII=';
				widget = jQuery('<div>');
					var section_class = ( is_mobile ) ? 'section compare-car-widget mobile' : 'section compare-car-widget';
					widget.attr('class', section_class);
						figure = jQuery('<figure>');
						figure.appendTo(widget);
							img = jQuery('<img />');
							img.attr({'src':img_blank, 'pm-lazy-src':img_url, 'alt':title, 'scale':'0'}).appendTo(figure);
						text_component = jQuery('<div>');
						text_component.attr('class', 'text-component').appendTo(widget);
							jQuery('<h2>').text(title).appendTo(text_component);
							jQuery('<span>').attr('class', 'trim').text(trim).appendTo(text_component);
							jQuery('<h3>').text( numberFormat( msrp ) ).appendTo(text_component);
							btm_wrap = jQuery('<div>').attr('class', 'bottom-wrap').appendTo(widget);
							
							if( is_mobile ){
							upper_btn_mob = jQuery('<div>');
							upper_btn_mob.attr('class', 'compare-upper-btn mobile').appendTo(btm_wrap);
							chkbox_wrap_mob = jQuery('<div>');
							chkbox_wrap_mob.attr('class', 'checkbox-wrap').appendTo(upper_btn_mob);
								chkbox_mob = jQuery('<input />');
								chkbox_mob.attr({'type':'checkbox', 'id':style_id, 'name':'compare_'+style_id, 'value':style_id, 'data-body-type':bodystyle, 'data-year':year, 'data-make':make, 'data-model':model, 'data-image':img_url, 'data-title':title, 'data-trim':trim, 'data-model-url':'/'+make+'/'+model, 'data-review-url':''});
								chkbox_mob.appendTo(chkbox_wrap_mob);

								lbl_mob = jQuery('<label>');
								lbl_mob.attr('for', style_id);
									span_compare = jQuery('<span>');
									span_compare.text('Compare');
									span_compare.css("padding-right",3);
									span_compare.appendTo(lbl_mob);

									span_base = jQuery('<span>');
									span_base.text('Base Model');
									span_base.appendTo(lbl_mob);

								lbl_mob.appendTo(upper_btn_mob);
							}
							
							btm = jQuery('<div>');
							btm.attr('class', 'bottom').appendTo(btm_wrap);
								h5gas = jQuery('<h5>');
								h5gas.text('City - ' + city).appendTo(btm);
								jQuery('<span>').text('Hwy - ' + hwy).appendTo(h5gas);
								if( ! is_mobile ){
									item_des = jQuery('<div>');
									item_des.attr('class', 'item desktop').appendTo(btm);
								}
								trim_button = jQuery('<div>');
								trim_button.attr({'class' : 'trim-options','data-id' : style_id, 'data-year' : year, 'data-make' : make, 'data-model' : model});
									trim_num_span = jQuery('<span>');
									trim_num_span.attr('class', 'trim-num');
									trim_num_span.text('Trim');
									trim_num_span.appendTo(trim_button);

									trim_plus_sign = jQuery('<span>');
									trim_plus_sign.attr({'class' : 'plus-sign'});
									trim_plus_sign.appendTo(trim_button);

									if( ! is_mobile ){
										trim_button.appendTo(item_des);
									} else {
										trim_button.appendTo(btm);
									}
									
									if( ! is_mobile ) {
										upper_btn_des = jQuery('<div>');
										upper_btn_des.attr('class', 'compare-upper-btn').appendTo(item_des);
											chkbox_wrap_des = jQuery('<div>');
											chkbox_wrap_des.attr('class', 'checkbox-wrap').appendTo(upper_btn_des);
												chkbox_des = jQuery('<input />');
												chkbox_des.attr({'type':'checkbox', 'id':style_id, 'name':'compare_'+style_id, 'value':style_id, 'data-body-type':bodystyle, 'data-year':year, 'data-make':make, 'data-model':model, 'data-image':img_url, 'data-title':title, 'data-trim':trim, 'data-model-url':'/'+make+'/'+model, 'data-review-url':''});
												chkbox_des.appendTo(chkbox_wrap_des);
												lbl_des = jQuery('<label>');
												span_compare = jQuery('<span>');
												span_compare.text('Compare');
												span_compare.css("padding-right",3);
												span_compare.appendTo(lbl_des);

												span_base = jQuery('<span>');
												span_base.text('Base Model');
												span_base.appendTo(lbl_des);

											lbl_des.attr('for', style_id);
											lbl_des.appendTo(upper_btn_des);
										lower_btn_des = jQuery('<div>');
										lower_btn_des.attr('class', 'compare-lower-btn').text('Pick One More').appendTo(item_des);
									}
						if( is_mobile ){
							lower_btn_mob = jQuery('<div>');
							lower_btn_mob.attr('class', 'compare-lower-btn mobile').text('Pick One More').appendTo(widget);
						}

				return widget;
			};

			for (var key in array_of_result_hits) {

				if( count % 2 == 0) {
					var sub_row = jQuery('<div>');
					sub_row.attr({'class':'sub-row', 'data-columns':'2'});
				}

				count++;

				var year = array_of_result_hits[key].year;
				var make = array_of_result_hits[key].make;
				var model = array_of_result_hits[key].model;
				var bodystyle = '';
				if(typeof array_of_result_hits[key].bodystyle !== 'undefined') {
					bodystyle = array_of_result_hits[key].bodystyle.toLowerCase().replace(' ', '-');
				}
				var msrp = ( null != array_of_result_hits[key].msrp ) ? array_of_result_hits[key].msrp : 'N/A';

				var litres = ' L/100Km';
				var hwy = ( null != array_of_result_hits[key].fueleconhwy ) ? array_of_result_hits[key].fueleconhwy.display + litres : 'N/A';
				var city = ( null != array_of_result_hits[key].fueleconcity ) ? array_of_result_hits[key].fueleconcity.display  + litres : 'N/A';

				var title = year + ' ' + make + ' ' + model;
				var trim = array_of_result_hits[key].stylename;
				var style_id = array_of_result_hits[key].styleid;

				var img_url = 'https://app.canada.com/chrome/get.svc/image/' + style_id + '?resize=compare:search';

				var checked = ( compare_arr.indexOf( 'key_' + style_id ) != -1 ) ? 'checked="checked"' : '';
				var active = ( compare_arr.indexOf( 'key_' + style_id ) != -1 ) ? 'active' : '';

				var sub_left, sub_right, compareWidget;

				if( count == 1 ){
					if(typeof style_id !== 'undefined') {
						sub_left = jQuery('<div>');
						sub_left.attr('class','sub-left').appendTo(sub_row);

						compareWidget = getCompareCarWidget(year,make,model,bodystyle,msrp,hwy,city,title,trim,style_id,img_url,checked,active);
						compareWidget.appendTo(sub_left);
					}
				}

				if( count == 2 ){
					if(typeof style_id !== 'undefined') {
						sub_right = jQuery('<div>');
						sub_right.attr('class', 'sub-right').appendTo(sub_row);

						compareWidget = getCompareCarWidget(year,make,model,bodystyle,msrp,hwy,city,title,trim,style_id,img_url,checked,active);
						compareWidget.appendTo(sub_right);
					}
				}

				if( count % 2 == 0) {
					count = 0;
				}
				search_body.append(sub_row);
			}
			/** 
			 * Scroll to search result area on iPad, uncomment to enable.*/
			if( !is_mobile) {
				// var pos = jQuery('.compare-search-results').position();
				// jQuery('html, body').stop().animate({ scrollTop: pos.top },1000, function() {
				// 	obj.results.stop().animate({ opacity: 1 },100);
				// });
				obj.results.stop().animate({ opacity: 1 },100);
			}

			if( data.count <= 0 ) {
				search_body = jQuery('<div>');
				search_body.attr('class', 'no-results').text('No vehicles match your criteria. Try removing some filters.');
			}

			return search_body;
		}

		function getArchiveSearchData( data, obj, archive_search ) {
			if (!archive_search.length) { // set the Search Results Header this is done after the search is run not statically shown

				jQuery('.search-result-summary h2').html('Search results (' + (data.count) + ')');
				jQuery('.search-result-summary h2').fadeIn();


				jQuery('.result-1').html('');
				jQuery('.showing-search-word').html('');
				jQuery('.original-search-words').html('');

				if (data.original_terms && data.terms) {
					jQuery('.showing-search-words').show();
					jQuery('.showing-search-words').html('Search instead for <a href="#" class="search_these_terms_instead" >' + data.terms + '</a>'); // show the users their search terms
				} else {
					jQuery('.showing-search-words').hide();
				}

				jQuery('.original-search-words').html('<br />Showing results for <strong>' + data.original_terms + '</strong>'); // show the users their search terms

				if (data.bestbets) { // bestbets is set in the Plugin do_search_callback() method
					jQuery('.best-bets').html(data.bestbets);
				}
			}
		}

		function getSearchResults( data, obj, archive_search ) {

			var array_of_result_hits = data['results'];
			var search_body = '';
			var tags = '';
			var video_span;
			var date;
			var non_highlighted_title;

			for (var key in array_of_result_hits) {

				if (array_of_result_hits[key].featuredlogo) {
					search_body = search_body + '<div class="search-result-item sponsor" >';
				} else {
					search_body = search_body + '<div class="search-result-item" >';
				}

				/*
				 * if a video exists then it will be stored in attachedmedia so create a span that has a class target to display
				 * a video icon
				 */

				if (jQuery.inArray('Video', array_of_result_hits[key].attachedmedia) > -1) {
					video_span = '<span class="video-play"></span>';
				} else {
					video_span = '';
				}

				/*
				 * Add the Thumbnail linked to the story
				 */
				if (array_of_result_hits[key].thumbnail) {
					search_body = search_body + '<a class="search-result-image" href="' + array_of_result_hits[key].url + '">' + video_span + '<img src="' + array_of_result_hits[key].thumbnail + '"/></a>';
				}

				/*
				 * Build the search results content wrapper
				 */
				search_body = search_body + '<div class="search-result-content">';

				/*
				 * Add the title linked to the story
				 */
				search_body = search_body + '<a class="search-title" href="' + array_of_result_hits[key].url + '"><h2>' + array_of_result_hits[key].title + '</h2></a>';


				/*
				 * Add the excerpt
				 */
				search_body = search_body + '<p>' + array_of_result_hits[key].excerpt + '</p>';

				/*
				 * Convert the unix timestamp to the date
				 */
				date = timeConverter(array_of_result_hits[key].lastmodified_unix_timestamp);

				/*
				 * Get the author. Much of the logic for this is in the class-pm-elastic-search.php
				 */
				if (array_of_result_hits[key].author.length != 0) {
					search_body = search_body + '<span class="author-date-info"> By ' + array_of_result_hits[key].author + ' / ' + date;
				} else {
					search_body = search_body + '<span class="author-date-info"> By Driving.ca / ' + date;
				}

				/*
				 * Close the <div.search-result-content
				 */
				search_body = search_body + '</div>';

				/*
				 * If featured logo exists then add it
				 */
				if (array_of_result_hits[key].featuredlogo) {
					non_highlighted_title = array_of_result_hits[key].non_highlighted_title;
					search_body = search_body + '<a class="sponsor-logo" href="' + array_of_result_hits[key].url + '" title="' + non_highlighted_title + '"><img class="no-resize" src="' + array_of_result_hits[key].featuredlogo + '" alt="' + non_highlighted_title + '"></a>';
				}

				/*
				 * Close the <div.search-result-item
				 */
				search_body = search_body + '</div>';

				/*
				 * if Ad exists then add it after the story
				 */
				if (array_of_result_hits[key].Ad) {
					search_body = search_body + array_of_result_hits[key].Ad;
				}
			}

			if( data.count <= 0 ) {
				search_body = 'No results found.';
			}

			return search_body;
		}

		/*
		 * Coverts Unix timestamp to a date ( In driving.ca preferred style )
		 *
		 * @param: 	UNIX_timestamp	- (integer) a valid unix timestamp
		 */
		function timeConverter(UNIX_timestamp) {
			var a = new Date(UNIX_timestamp);
			var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
			var year = a.getFullYear();
			var month = months[a.getMonth()];
			var date = a.getDate();

			return month + ' ' + date + ', ' + year;
		}

		/*
		 * Converts all words within a string to lowercase
		 *
		 * @param: 	str		- (string) a string
		 */
		function convertToLowercaseWords(str) {
			return str.toLowerCase().replace(/(^| )(\w)/g, function (x) {
				return x.toLowerCase();
			});
		}

		/*
		 * Converts all words within a string to Uppercase ( not all caps though ie "ford focus" becomes "Ford Focus"
		 *
		 * @param: 	str		- (string) a string
		 */
		function convertToUppercaseWords(str) {
			return str.toLowerCase().replace(/(^| )(\w)/g, function (x) {
				return x.toUpperCase();
			});
		}


		String.prototype.capitalize = function () {
			return this.replace(/(^|\s)([a-z])/g, function (m, p1, p2) {
				return p1 + p2.toUpperCase();
			});
		};

		/*
		 * Clear driving results, paging, search words and URL parameters
		 *
		 * @uses:	clearUrlParameters()
		 */
		function clearResults(filters_exist) {
			jQuery('search-result-summary h2').html('');
			jQuery('span.showing-search-words').html('');
			jQuery('span.original-search-words').html('');
			jQuery('div.results').html('');
			jQuery('.result-status').html('');
			if (!filters_exist) {
				if( compare_page.size() <= 0 ) {
					clearUrlParameters();
				}
			}

		}

		/*
		 * Clear only the results;
		 */
		function clear() {
			//jQuery('div.results').html('');
		}

		function loadModels(_this, make) {

			var data = {
				action: 'drv_get_models_by_make',
				slug: make,
				taxonomy: 'make'
			};

			var models_obj = jQuery(_this).parent().parent().next(); // find the models filter list div container


			jQuery.get(Postmedia.Urls.ajaxurl, data, function (response) {

				response = jQuery.parseJSON(response);

				if (response.count > 0) {

					models_obj.removeClass('inactive'); // remove inactive class from models dropdown

					var models = '';

					jQuery.each(response.terms, function (k, v) {
						models += '<li data-slug="' + k + '">' + v + '</li>';
					});

					models_obj.find('ul').html('<li data-slug="all-models">All Models</li>' + models); // inject models into the models list
				} else {
					models_obj.addClass('inactive').find('ul').html('<li>All Models<li>');
					models_obj.find('button').html('MODELS').append('<span />');

				}
			});

		}

		function isArray(obj) {
			return Array.isArray(obj);
		}

		function getSearchTerm() {
			var terms = '',
				ie_path = jQuery(location).attr('href');

			if (null != getURLParameter('terms', ie_path)) {
				terms = getURLParameter('terms', ie_path);
			} else {
				jQuery('input[name=terms]').each(function () {
					var val = jQuery(this).val();
					if (-1 == val.indexOf('Search') && null != val) {
						terms = jQuery(this).val();
						return terms;
					}
				})
			}

			return terms;
		}

		function getCompareFilters() {
			var filters = {},
				json_obj = JSON.parse(Driving.common.utilGetCookie('filtersJSON')), 
				prop_filters = new Array();
			if (null != json_obj) {
				properties = json_obj.properties;

				filters.compare = {};
				var sort_array = [];

				if (properties.sort) {

					if( properties.sort.orderby == 'make' ) {
						sort_array.push(properties.sort.orderby + ':' + properties.sort.order + ',' + 'model:asc');
					}
					else if( properties.sort.orderby == 'fueleconhwy.value' ) {
						if( properties.fuelecon ) {
							if( properties.fuelecon.type == 'hwy' ) {
								sort_array.push('fueleconhwy.value:asc');
							} else if( properties.fuelecon.type == 'city' ) {
								sort_array.push('fueleconcity.value:asc');
							}
						}
						else {
							sort_array.push(properties.sort.orderby + ':' + properties.sort.order);
						}
					}
					else {
						sort_array.push(properties.sort.orderby + ':' + properties.sort.order);
					}
				}

				if (properties.bodystyle) {
					filters.compare.bodystyle = (properties.bodystyle.type).join(',');
					prop_filters.push( 'Bodystyle' + '-' + filters.compare.bodystyle );
				}

				if (properties.drivetrain) {
					filters.compare.drivetrain = (properties.drivetrain.type).join(',');
					prop_filters.push( 'Drivetrain' + '-' + filters.compare.drivetrain );
				}

				if(properties.fuelecon) {

					properties.fuelecon.max = ( properties.fuelecon.max == properties.fuelecon.maxend ) ? '' : properties.fuelecon.max;

						if(properties.fuelecon.type == 'hwy') {
							filters.compare.fueleconhwy = ('range' + ',' + properties.fuelecon.min + ',' + properties.fuelecon.max);
						}
						if(properties.fuelecon.type == 'city') {
							filters.compare.fueleconcity = ('range' + ',' + properties.fuelecon.min + ',' + properties.fuelecon.max);
						}
					prop_filters.push( 'Fuel economy' + '-' + properties.fuelecon.output.replace( '-', ' to ' ) );
				}

				if (properties.fueltype) {
					filters.compare.fueltype = (properties.fueltype.type).join(',');
					prop_filters.push( 'Fueltype' + '-' + filters.compare.fueltype );
				}

				if (properties.horsepower) {

					filters.compare.horsepower = ('range' + ',' + properties.horsepower.min + ',');
					prop_filters.push( 'Horsepower' + '-' + properties.horsepower.output.replace( '-', ' to ' ) );
				}

				if (properties.make) {
					filters.compare.make = (properties.make.type).join(',');
					prop_filters.push( 'Make' + '-' + filters.compare.make );
				}

				if (properties.model) {
					 filters.compare.model = JSON.stringify( properties.model.type );
					 prop_filters.push( 'Model' + '-' + filters.compare.model );
				}

				if (properties.capacity) {
					properties.capacity.max = ( properties.capacity.max == properties.capacity.maxend ) ? '' : properties.capacity.max;
					filters.compare.capacity = ('range' + ',' + properties.capacity.min + ',' + properties.capacity.max);
					prop_filters.push( 'Capacity' + '-' + properties.capacity.output.replace( '-', ' to ' ) );
				}

				if (properties.msrp) {
					properties.msrp.to = ( properties.msrp.to == properties.msrp.max || typeof properties.msrp.to == "undefined" ) ? '' : properties.msrp.to;
					properties.msrp.from = ( properties.msrp.from == properties.msrp.min || typeof properties.msrp.from == "undefined"  ) ? '' : properties.msrp.from;
					if ( properties.msrp.from !='' || properties.msrp.to !='' ){
						filters.compare.msrp = 'range,';
						if ( properties.msrp.from !='' ){
							filters.compare.msrp += properties.msrp.from;
						}
						if( properties.msrp.to !='' ){
							filters.compare.msrp += ',' + properties.msrp.to;
						}
					}
				}

				if (properties.towingcapacity) {

					filters.compare.towingcapacity = ('range' + ',' + properties.towingcapacity.min + ',');
					prop_filters.push( 'Towing capacity' + '-' + properties.towingcapacity.output.replace( '-', ' to ' ) );
				}

				if (properties.year) {
					filters.compare.year = (properties.year.type).join(',');
					prop_filters.push( 'year' + '-' + filters.compare.year );
				}
				filters.compare.sequence = 0;
				filters.sb = sort_array;
			}

			return filters;
		}

		function getCallBack(compare_page) {
			var filters = getCompareFilters();
			callSearch(compare_page, filters);
			return true;
		}

		function numberFormat( value ) {
			if( isNaN( value ) ) {
				return value;
			}
			else {
				var x = value.toFixed(2);
				return '$' + x.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			}
		}


	})(window.DRV = window.DRV || {});

});