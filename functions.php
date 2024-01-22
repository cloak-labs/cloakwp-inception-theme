<?php

use CloakWP\CloakWP;
use CloakWP\Frontend;
use CloakWP\Admin\Enqueue\Script;
use CloakWP\Admin\Enqueue\Stylesheet;
use CloakWP\Utils;
use CloakWP\ACF\FieldGroup;
use Extended\ACF\Fields\Image;
use Extended\ACF\Location;

$IS_DEVELOPMENT_MODE = str_contains(\MY_FRONTEND_URL, 'localhost');
$THEME_VERSION = wp_get_theme()->get('Version');
$ASSETS_VERSION = $IS_DEVELOPMENT_MODE ? uniqid() : $THEME_VERSION;


if (class_exists('CloakWP\Utils')) {
  Utils::require_glob(get_stylesheet_directory() . '/globals/*.php');
}

// Add custom fields to WP's internal User Profile pages:
FieldGroup::make('User Fields')
  ->fields([
    Image::make('Headshot')
      ->helperText('Used for author profile/bio frontend components.')
      ->previewSize('medium')
  ])
  ->location([
    Location::where('user_form', 'edit')
  ])
  ->register();


/**
 * Below we include Twitter/X usernames in CloakWP's `author` meta, as returned by `Utils::get_pretty_author()` (affects the `author` 
 * field in REST API responses). We render this Twitter username under the author's name via the frontend's `AvatarProfileBadge` component. 
 * You can add/remove various user meta to the `author` object via this filter -- to see all available meta, return `true` and visit a 
 * post/page REST endpoint, then come back and return an array of all the meta keys you wish to include.
 */
add_filter('cloakwp/author_format/included_meta', function ($default_meta, $user_meta) {
  // return true; // return true to include all user_meta
  return array_merge($default_meta, ['twitter', 'description']);
}, 10, 2);


$CloakWP = CloakWP::getInstance();

$CloakWP
  ->frontends([
    Frontend::make('website', \MY_FRONTEND_URL)
      ->authSecret(\CLOAKWP_AUTH_SECRET)
      ->deployments([
        'https://my-project-dev-deployment.vercel.app'
      ])
      ->enableDefaultOnDemandISR()
      ->enableDecoupledPreview()
      ->apiRouteUrl(function () use ($IS_DEVELOPMENT_MODE) {
        if ($IS_DEVELOPMENT_MODE) {
          // we use the IP address alternative to localhost while in dev, ensuring API requests from Docker to our locally running frontend actually work:
          return 'http://172.25.219.69:5000';
        } else {
          return \MY_FRONTEND_URL;
        }
      })
  ])
  ->enqueueAssets([
    Script::make('my-editor-js')
      ->hook('enqueue_block_editor_assets')
      ->src(get_theme_file_uri('/assets/js/editor.js'))
      ->version($ASSETS_VERSION)
      ->deps(array('jquery', 'wp-blocks', 'wp-dom-ready', 'wp-i18n'))
      ->inFooter(),
    Stylesheet::make('my-editor-styles')
      ->hook('enqueue_block_editor_assets')
      ->src(get_theme_file_uri('/assets/css/editor.css'))
      ->version($ASSETS_VERSION),
    Stylesheet::make('my-admin-styles')
      ->src(get_theme_file_uri('/assets/css/admin.css'))
      ->version($ASSETS_VERSION)
  ])
  ->postTypes(__DIR__ . '/post-types')
  ->blocks(__DIR__ . '/blocks')
  ->enabledCoreBlocks([
    'core/paragraph',
    'core/heading',
    'core/buttons',
    'core/button',
    'core/list',
    'core/list-item',
    'core/html',
    'core/quote',
    'core/code',
    'core/columns' => [
      'postTypes' => ['page', 'post']
    ],
    'core/image' => [
      'postTypes' => ['page', 'post']
    ]
  ]);
