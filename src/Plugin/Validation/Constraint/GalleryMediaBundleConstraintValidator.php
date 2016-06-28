<?php

namespace Drupal\media\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the GalleryMediaBundle constraint.
 */
class GalleryMediaBundleConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if (!isset($value)) {
      return;
    }
    if ($value->hasField($constraint->sourceFieldName)) {
      $slideshowItems = $value->get($constraint->sourceFieldName);
      foreach ($slideshowItems as $item) {
        if ($item->entity->getType()->getPluginId() == "slideshow") {
          $this->context->addViolation($constraint->message);
        }
      }
    }
  }

}
