# The Postmedia Plugin WP Editorial Dashboard aka Pointers Plugin 
 Contributors 
 
	 Charles Jaimet ~ Query Builder Architect 
	 Rob Clarkson ~ JavaScript Query Builder Business Logic Code 
	 Matt Garvin ~ Query Builder business logic to front-end wiring 
	 Sergey Dragunsky ~ Query Builder front-end JavaScript / HTML 
	 Steve Browning ~ WordPress plugin JavaScript integration &amp; PHP unit tests 
	 Kelly Spriggs ~ WordPress plugin refactoring and bug fixes 
 

## Description
 General Design 
 QB ( Query Builder ) Javascript 
 
	 Has no PHP based admin forms, all functionality is mapped in JavaScript 
	 The QB JavaScript has 3 main areas of functionality:
		 
			 Front end made up from jQuery / JavaScript / HTML / CSS
			 Business logic to front-end wiring ( piping ) 
			 Business logic 
 
	 The QB allows people to set AND / OR / Must Not Elasticsearch queries to be defined
	 Once Elasticsearch results are returned, those stories that a site has a valid license to allows an admin user to view, edit, create a pointer or copy 

 WordPress Plugin
 
	Equeues all the QB styles and JavaScript 
	 Defines a 'Editorial Dashboard' menu in wp-admin 
	 Creates several AJAX endpoints for the QB to interact with in terms of licenses, client ids, copying and pointer creation. 
	 Copied posts are a basic form of their original form with only text, raw html, oembeds, and images copied 
	 Saves pointers to the custom pointers taxonomy and adds the post's meta data 
 
 Change Log 
 proposed version 1.2 <time datetime="2017-06-13 16:42">June 13th, 2017</time> 
 Overview 
 Throughout the initial development of the Editorial Dashboard there were some functionalities that could not be completed within the time of Launch and others that were defined out-of-scope for Launch, This is a starting list these features that we will need to prioritized: 
 Proposed Changes 

	 Move all the interaction with the APIs to the Postmedia Library code-base. This will affect the APIProxy.php and the CoreMethods Trait and is expected to be no more than 2 days of work. 
	 Paging functionality so posts don't get lost behind the first 150 results. Rob created this functionality already but it still needs to be integrated. This is a 2 - 4 day job to integrate the JS, change the handling in the PHP classes and running through a qa / staging cycle. 
	 Handling more complex post types for complex types. The Search and Content APIs both return the shortcodes and the raw html so we should be able to test for the existence of a plugin when a shortcode is encountered and pull the shortcode into the post. This is a more complex task than the other changes; we have to determine all the types that we'll support, diagnose what prerequisites that each have and see what we'll need to do programmatically to support these formats.
	 To allow non-whitelabled content via the API and the Editorial Dashboards. This is related to https://postmediadigital.atlassian.net/browse/PWCM-834 and https://postmediadigital.atlassian.net/browse/PWCM-796. This is complex issue that we'll need to work through to determine the proper scope to fit this work. 
	 Create by-lines from co-authors for copied posts. This relates to https://postmediadigital.atlassian.net/browse/PWCM-845. This is of medium complexity. It involves seeing if the co-authors plugin has public metthods that can be used to create co-authors. Adding this logic in the Editorial Dashboard and update the post copy functionality to use new Author rules. 
	 Complete the unit testing
 

 version 1.1 <time datetime="2017-05-14 13:57">May 16th, 2017</time> 
 Overview 
 Version 1.1 features the abilty for Editors to access a site's wp-admin, go to Editorial Dashboard, filter stories based on criteria and return results. The results process and match the stories that have pointer creation and copying rights it will grey out all create pointers and copying posts when a site does not have rights. It also presents an blue email icon to request access. 
 Changes 
 
 Icons have been updated to cleaner and smaller graphics 
 150 results are returned rather than 20 
 Bug fixes found in the QA testing 
 

 version 1.0 <time datetime="2017-03-14 14:53">March 14th, 2017</time> 

 Overview 
 Version 1.0 features the ability for Editors to login to the wp-admin, go to the Editorial Dashboard 
and search for stories that would fit the Editors' preference to create as a pointer. When a post is saved it will create a entry as a pointers custom post type and be added to a list that is most often used as a site 'Featured Stories' list 

 In future versions of this dashboards, it will allow editors to edit, and create lists 

## History

#### 1.0.0 - 2018-01-10
* Added CODEOWNERS and README



