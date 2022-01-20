<?php 


namespace Drupal\lp_lib\Session;


interface SessionBackendInterface {


  public function set($k, $v);


  public function get($k);


  public function startSession();


  public function destroySession();

}

