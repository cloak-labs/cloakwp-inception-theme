<?php

use CloakWP\ACF\FieldGroup;
use CloakWP\Admin\Enqueue\Script;
use CloakWP\Admin\Enqueue\Stylesheet;
use CloakWP\CloakWP;
use CloakWP\Content\PostType;
use CloakWP\Frontend;
use CloakWP\Utils;
use Extended\ACF\Fields\Text;

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

function custom_check_login_status()
{
  $isLoggedIn = false;
  if ($_COOKIE && is_array($_COOKIE)) {
    if (array_key_exists(LOGGED_IN_COOKIE, $_COOKIE)) $isLoggedIn = true;
  }
  return rest_ensure_response($isLoggedIn);
}

add_action('rest_api_init', function () {
  register_rest_route('jwt-auth/v1', '/is-logged-in', array(
    'methods' => 'GET',
    'callback' => 'custom_check_login_status',
    'permission_callback' => function ($request) {
      /*
        if JWT is passed as header, is_user_logged_in() should return true, otherwise false;
        but for some reason this only works if the route namespace is 'jwt-auth/v1'.
        TODO: look into making this work on routes with custom namespaces -- might need to fork the JWT Auth WP plugin
      */
      return is_user_logged_in();
    }
  ));
});



$cloakWP = CloakWP::getInstance();
$cloakWP
  ->frontends([
    Frontend::make('website', \MY_FRONTEND_URL)
      ->authSecret(\CLOAKWP_AUTH_SECRET)
      ->deployments([
        'https://my-project-dev-deployment.vercel.app'
      ])
      ->enableDefaultOnDemandISR()
      ->enableDecoupledPreview()
      ->separateApiRouteUrl(function () {
        if (str_contains(\MY_FRONTEND_URL, 'localhost')) {
          return 'http://172.25.219.69:5000';
        } else {
          return \MY_FRONTEND_URL;
        }
      })
  ])
  ->enqueueAssets([
    Script::make('my-gutenberg-editor-js')
      ->hook('enqueue_block_editor_assets')
      ->src(get_theme_file_uri('/js/gutenberg-scripts.js'))
      ->version('1.0.0')
      ->inFooter(),
    Stylesheet::make('my-gutenberg-editor-styles')
      ->hook('enqueue_block_editor_assets')
      ->src(get_theme_file_uri('/css/gutenberg-styles.css'))
      ->version('1.0.0'),
    Stylesheet::make('my-general-admin-styles')
      ->src(get_theme_file_uri('/css/admin-styles.css'))
      ->version('1.0.0')
  ])
  ->postTypes([
    PostType::make('post')
      ->rewrite([
        'permastruct' => '/blog/%post%'
      ])
      ->beforeChange(function ($data) {
        // add some custom ISR stuff on top of default post revalidation
        Utils::write_log('Save post !!');
      })
    // ->afterRead(function ($posts) {
    //   // TODO: move below validation into afterRead as built-in abstraction
    //   // if (!is_array($posts) || !count($posts)) return $posts;

    //   $res = array_map(function ($p) {
    //     // TODO: move below validation into afterRead as built-in abstraction
    //     // if ($p->post_type != 'post') return $p;

    //     $p->pathname = Utils::get_post_pathname($p->ID);
    //     return $p;
    //   }, $posts);
    //   return $res;
    // })
    // ->virtualFields([
    //   'pathname' => fn ($post) => Utils::get_post_pathname($post->ID)
    // ])
    ,
    PostType::make('page')
      ->beforeChange(function ($data) {
        // add some custom ISR stuff on top of default post revalidation
        Utils::write_log('Save page ##');
      })
    // ->virtualFields([
    //   'pathname' => fn ($post) => Utils::get_post_pathname($post->ID)
    // ])
    ,
    PostType::make('testimonial')
      ->menuIcon('dashicons-format-chat')
      ->hasArchive(false)
      ->public(true)
      ->showInRest(true)
      ->blockEditor(false)
      ->titlePlaceholder("Person's name")
      ->featuredImageLabel("Person's Headshot")
      ->rewrite([
        'permastruct' => '/testimonial/%testimonial%'
      ])
      ->adminCols([
        // A featured image column:
        'featured_image' => array(
          'title'          => 'Headshot',
          'featured_image' => 'thumbnail',
          'width'          => 60,
          'height'         => 60,
        ),
        // The default Title column:
        'title',
        // A meta field column:
        'last_modified' => array(
          'title'       => 'Last Modified',
          'post_field'    => 'post_modified',
          'date_format' => 'd/m/Y g:i A'
        ),
        'published' => array(
          'title'       => 'Published',
          'post_field'    => 'post_date',
          'date_format' => 'd/m/Y g:i A'
        ),
      ])
      ->fieldGroups([
        FieldGroup::make('Testimonial Fields')
          ->fields([
            Text::make('Company')
              ->instructions("The company this person works for."),
            Text::make('Position')
              ->instructions("The person's title/position at their company."),
          ])
      ])
      ->beforeChange(function ($postId) use ($cloakWP) {
        // when a testimonial post is created/updated, we also rebuild the /testimonials listing page:
        $cloakWP->getActiveFrontend()->revalidatePages([$postId, '/testimonials']);
      })
      ->apiResponse(function ($response, $post, $context) {
        // modify API response for Testimonial posts
        $data = $response->data;
        $data['customField'] = 'simple as that';
        $response->data = $data;
        return $response;
      })
  ])
  ->blocks(__DIR__ . '/blocks')
  ->coreBlocks([
    'core/paragraph',
    'core/heading',
    'core/button',
    'core/buttons',
    'core/list',
    'core/list-item',
    'core/embed',
    'core/html',
    'core/group',
    'core/columns' => [
      'postTypes' => ['page', 'post']
    ],
    'core/image' => [
      'postTypes' => ['page', 'post']
    ]
  ])
  ->init();
