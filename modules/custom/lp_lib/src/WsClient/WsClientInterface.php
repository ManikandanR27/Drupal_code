<?php

namespace Drupal\lp_lib\WsClient;

/**
 *
 */
interface WsClientInterface {

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function get($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function post($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function postJson($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function joinApiPost($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function joinApiGet($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function surveyPortalGet($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function surveyPortalPut($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function patch($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function profileUpdate($endpoint, array $data, array $options = [], $isAjax = FALSE);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function delete($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function cookiePortalGet($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function ajaxPost($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function ajaxPatch($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function verityAddressValidatePost($data, $type);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function putJson($endpoint, array $data, array $options = []);

  /**
   * @return Drupal\mylib\Lifepoints\Webservice\AbstractResponse
   */
  public function adobeAnalyticsGet($endpoint, array $data, array $options = []);

}
