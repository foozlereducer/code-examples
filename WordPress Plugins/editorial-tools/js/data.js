

$q.data = (function ($) {
	var wcmApiXhr = {};

    function Get(url, key, callback){

        Ajax(url, null, key, callback, "GET");

    }

    function Post(url, data, key, callback){
        Ajax(url, data, key, callback, "POST");
    }

    function Put(url, data, key, callback){

        Ajax(url, data, key, callback, "PUT");

    }


    function Ajax(url, data, key, callback, type){
        $q.interface.ShowHeadsupDisplay('Loading... ', false, false, true);

        var event, query;

        if( $q.config.mode == "Pointer"){

             if( $q.config.nonce != null ) {

                if(url.indexOf("clients/")!== -1){
                    event = "clients";
                }else if(url.indexOf("licenses/")!== -1){
                    event = "licenses";
                }else if(url.indexOf("content/")!== -1){
                    event = "content";
                }
                var qArr = url.split("?");
                if(qArr.length > 1) {
                    query = decodeURIComponent(qArr[1]);
                }

                if ( wcmApiXhr[event] ) {
                    wcmApiXhr[event].abort();
                }

                url = ajaxurl;
                type = "POST";

                if(data == null){
                    var data = {
                        action : "call_wcm_api",
                        nonce : $q.config.nonce,
                        event: event
                    }
                    if ( query ) {
                       data.query = query;
                    }
                }
            }
        }

        wcmApiXhr[event] = $.ajax({
            type: type,
            dataType: 'json',
            data: data,
            url: url,
            beforeSend: function(request) {
                request.setRequestHeader("x-api-key", key);
            },
            success: function(response) {
	            wcmApiXhr[event] = null;
                callback(response);
            },
            error: function(jqXHR, exception) {
                wcmApiXhr[event] = null;
                OnError(jqXHR, exception);
            }
        });

    }

    function OnError(jqXHR, exception){

        if(jqXHR.responseText && jqXHR.status !== 404 && jqXHR.status !== 400){
            var response = $q.utils.ParseJson(jqXHR.responseText);
            $q.interface.ShowUserMsg("Error: " + response.error + " Msg: " + response.message + " StatusCode: " + response.statusCode);
        } else {

            if (jqXHR.status === 0) {
                Log('Not connect.\n Verify Network.');
            } else if (jqXHR.status == 404) {
                //DEBUGGING
                //THIS WILL CHANGE MEANS NO DATA IN RESULT SET
                //Log('No Data', 'log');
                //$q.interface.ShowUserMsg("No Records Found");
                $q.interface.AddTableData($(".resultsPanel"), $q.templates.noData);
                $q.interface.AddTableData($(".lists"), $q.templates.noData);
                //DEBUGGING
            } else if (jqXHR.status == 400) {
                //DEBUGGING
                //THIS WILL CHANGE MEANS NO DATA IN RESULT SET
                //Log('No Data', 'log');

                $q.interface.ShowUserMsg("No Records Found");
                $q.interface.AddTableData($(".resultsPanel"), $q.templates.noData);
                $q.interface.AddTableData($(".lists"), $q.templates.noData);
                //DEBUGGING
            } else if (jqXHR.status == 500) {
                Log('Internal Server Error [500].');
            } else if (exception === 'parsererror') {
                Log('Requested JSON parse failed.');
            } else if (exception === 'timeout') {
                Log('Time out error.');
            } else if (exception === 'abort') {
                Log('Ajax request aborted.');
            } else {
                Log('Uncaught Error.\n' + jqXHR.responseText.replace(/&quot;/g,"'"));
            }
        }

        $q.interface.HideHeadsupDisplay (.5);
    }

    function Log(msg, type) {

        if(!type)
            type = "error";

        $q.utils.Log(msg, "error");

    }

    return {
        Get: Get,
        Post: Post,
        Put: Put
    }

})(jQuery);