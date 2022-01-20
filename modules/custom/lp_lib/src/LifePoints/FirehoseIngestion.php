<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\WsClient\WsClientInterface;

/**
 * OP-4436.
 *
 * Platone Firehose Ingest Interaction.
 */
class FirehoseIngestion {

  /**
   * The Common Service Client service.
   *
   * @var Drupal\lp_lib\WsClient\CommonServiceClient
   */
  protected $wsClient;

  /**
   * The User Session service.
   *
   * @var Drupal\lp_lib\Util\UserSession
   */
  protected $userSession;

  /**
   * Constructor to initialize the object.
   */
  public function __construct(WsClientInterface $wsClient, $userSession) {
    $this->wsClient    = $wsClient;
    $this->userSession = $userSession;
  }

  /**
   * Process the passed in json array for Firehose ingestion.
   *
   * Survey Opportunities Displayed By The Portal to ingest in Firehose.
   *
   * @return Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function postSurveyIngestion(array $data) {
    // Calling POST Ingest API.
    $response = $this->wsClient->postJSON('ingest', $data, ['urlParams' => NULL]);
    return $response;
  }

}
