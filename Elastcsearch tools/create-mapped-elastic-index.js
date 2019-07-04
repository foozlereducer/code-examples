var client = require('./connection.js');
client.indices.create({
    index: "wcm-posts",
    "aliases": {
    	"search-content": {}
    },
    body: {
        "mappings": {
            "post": {
                "properties": {
                    "id": { "type": "string", "index": "not_analyzed" },
                    "client_id": { "type": "string", "index": "not_analyzed" },
                    "license_id": { "type": "string", "index": "not_analyzed" },
                    "orgin_id": { "type": "string", "index": "not_analyzed" },
                    "orgin_cms": { "type": "string", "index": "not_analyzed" },
                    "orgin_url": { "type": "string", "index": "not_analyzed" },
                    "orgin_url_path": { "type": "string", "index": "not_analyzed" },
                    "global_slug": { "type": "string", "index": "not_analyzed" },
                    "label": { "type": "string", "index": "not_analyzed"  },
                    "type": { "type": "string", "index": "not_analyzed" },
                    "advertorial": { "type": "boolean" },
                    "imported_on": {
                    	"type": "date",
                    	"format": "strict_date_optional_time||epoch_millis"
                    },
                    "modified_on": {
                    	"type": "date",
                    	"format": "strict_date_optional_time||epoch_millis"
                    },
                    "published_on": {
                    	"type": "date",
                    	"format": "strict_date_optional_time||epoch_millis"
                    },
                    "status": { "type": "string" },
                    "credits": {
                    	"type": "object",
                    	"properties": {
                    		"id": { "type": "string", "index": "not_analyzed" },
                    		"name": { "type": "string" },
                    		"org": { "type": "string" },
                    		"slug": { "type": "string" },
                    		"description": { "type": "string", "index": "not_analyzed" },
                    		"email": { "type": "string" },
                    		"photo": { 
                    			"type": "object",
                    			"properties": {
                    				"$ref": { "type": "string", "index": "not_analyzed" }
                    			},
                    		},
                    		"social_links": {
                    			"type": "object",
                    			"properties": {
                    				"$ref": { "type": "string", "index": "not_analyzed" }
                    			}
                    		}
                    	}
                    },
                    "titles": {
                    	"type": "object",
                    	"properties": {
                    		"main": { "type": "string" },
                    		"subtitle": { "type": "string" },
                    		"alternate": { "type": "string"  },
                    		"concise": { "type": "string" },
                    		"seo": { "type": "string" }
                    	}
                    },
                    "excerpt": { "type": "string" },
                    "origin_slug": { "type": "string" },
                    "taxonomies": {
                    	"type": "object",
                    	"properties": {
                    		"tags": {
                    			"type": "nested",
                    			"properties": {
                    				"id": { "type": "string", "index": "not_analyzed" },
                    				"type": { "type": "string", "index": "not_analyzed" },
                    				"name": { "type": "string", "index": "not_analyzed" },
                    				"slug": { "type": "string", "index": "not_analyzed" },
                    				"path": { "type": "string", "index": "not_analyzed" },
                    				"main": { "type": "boolean", "index": "not_analyzed" }
                    			} 
                    		},
                    		"categories": {
                    			"type": "nested",
                    			"properties": {
                    				"id": { "type": "string", "index": "not_analyzed" },
                    				"type": { "type": "string", "index": "not_analyzed" },
                    				"name": { "type": "string", "index": "not_analyzed" },
                    				"slug": { "type": "string", "index": "not_analyzed" },
                    				"path": { "type": "string", "index": "not_analyzed" },
                    				"main": { "type": "boolean", "index": "not_analyzed" }
                    			} 
                    		},
                    		"makes": {
                    			"type": "nested",
                    			"properties": {
                    				"id": { "type": "string", "index": "not_analyzed" },
                    				"type": { "type": "string", "index": "not_analyzed" },
                    				"name": { "type": "string", "index": "not_analyzed" },
                    				"slug": { "type": "string", "index": "not_analyzed" },
                    				"path": { "type": "string", "index": "not_analyzed" },
                    				"main": { "type": "boolean", "index": "not_analyzed" }
                    			} 
                    		},
                    		"bodystyles": {
                    			"type": "nested",
                    			"properties": {
                    				"id": { "type": "string", "index": "not_analyzed" },
                    				"type": { "type": "string", "index": "not_analyzed" },
                    				"name": { "type": "string", "index": "not_analyzed" },
                    				"slug": { "type": "string", "index": "not_analyzed" },
                    				"path": { "type": "string", "index": "not_analyzed" },
                    				"main": { "type": "boolean", "index": "not_analyzed" }
                    			} 
                    		},
                    		"classifications": {
                    			"type": "nested",
                    			"properties": {
                    				"id": { "type": "string", "index": "not_analyzed" },
                    				"type": { "type": "string", "index": "not_analyzed" },
                    				"name": { "type": "string", "index": "not_analyzed" },
                    				"slug": { "type": "string", "index": "not_analyzed" },
                    				"path": { "type": "string", "index": "not_analyzed" },
                    				"main": { "type": "boolean", "index": "not_analyzed" }
                    			} 
                    		},
                    		"model_years": {
                    			"type": "nested",
                    			"properties": {
                    				"id": { "type": "string", "index": "not_analyzed" },
                    				"type": { "type": "string", "index": "not_analyzed" },
                    				"name": { "type": "string", "index": "not_analyzed" },
                    				"slug": { "type": "string", "index": "not_analyzed" },
                    				"path": { "type": "string", "index": "not_analyzed" },
                    				"main": { "type": "boolean", "index": "not_analyzed" }
                    			} 
                    		},
                    		"specialsections": {
                    			"type": "nested",
                    			"properties": {
                    				"id": { "type": "string", "index": "not_analyzed" },
                    				"type": { "type": "string", "index": "not_analyzed" },
                    				"name": { "type": "string", "index": "not_analyzed" },
                    				"slug": { "type": "string", "index": "not_analyzed" },
                    				"path": { "type": "string", "index": "not_analyzed" },
                    				"main": { "type": "boolean", "index": "not_analyzed" }
                    			} 
                    		},
                    		"main_taxonomies": {
                    			"type": "nested",
                    			"properties": {
                    				"id": { "type": "string", "index": "not_analyzed" },
                    				"type": { "type": "string", "index": "not_analyzed" },
                    				"name": { "type": "string", "index": "not_analyzed" },
                    				"slug": { "type": "string", "index": "not_analyzed" },
                    				"path": { "type": "string", "index": "not_analyzed" },
                    				"main": { "type": "boolean", "index": "not_analyzed" }
                    			} 
                    		},
                    	}
                    },
                    "content_elements": {
                    	"dynamic": "true",
                    	"properties": {
                    		"content1": {
                    			"type": "object"
                    		},
                    		"content2": {
                    			"type": "object"
                    		}
                    	}
                    },
                    "featured_media": {
                    	"type": "nested",
            			"properties": {
            				"id": { "type": "string", "index": "not_analyzed" },
                    		"name": { "type": "string" },
                    		"org": { "type": "string" },
                    		"slug": { "type": "string" },
                    		"description": { "type": "string", "index": "not_analyzed" },
                    		"email": { "type": "string", "index": "not_analyzed" },
                    		"url": { "type": "string", "index": "not_analyzed" },
                    		"photo": { 
                    			"type": "object",
                    			"properties": {
                    				"$ref": { "type": "string", "index": "not_analyzed" }
                    			},
                    		},
                    		"social_links": {
                    			"type": "object",
                    			"properties": {
                    				"$ref": { "type": "string", "index": "not_analyzed" }
                    			}
                    		}
            			}
                    },
                     "related_content": { "type": "string", "index": "not_analyzed" },
                     "stock_symbols": { "type": "string", "index": "not_analyzed" },
                     "meta_data": {
                    	"dynamic": "true",
                    	"properties": {
                    		"metadata1": {
                    			"type": "object"
                    		},
                    		"metadata2": {
                    			"type": "object"
                    		}
                    	}
                    },
                    "version": { "type": "string", "index": "not_analyzed" }
                }
            }
        }
    }
}, function (err, resp, respcode) {
    console.log(err, resp, respcode);
});
