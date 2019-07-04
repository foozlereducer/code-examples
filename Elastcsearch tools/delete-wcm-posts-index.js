var client = require('./connection.js');

client.indices.delete( { index: 'wcm-posts' },function( err , resp , status ) {  
	console.log("delete",resp);
});