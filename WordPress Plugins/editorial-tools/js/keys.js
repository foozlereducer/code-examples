$q.keys = (function ($) {

var val = {

            get:endPoints.headers.GET["x-api-key"],
            post:endPoints.headers.POST["x-api-key"],
            put:endPoints.headers.PUT["x-api-key"],
            clientId:endPoints.clientId,

    }

    return{
        val: val
    }

})(jQuery);