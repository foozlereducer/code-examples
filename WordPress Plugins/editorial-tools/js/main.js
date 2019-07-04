

$q.main = (function ($) {

    function Init(){

        $q.utils.Log('QB version ' + $q.config.version);

        var pageId = $("#app").attr("data-id");

        $q.config.domain = $q.utils.GetDomain();

        switch(pageId){

            case "listSearch":
                $q.list.Init();
                break;

            case "createSearch":
                $q.edit.Init();
                break;

             case "pointers":
                $q.pointers.Init();
                break;
        }
    }

    return {
        Init: Init
    }

})(jQuery);

$q.main.Init();



