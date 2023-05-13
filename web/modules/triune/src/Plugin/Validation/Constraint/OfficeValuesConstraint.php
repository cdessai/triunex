<?php

namespace Drupal\triune\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that we have name,location values.
 *
 * @Constraint(
 *   id = "OfficeValuesConstraint",
 *   label = @Translation("Office Values Constraint", context = "Validation"),
 *   type = "string"
 * )
 */
class OfficeValuesConstraint extends Constraint {

  /**
   * Constraint message.
   *
   * @var string
   */
  public $namemessage = 'Name Field cannot be empty.';

  public $locationmessage = 'Location Field cannot be empty.';

}