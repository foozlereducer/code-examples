$q.pager = (function ($) {


    function Reset(){
        $q.config.api.from = 0;
        $q.config.api.page = 1;
        $q.config.api.counter = 0;

    }

    function GetQuery(){

        var query = "size=" + $q.config.api.topN + "&from=" + $q.config.api.from;
        return query;
    }

     function GetNext(){

        $q.config.api.page++;
        $q.interface.ShowPrevBtn();

    }
    
    function GetPrev(){

        $q.config.api.page--;
        $q.config.api.from = $q.config.api.from - $q.config.api.topN;
        //Check to see if we are at top  end  of list
        if($q.config.api.from > 0){
            $q.interface.ShowPrevBtn();
        }
        else{
            $q.config.api.from = 0;
            $q.interface.HidePrevBtn();
        }
    }

    function SetFrom(val){
        $q.config.api.from = val;
    }

    return{
        Reset: Reset,
        GetQuery: GetQuery,
        GetNext: GetNext,
        GetPrev: GetPrev,
        SetFrom: SetFrom
    }

})(jQuery);