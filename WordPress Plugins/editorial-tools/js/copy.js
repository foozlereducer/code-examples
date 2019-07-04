function pn_edash_copy_post( wcm_id_ ) {
  console.clear();
  console.log( 'Copy post: ', wcm_id_ );
  if( $q.config.nonce != null ) {
    var data_ = {
      action: 'json_copy_post',
      nonce: $q.config.nonce,
      wcm_id: wcm_id_,
      client_id: $( '#wcm_client_id' ).val(),
    };
    jQuery.post( ajaxurl, data_, function( response_ ) {
      result_ = JSON.parse( response_ );
      console.log( result_ );
    } );
  } else {
    result_ = 'Forbidden';
    console.log( result_ );
  }
}

function pn_edash_lookup_posts( wcm_id_list_ ) {
  console.clear();
  console.log( 'pn_edash_lookup_posts post: ', wcm_id_list_ );
  if( $q.config.nonce != null ) {
    var data_ = {
      action: 'json_lookup_posts',
      nonce: $q.config.nonce,
      wcm_id_list: wcm_id_list_,
    };
    jQuery.post( ajaxurl, data_, function( response_ ) {
      result_ = JSON.parse( response_ );
      console.log( result_ );
    } );
  } else {
    result_ = 'Forbidden';
    console.log( result_ );
  }
}

function pn_edash_get_clients() {
  console.clear();
  console.log( 'pn_edash_get_clients');
  if( $q.config.nonce != null ) {
    var data_ = {
      action: 'json_get_clients',
      nonce: $q.config.nonce,
    };
    jQuery.post( ajaxurl, data_, function( response_ ) {
      result_ = JSON.parse( response_ );
      console.log( result_ );
    } );
  } else {
    result_ = 'Forbidden';
    console.log( result_ );
  }
}

function pn_edash_get_licenses() {
  console.clear();
  console.log( 'pn_edash_get_licenses');
  if( $q.config.nonce != null ) {
    var data_ = {
      action: 'json_get_licenses',
      nonce: $q.config.nonce,
    };
    jQuery.post( ajaxurl, data_, function( response_ ) {
      result_ = JSON.parse( response_ );
      console.log( 'POINTER');
      console.log( result_ );
    } );
  } else {
    result_ = 'Forbidden';
    console.log( result_ );
  }
}

function pn_edash_get_client_licenses() {
  console.clear();
  console.log( 'pn_edash_get_licenses');
  if( $q.config.nonce != null ) {
    var data_ = {
      action: 'json_get_client_licenses',
      nonce: j$q.config.nonce,
    };
    jQuery.post( ajaxurl, data_, function( response_ ) {
      result_ = JSON.parse( response_ );
      console.log( result_ );
    } );
  } else {
    result_ = 'Forbidden';
    console.log( result_ );
  }
}

// CMJ Pointers
function pn_edash_add_pointer( wcm_id_ ) {
  console.clear();
  console.log( 'Add pointer: ', wcm_id_ );
  if( $q.config.nonce != null ) {
    var data_ = {
      action: 'json_add_pointer',
      nonce: $q.config.nonce,
      wcm_id: wcm_id_,
      client_id: $( '#wcm_client_id' ).val(),
    };
    jQuery.post( ajaxurl, data_, function( response_ ) {
      result_ = JSON.parse( response_ );
      console.log( result_ );
    } );
  } else {
    result_ = 'Forbidden';
    console.log( result_ );
  }
}
