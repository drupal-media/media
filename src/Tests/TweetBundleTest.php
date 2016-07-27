<?php

namespace Drupal\media\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media bundle for tweets can be created.
 *
 * @group media
 */
class TweetBundleTest extends WebTestBase {
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
    'media_entity_twitter',
    'node',
    'link',
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
    $this->testBundle = $this->container->get('entity.manager')->getStorage('media_bundle')->load('tweet');

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
   * Tests tweet media bundle creation from config files.
   */
  public function testMediaBundleCreationFromModule() {
    $type_configuration = [
      'use_twitter_api' => FALSE,
      'source_field' => 'field_tweet_url',
      'consumer_key' => '',
      'consumer_secret' => '',
      'oauth_access_token' => '',
      'oauth_access_token_secret' => '',
    ];
    $field_map = [
      'id' => 'field_tweet_id',
      'user' => 'field_tweet_author',
    ];

    $this->assertTrue((bool) $this->testBundle, 'The media bundle from default configuration has been created in the database.');
    $this->assertEqual($this->testBundle->get('label'), 'Tweet', 'Correct label detected.');
    $this->assertEqual($this->testBundle->get('description'), 'Use this to embed Twitter content on your site.', 'Correct description detected.');
    $this->assertEqual($this->testBundle->get('type'), 'twitter', 'Correct plugin ID detected.');
    $this->assertEqual($this->testBundle->get('type_configuration'), $type_configuration, 'Type configuration correct.');
    $this->assertEqual($this->testBundle->get('field_map'), $field_map, 'Correct field map detected.');
  }

  /**
   * Tests item creation.
   */
  public function testMediaBundleItemCreation() {
    // Define the media item name.
    $name = $this->randomMachineName();
    $tweet_url = 'https://twitter.com/jack/status/20';
    $edit = [
      'name[0][value]' => $name,
      'field_tweet_url[0][uri]' => $tweet_url,
    ];

    // Save the tweet.
    $this->drupalPostForm('media/add/' . $this->testBundle->id(), $edit, t('Save and publish'));
    // Let's retrieve the media id.
    $media_id = $this->container->get('entity.query')->get('media')->condition('bundle', 'tweet')->sort('created', 'DESC')->execute();
    $media_id = reset($media_id);
    $media = $this->container->get('entity_type.manager')
      ->getStorage('media')
      ->loadUnchanged($media_id);
    $properties = $media->toArray();
    $this->assertEqual($media->get('field_tweet_author')[0]->getValue()['value'], "jack", "Correct tweet author stored.");
    $this->assertEqual($media->get('field_tweet_id')[0]->getValue()['value'], "20", "Correct tweet id stored.");
  }

}
