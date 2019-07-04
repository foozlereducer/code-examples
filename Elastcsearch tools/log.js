/**
 * Search Get Log 
 * @see https://github.com/trentm/node-bunyan	
 * @return 	bunyan-log ~ returns an instance of the bunuan log
 */
var getLog = function() {
	var bunyan = require('bunyan');
	return bunyan.createLogger({ name: 'postmedia-api-search', src: true } );
};

// make internally scoped log available
module.exports.log = getLog;