<?php 

namespace Drupal\lp_lib\Session;

class SessionBackendDefault implements SessionBackendInterface {

  protected $sessionManager;
  protected $tempstore;
  protected $currentUser;

  /**
    * Constructor for setting up the variables.
    */
  public function __construct($sessionManager, $tempstore, $currentUser){
    $this->sessionManager   = $sessionManager;
    $this->tempstore        = $tempstore->get('lp_lib');
    $this->currentUser      = $currentUser;
  }

  /**
    * Setter for sesstion data.
    */
  public function set($key, $value) {
    $this->tempstore->set($key, $value);
  }

  /**
    * Getter for sesstion data based on key.
    */
  public function get($key) {
    return $this->tempstore->get($key);
  }

  /**
    * Delete function for sesstion data based on key.
    */
  public function delete($key) {
    return $this->tempstore->delete($key);
  }

  /**
    * Function to start session.
    */
  public function startSession() {
    if( !$this->isStarted() ){
      $_SESSION['lp_session'] = true;
      $this->sessionManager->start();
    }
  }

  /**
    * Function to check current session is started.
    */
  public function isStarted(){
    if( $this->currentUser->isAnonymous() && !isset($_SESSION['lp_session']) ) {
      return false; 
    }else{
      return true;
    }
  }


  /**
    * Function to destroy current session.
    */
  public function destroySession() {
    $this->tempstore->delete('panelist');
    $this->sessionManager->clear();
  }
}
