$q.utils = (function ($) {

    function Log(a, type, override) {

        if($q.config.prod !== true || GetQueryParameter("debug") == "true"){
            if(!type)
                type="log";

            console[type](a);
        }
    }

    function FormatDate(timestamp){

        var d = new Date(timestamp);
        return d.toDateString();

    }

    function GetQueryParameter(name) {
        name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
        var regexS = "[\\?&]" + name + "=([^&#]*)";
        var regex = new RegExp(regexS);
        var results = regex.exec(window.location.href);
        if (results == null)
            return null;
        else
            return decodeURIComponent(results[1].replace(/\+/g, " "));
    }

    function CreateSelect(selectName, className, selectTitle, data, onChange, indexSelected, value, name, model){

        data.sort(function(el1,el2){
            return Compare(el1, el2, name)
        });

        var selectBox = $('<select>')
            .attr({
                id: selectName,
                name: selectName,
                'data-model': model,
            })
            .addClass(className);


        if(onChange != null){
            selectBox.attr('onchange', onChange + '(this)');
        }

        if(selectTitle != null){
            selectBox.append(
                $('<option>').attr('value', -1).text(selectTitle)
            );
        }

        $.each(data, function(index, val) {

            if(className == "mainDropDown" || selectName == "tagsDropDown"){
                if(val.enabled){
                    selectBox.append(AddOption(val[name], val[value], val[name], indexSelected));
                }
            }else{
                selectBox.append(AddOption(val[name], val[value], val[name], indexSelected));
            }

        });

        return selectBox;

    }

    function AddOption(id, value, name, indexSelected){
        var option = $('<option>').val(value).text(name);

        if(indexSelected != null){

            if(id == indexSelected){
                option.prop('selected', true);
            }
        }

        return option;

    }

    function RemoveOption(parent, value){
        //Remove selected value from DD

        jQuery("option", parent).filter(function(){
            return $.trim($(this).text()) ==  value
        }).remove();
    }

    function Compare(el1, el2, index) {
        var s1 = el1[index].toLowerCase(), s2 = el2[index].toLowerCase();
        return s1 == s2 ? 0 : (s1 < s2 ? -1 : 1);
    }

    // if checkedBooleanIndex == 0 then select first radio button,
    // if checkedBooleanIndex == 1 then select 2nd radio button...
    function CreateRadios(filterMax, radioMax, checkedBooleanIndex) {

        var radioMaxtoString = radioMax.toString();

        var checkInc = $('<input>')
			.attr({
				id: 'include' + radioMaxtoString,
				type: 'radio',
				name: 'radio' + radioMaxtoString,
			})
			.val('');

        var checkExc = $('<input>')
			.attr({
				id: 'exclude' + radioMaxtoString,
				type: 'radio',
				name: 'radio' + radioMaxtoString,
			})
			.val('!');

		if (checkedBooleanIndex) {
			checkExc.attr('checked', 'checked');
		} else {
			checkInc.attr('checked', 'checked');
		}

        return [
    		$('<label>').addClass('filter').text('Filter ' + filterMax),
			checkInc,
			$('<label>').attr({'for':'include' + radioMaxtoString, 'class': 'radioButton'}).text('Include'),
			checkExc,
			$('<label>').attr({'for': 'exclude' + radioMaxtoString,'class': 'radioButton'}).text('Exclude')
		];
    }

    function GetCatObj(name){

        var catObj = {
            index: null,
            name : null,
            slug : null
        };

        $.each($q.config.filterType, function( key, value ) {
            if(value.slug == name){
                catObj.index = value.id;
                catObj.name = value.name;
                catObj.slug = value.slug;
            }
        });

        return catObj;
    }

    function GetCategories(data) {

        var fTypes = data.split("&"),
            filters = [];

        $.each(fTypes, function( key, value ) {

            value = decodeURIComponent(value);
            var cats = value.split("=");
            var catObj = $q.utils.GetCatObj(cats[0]);

            if(cats.length > 0){

                if( Object.prototype.toString.call( $q.utils.ParseJson(cats[1]) ) === '[object Array]' ) {

                    var catVals = $q.utils.ParseJson(cats[1]);

                    $.each(catVals, function( k, v ) {

                        var obj = null;
                        if(catObj.slug == 'clients' || catObj.slug == "licenses"){
                            //get the value for the _id

                             for (var i = 0, len = v.length; i < len; i++){
                                obj = GetObjFromJson($q.config.data[catObj.slug], "_id", v[i].replace("!", ""));
                                v[i] = obj.name;
                            }

                        }

                        var filterObj = new $q.filters.Filter(catObj.index, catObj.name, v);
                        filterObj = $.extend( filterObj, obj);

                        filters.push(filterObj);
                    });

                }else{

                    //This is for boolean values in querysting that are not in an Array

                    //advertorial
                    var str = cats[1];
                    if(catObj.slug == "advertorial"){
                        //this is to set radio Include Exclude
                        if(cats[1] === false){
                            str = "!";
                        }else{
                            str = "";
                        }

                    //query
                    }else if(catObj.slug == "q"){
                        str = str.replace(/"/g, "");
                    }

                    var filterObj = new $q.filters.Filter(catObj.index, catObj.name, [str], null);
                    filters.push(filterObj);

                }

            }else{

                Log("$q.Edit.GetCats(): No Params in JSON");

            }
        });

        return filters;
    }

    // sort filter array function
    function DynamicSort(property) {
        var sortOrder = 1;
        if(property[0] === "-") {
                sortOrder = -1;
                property = property.substr(1);
        }
        return function (a,b) {
                var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
                return result * sortOrder;
        }
    }

    function GetFilterObj( mainDropDownValue, radioValue, tags ) {
        filterObj = {};
        filterObj.sortKey = mainDropDownValue + radioValue;
        filterObj.mainDropDownValue = mainDropDownValue;
        filterObj.radioValue = radioValue;
        filterObj.tags = tags;
        return filterObj;
    }

    function GetObjFromJson(arr, key, val){

        for (var i = 0, len = arr.length; i < len; i++)
        {
            if (arr[i][key] === val)
            {
                return arr[i]; // Return as soon as the object is found
            }
        }
    }

    function ParseJson(str) {

        var obj;

        try {
            obj = JSON.parse(str);
        } catch (e) {
            obj = str;
        }

        return obj;
}

    function MergeTwoArrays(arr1, arr2){

        for (var i = 0, len = arr1.length; i < len; i++) {
            for (var j = 0, len2 = arr2.length; j < len2; j++) {
                if (arr1[i]._id === arr2[j].id) {

                    var newObj = {
                        permissions: arr2[j]
                    }

                    $.extend( arr1[i], newObj );
                }
            }
        }

        return arr1;

    }

    function CreateSlug(str){

        if(str)
            return str.replace(/\s+/g, '-').toLowerCase();
        else
            return null;
    }

    function GoBackWithRefresh(event) {
        if ('referrer' in document) {
            window.location = document.referrer;
            /* OR */
            //location.replace(document.referrer);
        } else {
            window.history.back();
        }
    }

    function GetDomain() {

        var pageHost = window.location.host;
        var splitHost = pageHost.split('.');

        if (splitHost.length > 1) {

            domain = splitHost[splitHost.length - 2] + "." + splitHost[splitHost.length - 1];

        } else {

            //so it will work with localhost etc...
            domain = pageHost;
        }

        return domain;
    }

    function CleanJS( str ) {
        return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
    }

    function IsGuid(value) {
        var regex = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
        var match = regex.exec(value);
        return match != null;
    }

    return{
        FormatDate: FormatDate,
        GetQueryParameter: GetQueryParameter,
        GetCatObj: GetCatObj,
        Log: Log,
        IsGuid: IsGuid,
        CleanJS: CleanJS,
        GetDomain: GetDomain,
        CreateSelect: CreateSelect,
        MergeTwoArrays: MergeTwoArrays,
        ParseJson:ParseJson,
        AddOption: AddOption,
        RemoveOption: RemoveOption,
        CreateRadios : CreateRadios,
        GetCategories: GetCategories,
        CreateSlug: CreateSlug,
        DynamicSort: DynamicSort,
        GetObjFromJson: GetObjFromJson,
        GetFilterObj: GetFilterObj,
        GoBackWithRefresh: GoBackWithRefresh
    }

})(jQuery);
