<?php

namespace Drupal\media\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media bundle for instagram can be created.
 *
 * @group media
 */
class InstagramBundleTest extends WebTestBase {
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
    'media_entity_instagram',
    'link',
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
    $this->testBundle = $this->container->get('entity.manager')->getStorage('media_bundle')->load('instagram');

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
   * Tests instagram media bundle creation from config files.
   */
  public function testMediaBundleCreationFromModule() {
    $type_configuration = [
      'use_instagram_api' => FALSE,
      'source_field' => 'field_instagram_url',
      'client_id'  => '',
    ];
    $field_map = [
      'shortcode' => 'field_instagram_shortcode',
    ];

    $this->assertTrue((bool) $this->testBundle, 'The media bundle from default configuration has been created in the database.');
    $this->assertEqual($this->testBundle->get('label'), 'Instagram Post', 'Correct label detected.');
    $this->assertEqual($this->testBundle->get('description'), 'Use this to attach Instagram posts to your content.', 'Correct description detected.');
    $this->assertEqual($this->testBundle->get('type'), 'instagram', 'Correct plugin ID detected.');
    $this->assertEqual($this->testBundle->get('type_configuration'), $type_configuration, 'Type configuration correct.');
    $this->assertEqual($this->testBundle->get('field_map'), $field_map, 'Correct field map detected.');
  }

  /**
   * Tests item creation and thumbnail.
   */
  public function testMediaBundleItemCreation() {
    // Define the media item name.
    $name = $this->randomMachineName();
    $instagram_url = 'https://www.instagram.com/p/C/';
    $edit = [
      'name[0][value]' => $name,
      'field_instagram_url[0][uri]' => $instagram_url,
    ];

    // Save the Instagram post.
    $this->drupalPostForm('media/add/' . $this->testBundle->id(), $edit, t('Save and publish'));

    // Assert that the formatter exists on this page.
    $this->assertFieldByXPath('//iframe');

    // Let's retrieve the media id and corresponding media entity object.
    $media_id = $this->container->get('entity.query')->get('media')->condition('bundle', 'instagram')->sort('created', 'DESC')->execute();
    $media_id = reset($media_id);
    /** @var \Drupal\media_entity\MediaInterface $media */
    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')
      ->loadUnchanged($media_id);
    $properties = $media->toArray();
    $this->assertEqual($media->get('field_instagram_shortcode')[0]->getValue()['value'], "C", "Correct shortcode stored.");
  }

}
