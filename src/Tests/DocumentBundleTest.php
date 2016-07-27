<?php

namespace Drupal\media\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media bundle for document can be created.
 *
 * @group media
 */
class DocumentBundleTest extends WebTestBase {
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
    'media_entity_document',
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
    $this->testBundle = $this->container->get('entity_type.manager')->getStorage('media_bundle')->load('document');

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
   * Tests document media bundle creation from config files.
   */
  public function testMediaBundleCreationFromModule() {
    $type_configuration = [
      'source_field' => 'field_document',
    ];
    $field_map = [
      'mime' => 'field_mime_type',
      'size' => 'field_document_size',
    ];

    $this->assertTrue((bool) $this->testBundle, 'The media bundle from default configuration has been created in the database.');
    $this->assertEqual($this->testBundle->get('label'), 'Document', 'Correct label detected.');
    $this->assertEqual($this->testBundle->get('description'), 'Use Document for uploading document files such as PDF.', 'Correct description detected.');
    $this->assertEqual($this->testBundle->get('type'), 'document', 'Correct plugin ID detected.');
    $this->assertEqual($this->testBundle->get('type_configuration'), $type_configuration, 'Type configuration correct.');
    $this->assertEqual($this->testBundle->get('field_map'), $field_map, 'Correct field map detected.');
  }

  /**
   * Tests thumbnails of the document items.
   */
  public function testDocumentItemThumbnail() {
    // Array of test files and corresponding file icons.
    $files = [
      'Test.pdf' => 'public://media-icons/generic/application-pdf.png',
      'Test.doc' => 'public://media-icons/generic/application-msword.png',
      'Test.docx' => 'public://media-icons/generic/application-vnd.openxmlformats-officedocument.wordprocessingml.document.png',
      'Test.ods' => 'public://media-icons/generic/application-vnd.oasis.opendocument.spreadsheet.png',
      'Test.odt' => 'public://media-icons/generic/application-vnd.oasis.opendocument.text.png',
      'Test.ott' => 'public://media-icons/generic/application-vnd.oasis.opendocument.text-template.png',
      'Test.ppt' => 'public://media-icons/generic/application-vnd.ms-powerpoint.png',
      'Test.pptx' => 'public://media-icons/generic/application-vnd.openxmlformats-officedocument.presentationml.presentation.png',
      'Test.rtf' => 'public://media-icons/generic/application-rtf.png',
      'Test.txt' => 'public://media-icons/generic/text-plain.png',
      'Test.xls' => 'public://media-icons/generic/application-vnd.ms-excel.png',
      'Test.xlsx' => 'public://media-icons/generic/application-vnd.openxmlformats-officedocument.spreadsheetml.sheet.png',
    ];

    foreach ($files as $fileName => $thumbnail) {
      $file = drupal_get_path('module', 'media') . '/files/' . $fileName;
      $name = $this->randomMachineName();
      $this->drupalGet('media/add/document');
      $edit = [
        'files[field_document_0]' => $file,
      ];
      $this->drupalPostAjaxForm(NULL, $edit, "field_document_0_upload_button");
      $fid = (string) current($this->xpath('//input[@data-drupal-selector="edit-field-document-0-fids"]/@value'));
      $edit = [
        'name[0][value]' => $name,
        'form_id' => 'media_document_form',
        'field_document[0][fids]' => $fid,
        'field_document[0][display]' => 1,
      ];
      $this->drupalPostForm(NULL, $edit, t('Save and publish'));
      $recentThumbnail = $this->getMostRecentThumbnail();
      $this->assertEqual($thumbnail, $recentThumbnail, "Correct thumbnail detected for " . $fileName);
    }
  }

  /**
   * Returns the thumbnail of the most recent document.
   *
   * @return string
   *   Path of the thumbnail.
   */
  public function getMostRecentThumbnail() {
    $document_id = $this->container->get('entity.query')->get('media')->condition('bundle', 'document')->sort('created', 'DESC')->execute();
    $item = $this->container->get('entity_type.manager')
      ->getStorage('media')
      ->loadUnchanged(reset($document_id));
    return $item->getType()->thumbnail($item);
  }

}
