
$q.filters = (function ($) {

	var radioMax = 1;			// keep unique radio numbers (this is a needed global variable)

    function Filter(index, type, values) {
        this.index = index;
        this.catType = type;
        this.values = values;
    }

    function CreateQueryStr() {
        Log('>> CreateQueryStr ');

        var query = "?",
            radioValue = '',
            mainDropDownValue = '',
            secondaryDropDownValue = '',
            tagsStr = '',															// comma separated tags
            tags = [],																	// array of tags
            tagsExclude = '',											// using radios, add a "!" for tags excluded
            filter = [],																	// filter array
            filterObj = {},														// each filter is made of of an object (with properies like filterObj.radioValue...)
            filterMax = 0,														// # of filters in array
            filterLastSortKey = '',								// the key to sort filters on
            filterLastMDDKey = '',							// used to add param separators in querystring
            filterNeedsComma = '',						// used to separate tokens in querystring
            filterSeparator = '?',									// used to separate terms in querystring
            filterQueryStr = '',										// the generated querystring
            tempStr = '';
        // begin step through each row
        $("tr.generatedRow").each(function (i) {

            // grab radio
            radioValue = $(this).find('input[type=radio]:checked').val();
            // add the ! in front of tags if excluded
            tagsExclude = radioValue;

            // grab first dropdown
            mainDropDownValue = $(this).find('.mainDropDown option:selected').val();
            if (mainDropDownValue == 'advertorial' || mainDropDownValue == 'q') {
                if (mainDropDownValue == 'advertorial') {

                    if (radioValue == '!') {
                        tempStr = 'false';
                    }
                    else {
                        tempStr = 'true';
                    }
                }
                else {
                    // it must be a "q" if not advertorial
                    var tag = encodeURIComponent($(this).find('ul.tags li').text());

                    if (radioValue == '!') {
                        tempStr = '"!' + tag + '"';
                    }
                    else {
                        tempStr = '"' + tag + '"';
                    }
                }

                filterQueryStr += filterSeparator + mainDropDownValue + '=' + tempStr;
                filterSeparator = '&';
            }
            else {
                tags = [];
                tagsStr = '';
                $(this).find('ul.tags li').each(function (i) {
                     // **** left off here
                     var tagVal = $(this).text();

                     if(mainDropDownValue == 'clients' || mainDropDownValue == "licenses"){
                        //get the _id for the value
                        var obj = $q.utils.GetObjFromJson($q.config.data[mainDropDownValue], "name", tagVal);
                        if(obj !== null){
                            tagVal = obj._id;
                        }
                    }

                    tags.push('"' + tagsExclude + tagVal + '"');
                });

				if (tags.length > 0) {
                    tagsStr = tags.join(',');

                    // construct filter obj, then push it into a filter array
                    filterObj = $q.utils.GetFilterObj( mainDropDownValue, radioValue, tagsStr );
                    filter.push(filterObj);
                } else {
					Log( '...one of the ' + filterObj.mainDropDownValue + ' was empty, so no filter added');
				}
            }
        });
        // end step through each row	... we now have an filter array of filterObj

        // add default types filters
        filter.push( $q.utils.GetFilterObj( 'types', '', '"!pointer"') );
        filter.push( $q.utils.GetFilterObj( 'types', '', '"!versus"') );

        // sort on sortKey, which is mainDropDownValue + radioValue (like "licenses_include")
        filter.sort($q.utils.DynamicSort("sortKey"));


        // consruct querystring
        filterMax = filter.length;
        if (filterMax > 0) {
            for (i = 0; i < filterMax; i++) {
                //console.log(filter[i].sortKey);
                // check main dropdown to see if it has changed...
                if (filterLastMDDKey != filter[i].mainDropDownValue) {
                    filterQueryStr += filterSeparator + filter[i].mainDropDownValue + '=[';
                    filterNeedsComma = '';

                    if (filterSeparator == '?' || filterSeparator == '&') {
                        filterSeparator = ']&';
                    }
                }
                else {
                    filterNeedsComma = ',';
                }
                filterLastMDDKey = filter[i].mainDropDownValue;

                // check rest
                if (filterLastSortKey != filter[i].sortKey) {
                    // new filter type
                    filterQueryStr += filterNeedsComma + '[' + encodeURIComponent(filter[i].tags) + ']';
                }
                else {
                    // same filter type
                    filterQueryStr += filterNeedsComma + '[' + encodeURIComponent(filter[i].tags) + ']';
                }
                filterLastSortKey = filter[i].sortKey;
            }

            filterQueryStr += ']';
            Log(filterQueryStr);
        }
        else {
            // No filters with [], but there may be keywords or advertorials...
						if ( filterQueryStr == '' ) {
								// no query string built at all...
								Log('No filters to run whatsoever somehow??');
								return null;
						}
						else {
								// there aere no regular filters, but there is an advertorial or keyowrd or both...
								Log(filterQueryStr);
						}
        }

        Log('-------------------');
        //debugger;
        //Need to send data here

        //next line is for deeplinking if necessary
        //history.pushState({}, null, filterQueryStr);
        return filterQueryStr;
    }

    function AddFilter(data) {

        //Log('>> Add Filter ');
        var selectedIndex = -1;
        var checkedBooleanIndex = 0; // if 0, select 1st radio, if 1 select 2nd

        if (data) {
            selectedIndex = data.catType;

            //Set Include / Exclude
            $(data.values).each(function (index, value) {
                if (value.indexOf("!") > -1) {
                    checkedBooleanIndex = 1;
                    data.values[index] = value.replace("!", "");
                }
            });
        }

        var filterLast = $('tr.generatedRow').length,
            filterMax = filterLast + 1,
            tableRow = '';
        radioMax++;

        var selectName = "mainDropDown" + radioMax,
            className = 'mainDropDown',
            catSelectBox = $q.utils.CreateSelect(selectName, className, "Choose Option", $q.config.filterType, "$q.interface.OnMainSelectChange", selectedIndex, "slug", "name", "$q.config.filterType"),
            radioButtons = $q.utils.CreateRadios(filterMax, radioMax, checkedBooleanIndex);


		tableRow = $('<tr>')
					.addClass('generatedRow')
					.append([
							$('<td>').html(radioButtons),
							$('<td>').append(catSelectBox),
							$('<td>').attr({ 'class':'col3and4', 'colspan': '2'}),
							$('<td>').append($('<button>').attr({ 'class': 'btn red shadow deleteFilter', 'type': 'button'}).text("X"))
							]);
		$('button#buttonAddFilter').parents('tr').before(tableRow);


        //Unbind all radio buttons, then rebind
        $('input[type=radio]').off();
        $('input[type=radio]').change(function() {
            $q.interface.RunFilter();
        });

        if (data) {
            var nextTD = $("#" + selectName).parent().next(),
                ulTag;
            AddInput(data.catType, nextTD);
            ulTag = $("#" + selectName).parent().next().find('ul.tags');
            $("input", nextTD).val(data.values);
            // build the grey tags
            AddTags(ulTag, data);
        }
    }

    function AddInput(optionText, nextTD) {

		// build next element (if required)
        var radioTd = $(nextTD).parent().find("td")[0];
         $(radioTd).css("visibility", "visible");
		switch (optionText) {
			case 'Choose Option':
				ChoseOptionDefault(nextTD);
				break;
			case 'Brand/Product':
				$(nextTD)
					.empty()
					.append([
							$('<div>').attr('style','width: 52.3%;').append($q.utils.CreateSelect("clientsDropDown", "tagsDropDown", "Add Brand", $q.config.data.clients, null, null, "_id", "name", "$q.config.data.clients")),
							$('<div>').attr({'class':'tagsHolder', 'data-model':'$q.config.data.clients'}).append($('<ul>').addClass('tags'))
							]);
				break;
			case 'License':
				$(nextTD)
					.empty()
					.append([
							$('<div>').attr('style','width: 52.3%;').append( $q.utils.CreateSelect("licensesDropDown", "tagsDropDown", "Add License", $q.config.data.licenses, null, null, "_id", "name", "$q.config.data.licenses")),
							$('<div>').attr({'class':'tagsHolder', 'data-model':'$q.config.data.license'}).append($('<ul>').addClass('tags'))
							]);
				break;
			case 'Post Type':
				$(nextTD)
					.empty()
					.append([
						$('<div>').attr('style','width: 52.3%;').append($q.utils.CreateSelect("tagsDropDown", "tagsDropDown", "Add Post Type", $q.config.postTypes, null, null, "slug", "name", "$q.config.postTypes")),
						$('<div>').attr({'class':'tagsHolder', 'data-model':'$q.config.postTypes'}).append($('<ul>').addClass('tags'))
					]);
				break;
			case 'Category':
				ChoseCreateSecondaryDropDown(nextTD, $q.templates.HTMLfragmentGenericInput);
				break;
			case 'Tags':
				ChoseCreateSecondaryDropDown(nextTD, $q.templates.HTMLfragmentGenericInput);
				break;
			case 'Keyword':
				ChoseCreateSecondaryDropDown(nextTD, $q.templates.HTMLfragmentKeywordInput);
				$(radioTd).css("visibility", "hidden");
				break;
			default:
				ChoseOptionDefault(nextTD);
		}
	}

    // universal Add Tags...  (requires the parent UL to be passed in...)
	function AddTags(ulTag, obj) {

        //Need to fix values being passed in
        var html = "",
            parent = $(ulTag).parent().prev().find("select");

        if(typeof obj === "object"){

            if($.isArray(obj.values)){

                $.each(obj.values, function(i, val){
					html = $('<li>').text(val);
					$q.utils.RemoveOption(parent, val);
				});
				}else{
				html = $('<li>').text( obj.name )
				$q.utils.RemoveOption(parent, obj.name);
			}
		}else{
			html = $('<li>').text(obj);
			$q.utils.RemoveOption(parent, obj);
		}
		$(ulTag).append(html);
	}

    function ChoseOptionDefault(obj) {
		//Log('choseOptionDefault ');
		$(obj).empty();
	}

    function ChoseCreateSecondaryDropDown(obj, htmlStr) {
		// Log('choseCreateDropDown' );
		$(obj).empty();
		$(obj).html(htmlStr);
	}

    function Log(msg, type) {

        $q.utils.Log(msg, type);

    }

    return{
        AddInput: AddInput,
        AddTags: AddTags,
        AddFilter: AddFilter,
        Filter: Filter,
        CreateQueryStr: CreateQueryStr
    }

})(jQuery);