<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_curiosity_question\Util\TripleSXMLtoArray;
use Drupal\lp_lib\Util\MappingUsages;

/**
 * This class for getting Curiosity questions from API response
 */
class GetCuriosityQuestions {

  protected $userSession;
  protected $session;
  protected $curiosityQuestions;

  /**
   * Constructor to initialize the object.
   */
  public function __construct($session, $userSession, $curiosityQuestions) {
    $this->session = $session;
    $this->userSession = $userSession;
    $this->curiosityQuestions = $curiosityQuestions;
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function getCuriosityQuestions() {
    // Get panelid.
    $panelId = MappingUsages::getPanelId();
    // Get the panelist data.
    $panelistSessionData = $this->userSession->getPanelistSessionData();
    $panel = explode('_', $panelistSessionData['panelistActivePanel']);

    $panelist_info = [
      'country' => $panel[0],
      'language' => $panel[1],
      'domain' => $panelId,
      'panelist_id' => $panelistSessionData['panelistId'],
    ];

    $this->session->startSession();
    // Initialize empty array.
    $questionDetailsXmlArray = [];
    $questionDetailsArray = [];
    $questionsArray = [];

    // Call the get question api.
    $response = $this->curiosityQuestions->getQuestions($panelist_info);

    // Check if response is not empty.
    if (!empty($response)) {
      $questionIdsArray = $response->data();
      if (!empty($questionIdsArray['ir_question_ids'])) {
        $this->userSession->addPanelistSessionData('ir_question_ids', $questionIdsArray['ir_question_ids']);
        // Processing each question ids.
        foreach ($questionIdsArray['ir_question_ids'] as $questionId) {
          // Calling the get question details api.
          $questionDetailsXML = $this->curiosityQuestions->getNextQuestions($questionId);
          // If response not empty then store xml in array.
          if (!empty($questionDetailsXML)) {
            $questionDetailsXML = $questionDetailsXML->data();
            // Create array of xml fetched for each question id.
            $questionDetailsXmlArray[$questionId] = $questionDetailsXML;
            // Converting xml to array.
            $questionDetailsArray[] = TripleSXMLtoArray::XMLtoArray($questionDetailsXML);
          }
        }
      }
    }

    // If not empty questionDetails.
    if (!empty($questionDetailsArray)) {
      // Processing the output of triple s xml array for each question.
      foreach ($questionDetailsArray as $ques) {
        // Question array.
        $getQuestion = $ques['sss']['survey']['record']['variable']['label']['text'];
        // Options array.
        $getOptions = $ques['sss']['survey']['record']['variable']['values']['value'];

        // Assigining empty array to the key i.e. question id.
        $questionsArray[$ques['sss']['survey']['record']['variable']['@attributes']['ident']] = [];

        // Fetching question of each language.
        foreach ($getQuestion as $k => $q) {

          if (is_numeric($k)) {
            // Assigning question id -> language -> question title.
            $questionsArray[$ques['sss']['survey']['record']['variable']['@attributes']['ident']][$q['@attributes']['lang']]['question'] = $q['_value'];
            $questionsArray[$ques['sss']['survey']['record']['variable']['@attributes']['ident']][$q['@attributes']['lang']]['type'] = $ques['sss']['survey']['record']['variable']['@attributes']['type'];
          }
          else {
            // Assigning question id -> language -> question title.
            $questionsArray[$ques['sss']['survey']['record']['variable']['@attributes']['ident']][$getQuestion['@attributes']['lang']]['question'] = $getQuestion['_value'];
            $questionsArray[$ques['sss']['survey']['record']['variable']['@attributes']['ident']][$getQuestion['@attributes']['lang']]['type'] = $ques['sss']['survey']['record']['variable']['@attributes']['type'];
          }
        }
        // Fetching options of each language.
        foreach ($getOptions as $option) {
          foreach ($option['text'] as $k => $o) {
            if (is_numeric($k)) {
              // Assigning question id -> language -> options -> key value pair.
              $questionsArray[$ques['sss']['survey']['record']['variable']['@attributes']['ident']][$o['@attributes']['lang']]['options'][$option['@attributes']['code']] = $o['_value'];

              if (isset($option['@attributes']['exclusive'])) {
                $questionsArray[$ques['sss']['survey']['record']['variable']['@attributes']['ident']][$o['@attributes']['lang']]['exclusive'][] = $option['@attributes']['code'];
              }
            }
            else {
              // Assigning question id -> language -> options -> key value pair.
              $questionsArray[$ques['sss']['survey']['record']['variable']['@attributes']['ident']][$option['text']['@attributes']['lang']]['options'][$option['@attributes']['code']] = $option['text']['_value'];

              if (isset($option['@attributes']['exclusive'])) {
                $questionsArray[$ques['sss']['survey']['record']['variable']['@attributes']['ident']][$option['text']['@attributes']['lang']]['exclusive'][] = $option['@attributes']['code'];
              }
            }
          }
        }
      }
    }

    \Drupal::logger('curiosity_popup')->info('questions_array_php_array: <br><pre><code>' . print_r($questionsArray, TRUE) . '</code></pre>');
    return $questionsArray;
  }

}
