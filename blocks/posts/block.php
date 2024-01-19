<?php

use CloakWP\ACF\Block;
use CloakWP\Utils;
use CloakWP\ACF\Fields\PostTypeSelect;
use Extended\ACF\Fields\Checkbox;
use Extended\ACF\Fields\Text;

return Block::make(__DIR__ . '/block.json')
  ->fields([
    PostTypeSelect::make('Post Type')
      ->stylized(),
    Text::make('CTA Text')
      ->helperText('Optionally add a CTA link to all posts.')
      ->placeholder('eg. Read More'),
    Checkbox::make('Post Meta')
      ->helperText('Optionally display some meta info at the bottom of each card.')
      ->choices([
        'date' => 'Published Date',
        'last_modified' => 'Modified Date',
        'author.display_name' => 'Author'
      ]),
  ])
  ->apiResponse(function ($formatted_block) {




    // TODO: figure out why this isn't running anymore (we changed Block->register stuff)
  


    // Modify the REST API response for the "Posts" ACF Block -- we fetch the posts from the selected post type and include them in the block's data 
    $post_type = $formatted_block['data']['post_type'];

    $args = array(
      'post_type' => $post_type,
      'posts_per_page' => -1,
      'suppress_filters' => false
    );

    // Get all posts from post_type
    $posts_array = get_posts($args);

    // Filter out unwanted fields so we don't clutter the REST API responses
    $filtered_posts = array_map(function ($post) use ($post_type) {
      $acf_fields = get_fields($post->ID);
      $other_fields = array();

      if ($post_type == 'Testimonial') {
        $other_fields = array(
          'content' => $post->post_content
        );
      }

      return (object) array_merge(
        array(
          'id' => $post->ID,
          'title' => $post->post_title,
          'excerpt' => $post->post_excerpt,
          'date' => $post->post_date,
          'last_modified' => $post->post_modified,
          'href' => $post->pathname,
          'test' => $post->test,
          'author' => Utils::get_pretty_author($post->post_author),
          'acf' => $acf_fields,
          'image' => $post->featured_image,
          // Add more fields you want to keep
        ),
        $other_fields
      );
    }, $posts_array);

    $formatted_block['data']['posts'] = $filtered_posts;
    return $formatted_block;
  });
