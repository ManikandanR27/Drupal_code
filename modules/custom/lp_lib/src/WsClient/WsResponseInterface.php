<?php

namespace Drupal\lp_lib\WsClient;

use Psr\Http\Message\ResponseInterface;

/**
 *
 */
interface WsResponseInterface {

  /**
   * Set response, return from api call.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   return from guzzle ws client.
   */
  public function setResponse(ResponseInterface $response);

  /**
   * Gets the api response status code.
   *
   * The status code is a 3-digit integer (400, 503) result code of the server's attempt
   * to understand and satisfy the request.
   *
   * @param int
   */
  public function httpStatus();

  /**
   * Gets the response reason phrase associated with the status code.
   *
   * @return string
   *
   * @link http://tools.ietf.org/html/rfc7231#section-6
   */
  public function httpMsg();

  /**
   * Check for error code return from ws response when ws call is made successfully.
   *
   * @return bool
   */
  public function isError();

  /**
   * Gets the error code from ws response when ws call is made successfully.
   *
   * @return int
   */
  public function errorCode();

  /**
   * Gets the error message accociated with the error code.
   *
   * @return string
   */
  public function errorMsg();

  /**
   * Gets the body contents from the ws response.
   */
  public function data();

}
