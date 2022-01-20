<?php
/**
 * @file
 * Contains \Drupal\student_registration\Form\RegistrationForm.
 */
namespace Drupal\student_registration\Form;
use Drupal\lp_lib\WsClient\WsClientInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lp_lib\Util\MappingUsages;
use Drupal\lp_lib\Util\CommonMessenger;
class RegistrationForm extends FormBase {
  
  // protected $wsClient;
  // protected $userSession;
  // protected $request;

  // public function __construct(WsClientInterface $wsClient, $userSession, $request) {
  //   $this->wsClient    = $wsClient;
  //   $this->userSession = $userSession;
  //   $this->request     = $request->getCurrentRequest();
  // }
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_registration_form';
  }
  
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $form['student_name'] = array(
    //   '#type' => 'textfield',
    //   '#title' => t('Enter Name:'),
    //   '#required' => TRUE,
    // );
    // $form['student_rollno'] = array(
    //   '#type' => 'textfield',
    //   '#title' => t('Enter Enrollment Number:'),
    //   '#required' => TRUE,
    // );
    $form['student_mail'] = array(
      '#type' => 'email',
      '#title' => t('Enter Email ID:'),
      '#required' => TRUE,
    );
    $form['student_phone'] = array (
      '#type' => 'tel',
      '#title' => t('Enter Password'),
    );
    // $form['student_dob'] = array (
    //   '#type' => 'date',
    //   '#title' => t('Enter DOB:'),
    //   '#required' => TRUE,
    // );
    // $form['student_gender'] = array (
    //   '#type' => 'select',
    //   '#title' => ('Select Gender:'),
    //   '#options' => array(
    //     'Male' => t('Male'),
		// 'Female' => t('Female'),
    //     'Other' => t('Other'),
    //   ),
    // );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Login'),
      '#button_type' => 'primary',
    );
    return $form;
  }
  
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if(strlen($form_state->getValue('student_rollno')) < 8) {
    //   $form_state->setErrorByName('student_rollno', $this->t('Please enter a valid Enrollment Number'));
    // }
    // if(strlen($form_state->getValue('student_phone')) < 10) {
    //   $form_state->setErrorByName('student_phone', $this->t('Please enter a valid Contact Number'));
    // }
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addMessage(t("Student Registration Done!! Registered Values are:"));
	foreach ($form_state->getValues() as $key => $value) {
	  \Drupal::messenger()->addMessage($key . ': ' . $value);
    //     var_dump($data);
    // echo "</pre>";
    // die();
    }
    try {
      $data = [];
      $data["userName"] = trim($form_state->getValue('student_mail'));
      $data["password"] = $form_state->getValue('student_phone');
      $response = $this->login->login($data);
     
      return $data;
    }
    catch (\Exception $e) {
      MappingUsages::exceptionLogInfo($e, $data);
      $this->messenger->addError(CommonMessenger::errorMessageMapping("error_submit_form"));
    }
  }

}