<?php

namespace Drupal\media\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media bundle for images can be created.
 *
 * @group media
 */
class ImageBundleTest extends WebTestBase {
  /**
   * Exempt from strict schema checking.
   *
   * @see \Drupal\Core\Config\Testing\ConfigSchemaChecker
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'media',
    'media_entity',
    'media_entity_image',
    'image',
    'node',
    'editor',
  ];

  /**
   * The test media bundle.
   *
   * @var \Drupal\media_entity\MediaBundleInterface
   */
  protected $testBundle;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->testBundle = $this->container->get('entity.manager')->getStorage('media_bundle')->load('image');

    $adminUser = $this->drupalCreateUser([
      'view media',
      'create media',
      'update media',
      'update any media',
      'delete media',
      'delete any media',
      'access media overview',
    ]);
    $this->drupalLogin($adminUser);
  }

  /**
   * Tests image media bundle creation from config files.
   */
  public function testMediaBundleCreationFromModule() {
    $type_configuration = [
      'source_field' => 'field_image',
      'gather_exif' => FALSE,
    ];

    $this->assertTrue((bool) $this->testBundle, 'The media bundle from default configuration has been created in the database.');
    $this->assertEqual($this->testBundle->get('label'), 'Image', 'Correct label detected.');
    $this->assertEqual($this->testBundle->get('description'), 'Use Image for uploading locally hosted images.', 'Correct description detected.');
    $this->assertEqual($this->testBundle->get('type'), 'image', 'Correct plugin ID detected.');
    $this->assertEqual($this->testBundle->get('type_configuration'), $type_configuration, 'Type configuration correct.');
    $this->assertEqual($this->testBundle->get('field_map'), [], 'Correct field map detected.');
  }

  /**
   * Tests item creation and thumbnail.
   */
  public function testMediaBundleItemCreation() {
    // Define the media item name.
    $name = $this->randomMachineName();
    $image_files = $this->drupalGetTestFiles('image');
    $testImage = current($image_files);
    $file_path = $this->container->get('file_system')->realpath($testImage->uri);
    $edit = [
      'name[0][value]' => $name,
      'files[field_image_0]' => $file_path,
    ];

    // Save the image.
    $this->drupalPostForm('media/add/' . $this->testBundle->id(), $edit, t('Save and publish'));
    $this->drupalPostForm(NULL, ['field_image[0][alt]' => $name], t('Save and publish'));

    // Let's retrieve the media id and corresponding media entity object.
    $media_id = $this->container->get('entity.query')->get('media')->execute();
    $media_id = reset($media_id);
    /** @var \Drupal\media_entity\MediaInterface $media */
    $media = $this->container->get('entity_type.manager')
        ->getStorage('media')
        ->loadUnchanged($media_id);
    $this->assertEqual($media->get('name')[0]->getValue()['value'], $name, "Correct name stored.");
    $image = $media->getType();
    $thumbnail = $image->thumbnail($media);
    $default_thumbnail = $image->getDefaultThumbnail();
    $this->assertNotEqual($thumbnail, $default_thumbnail, "The thumbnail generated is different from the default thumbnail.");
  }

}
