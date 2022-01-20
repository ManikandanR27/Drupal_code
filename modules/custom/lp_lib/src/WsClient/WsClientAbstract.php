<?php

namespace Drupal\lp_lib\WsClient;

/**
 *
 */
abstract class WsClientAbstract implements WsClientInterface {

  protected $config;
  protected $httpClient;
  protected $response;

  /**
   * Constructor to set api config and Drupal HTTP client.
   */
  public function __construct($config, $httpClient) {
    $this->config     = $config;
    $this->httpClient = $httpClient;
  }

  /**
   * Get function to handle api request with get method.
   */
  public function get($endpoint, array $query_data, array $options = []) {
    $response = $this->getRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * Post function to handle api request with POST method.
   */
  public function post($endpoint, array $query_data, array $options = []) {
    $response = $this->postRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * Post function to handle api request with POST method.
   */
  public function postJson($endpoint, array $query_data, array $options = []) {
    $response = $this->postJsonRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * Post function to handle join api request with POST method.
   */
  public function joinApiPost($endpoint, array $query_data, array $options = []) {
    $response = $this->joinApiPostRequest($endpoint, $query_data, $options);
    return $response;
  }



  /**
   * Get function to handle join api get request.
   */
  public function joinApiGet($endpoint, array $query_data, array $options = []) {
    $response = $this->joinApiGetRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * Patch function to handle api request with PATCH method.
   */
  public function patch($endpoint, array $query_data, array $options = []) {
    $response = $this->patchRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * Profile update function to handle api request with PATCH method.
   *
   * @param string $endpoint
   *   RIL End point URL name.
   * @param array $query_data
   *   Can be required data for api end point.
   * @param array $options
   *   Can be URL query params options.
   * @param string $isAjax
   *   Can be pass TRUE(Called AJAX) or FALSE(No AJAX) and get response.
   *
   * @return object
   *   Drupal\lp_lib\WsClient\CommonServiceResponse
   */
  public function profileUpdate($endpoint, array $query_data, array $options = [], $isAjax = FALSE) {
    $response = $this->profileUpdateRequest($endpoint, $query_data, $options, $isAjax);
    return $response;
  }

  /**
   * Delete function to handle api request with DELETE method.
   */
  public function delete($endpoint, array $query_data, array $options = []) {
    $response = $this->deleteRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * GET function to handle API request with Survey Portal URL method.
   */
  public function surveyPortalGet($endpoint, array $query_data, array $options = []) {
    $response = $this->surveyPortalGetRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * PUT function to handle API request with Survey Portal URL method.
   */
  public function surveyPortalPut($endpoint, array $query_data, array $options = []) {
    $response = $this->surveyPortalPutRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * GET function to send adobe analytics.
   */
  public function adobeAnalyticsGet($endpoint, array $query_data, array $options = []) {
    $response = $this->adobeAnalyticsGetRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * Getter for httpclient.
   */
  public function getHttpClient() {
    return $this->$httpClient;
  }

  /**
   *
   */
  abstract protected function getRequest($endpoint, array $data, array $options);

  /**
   *
   */
  abstract protected function postRequest($endpoint, array $data, array $options);

  /**
   *
   */
  abstract protected function postJsonRequest($endpoint, array $data, array $options);

  /**
   *
   */
  abstract protected function patchRequest($endpoint, array $data, array $options);

  /**
   *
   */
  abstract protected function profileUpdateRequest($endpoint, array $data, array $options, $isAjax = FALSE);

  /**
   *
   */
  abstract protected function deleteRequest($endpoint, array $data, array $options);

  /**
   *
   */
  abstract protected function surveyPortalGetRequest($endpoint, array $data, array $options);

  /**
   *
   */
  abstract protected function surveyPortalPutRequest($endpoint, array $data, array $options);

  /**
   *
   */
  abstract protected function joinApiPostRequest($endpoint, array $data, array $options);

  /**
   *
   */
  abstract protected function joinApiGetRequest($endpoint, array $data, array $options);

  /**
   *
   */
  abstract protected function adobeAnalyticsGetRequest($endpoint, array $data, array $options);

  /**
   * GET function to handle API request with Cookie Portal URL method.
   */
  public function cookiePortalGet($endpoint, array $query_data, array $options = []) {
    $response = $this->cookiePortalGetRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * Post function to handle api request with POST method for ajax calls.
   */
  public function ajaxPost($endpoint, array $query_data, array $options = []) {
    $response = $this->ajaxPostRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * Patch function to handle api request with POST method for ajax calls.
   */
  public function ajaxPatch($endpoint, array $query_data, array $options = []) {
    $response = $this->ajaxPatchRequest($endpoint, $query_data, $options);
    return $response;
  }

  /**
   * POST function to handle Verity API request with POST method.
   */
  public function verityAddressValidatePost($data, $type) {
    $response = $this->verityAddressValidatePostRequest($data, $type);
    return $response;
  }

  /**
   * Curiosity function to handle API request with PUT method.
   */
  public function putJson($endpoint, array $query_data, array $options = []) {
    $response = $this->putJsonRequest($endpoint, $query_data, $options);
    return $response;
  }

}
