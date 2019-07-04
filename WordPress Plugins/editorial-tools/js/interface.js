/* eslint-disable no-undef, func-names, no-shadow, new-cap */
$q.interface = ( function( $ ) {
	function Log( msg, type ) {
		$q.utils.Log( msg, type );
	}

	function RunFilter() {

		var filterData = $q.filters.CreateQueryStr();
		$q.interface.HideNextBtn();
		$q.interface.HidePrevBtn();

		if ( $q.config.data.searchQ != filterData && null != filterData ) {
			// store new query so we can compare later
			$q.pager.Reset();
			$q.config.data.searchQ = filterData;
			$q.edit.GetPostsByQuery( filterData );

		} else {

			if ( filterData == null ) {
				//Empty table
				$q.config.data.searchQ = '';
				$q.interface.AddTableData( $( '.resultsPanel' ), '' );
				$( '.resultsPanel' ).hide();

			} else {

				$q.interface.ShowHeadsupDisplay( 'Search Parameters Unchanged....', false, false, true );
				$q.interface.HideHeadsupDisplay( 1.5 );
			}

			Log( 'Interface: RunFilter: Search Query has not changed or no filters, not getting data' );
		}
	}

	function ToggleRunPanel() {

		// Log('>> Toggle Run Panel [TRP]');
		var showPanel = false,
			mDD, sDD;

		$( 'tr.generatedRow' ).each( function( i ) {
			mDD = $( this ).find( '.mainDropDown option:selected' ).text();
			if ( mDD === 'Sponsored' ) { showPanel = true; }
			else {
				sDD = $( this ).find( 'ul.tags li' ).length;
				// Log(sDD);
				if ( sDD > 0 ) { showPanel = true; }
			}
		});

		if ( true == showPanel ) {
			$( '.runPanel' ).show();
			$( '.savePanel' ).show();
		} else {
			// Log('>> -- TRP nothing found, hide Run Panel');
			$( '.runPanel' ).hide();
			$( '.savePanel' ).hide();
		}

		//This may be added later and we get rid of search button
		RunFilter();

		return;
	}

	Log( '>> Using interface.js version 2g' );

	// add "Modal Window"...
	$( 'div#app' ).after( function() {
		return '<div id="modalWindowHolder"><div id="modalWindow" class="safari_only"><div id="modalWindowContent"><br /></div></div></div>';
	} );

	// add "Heads Up Display"...
	$( 'div#app' ).after( function() {
		return '<div id="headsUpDisplay">Empty</div>';
	} );

	// keyword hidden tag maker
	$( document ).on( 'blur', '.keyword', function() {
		var txt = $( this ).val(),
			ulTag = $( this ).parent().next().find( 'ul.tags' );
		if ( '' !== txt ) {
			// only one keyword allowed, so empty UL first
			$( ulTag ).empty();
			$q.filters.AddTags( ulTag, txt );
		} else {
			$( ulTag ).empty();
		}
		ToggleRunPanel();

		return;
	} ).on( 'keyup', '.keyword', function( e ) {
		//If user presses enter, trigger same action
		if( 13 === e.keyCode ) {
			this.blur();
		}
	} );

	// comma delimited tag maker
	$( document ).on( 'blur', '.commaDelimitedInput', function( e ) {
		var txt = $(this).val(),
			tags = '',
			ulTag = $(this).parent().next().find('ul.tags');
		$(ulTag).empty();

		// remove extra whitespace (so only single spaces allowed)
		txt = txt.replace(/\s\s+/g, ' ');
		// remove dangling commas (like "cat , dog"), and
		// add space after comma if needed
		txt = txt.replace(/\s*,\s*/g, ', ' ).replace(/^,?\s*|,?\s*$/g, '');
		$(this).val(txt);

		if (txt != '') {
			// now split on ', ' to get tags
			tags = txt.split(', ');
			var arrayLength = tags.length,
				buildStr = '';
			for (var i = 0; i < arrayLength; i++) {
				// $ (ulTag).append('<li>' +  tags[i] + '</li>');
				$q.filters.AddTags(ulTag, tags[i]);
			}
			ToggleRunPanel();
		}

		return;
	}).on("keyup", '.commaDelimitedInput', function (e) {

		//If user presses enter, trigger same action
		if(e.keyCode == 13){
			this.blur();
		}
	});

	// bind tag remover
	$(document).on("click", '.tagsHolder ul.tags li', function (e) {

		var tag = $(this),
			value = tag.html(),
			parent = tag.parent().parent(),
			parentDD = parent.prev().find("select"),
			parentInput = parent.prev().find("input"),
			dataModel = parent.attr("data-model"),
			obj = {};

		//Grab the object from original data so we can add back if it is a DD
		if(dataModel != undefined){

			obj = $q.utils.GetObjFromJson(eval(dataModel), "name", value);

			//There is a difference between locally defined objects and json from AJAX
			if(obj._id){
				option = $q.utils.AddOption(null, obj._id, obj.name, null);
			}
			else{
				option = $q.utils.AddOption(null, obj.name, obj.name, null);
			}

			//Append back to select in correct order
			var added = false;
			$( 'option', parentDD).each(function() {
				//Append back to the DD in place
				if ($(this).text().toLowerCase() > obj.name.toLowerCase()) {
					$(this).before(option);
					added = true;
					return false;
				}
			});
			if(!added) parentDD.append(option);
		}else{
			//its an input box not a select
			var textToRemove = value;
			if(parentInput.val().indexOf(textToRemove + ", ") >= 0){
				textToRemove = value + ", ";
			}

			parentInput.val(parentInput.val().replace(textToRemove, ""));

		}
		//Remove the Tag
		tag.remove();

		ToggleRunPanel();
	});

	// remove filter
	$(document).on("click", 'button.deleteFilter', function (e) {

		// Log('>> Remove Filter ');
		var c = 0;
		e.stopPropagation();
		e.preventDefault();
		$(this).parents('tr' ).remove();

		// rename filters
		$( 'tr.generatedRow' ).each(function () {
			c++;
			$(this).find('label.filter' ).text('Filter ' + c);
		});

		ToggleRunPanel();

	});

	// add filter button

	$( 'button#buttonAddFilter' ).click(function (e) {
		e.stopPropagation();
		e.preventDefault();
		$q.filters.AddFilter();
	});

	// RUN FILTERS

	$( 'button#buttonRunFilters' ).click(function (e) {
		RunFilter();
	});

	$( 'button#btnSaveFilters' ).click(function (e) {
		var filterData = $q.filters.CreateQueryStr();

		if($q.config.mode == "Edit")
			$q.edit.UpdateQuery(filterData, "Update");
		if($q.config.mode == "Create")
			$q.edit.CreateQuery(filterData, "Create");

	});

	$( 'button#btnCopyFilters' ).click(function (e) {

		var filterData = $q.filters.CreateQueryStr();

		if($q.config.mode == "Edit")
			$q.edit.CreateQuery(filterData, "Copy");
	});

	//cancel (go back)
	$( 'button#btnCancel' ).click(function (e) {
		$q.utils.GoBackWithRefresh(e);
	});

	//Next 15 (List Page)
	$( 'button.btnListNext' ).click(function (e) {

		if($q.config.mode == "List")
			$q.list.GetNext();
		else
			$q.edit.GetNext();
	});

	//Prev 15 (List Page)
	$( 'button.btnListPrev' ).click(function (e) {
		if($q.config.mode == "List")
			$q.list.GetPrev();
		else
			$q.edit.GetPrev();
	});

		// For ListSearch.html searchBox
	$( 'button#btnSearchList' ).click(function (e) {
		$q.list.GetSearchList();
	});

	$( '#txtSearchlist' ).on("keyup", function (e) {
		// If user presses enter, trigger same action
		if(e.keyCode == 13){
			$q.list.GetSearchList();
		}
	});

	// secondary "Branding" dropdown changed
	$(document).on("change", 'select.tagsDropDown', function (e) {

		var optionTag = $( 'option:selected', this).text(),
			optionVal = $( 'option:selected', this).val(),
			ulTag = $(this).parent().next().find('ul.tags'),
			model = $(this).attr("data-model");

		$q.filters.AddTags(ulTag, optionTag);
		ToggleRunPanel();

	});

	//Bind Title
	$( "#searchTitle" ).blur(function() {
		Log("Title Changed:" + $(this).val());
		$q.config.data.query.title = $(this).val();
	});

	function ShowHeadsupDisplay(str, linkTxt, href, showLoading, secs) {
		var delay = secs ? secs * 1000 : 1000;

		$('#headsUpDisplay').empty();
		$('#headsUpDisplay').append($('<span>').text(str));

		if (showLoading) {
			$('#headsUpDisplay').append($('<i>').addClass('fa fa-spinner fast-spin'));
		}

		if (linkTxt && href) {
			$('#headsUpDisplay').append($('<a>').attr('href', href).text(linkTxt));
		}

		if (! secs) {
			$( '#headsUpDisplay' ).fadeIn();
		}
		$( '#headsUpDisplay' ).delay(delay);

		return;
	}

	function HideHeadsupDisplay ( secs ) {
			if ( arguments.length == 0 ) {
				$( '#headsUpDisplay' ).fadeOut();
			}
			else {
				var secs = secs*1000;
				setTimeout(function(){ $( '#headsUpDisplay' ).fadeOut() }, secs);
			}

			return;
	}

	// main dropdown changed
	function OnMainSelectChange(catSelectBox) {

		var optionText = $( 'option:selected', catSelectBox).text(),
			nextTD = $(catSelectBox).parent().next();

		// Log('>> Chose "' + optionText + '" from main dropdown');
		$q.filters.AddInput(optionText, nextTD);
		// because something was selected,  make see if "Run Panel" should show
		if(optionText == "Sponsored")
			ToggleRunPanel();

	}

	function SetPageTitles(){

		var title = $q.config.mode + " List",
			filterId = $q.config.filterId;

		$(".breadcrumb .current").html(title);
		$("#pageTitle").html(title);

		if($q.config.mode == "Edit"){
			title += " - " + filterId;
			$("#searchId").html(filterId);
			ShowPanels();
		}

		$(document).attr("title", title);

	}

	function ShowPanels(){

		$(".alreadySavedPanel").show();
		$(".savePanel").show();
		$(".runPanel").show();
		$(".resultsPanel").show();

	}

	function ShowNextBtn(){
		$( 'button.btnListNext' ).show();
	}

	function ShowPrevBtn(){
		$( 'button.btnListPrev' ).show();
	}

	function HideNextBtn(){
		$( 'button.btnListNext' ).hide();
	}

	function HidePrevBtn(){
		$( 'button.btnListPrev' ).hide();
	}

	function LightBox(content) {

		$( '#modalWindowContent' ).html(content.body.replace(/{carriage}/g, "<br /><br />"));
		$( '#modalWindowContent' ).prepend($('<h2>').text(content.header));
		$( '#modalWindowHolder' ).fadeIn();

		return;
	}

	// make so-called "Lightbox" close-able (when you click on backgorund...)
	$( 'body' ).on('click', '#modalWindowHolder', function ( ){
			$( this ).fadeOut();
	});

	// make so-called "Lightbox" itself not close when clicked
	$( 'body' ).on('click', '#modalWindow', function ( event ){
			event.stopPropagation();
	});

	function AddTableData(target, htmlRows){
		body = target.find("tbody");

		// Remove Old Data
		body.find("tr").remove();

		$(body).append(htmlRows);
		// zebra-ify the lists table
		$("tr:odd", body).addClass("odd");

		//Show it in case it's not shown
		target.show();
		// HideHeadsupDisplay (.5);
	}


	function ShowUserMsg(msg, linkTxt, href){
		var container = $('<p>');
		var message = $('<span>').text(msg);
		container.append(message);
		if (linkTxt && href) {
			var link = $('<a>').attr('href', href).text(linkTxt);
			container.append(link);
		}
		$("#msg").append(container);
		Log(msg);
	}

	function ShowUserAlert(msg){

		var content = {
			header: "Access Request",
			body: msg
		}

		// LightBox(content);
		// alert('***' + msg);
		//ShowHeadsupDisplay ( '<i class="fa fa-exclamation-triangle"></i> <span>' + msg + '</span>' );
		ShowHeadsupDisplay ( msg );

	}

	function DisableButton(btn){

		//DEBUGGING
		//Need to figure out how to add back disabled msg

		$(btn).attr('disabled', 'disabled');
		$(btn).removeClass("blue");
		$(btn).removeClass("red");
		$(btn).addClass("grey");

	}

	function EnableButton(btn, className){
		if ( ! className ) {
			className = "blue";
		}

		//DEBUGGING
		//Need to figure out how to add back disabled msg

		$(btn).removeAttr('disabled');
		$(btn).addClass(className);
		$(btn).removeClass("grey");

	}

	return {
		RunFilter: RunFilter,
		ShowNextBtn:ShowNextBtn,
		HideNextBtn: HideNextBtn,
		ShowPrevBtn: ShowPrevBtn,
		HidePrevBtn: HidePrevBtn,
		SetPageTitles: SetPageTitles,
		AddTableData: AddTableData,
		OnMainSelectChange: OnMainSelectChange,
		ShowUserMsg: ShowUserMsg,
		ShowUserAlert: ShowUserAlert,
		ShowHeadsupDisplay: ShowHeadsupDisplay,
		HideHeadsupDisplay: HideHeadsupDisplay,
		LightBox: LightBox,
		DisableButton: DisableButton,
		EnableButton: EnableButton
	}

}(jQuery));