<?php
class UsersController extends AppController {

	var $name = 'Users';
	
	var $components=array('Drupal');
	
	function index() {
		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid user', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('viewuser', $this->User->read(null, $id));
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid user', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			$preeditedUser = $this->User->read(null,$id);
			if ($this->data['User']['psword'] != '') {
				$this->data['User']['password'] = $this->Auth->password($this->data['User']['psword']);
			}
			
			if ($this->User->save($this->data)) {
				$postValue='edituser='.$preeditedUser['User']['username'];
    			if ($this->data['User']['psword'] != '') $postValue.= '&new_pword=' . $this->data['User']['psword'];
    			if ($this->data['User']['username'] != '') $postValue.= '&new_username=' . $this->data['User']['username'];
    			if ($this->data['User']['first_name'] != '') $postValue.= '&new_firstname=' . $this->data['User']['first_name'];
    			if ($this->data['User']['last_name'] != '') $postValue.= '&new_lastname=' . $this->data['User']['last_name'];
    			$this->Drupal->connect();
    			$post_response = $this->Drupal->call(Configure::read('Drupal.url').'user/external_update',$postValue);
  				$this->Drupal->disconnect();
				$this->Session->setFlash(__('The user has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->User->read(null, $id);
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for user', true));
			$this->redirect(array('action'=>'index'));
		}
		$deletedUser = $this->User->read(null,$id);
		if ($this->User->delete($id)) {
			$postValue='deluser='.$deletedUser['User']['username'];
			$this->Drupal->connect();
    		$post_response = $this->Drupal->call(Configure::read('Drupal.url').'user/external_delete',$postValue);
  			$this->Drupal->disconnect();
			$this->Session->setFlash(__('User deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('User was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
	function admin_index() {
		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid user', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('user', $this->User->read(null, $id));
	}

	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid user', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->User->save($this->data)) {
				$this->Session->setFlash(__('The user has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->User->read(null, $id);
		}
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for user', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->User->delete($id)) {
			$this->Session->setFlash(__('User deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('User was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
	
	/**
     *  The AuthComponent provides the needed functionality
     *  for login, so you can leave this function blank.
     */
    function login() {
    	$this->set('indata',$this->data);
		if(empty($this->data)){
			$this->pageTitle = 'Log in';
			$cookie = $this->Cookie->read('Auth.User');
		}
    }

    function logout() {
        $this->redirect($this->Auth->logout());
    }
	
	function externalUserCreation() {
		$retString="OK";
		$retID=0;
		if($this->User->save($this->data)) {
			$retID=$this->User->id;
		} else {
			$retString="FAIL";
		}
		$this->set('respString',$retString);
		$this->set('uid',$retID);
		$this->layout = 'xml';
	}
	
	function externalUserUpdate() {
		$existingUser=$this->User->find('first', array('conditions'=>array('User.username' => $this->data['dexisting_username'])));
		$retString="OK";
		$retID=0;
		if(!empty($existingUser)) {
			if(isset($this->data['new_username'])) $existingUser['User']['username']=$this->data['new_username'];
			if(isset($this->data['new_firstname'])) $existingUser['User']['first_name']=$this->data['new_firstname'];
			if(isset($this->data['new_lastname'])) $existingUser['User']['last_name']=$this->data['new_lastname'];
			if(isset($this->data['new_pword'])) $existingUser['User']['password']=$this->data['User']['password'] = $this->Auth->password($this->data['new_pword']);;
			if($this->User->save($existingUser)) {
				$retID=$this->User->id;
			} else {
				$retString="FAIL";
			}
		}
		$this->set('respString',$retString);
		$this->set('uid',$retID);
		$this->layout = 'xml';		
	}
	
	function externalUserDelete() {
		$existingUser=$this->User->find('first', array('conditions'=>array('User.username' => $this->data['dusername'])));
		$retString="OK";
		$retID=0;
		if(!empty($existingUser)) {
			if ($this->User->delete($existingUser['User']['id'])) {
				$retID=$this->User->id;
			} else {
				$retString="FAIL";
			}
		}
		$this->set('respString',$retString);
		$this->layout = 'xml';		
	}
	
	function establishToken($userName) {
		$theUser = $this->User->find('first', array('conditions'=>array('User.username' => $userName)));
		$length = 32;
    	$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    	$token = '';    

    	for ($p = 0; $p < $length; $p++) {
        	$token .= $characters[rand(0, strlen($characters)-1)];
    	}
    	$user = array();
    	$user['User']['id']=$theUser['User']['id'];
    	$user['User']['sso_token']=$token;
		$this->User->save($user);
    	
		$this->set('generatedToken',$token);
		$this->layout='xml';
	}
	
	function cashinToken($userName,$token) {
		$theUser = $this->User->find('first', array('conditions'=>array('User.username' => $userName,'User.sso_token' => $token)));
		if(!empty($theUser)) {
			$cookie = array();
			$cookie['username'] = $theUser['User']['username'];
			$cookie['password'] = $theUser['User']['password'];
			if($this->Auth->login($cookie)){
				$user['User']['id']=$theUser['User']['id'];
    			$user['User']['sso_token']='';
				$this->User->save($user);
				$this->redirect($this->Auth->redirect());
			}
		}
	}
	
	function ssoSignin() {
		$postValue='tokenuser='.$this->Auth->user('username');
    	$this->Drupal->connect();
    	$post_response = $this->Drupal->call(Configure::read('Drupal.url').'user/sso_token_create',$postValue);
  		$this->Drupal->disconnect();
  		$xmlized = simplexml_load_string($post_response);
  		if($xmlized->code=='OK') {
  			header ("Location: " . Configure::read('Drupal.url') . 'user/sso_token_cashin/' . $this->Auth->user('username') . '/' . $xmlized->token);
  		}
	}
}
?>