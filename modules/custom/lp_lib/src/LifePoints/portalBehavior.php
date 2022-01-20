<?php

namespace Drupal\lp_lib\LifePoints;

use Drupal\lp_lib\Util\MappingUsages;

/**
 * Field Configuration as per panel.
 */
class PortalBehavior {

  protected $reasonCode;
  protected $label;
  protected $description;
  protected $canLogIn;
  protected $offerClientSurvey;
  protected $canRedeemPoints;
  protected $canAccessProfiler;
  protected $PortalBehavior;
  protected $PortalBehaviorOptions;

  /**
   * Function to set Portal Behavior Configuration.
   */
  public function setPortalBehavior($reasonCode) {
    $properties = [];
    // Panel name.
    $properties['name'] = $reasonCode;
    $properties['vid'] = 'lp_portal_behavior';
    // Get the panel data based on the property defined.
    $this->PortalBehavior = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $this->PortalBehavior = reset($this->PortalBehavior);
    // Get Portal Behavior options array.
    if ($this->PortalBehavior) {
      $this->PortalBehaviorOptions = array_column($this->PortalBehavior->field_lp_portal_behavior->getValue(), 'value');
      if (!empty($this->PortalBehavior)) {
        $this->canLogIn = (in_array('can_log_in', $this->PortalBehaviorOptions)) ? TRUE : FALSE;
        $this->offerClientSurvey = (in_array('offered_client_surveys', $this->PortalBehaviorOptions)) ? TRUE : FALSE;
        $this->canRedeemPoints = (in_array('can_redeem_points', $this->PortalBehaviorOptions)) ? TRUE : FALSE;
        $this->canAccessProfiler = (in_array('can_access_profilers', $this->PortalBehaviorOptions)) ? TRUE : FALSE;
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
  }

  /**
   * Function to get Login Access.
   */
  public function canLogIn() {
    return $this->canLogIn;
  }

  /**
   * Function to get Login Access.
   */
  public function offeredClientSurveys() {
    return $this->offerClientSurvey;
  }

  /**
   * Function to get Login Access.
   */
  public function canRedeemPoints() {
    return $this->canRedeemPoints;
  }

  /**
   * Function to get Login Access.
   */
  public function canAccessProfiler() {
    return $this->canAccessProfiler;
  }

}
