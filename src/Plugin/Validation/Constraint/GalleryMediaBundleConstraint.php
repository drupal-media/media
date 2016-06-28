<?php

namespace Drupal\media\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Check that there is no Gallery type item in slideshow items.
 *
 * @Constraint(
 *   id = "GalleryMediaBundle",
 *   label = @Translation("Gallery media bundle", context = "Validation"),
 * )
 */
class GalleryMediaBundleConstraint extends Constraint {

  /**
   * Name of the source field for slideshow bundle.
   *
   * @var string
   */
  public $sourceFieldName;

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'Gallery cannot contain gallery type item.';
}
