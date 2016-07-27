<?php

namespace Drupal\media\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media bundle for gallery can be created.
 *
 * @group media
 */
class GalleryBundleTest extends WebTestBase {
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
    'media_entity_slideshow',
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
    $this->testBundle = $this->container->get('entity_type.manager')->getStorage('media_bundle')->load('gallery');

    $adminUser = $this->drupalCreateUser([
      'view media',
      'create media',
      'update media',
      'update any media',
      'delete media',
      'delete any media',
      'access media overview',
      'access gallery_media_library entity browser pages',
    ]);
    $this->drupalLogin($adminUser);
  }

  /**
   * Tests gallery media bundle creation from config files.
   */
  public function testMediaBundleCreationFromModule() {
    $type_configuration = [
      'source_field' => 'field_slide',
    ];

    $this->assertTrue((bool) $this->testBundle, 'The media bundle from default configuration has been created in the database.');
    $this->assertEqual($this->testBundle->get('label'), 'Gallery', 'Correct label detected.');
    $this->assertEqual($this->testBundle->get('description'), 'Use Gallery for creating a collection of different media items.', 'Correct description detected.');
    $this->assertEqual($this->testBundle->get('type'), 'slideshow', 'Correct plugin ID detected.');
    $this->assertEqual($this->testBundle->get('type_configuration'), $type_configuration, 'Type configuration correct.');
    $this->assertEqual($this->testBundle->get('field_map'), [], 'Correct field map detected.');
  }

  /**
   * Tests thumbnail of the gallery item.
   */
  public function testGalleryItemThumbnail() {
    // Let's add one image and one video.
    $imageItem = $this->addImageItem();
    $videoItem = $this->addVideoItem();
    $this->drupalGet('media/add/gallery');
    $pathValue = (string) current($this->xpath('//input[@data-drupal-selector="edit-field-slide-entity-browser-entity-browser-path"]/@value'));
    $edit = [
      'name[0][value]' => 'Gallery item',
      'field_slide[target_id]' => 'media:' . $imageItem['id'] . ' media:' . $videoItem['id'],
      'field_slide[entity_browser][entity_browser][path]' => $pathValue,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));

    // Let's load all the media items.
    $gallery_id = $this->container->get('entity.query')->get('media')->condition('bundle', 'gallery')->sort('created', 'DESC')->execute();
    $gallery = $this->loadMediaItem(reset($gallery_id));
    $image = $this->loadMediaItem($imageItem['id']);
    $video = $this->loadMediaItem($videoItem['id']);
    // Let's check thumbnail now.
    $gallery_thumbnail = $gallery->getType()->thumbnail($gallery);
    $image_thumbnail = $image->getType()->thumbnail($image);
    $video_thumbnail = $video->getType()->thumbnail($video);
    $this->assertEqual($gallery_thumbnail, $image_thumbnail, "Correct thumbnail detected.");

    $this->drupalGet('media/add/gallery');
    $edit = [
      'name[0][value]' => 'Gallery item 2',
      'field_slide[target_id]' => 'media:' . $videoItem['id'] . ' media:' . $imageItem['id'],
      'field_slide[entity_browser][entity_browser][path]' => $pathValue,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));

    // Let's check the thumbnail again.
    $gallery_id = $this->container->get('entity.query')->get('media')->condition('bundle', 'gallery')->sort('created', 'DESC')->execute();
    $gallery = $this->loadMediaItem(reset($gallery_id));
    $gallery_thumbnail = $gallery->getType()->thumbnail($gallery);
    $this->assertEqual($gallery_thumbnail, $video_thumbnail, "Correct thumbnail detected.");
  }

  /**
   * Tests that gallery option isn't available in gallery create bundle filters.
   */
  public function testGalleryOption() {
    // Open the media library iframe used on add gallery page.
    $this->drupalGet('entity-browser/modal/gallery_media_library');
    $this->assertNoOption('edit-bundle-1', 'gallery');
  }

  /**
   * Adds image type item.
   */
  public function addImageItem() {
    // Let's add image first.
    $name = $this->randomMachineName();
    $testImage = current($this->drupalGetTestFiles('image'));
    $file_path = $this->container->get('file_system')->realpath($testImage->uri);
    $edit = [
      'name[0][value]' => $name,
      'files[field_image_0]' => $file_path,
    ];
    // Save the image.
    $this->drupalPostForm('media/add/image', $edit, t('Save and publish'));
    $this->drupalPostForm(NULL, ['field_image[0][alt]' => $name], t('Save and publish'));
    // Obtain the image id.
    $media_id = $this->container->get('entity.query')->get('media')->condition('bundle', 'image')->sort('created', 'DESC')->execute();
    $media_id = reset($media_id);
    $edit['id'] = $media_id;

    return $edit;
  }

  /**
   * Adds video type item.
   */
  public function addVideoItem() {
    $edit = [
      'name[0][value]' => 'Drupal video!',
      'field_video[0][value]' => 'https://www.youtube.com/watch?v=XgYu7-DQjDQ',
    ];
    $this->drupalPostForm('media/add/video', $edit, t('Save and publish'));
    // Obtain the video id.
    $media_id = $this->container->get('entity.query')->get('media')->condition('bundle', 'video')->sort('created', 'DESC')->execute();
    $media_id = reset($media_id);
    $edit['id'] = $media_id;

    return $edit;
  }

  /**
   * Loads the media entity item.
   *
   * @param int $id
   *   The id of the item.
   *
   * @return \Drupal\media_entity\MediaInterface
   *   The media entity item.
   */
  public function loadMediaItem($id) {
    $item = $this->container->get('entity_type.manager')
      ->getStorage('media')
      ->loadUnchanged($id);
    return $item;
  }

}
