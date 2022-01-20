<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * WS response object for common service.
 */
class Login {

  const DEFAULT_ERROR_MSG = 'An unexpected error occurred while logging in. Please try again';

  protected $wsClient;
  protected $userSession;
  protected $request;

  /**
   * Constructor to setting up required variables.
   */
  public function __construct(WsClientInterface $wsClient, $userSession, $request) {
    $this->wsClient    = $wsClient;
    $this->userSession = $userSession;
    $this->request     = $request->getCurrentRequest();
  }

  /**
   * Function to consume login api.
   */
  public function login(array $data) {
    $response = $this->wsClient->post('authenticate', $data);
    return $response;
  }

  /**
   * Function to call Resend DOI Email endpoint.
   */
  public function resendDoiEmail(array $data) {
    if (!isset($data["panelId"])) {
      throw new \Exception(self::DEFAULT_ERROR_MSG);
    }

    $response = $this->wsClient->ajaxPost('OneP-AccountServices/ResendDoi', $data);
    return $response;
  }

  /**
   * Function to consume forget password endpoint.
   */
  public function panelistForgotPasword(array $data) {
    $response = $this->wsClient->post('OneP-AccountServices/Panelist/ForgotPassword', $data);
    return $response;
  }

  /**
   * Function to consume forget credential endpoint.
   */
  public function forgotLoginCredentials(array $data) {
    /* To-do: Business logic to access forgot credential endpoint. */
  }

  /**
   * Get Panelist details by Email address.
   */
  public function getPanelistByEmail(array $data) {
    $response = $this->wsClient->get('OneP-AccountServices/Panelist', $data);
    return $response;
  }

}
