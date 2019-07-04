
var $q = {};

$q.config = (function ($) {

var api = {
        base: endPoints.base,
        topN: 25,  //Max results returned
        page:1, //Page num for results
        from: 0, //Last set of results grabbed from index
        get: {
            clients: endPoints.url.clients,
            licenses: endPoints.url.licenses + "?size=0",
            lists: endPoints.url.lists,
            content: endPoints.url.content
        },
        post:{
            clients: endPoints.url.clients,
            licenses: endPoints.url.licenses,
            lists: endPoints.url.lists,
            content: endPoints.url.content
        }
    },
    data = {
        lists: [],
        licenses: [],
        clients:[],
        clientLicenses:[],
        query:{},
        searchQ: ""
    },
    //need to put this into params object
    mode = "",
    mailTo = "wcmaccess@postmedia.com",
    filterId = null,
    version = "1.7.3",
    domain = null,
    prod = false,
    nonce =   postmedia_edash.security,
    filterType =
    [
        {
            "id" : 1,
            "name": "Brand/Product",
            "slug": "clients",
            "enabled": true
        },
        {
            "id" : 4,
            "name": "License",
            "slug": "licenses",
            "enabled": true
        },
        {
            "id" : 5,
            "name": "Post Type",
            "slug": "types",
            "enabled": true
        },
        {
            "id" : 2,
            "name": "Category",
            "slug": "cats",
            "enabled": true
        },
        {
            "id" : 7,
            "name": "Tags",
            "slug": "tags",
            "enabled": true
        },
        {
            "id" : 6,
            "name": "Sponsored",
            "slug": "advertorial",
            "enabled": true
        },
        {
            "id" : 3,
            "name": "Keyword",
            "slug": "q",
            "enabled": true
        }
    ],
    postTypes = [
        {
            "name": "story",
            "slug": "story",
            "enabled": true
        },
        {
            "name": "gallery",
            "slug": "gallery",
            "enabled": true
        },
        {
            "name": "pointer",
            "slug": "pointer",
            "enabled": true
        },
        {
            "name": "sunshinegirl",
            "slug": "sunshinegirl",
            "enabled": true
        },
        {
            "name": "feature",
            "slug": "feature",
            "enabled": true
        }
    ],
    searchTypes = {
        status : [
            /*{
                "name" :"All",
                "slug" : "all"
            },*/
            {
                "name" :"Published",
                "slug" : "published"
            },
            {
                "name" :"Draft",
                "slug" : "draft"
            },
            {
                "name" :"Deleted",
                "slug" : "deleted"
            }
        ],
        type : [
            /*{
                "name" :"All",
                "slug" : "all"
            },*/
            {
                "name" :"Dynamic",
                "slug" : "dynamic"
            },
            {
                "name" :"Curated",
                "slug" : "curated"
            }
        ]
    }

    return {
        data: data,
        api: api,
        nonce: nonce,
        domain: domain,
        prod: prod,
        version: version,
        filterType: filterType,
        postTypes: postTypes,
        searchTypes: searchTypes,
        mode: mode,
        mailTo: mailTo

    }

})(jQuery);