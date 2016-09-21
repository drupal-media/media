<?php

/**
 * @file
 * Post update functions for Media.
 */

/**
 * @addtogroup updates-8.x-1.x
 * @{
 */

/**
 * Update default config with default uuid.
 */
function media_post_update_default_uuid() {
  $configuration = \Drupal::configFactory()->getEditable('embed.button.media');
  // Default uuid in the config.
  $uuid = '39004672-000c-4b13-af59-838822a4bc5a';
  // Set file uuid in the config.
  $configuration->set('icon_uuid', $uuid);
  $configuration->save();
  // Load the media_embed_icon form the storage.
  $files = \Drupal::entityTypeManager()
    ->getStorage('file')
    ->loadByProperties(['uri' => 'public://media_embed_icon.png']);
  if (!empty($files)) {
    $file = reset($files);
    // Set file uuid same as default config.
    $file->set('uuid', $uuid);
    $file->save();
  }
}

/**
 * @} End of "addtogroup updates-8.x-1.x".
 */
