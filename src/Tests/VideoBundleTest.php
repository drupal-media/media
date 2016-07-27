<?php

namespace Drupal\media\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media bundle for videos can be created.
 *
 * @group media
 */
class VideoBundleTest extends WebTestBase {
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
    'video_embed_field',
    'video_embed_media',
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
    $this->testBundle = $this->container->get('entity.manager')->getStorage('media_bundle')->load('video');

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
   * Tests video media bundle creation from config files.
   */
  public function testMediaBundleCreationFromModule() {
    $type_configuration = [
      'source_field' => 'field_video',
    ];

    $field_map = [
      'id' => 'field_id',
      'source_name' => 'field_source',
    ];

    $this->assertTrue((bool) $this->testBundle, 'The media bundle from default configuration has been created in the database.');
    $this->assertEqual($this->testBundle->get('label'), 'Video', 'Correct label detected.');
    $this->assertEqual($this->testBundle->get('description'), 'Use Video for embedding videos hosted by YouTube, Vimeo, or some other provider.', 'Correct description detected.');
    $this->assertEqual($this->testBundle->get('type'), 'video_embed_field', 'Correct plugin ID detected.');
    $this->assertEqual($this->testBundle->get('type_configuration'), $type_configuration, 'Type configuration correct.');
    $this->assertEqual($this->testBundle->get('field_map'), $field_map, 'Correct field map detected.');
  }

  /**
   * Tests video media bundle field maps.
   */
  public function testBundleFieldMap() {
    $edit = [
      'name[0][value]' => 'Drupal video!',
      'field_video[0][value]' => 'https://www.youtube.com/watch?v=XgYu7-DQjDQ',
    ];
    $this->drupalPostForm('media/add/' . $this->testBundle->id(), $edit, t('Save and publish'));

    // Let's retrieve the media id and corresponding media entity object.
    $media_id = $this->container->get('entity.query')->get('media')->execute();
    $media_id = reset($media_id);
    /** @var \Drupal\media_entity\MediaInterface $media */
    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')
      ->loadUnchanged($media_id);
    $properties = $media->toArray();
    $this->assertEqual($properties['field_id'][0]['value'], 'XgYu7-DQjDQ', 'Correct video ID detected.');
    $this->assertEqual($properties['field_source'][0]['value'], 'youtube', 'Correct video source detected.');
  }

}
