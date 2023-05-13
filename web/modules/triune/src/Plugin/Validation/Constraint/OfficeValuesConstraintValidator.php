<?php

namespace Drupal\triune\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the OfficeValuesConstraint constraint.
 */
class OfficeValuesConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if ($entity->get('label')->value == "") {
      $this->context->addViolation($constraint->namemessage);
    }
    elseif ($entity->get('location_id')->value == "") {
      $this->context->addViolation($constraint->locationmessage);
    }
    else {
      \Drupal::messenger()->addMessage('Saved', 'status');
    }
  }

}