#!/bin/bash
# Simple script to install drupal for travis-ci running.

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
drupal_ti_ensure_drupal

# Change to drupal directory
cd "$DRUPAL_TI_DRUPAL_DIR"

# Let's enable the video_embed_field module
drush --yes en video_embed_field

# Let's enable the video_embed_media module
drush --yes en video_embed_media
