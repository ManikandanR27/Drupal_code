<?php

namespace Drupal\lp_lib\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class FunctionalMaintenanceForm.
 *
 * @package Drupal\lp_lib\Form
 */
class FunctionalMaintenanceForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'lp_lib.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'functional_maintenance_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('lp_lib.settings');
    // Registration Maintenance congiguarations.
    $form['registration_maintenance'] = [
      '#type' => 'fieldset',
      '#title' => t('Registration maintenance'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['registration_maintenance']['global_registration_on_hold'] = [
      '#type' => 'fieldset',
      '#title' => t('Global Registration on hold'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#states' => [
        'disabled' => [
          ':input[name="registration_on_hold_required"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['registration_maintenance']['global_registration_on_hold']['global_registration_on_hold_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('<strong>Put Registration on-hold globally</strong>'),
      '#description' => $this->t('Tick this option if you want to hold registration globally without time period.'),
      '#default_value' => $config->get('global_registration_on_hold_required'),
    ];

    $form['registration_maintenance']['markup'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<strong>OR</strong>'),
    ];
    $form['registration_maintenance']['registration_on_hold'] = [
      '#type' => 'fieldset',
      '#title' => t('Registration on hold'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#states' => [
        'disabled' => [
          ':input[name="global_registration_on_hold_required"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['registration_maintenance']['registration_on_hold']['registration_on_hold_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('<strong>Put Registration on-hold</strong>'),
      '#description' => $this->t('Tick this option if you want to hold registration based on time period.'),
      '#default_value' => $config->get('registration_on_hold_required'),
    ];
    $timezone = date_default_timezone_get();
    $date = date('m-d-Y h:i:s A');
    $default_start_t = '';
    if ($config->get('start_date')) {
      $default_start_t = DrupalDateTime::createFromTimestamp($config->get('start_date'));
    }
    else {
      $default_start_t = DrupalDateTime::createFromTimestamp(time());
    }
    $form['registration_maintenance']['registration_on_hold']['start_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Start Date'),
      '#default_value' => $default_start_t,
      '#description' => $this->t('Enter the start-date and time for registration on-hold process.'),
    ];
    $default_end_t = '';
    if ($config->get('end_date')) {
      $default_end_t = DrupalDateTime::createFromTimestamp($config->get('end_date'));
    }
    else {
      $default_end_t = DrupalDateTime::createFromTimestamp(time());
    }
    $form['registration_maintenance']['registration_on_hold']['end_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('End Date'),
      '#default_value' => $default_end_t,
      '#description' => $this->t('Enter the end-date and time for registration on-hold finish. End date and time should be greater than start date and time.<br/><br/>
      Timezone: ' . $timezone . '<br/>
      Current UTC (Coordinated Universal Time): ' . $date),
    ];
    // Login Maintenance congiguarations.
    $form['login_maintenance'] = [
      '#type' => 'fieldset',
      '#title' => t('Login maintenance'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['login_maintenance']['global_login_on_hold'] = [
      '#type' => 'fieldset',
      '#title' => t('Global Login on hold'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#states' => [
        'disabled' => [
          ':input[name="login_on_hold_required"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['login_maintenance']['global_login_on_hold']['global_login_on_hold_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('<strong>Put Login on-hold globally</strong>'),
      '#description' => $this->t('Tick this option if you want to hold login globally without time period.'),
      '#default_value' => $config->get('global_login_on_hold_required'),
    ];
    $form['login_maintenance']['login_markup'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<strong>OR</strong>'),
    ];

    $form['login_maintenance']['login_on_hold'] = [
      '#type' => 'fieldset',
      '#title' => t('Login on hold'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#states' => [
        'disabled' => [
          ':input[name="global_login_on_hold_required"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['login_maintenance']['login_on_hold']['login_on_hold_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('<strong>Put Login on-hold</strong>'),
      '#description' => $this->t('Tick this option if you want to hold login based on time period.'),
      '#default_value' => $config->get('login_on_hold_required'),
    ];
    $timezone = date_default_timezone_get();
    $login_date = date('m-d-Y h:i:s A');
    $default_login_start_t = '';
    if ($config->get('login_start_date')) {
      $default_login_start_t = DrupalDateTime::createFromTimestamp($config->get('login_start_date'));
    }
    else {
      $default_login_start_t = DrupalDateTime::createFromTimestamp(time());
    }
    $form['login_maintenance']['login_on_hold']['login_start_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Start Date'),
      '#default_value' => $default_login_start_t,
      '#description' => $this->t('Enter the start-date and time for login on-hold process.'),
    ];
    $default_login_end_t = '';
    if ($config->get('login_end_date')) {
      $default_login_end_t = DrupalDateTime::createFromTimestamp($config->get('login_end_date'));
    }
    else {
      $default_login_end_t = DrupalDateTime::createFromTimestamp(time());
    }
    $form['login_maintenance']['login_on_hold']['login_end_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('End Date'),
      '#default_value' => $default_login_end_t,
      '#description' => $this->t('Enter the end-date and time for login on-hold finish. End date and time should be greater than start date and time.<br/><br/>
      Timezone: ' . $timezone . '<br/>
      Current UTC (Coordinated Universal Time): ' . $login_date),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('registration_on_hold_required') && empty($form_state->getValue('start_date'))) {
      $form_state->setErrorByName('start_date', $this->t('Start date is required.'));
    }
    if ($form_state->getValue('registration_on_hold_required') && empty($form_state->getValue('end_date'))) {
      $form_state->setErrorByName('end_date', $this->t('End date is required.'));
    }
    if (strtotime($form_state->getValue('start_date')) > strtotime($form_state->getValue('end_date'))) {
      $form_state->setErrorByName('end_date', $this->t('End date and time must be greater than start date '));
    }
    // Validation for Login configs.
    if ($form_state->getValue('login_on_hold_required') && empty($form_state->getValue('login_start_date'))) {
      $form_state->setErrorByName('login_start_date', $this->t('Start date is required.'));
    }
    if ($form_state->getValue('login_on_hold_required') && empty($form_state->getValue('login_end_date'))) {
      $form_state->setErrorByName('login_end_date', $this->t('End date is required.'));
    }
    if (strtotime($form_state->getValue('login_start_date')) > strtotime($form_state->getValue('login_end_date'))) {
      $form_state->setErrorByName('login_end_date', $this->t('End date and time must be greater than start date '));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    // If global option ticked then empty start & end date.
    if ($form_state->getValue('global_registration_on_hold_required')) {
      $start_date = '';
      $end_date = '';
    }
    // If time based option not ticked then empty start & end date.
    elseif (!$form_state->getValue('registration_on_hold_required')) {
      $start_date = '';
      $end_date = '';
    }
    // Convert date & time to str to timestamp format
    else {
      $start_date = strtotime($form_state->getValue('start_date'));
      $end_date = strtotime($form_state->getValue('end_date'));
      // Login start and end date.
      $login_start_date = strtotime($form_state->getValue('login_start_date'));
      $login_end_date = strtotime($form_state->getValue('login_end_date'));
    }

    // If global option ticked then empty start & end date.
    if ($form_state->getValue('global_login_on_hold_required')) {
      $login_start_date = '';
      $login_end_date = '';
    }
    // If time based option not ticked then empty start & end date.
    elseif (!$form_state->getValue('login_on_hold_required')) {
      $login_start_date = '';
      $login_end_date = '';
    }
    // Convert date & time to str to timestamp format
    else {
      // Login start and end date.
      $login_start_date = strtotime($form_state->getValue('login_start_date'));
      $login_end_date = strtotime($form_state->getValue('login_end_date'));
    }
    // Save field values.
    $this->config('lp_lib.settings')
      ->set('global_registration_on_hold_required', $form_state->getValue('global_registration_on_hold_required'))
      ->set('registration_on_hold_required', $form_state->getValue('registration_on_hold_required'))
      ->set('start_date', $start_date)
      ->set('end_date', $end_date)

      ->set('global_login_on_hold_required', $form_state->getValue('global_login_on_hold_required'))
      ->set('login_on_hold_required', $form_state->getValue('login_on_hold_required'))
      ->set('login_start_date', $login_start_date)
      ->set('login_end_date', $login_end_date)
      ->save();
  }

}
