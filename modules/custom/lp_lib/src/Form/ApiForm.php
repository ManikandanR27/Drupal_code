<?php

namespace Drupal\lp_lib\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation;

/*
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use iDrupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\UpdateBuildIdCommand;*\

/**
 * Registration form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class ApiForm extends FormBase {

  public function buildForm(array $form, FormStateInterface $form_state, $confirmCode = null) { 
    $this->registration = \Drupal::service('lp.registration');
       // }
        $reg_query_data = array();
        $reg_response = $this->registration->soiRegistration($reg_query_data);
        die;

  }

 /**
    * Getter method for Form ID.
    */
    public function getFormId() {
        return 'lp_registration_form';
    }

    /**
    * Implements form validation.
    */
    public function validateForm(array &$form, FormStateInterface $form_state) {
 
    }

    /**
    * Implements a form submit handler.
    * {@inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state) {

	
    }



}