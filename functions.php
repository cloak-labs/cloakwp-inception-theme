<?php

use CloakWP\CloakWP;
use CloakWP\Frontend;
use CloakWP\Admin\Enqueue\Script;
use CloakWP\Admin\Enqueue\Stylesheet;
use CloakWP\Content\PostType;
use CloakWP\Utils;
use CloakWP\ACF\FieldGroup;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Image;
use Extended\ACF\Location;

$IS_DEVELOPMENT_MODE = str_contains(\MY_FRONTEND_URL, 'localhost');
$THEME_VERSION = wp_get_theme()->get('Version');
$ASSETS_VERSION = $IS_DEVELOPMENT_MODE ? uniqid() : $THEME_VERSION;


if (class_exists('CloakWP\Utils')) {
  Utils::require_glob(get_stylesheet_directory() . '/globals/*.php');
}

/**
 *  Example of modifying REST API response format of ACF Block fields (try appending 'name={field_name}', or 'type={field_type}', or 'blockName={block_name}' to target things more granularly):
 * 
 *  add_filter('cloakwp/rest/blocks/acf_response_format', function($field_value, $acf_field_object) {
 *    if (is_array($field_value) && isset($field_value['type']) && isset($field_value['value'])) {
 *      return $field_value;
 *    }
 *
 *    return array(
 *      'type' => $acf_field_object['type'],
 *      'value' => $field_value
 *    );
 *  }, 10, 3); 
 */

/**
 * TODO:
 *  - add back in the PluginLoader class, rename it to CloakWPLoader, and register all WP hooks used within the CloakWP class through it; add a "run()" method to CloakWP 
 * that triggers all these hooks to get initiated. This is important because it turns CloakWP into a state object that doesn't do anything until run() is called, making 
 * it more extendable so that users can hook into and modify the CloakWP state before the run() method is executed. It enables us to build a plugin system into the CloakWP 
 * class; a plugins() method could accept an array of Classes that must implement a CloakWP_Plugin interface, which specifies that a plugin class must have a "run" method that 
 * returns a new CloakWP instance); i.e. similar to PayloadCMS plugins, a CloakWP plugin receives the current CloakWP configuration object, modifies it, and returns the 
 * new version, which the next plugin receives, modifies, and returns, and so on...  the final run() function executes all plugins, and then uses the final CloakWP 
 * object to run all hooks.
 *  - add cors() method that accepts either a whitelist array of URLS to allow CORS requests from, or a wildcard string ('*') to accept incoming requests from any domain.
 *  - add globals() method that accepts an array of Global classes -- these are ACF Options pages, which we're essentially renaming to "Globals"
 */



// Add custom fields to WP's internal User Profile pages:
FieldGroup::make('User Fields')
  ->fields([
    Image::make('Headshot')
      ->helperText('Used for author profile/bio frontend components.')
      ->previewSize('medium')
    // ->withSettings(['local' => 'php'])
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

/** @disregard -- ignore \MY_FRONTEND_URL Intelephense error */
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
        /** @disregard -- ignore \MY_FRONTEND_URL Intelephense error */
        if ($IS_DEVELOPMENT_MODE) {
          // we use the IP address alternative to localhost while in dev, ensuring API requests from Docker to our locally running frontend actually work:
          return 'http://172.25.219.69:5000';
        } else {
          /** @disregard -- ignore \MY_FRONTEND_URL Intelephense error */
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
    'core/embed',
    'core/html',
    'core/group',
    'core/quote',
    'core/code',
    'core/columns' => [
      'postTypes' => ['page', 'post']
    ],
    'core/image' => [
      'postTypes' => ['page', 'post']
    ]
  ]);
