<?php
/**
 * @file Contains \Drupal\triune\Entity\JobRateInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface JobRateInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the JobRate value.
     *
     * @return string
     */
    public function getJobRate();
}
?>