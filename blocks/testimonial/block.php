<?php

use CloakWP\ACF\Block;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\RadioButton;
use Extended\ACF\Fields\Relationship;

return Block::make(__DIR__ . '/block.json')
  ->fields([
    RadioButton::make('Testimonial selection', 'all_or_some')
      ->column(50)
      ->choices([
        'all' => 'Most recent',
        'random' => 'Random',
        'some' => 'Manually select',
      ]),
    Number::make('Limit')
      ->helperText('Optionally choose max number of testimonials to display. Leave empty to show all.')
      ->conditionalLogic([
        ConditionalLogic::where('all_or_some', '!=', 'some')
      ]),
    RadioButton::make('Display type')
      ->column(50)
      ->choices([
        'grid' => 'Grid',
        'masonry' => 'Masonry Grid',
        'carousel' => 'Carousel'
      ]),
    Group::make('Carousel Options')
      ->fields(require get_theme_file_path('/fields/carousel.php'))
      ->conditionalLogic([
        ConditionalLogic::where('display_type', '==', 'carousel')
      ]),
    Relationship::make('Testimonials')
      ->helperText('Select the testimonials to display.')
      ->postTypes(['testimonial'])
      ->filters(['search'])
      ->conditionalLogic([
        ConditionalLogic::where('all_or_some', '==', 'some')
      ]),
  ])
  ->apiResponse(function ($formatted_block) {
    $query_type = isset($formatted_block['data']) ? $formatted_block['data']['all_or_some'] : 'all';
    $limit = isset($formatted_block['data']) ? $formatted_block['data']['limit'] : null;

    if ($query_type == 'some') {
      // add back in post_content to testimonials -- by default it gets stripped out of ACF Relationship field values within blocks due to speed/size concerns 
      $final_testimonials = [];
      foreach ($formatted_block['data']['testimonials'] as $testimonial) {
        $post = get_post($testimonial->id);
        $testimonial->content = $post->post_content;
        $final_testimonials[] = $testimonial;
      }
      $formatted_block['data']['testimonials'] = $final_testimonials;
      return $formatted_block;
    }

    // Modify the block's REST API response if "all" testimonials were selected -- we fetch and include all Testimonial posts 
    $args = array(
      'post_type' => 'testimonial',
      'posts_per_page' => -1,
      'suppress_filters' => false
    );

    if ($limit)
      $args['posts_per_page'] = $limit;
    if ($query_type == 'random')
      $args['orderby'] = 'rand';

    // Get all testimonial posts
    $testimonials = get_posts($args);

    // Filter out unwanted fields so we don't clutter the REST API responses
    $filtered_testimonials = array_map(function ($post) {
      $acf_fields = get_fields($post->ID);

      return (object) array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'acf' => $acf_fields,
        'image' => $post->featured_image,
        // Add more fields you want to keep
      );
    }, $testimonials);

    $formatted_block['data']['testimonials'] = $filtered_testimonials;
    return $formatted_block;
  });
