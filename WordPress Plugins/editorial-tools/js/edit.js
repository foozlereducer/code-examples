$q.edit = (function ($) {

    var api = $q.config.api,
        userMsgs = $q.templates.userMsgs;

    function Init(){

        var filterId = $q.utils.GetQueryParameter("filterId");

        //disable "keyword" (need to find better way
        $q.config.filterType[6].enabled = false;

        if(filterId != null){

            $q.config.mode = "Edit";
            $q.config.filterId = filterId;
            LoadEditData();
            Log("Edit mode: Id= " + $q.config.filterId);

        }else{
            $q.config.mode = "Create";
            LoadCreateData();
            Log("New Query Mode");
        }

        $q.interface.SetPageTitles();
    }

    function LoadEditData(){

        //Need to make this async
        GetFilterById(function () {
            GetLicences(function () {
                GetClients(function () {
                    PopulateFields();
                });
            });
        });
    }

    function LoadCreateData(){
        GetLicences(function () {
            GetClients(function () {
            });
        });
    }

    //data get FilterById
    function GetFilterById(callback){

        var uri = api.base + api.get.lists + $q.config.filterId + "/" + "?expanded=false";

        $q.data.Get(uri,$q.keys.val.get, function (response) {
            $q.config.data.query = response[0];
            callback(response);
        });
    }

    //data get FilterById
    function GetLicences(callback){

        var uri = api.base + api.get.licenses;

        $q.data.Get(uri,$q.keys.val.get, function (response) {
            $q.config.data.licenses = response;
            callback();
            $q.interface.HideHeadsupDisplay (.5);
        });
    }

    //data get FilterById
    function GetClients(callback){

        var uri = api.base + api.get.clients;
        $q.data.Get(uri,$q.keys.val.get, function (response) {
            $q.config.data.clients = response;
            callback();
            $q.interface.HideHeadsupDisplay (.5);
        });
    }

    //data get Posts By Filters
    function GetPostsByQuery(queryString) {

        //var uri = api.base + api.get.content + queryString  + "&size=" + $q.config.api.topN;
        var uri = api.base + api.get.content + queryString + "&" + $q.pager.GetQuery();

        if($q.config.mode == "Pointer"){
            uri += '&status=[["published"]]';
        }

        $q.data.Get(uri, $q.keys.val.get, function (response) {

            $q.config.data.lists = response;

            if($q.config.mode != "Pointer"){

                ConstructTable(response);

            }else{

                $q.pointers.LookUpPosts(response, function(response2){
                    $q.pointers.ConstructTable($q.config.data.lists, response2);
                });

            }
        });

        Log("GetPostsByQuery=" + uri);

    }

    function GetNext(){

        $q.pager.SetFrom($q.config.api.from + $q.config.api.topN);
        $q.pager.GetNext();
        var query = $q.filters.CreateQueryStr();
        GetPostsByQuery(query);
    }


    function GetPrev(){

        $q.pager.GetPrev();
        var query = $q.filters.CreateQueryStr();
        GetPostsByQuery(query);
    }

    function UpdateQuery(queryString, action){

        var upDateObj = {
            "type": $q.config.data.query.type,
            "client_id": $q.keys.val.clientId,
            "title": $q.config.data.query.title,
            "status": $q.config.data.query.status,
            "query": queryString
        };

        var queryId = $q.config.data.query._id
        var uri = api.base + api.post.lists + queryId;

        $q.data.Put(uri, upDateObj, $q.keys.val.put, function (response) {
            var data = $q.config.data.query = response;
            $q.interface.ShowUserMsg(userMsgs.filterUpdate.replace("{dataId}", data._id).replace("{query}", queryString));
        });

        Log("UpdateQuery=" + uri);
        Log(JSON.stringify(upDateObj));
    }

    function CreateQuery(queryString, action){

        var title = $q.config.data.query.title;

        var upDateObj = {
            "type": "dynamic",
            "client_id": $q.keys.val.clientId,
            "title": title,
            "status": "published",
            "slug" : $q.utils.CreateSlug(title),
            "description": title,
            "query": queryString
        };

        var uri = api.base + api.post.lists;
        $q.data.Post(uri, upDateObj, $q.keys.val.post, function (response) {
            var data = $q.config.data.query = response;

            //List has been created, switch to edit mode
            $q.config.mode = "Edit";

            if(action == "Copy")
                $q.interface.ShowUserMsg(userMsgs.filterCopy.replace("{dataId}", data._id));
            else
                $q.interface.ShowUserMsg(userMsgs.filterCreate.replace("{dataId}", data._id));
        });

        Log("UpdateQuery=" + uri);
        Log(JSON.stringify(upDateObj));

    }

    function PopulateFields(){

        var data = $q.config.data.query;

        $("#searchTitle").val(data.title);
        $("#modifiedOn").html($q.utils.FormatDate(data.modified_on));

        //Populate Filters
        if(data.query){
            var filters = $q.utils.GetCategories(data.query.replace("?", ""));
            SetFilters(filters);
        }
    }

    function SetFilters(filters){

        $.each(filters, function( key, value ) {
             $q.filters.AddFilter(value);
        });

        //Store the querystring so we can compare later
        $q.config.data.searchQ = $q.config.data.query.query;
        GetPostsByQuery($q.config.data.query.query);
    }

    function ConstructTable(data){

        var html = "", counter = 0, numResults = 0;

		if(data.length > 0){

			$.each(data, function( index, value ) {

                if(value.type.toLowerCase() !== "versus"){

                    counter++;
                    var row = $q.templates.filterSearchResults;
                    var clientObj = $q.utils.GetObjFromJson($q.config.data.clients, "_id", value.client_id);
                    var name = "";

                    if(clientObj) name = clientObj.name;

                    row = row.replace("{index}", ($q.config.api.from + counter));
                    row = row.replace(/{id}/g, "'" + value._id + "'");
                    row = row.replace("{Title}", value.titles.main);
                    row = row.replace("{Source}", name);

                    html += row;
                }
			});

            numResults = data.length;

            if(numResults >= $q.config.api.topN)
                $q.interface.ShowNextBtn();
            else
                $q.interface.HideNextBtn();

		}else{
            $q.interface.HideNextBtn();
            html += $q.templates.noData;
		}

		$q.interface.AddTableData($(".resultsPanel"), html);
    }

    function Log(msg, type) {

        $q.utils.Log(msg, type);

    }

    return {
        Init: Init,
        GetPostsByQuery: GetPostsByQuery,
        UpdateQuery: UpdateQuery,
        CreateQuery: CreateQuery,
        GetClients: GetClients,
        GetLicences: GetLicences,
        GetFilterById: GetFilterById,
        GetNext: GetNext,
        GetPrev: GetPrev
    }

})(jQuery);