/**
 * Elastic Search Client
 * @see 	https://github.com/elastic/elasticsearch-js
 * @see 	https://www.elastic.co/guide/en/elasticsearch/client/javascript-api/current/quick-start.html
 * @type 	ElasticSearch Javascrit Object
 */
var es =require('elasticsearch');

// Create elastic search client to our development found.io elasticsearch
var client = new es.Client( {
    host: [
        {
            host: '*************',
            auth: '*************',
            protocol: 'https',
            port: 9243
        }
    ],
    apiVersion: '2.3',
} );

// make locally scoped elasticsearch available
module.exports = client;