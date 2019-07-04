

$q.templates = (function ($) {

    var listTable = '<table class="lists striped"><thead><tr>';
        listTable += '<th class="num">#</th>';
        listTable += '<th class="title">Name</th>';
        listTable += '<th>Source</th>';
        listTable += '<th>Status</th>';
        listTable += '<th>Published</th>';
        listTable += '</tr>';
        listTable += '</thead><tbody>';

    var listRow = '<tr>';
        listRow += '<td>{index}</td>';
        listRow += '<td>{Title}</td>';
        listRow += '<td>{Client}</td>';
        listRow += '<td title={id}>{License}</td>';
        listRow += '<td>{DateModified}</td>';
        listRow += '<td class="r">';
        listRow += '{EditButton}';
        listRow += '{ActionButton}';
        listRow += '</td>';
        listRow += '</tr>';

    var filterSearchResults = '<tr class="{class}">';
        filterSearchResults += '<td>{index}</td>';
        filterSearchResults += '<td title={id}>{Title}</td>';
        filterSearchResults += '<td>{Source}</td>';
        filterSearchResults += '<td>{Published}</td>';
        filterSearchResults += '</tr>';

    var pointerSearchResults = '<tr>';
        pointerSearchResults += '<td>{index}</td>';
        pointerSearchResults += '<td title={id}>{Title}</td>';
        pointerSearchResults += '<td>{Source}</td>';
		    pointerSearchResults += '<td>{Status}</td>';
        pointerSearchResults += '<td>{Published}</td>';
        pointerSearchResults += '<td class="r">{Actions}</td>';
        pointerSearchResults += '</tr>';

    var noData = '<tr><td>1</td><td>NO RESULTS</td></tr>';

    var HTMLfragmentMainDropDownOptions = '<option>Choose Option</option><option>Brand / Product</option><option>License</option><option>Post Type</option><option>Category</option><option>Tags</option><option>Sponsored</option><option>Keyword</option>',
		HTMLfragmentGenericInput = '<div style="width: 52.3%;"><input type="text" class="commaDelimitedInput" /></div><div class="tagsHolder"><ul class="tags"></ul></div>';
		HTMLfragmentKeywordInput = '<div style="width: 52.3%;"><input type="text" class="keyword" /></div><div class="tagsHolder"><ul class="tags" style="display: none;"></ul></div>';

    var pointerActions = {
        view: '<button type="button" onclick="{Action}" class="btn small shadow grey fa-icon fa  fa-eye" title="View"></button>',
        edit: '<button type="button" onclick="{Action}" class="btn small shadow grey fa-icon fa fa-edit" title="Edit Original"></button>',
        listEdit: '<button type="button" onclick="{editAction}" class="btn blue shadow fa-icon fa fa-edit" title="Edit"></button>',
        listView: '<button type="button" onclick="{viewAction}" class="btn blue shadow fa-icon fa fa-eye" title="View"></button>',
        email: '<button type="button" onclick="{Action}" class="btn small shadow grey fa-icon fa fa-envelope-o" title="Email"></button>',
        copy: '<button type="button" onclick="{Action}" class="btn small shadow grey fa-icon fa fa-files-o" title="Create a Copy"></button>',
        create: '<button type="button" onclick="{Action}" class="btn small shadow grey fa-icon fa fa-plus" title="Create Pointer"></button>',
        delete: '<button type="button" onclick="{Action}" class="btn red shadow fa-icon fa fa-times" title="Delete"></button>',
        restore: '<button type="button" onclick="{Action}" class="btn red shadow fa-icon fa fa-undo" title="Restore"></button>'
    };

    var userMsgs = {
        filterCopy : "Query ID: {dataId} Successfully Copied.",
        filterCreate: "Query ID: {dataId} Successfully Created.",
        filterUpdate: "Query ID: {dataId} Successfully Updated with query: {query}",
        listItemDelete: "Query: {title} Successfully Deleted.",
        listRestore: "List {dataId} Successfully Restored."
    };

    return{
        listRow: listRow,
        listTable: listTable,
        pointerActions: pointerActions,
        noData: noData,
        userMsgs: userMsgs,
        HTMLfragmentMainDropDownOptions: HTMLfragmentMainDropDownOptions,
        HTMLfragmentGenericInput: HTMLfragmentGenericInput,
		    HTMLfragmentKeywordInput: HTMLfragmentKeywordInput,
        filterSearchResults: filterSearchResults,
        pointerSearchResults: pointerSearchResults
    }

})(jQuery);
