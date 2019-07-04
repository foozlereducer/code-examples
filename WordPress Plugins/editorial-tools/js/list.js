

$q.list = (function ($) {

    var api = $q.config.api,
        userMsgs = $q.templates.userMsgs,
        isFirstSearch = true;

    function Init(){

        $q.config.mode = "List";
        SetSelectBoxes();
        //Need client list for viewing curated Posts
        $q.edit.GetClients(function () {
            GetSearchList();
        });
    }

    //***************************************************
    //Data
    //***************************************************

    function GetSearchList(){

        // This is only called onChange of the searchBox so Reset paging
        $q.pager.Reset();

        //THis is a hack.  Check to see if this is A WCM ID.  If so we will be making ajax call to see if it exists. This
        //Will eventually be handled by the list API q=searchTerm as well
        var searchTerm = GetSearchTerm();
        if($q.utils.IsGuid(searchTerm)){

            Log("GUID yes");

            $q.config.filterId = searchTerm;
            $q.edit.GetFilterById(function(response){
                $q.config.data.lists = response;
                AddListData($q.config.data.lists);
            });

        }else{
            //End of Hack
            //It should just be the 2 lines below in this function
            var query = BuildQuery();
            GetSearchData(query);
        }

    }

    function GetSearchData(query){

        var uri = api.base + api.get.lists;

        if(query != "" && query != undefined)
            uri += query;

        Log(uri);

        $q.data.Get(uri,$q.keys.val.get, function(response){
            $q.config.data.lists = response;
            AddListData($q.config.data.lists);
        });

    }

    //***************************************************
    //ui
    //***************************************************
    function AddListData(data){

        var html = "",
            numResults = 0,
            searchTerm = GetSearchTerm();

        if(data.length > 0){

            $.each(data, function( index, value ) {

                var row = $q.templates.listRow,
                    btns = $q.templates.pointerActions,
                    title = $q.utils.CleanJS(value.title);

                var clientObj = $q.utils.GetObjFromJson($q.config.data.clients, "_id", value.client_id);
                var name = "[Central]";
                if(clientObj) name = clientObj.name;

                row = row.replace("{index}",  ($q.config.api.from + index +1));
                row = row.replace(/{id}/g, "'" + value._id + "'");
                row = row.replace("{Title}", value.title);
                row = row.replace("{Client}",name);
                row = row.replace("{License}", value._id);
                row = row.replace("{DateModified}", $q.utils.FormatDate(value.modified_on));

                if(value.type != "curated"){

                    var btn = btns.listEdit.replace("{editAction}", "$q.list.EditListItem(" + "'" + value._id + "')");
                    row = row.replace("{EditButton}", btn);

                    if(value.status != "deleted")
                    {
                        var btn = btns.delete.replace("{Action}", "$q.list.DeleteListItem(" + "'" + value._id + "', '" + title + "', this)");
                        row = row.replace("{ActionButton}", btn);
                    }
                    else
                    {
                        var btn = btns.restore.replace("{Action}", "$q.list.RestoreListItem(" + "'" + value._id + "', '" + title + "', this)");
                        row = row.replace("{ActionButton}", btn);
                    }

                }else{
                    //No Buttons
                    row = row.replace("{EditButton}", "");

                    //var btn = btns.listEdit.replace("{editAction}", "$q.list.EditListItem(" + "'" + value._id + "')");
                      //  row = row.replace("{EditButton}", btn);

                    var btn = btns.listView.replace("{viewAction}", "$q.list.ViewListItem(" + "'" + value._id + "', '" + title + "', this)");
                        row = row.replace("{ActionButton}", btn);
                }

                html += row;

            });

            numResults = data.length;

            if(numResults >= $q.config.api.topN)
                $q.interface.ShowNextBtn();
            else
                $q.interface.HideNextBtn();
        }else{

            html += $q.templates.noData;
            $q.interface.HideNextBtn();
		}

        var infoHtml = "Displaying " + numResults + " entries.  Page " + $q.config.api.page;

        if(searchTerm != "")
            infoHtml = "Search results for '" + searchTerm + "'. " + infoHtml;

        $("#listInfo").text(infoHtml);
        $q.interface.AddTableData($(".lists"), html);
    }

    //***************************************************
    //Actions
    //***************************************************

    function EditListItem(filterId){
        window.location.href="createSearch.html?filterId=" + filterId;
    }

    function ViewListItem(filterId, filterName){

        var uri = api.base + api.get.lists + filterId;

        $q.data.Get(uri,$q.keys.val.get, function (response) {

            var body = $q.templates.listTable;

            if(response.length > 0){

                $.each(response, function(i, val){

                    var row = $q.templates.filterSearchResults;
                    var clientObj = $q.utils.GetObjFromJson($q.config.data.clients, "_id", val.client_id);
                    var name = "[Central]";
                    if(clientObj) name = clientObj.name;

                    if((i+1)%2)
                        row = row.replace("{class}", "odd");

                    row = row.replace("{index}", i+1);
                    row = row.replace("{Title}", this.titles.main);
                    row = row.replace("{Source}", name);

                    body += row;
                });

            }else{

                body += $q.templates.noData;
            }

            body += '</tbody></table>';

            var content = {
                header: "List of Posts for Curated List '" + filterName + "'",
                body: body
            }

            $q.interface.LightBox(content);

        });
    }

    function DeleteListItem(filterId, filterName, btn){

        var deleteItem = confirm("Are you sure you want to delete item #" + filterId + " | " + filterName + "?");

		if (deleteItem == true) {

            var upDateObj = {
                "type": "dynamic",
                "client_id": $q.keys.val.clientId,
                "status": "deleted"
            };

            var queryId = filterId;
            var uri = api.base + api.post.lists + queryId;

            Log("DeleteListItem:" + uri);
            Log(JSON.stringify(upDateObj));

            $q.data.Put(uri, upDateObj, $q.keys.val.put, function(response){
                $q.interface.ShowUserMsg($q.templates.userMsgs.listItemDelete.replace("{title}", response.title));
                $q.interface.DisableButton(btn);
                //GetSearchList(); //Refresh data
            });
		}
    }

    function RestoreListItem(filterId, title, btn){

            var upDateObj = {
                "type": "dynamic",
                "client_id": $q.keys.val.clientId,
                "status": "published"
            };

            var queryId = filterId;
            var uri = api.base + api.post.lists + queryId;

            Log("RestoreListItem:" + uri);
            Log(JSON.stringify(upDateObj));

            $q.data.Put(uri, upDateObj, $q.keys.val.put, function (response) {
                $q.interface.ShowUserMsg(userMsgs.listRestore.replace("{dataId}", response.title));
                $q.interface.DisableButton(btn);
                //GetSearchList();  //Refresh data
            });

    }

    function SetSelectBoxes(){

        //these are preslected indexes from $q.config.searchTypes
        var typeSelectedIndex = "Dynamic", //dynamic
            statusSelectedIndex = "Published"; //published

        var typeSelectBox = $q.utils.CreateSelect("selectType", "", "Choose Type", $q.config.searchTypes.type, "$q.list.OnSelectChange", typeSelectedIndex, "slug", "name"),
            statusSelectBox = $q.utils.CreateSelect("selectStatus", "", "Choose Status", $q.config.searchTypes.status, "$q.list.OnSelectChange", statusSelectedIndex, "slug", "name");

        $("#searchSelect").empty().append([statusSelectBox, typeSelectBox]);
    }

    	// main dropdown changed
	function OnSelectChange() {
		GetSearchList();
	}

    function BuildQuery(fromVal){

        var typeSelect = $("#selectType"),
            statusSelect = $("#selectStatus"),
            typeOptionVal = $('option:selected', typeSelect).val(),
            statusOptionVal = $('option:selected', statusSelect).val(),
            searchWord = GetSearchTerm(),
            query = "",
            querySep = "&",
            searchQ = "";

         if(searchWord != ""){

            searchQ = "&q=" + searchWord;

            if(isFirstSearch){

                $q.pager.Reset();
                isFirstSearch = false;

            }

        }else{

            isFirstSearch = true;
        }

        query = "?" + $q.pager.GetQuery();

        if(typeOptionVal != "-1" && typeOptionVal != "all"){

            query += querySep + "type=" + typeOptionVal;
            querySep = "&";
        }

        if(statusOptionVal != "-1"  && statusOptionVal != "all"){
            query += querySep + "status=" + statusOptionVal;
        }

        return query + searchQ;

    }

    function GetNext(){

        $q.pager.SetFrom($q.config.api.from + $q.config.api.topN);
        $q.pager.GetNext();
        var query = BuildQuery();
        GetSearchData(query);

    }


    function GetPrev(){

        $q.pager.GetPrev();
        query = BuildQuery();

        GetSearchData(query);
    }

   function GetSearchTerm(){

        var searchBox = $("#txtSearchlist");
        var query = "";

        if(searchBox.val() != ""){

            //get it, trim it, put it back cleaned up
            query = searchBox.val().trim();
            searchBox.val(query);

        }

        return query;

   }

   function Log(msg, type) {

        $q.utils.Log(msg, type);

   }

    return {
        Init: Init,
        GetSearchList: GetSearchList,
        GetNext: GetNext,
        GetPrev: GetPrev,
        EditListItem: EditListItem,
        ViewListItem: ViewListItem,
        DeleteListItem: DeleteListItem,
        RestoreListItem: RestoreListItem,
        OnSelectChange: OnSelectChange
    }

})(jQuery);