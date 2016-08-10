<?php

namespace Drupal\Tests\media\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Ensures that embedding functionality works perfectly.
 *
 * @group media
 */
class EmbedButtonTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'path',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Manually installing modules to preserve the order.
    $this->installModule('media_embed_test');
    $this->installModule('media');
    $adminUser = $this->drupalCreateUser([
      'access content',
      'use text format basic_html',
      'use text format full_html',
      'access media_embed entity browser pages',
      'view media',
      'create media',
      'update media',
      'update any media',
      'delete media',
      'delete any media',
      'access media overview',
      'create page content',
      'edit any page content',
    ]);
    $this->drupalLogin($adminUser);
  }

  /**
   * Tests that the entity embed dialog is working.
   */
  public function testMediaEmbedDialog() {
    // Find the button and click it to see if the modal opens.
    $this->drupalGet('node/add/page');
    $this->find('.cke_button__media')->click();
    $this->wait();
    $this->assertSession()->pageTextContains('Select media to embed');

    // Test for the button in the basic_html editor.
    $this->drupalGet('entity-embed/dialog/basic_html/media');
    $this->assertEquals(200, $this->getSession()->getStatusCode());
    $this->assertSession()->pageTextContains('Select media to embed');

    // Test for the button in the full_html editor.
    $this->drupalGet('entity-embed/dialog/full_html/media');
    $this->assertEquals(200, $this->getSession()->getStatusCode());
    $this->assertSession()->pageTextContains('Select media to embed');

    $this->drupalGet('entity-browser/iframe/media_embed');
    $this->assertEquals(200, $this->getSession()->getStatusCode());
    $filter = $this->getSession()->getPage()->find('css', 'input[name="name"]');
    $this->assertTrue($filter, "Found filter");
  }

  /**
   * Installs the module using module_handler service.
   *
   * @param string $module_name
   *   Name of the module to install.
   */
  public function installModule($module_name) {
    if (!$this->container->get('module_handler')->moduleExists($module_name)) {
      $this->container->get('module_installer')->install(array($module_name));
    }
  }

  /**
   * Wait for AJAX.
   */
  protected function wait() {
    $this->getSession()->wait(20000, '(0 === jQuery.active)');
  }

  /**
   * Find an element based on a CSS selector.
   *
   * @param string $css_selector
   *   A css selector to find an element for.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The found element or null.
   */
  protected function find($css_selector) {
    return $this->getSession()->getPage()->find('css', $css_selector);
  }

}
