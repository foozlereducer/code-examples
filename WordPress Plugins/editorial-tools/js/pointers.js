

$q.pointers = (function ($) {

    var api = $q.config.api,
        nonce = $q.config.nonce;

    function Init(){

        $q.config.mode = "Pointer";
        $q.config.postTypes[2].enabled = false;
        $q.interface.SetPageTitles();
        counter = 0;

        $q.edit.GetClients(function (response) {});
        $q.edit.GetLicences(function (response) {});
    }

    function ConstructTable(data, assert){
     var html = "", counter = 0, numResults = 0;

        //Combine with the Copy / Create Pointer Object
        if(assert){
            data = $q.utils.MergeTwoArrays(data, assert);
        }

        if(data.length > 0){

            $.each(data, function( index, value ) {
                    counter++;
                    var clientObj = $q.utils.GetObjFromJson($q.config.data.clients, "_id", value.client_id);
                    var buttons = CreateEditViewButtons(value, clientObj);
                    var row = $q.templates.pointerSearchResults;
                    var name = "";

                    if(clientObj) name = clientObj.name;

                    row = row.replace("{index}", ($q.config.api.from + counter));
                    row = row.replace(/{id}/g, "'" + value._id + "'");
                    row = row.replace("{Title}", value.titles.main);
                    row = row.replace("{Source}", name);
                    row = row.replace("{Status}", value.status);
                    row = row.replace("{Published}", formatDate(value.published_on));
                    row = row.replace("{Actions}", buttons);

                    html += row;
            });

            numResults = data.length;

            if(numResults >= $q.config.api.topN) {
                $q.interface.ShowNextBtn();
            }
            else {
                $q.interface.HideNextBtn();
            }

        }else{
            $q.interface.HideNextBtn();
            html += $q.templates.noData;
        }

        $q.interface.AddTableData($(".resultsPanel"), html);
    }

    function CreateEditViewButtons(value, clientObj){

        var btns = $q.templates.pointerActions,
            originUrl = "",
            editUrl = "",
            title = "&#8216;" + $q.utils.CleanJS(value.titles.main) + "&#8216;";

        //Default Btns
        var viewBtn = btns.view.replace("{Action}", "$q.pointers.LightBox('" + value._id + "')").replace("grey", "blue");
            editBtn = btns.edit.replace("{Action}", "$q.interface.ShowUserAlert('Story " + title + " cannot be edited.')"),
            copyBtn = btns.copy.replace("{Action}",  "$q.interface.ShowUserAlert('A pointer for Story " + title + " cannot be copied.')"),
            createBtn = btns.create.replace("{Action}", "$q.interface.ShowUserAlert('A pointer for Story " + title + " cannot be created.')"),
            emailBtn = btns.email.replace("{Action}",  "$q.interface.ShowUserAlert('Valid Access for " + title + " is not available.')");

        //Need to change the logic for these buttons
        if(clientObj){

            //Edit / View
            if(value.origin_cms == "wordpress"){

                originUrl = "http://" + clientObj.domain + "/?p=" + value.origin_id;
                editUrl = "http://" + clientObj.domain + "/wp-admin/post.php?action=edit&post=" + value.origin_id;

                if(value.origin_url){
                    originUrl = value.origin_url;
                }

                viewBtn = btns.view.replace("grey", "blue").replace("{Action}", "window.open('" + originUrl + "','_blank')");
                editBtn = btns.edit.replace("grey", "blue").replace("{Action}", "window.open('" + editUrl + "')");

            }

            if(value.permissions){

                //Copy
                // The copy button will be blue and clickable only if:
                // .has_license = true && .is_origin = false && .can_copy = true
                if(value.permissions.can_copy && value.permissions.has_license && !value.permissions.is_origin){
                    copyBtn = btns.copy.replace("grey", "blue").replace("{Action}", "$q.pointers.CopyPointer('" + value._id +"', this)");
                }

                //Create
                // The pointer button will be blue and clickable only if:
                // .has_license = true && .is_origin = false && .can_point = true
                if(value.permissions.can_point && value.permissions.has_license && (!value.permissions.is_origin || 'fatwire' === value.origin_cms)){
                    createBtn = btns.create.replace("grey", "blue").replace("{Action}", "$q.pointers.CreatePointer('" + value._id +"', this)");
                }

                //Email
                // The request access button will be blue and clickable only if:
                // .has_license = false && .is_origin = false
                if(!value.permissions.has_license && !value.permissions.is_origin){
                    emailBtn = btns.email.replace("grey", "blue").replace("{Action}", "javascript:location.href='mailto:" +  $q.config.mailTo + "?subject=Access%20Request: PostId:" + value.origin_id + "&body=Access request for postId:" + value.origin_id + "%0D%0ATitle:" + $q.utils.CleanJS(value.titles.main) + "%0D%0ASent from " + $q.config.domain + "'");
                }

            }

        }

        return viewBtn + editBtn + copyBtn + createBtn;
    }

    function CopyPointer(postId, btn){

        if( $q.config.nonce != null ) {

            //The story is being copied…
            var upDateObj = {
                action: "json_copy_post",
                nonce: $q.config.nonce, //only when on WP site
                wcm_id: postId,
                client_id: $( '#wcm_client_id' ).val(),
            };

            $q.interface.DisableButton(btn);

            WPPost(upDateObj, "Copy Pointer", function(response){

                if(response){
                    var msg = "";
                    var r = $q.utils.ParseJson(response);
                    if(r.success == true){
                        msg = " The story has been copied and can be edited ";
                        $q.interface.ShowUserMsg(r.message + msg, 'here', r.edit_url);
                        $q.interface.ShowHeadsupDisplay(r.message + msg, 'here', r.edit_url, false, 15 );
                    } else {
                        $q.interface.EnableButton(btn);
                        $q.interface.ShowUserMsg(r.message + msg);
                        $q.interface.ShowHeadsupDisplay(r.message + msg, false, false, false, 15 );
                    }
                    $q.interface.HideHeadsupDisplay( 2 );
                }
            });
         } else {
            var r = {};
            r.message = null;
            msg = ' Forbidden.';
            $q.interface.ShowUserMsg( r.message + msg);
            $q.interface.ShowHeadsupDisplay(r.message + msg, false, false, false, 15 );
            $q.interface.HideHeadsupDisplay( 2 );
         }
    }

    function CreatePointer(postId, btn){

        //“The pointer is being created…”
        //•    Successful completion of the pointer creation should render the button grey and unclickable and display the
        // message “The pointer has been created and can be edited here”. The word “here” should be a link to the
        // edit screen for the pointer on the local site, and should open in a new window/tab.
        if( $q.config.nonce != null ) {
            var upDateObj = {
                action: "json_add_pointer",
                nonce: $q.config.nonce, //only when on WP site
                wcm_id: postId,
                client_id: $( '#wcm_client_id' ).val(),
            };

            $q.interface.DisableButton(btn);

            WPPost(upDateObj, "Create Pointer", function(response){

                if(response){
                    var msg = "";
                    var r = $q.utils.ParseJson(response);
                    if(r.success == true){
                        msg = " The pointer has been created and can be edited ";
                        $q.interface.ShowUserMsg(r.message + msg, 'here', r.edit_url);
                        $q.interface.ShowHeadsupDisplay(r.message + msg, 'here', r.edit_url, false, 15 );
                    } else {
                        $q.interface.EnableButton(btn);
                        $q.interface.ShowUserMsg(r.message + msg);
                        $q.interface.ShowHeadsupDisplay(r.message + msg, false, false, false, 15 );
                    }

                    $q.interface.HideHeadsupDisplay (2);
                }

            });
        } else {
            var msg = "";
            var r = {};
            r.message = null;
            msg = " Forbidden.";
            $q.interface.ShowUserMsg( r.message + msg);
            $q.interface.ShowHeadsupDisplay(r.message + msg, false, false, false, 15 );
            q.interface.HideHeadsupDisplay (2);
        }

    }

    function LookUpPosts(postsArr, callback){

        var postIds = [], postObjs = [], postObj,
            postId;

        $.each(postsArr, function(){

            postId = {
                "id" : this._id
            }

            postObj = {
                "wcm_id" : this._id,
                "license_id": this.license_id,
                "client_id" : this.client_id,
                "origin_url": this.origin_url
            }

            postObjs.push(postObj);
            postIds.push(postId);

        });

        var upDateObj = {
            action: 'json_lookup_posts',
            nonce: nonce,
            wcm_id_list: JSON.stringify(postIds),
            wcm_obj_list: JSON.stringify(postObjs),
            client_id: $( '#wcm_client_id' ).val(),
        };

        WPPost(upDateObj, "LookUpPosts", callback);
    }

    function GetClientLicences(callback){

         var upDateObj = {
            action: "json_get_client_licenses",
            nonce: nonce //only when on WP site
        };

        WPPost(upDateObj, "ClientLicences", callback);

    }

    function WPPost(upDateObj, action, callback){

        if(window['ajaxurl'] != undefined){

            $q.data.Post(ajaxurl, upDateObj, null, function (response) {

                if(callback ==  null){

                    if(response){

                        var r = $q.utils.ParseJson(response);
                        if(r.message)
                            $q.interface.ShowUserMsg(action + " " + r.post_id + " : " + r.message);
                    }

                }else{

                    callback(response);
                }

                $q.interface.HideHeadsupDisplay (.5);
            });

        }else{

            WpAlert();
        }

    }

    function LightBox(postId){

        var postObj = GetPostFromId(postId),
            content = {
                header: "",
                body:""
            };

        if(postObj){

            if(postObj.content_elements){

                $.each(postObj.content_elements, function(i, val){
                    if(val.type === "text")
                        content.body += val.content + "{carriage}";
                });

            }else{
                content.body = postObj.excerpt;
            }

            content.header = postObj.titles.main;
            content.body = content.body;
        }

        $q.interface.LightBox(content);

    }

    function GetPostFromId(postId){

        var postObj = $q.utils.GetObjFromJson($q.config.data.lists, "_id", postId);
        return postObj;
    }

    function Log(msg, type) {

        $q.utils.Log(msg, type);

    }

    function WpAlert(){
        alert("Create / Copy Pointer will only work when in wordpress environment!");
    }

    function formatDate(theDate, includeTime) {
        var convertedDate;

        if(includeTime) {
            if(theDate.includes(".000Z")) {
                //Timezone for Karachi is needed since the published date was stored in an inconsistent format across CMSes.
                convertedDate = new Date(theDate).toLocaleString('en-US', {timeZone: 'Asia/Karachi'});
            } else {
                convertedDate = new Date(theDate).toLocaleString('en-US');
            }
        } else {
            convertedDate = new Date(theDate).toDateString();
        }

        return convertedDate;
    }

    return {
        Init: Init,
        ConstructTable: ConstructTable,
        CreatePointer: CreatePointer,
        LookUpPosts: LookUpPosts,
        CopyPointer: CopyPointer,
        GetPostFromId: GetPostFromId,
        LightBox: LightBox,
        clientSelectCallback: function( args ) {
            PNAutocomplete.inlineCallback( args );
            var page2 = $( '#page2' );
            if ( args.data && args.data.value ) {
                // If results are already visible, run the query again for the new client.
                if ( 'none' !== $( '.resultsPanel' ).css( 'display' ) ) {
                    var query = $q.filters.CreateQueryStr();
                    $q.edit.GetPostsByQuery( query );
                }

                page2.show();
            } else {
                page2.hide();
            }
        },
    }

})(jQuery);
