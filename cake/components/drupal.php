<?php
/**
 * Drupal component
 *
 * Manages connections and routing to a Drupal instance
 *
 */

App::import('Core');

class DrupalComponent extends Object {

/**
 * A reference to the curl connection used to "connect" to drupal and transfer data back and forth
 *
 */
	var $connection = null;

/**
 * Initializes DrupalComponent for use in the controller
 *
 */
	function initialize(&$controller, $settings = array()) {
	
	}

/**
 * Main execution method.  
 *
 */
	function startup(&$controller) {
		
	}

/**
 * Connects to Drupal and stores curl handle in connection variable
 * 
 */
	function connect() {
		$this->connection = curl_init(Configure::read('Drupal.url').'user/login'); // initiate curl object
    	curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
    	curl_setopt($this->connection, CURLOPT_POST, 1); // Returns response data instead of TRUE(1)
    	// This array will hold the field names and values.
    	$postdata=array(
      		"name"=>Configure::read('Drupal.admin_user'), 
      		"pass"=>Configure::read('Drupal.admin_pass'),
      		"form_id"=>"user_login", 
      		"op"=>"Log in"
    	);
    	// Tell curl we're going to send $postdata as the POST data
    	curl_setopt($this->connection, CURLOPT_POSTFIELDS, $postdata);
    	curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
		curl_setopt($this->connection, CURLOPT_COOKIESESSION, TRUE); 
		curl_setopt($this->connection, CURLOPT_COOKIEFILE, "d6_cookie.txt");
		curl_setopt($this->connection, CURLOPT_COOKIEJAR, "d6_cookie.txt");
		curl_setopt($this->connection, CURLOPT_FOLLOWLOCATION, 1); 
		curl_exec($this->connection);
	}
	
	function disconnect() {
		@curl_close ($this->connection); // close curl object
		$this->connection=null;
	}
	
	function call($url,$data=null) {
		if($this->connection) {
			curl_setopt($this->connection, CURLOPT_URL, $url);
		} else {
			$this->connection=curl_init($url);
		}
		if($data!=null) curl_setopt($this->connection, CURLOPT_POSTFIELDS, $data);
		return curl_exec($this->connection);
	}
}
