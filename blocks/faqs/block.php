<?php

use CloakWP\ACF\Block;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\RadioButton;
use Extended\ACF\Fields\Relationship;

return Block::make(__DIR__ . '/block.json')
  ->fields([
    RadioButton::make('Display all or some?', 'all_or_some')
      ->column(50)
      ->choices([
        'all' => 'All FAQs',
        'some' => 'Some FAQs',
      ])
      ->layout('horizontal'),
    RadioButton::make('FAQ Style', 'faq_style')
      ->choices([
        'inline' => 'Inline',
        'boxed' => 'Boxed',
      ])
      ->default('inline')
      ->layout('horizontal')
      ->column(50),
    Relationship::make('FAQs')
      ->helperText('Select the FAQs to display.')
      ->postTypes(['faq'])
      ->filters(['search'])
      ->conditionalLogic([
        ConditionalLogic::where('all_or_some', '==', 'some')
      ]),
  ])
  ->apiResponse(function ($formatted_block) {
    $all_or_some = isset($formatted_block['data']) ? $formatted_block['data']['all_or_some'] : 'all';

    if ($all_or_some == 'some') {
      // add back in post_content to FAQs -- by default it gets stripped out of ACF Relationship field values within blocks due to speed/size concerns 
      $final_faqs = [];
      foreach ($formatted_block['data']['faqs'] as $faq) {
        $post = get_post($faq->id);
        $formatted_faq = (object) array(
          'id' => $post->ID,
          'question' => $post->post_title,
          'answer' => $post->post_content,
          // Add more fields you want to keep
        );
        $final_faqs[] = $formatted_faq;
      }
      $formatted_block['data']['faqs'] = $final_faqs;
      return $formatted_block;
    }

    // Modify the block's REST API response if "all" FAQs were selected -- we fetch and include all FAQ posts 
  
    $args = array(
      'post_type' => 'faq',
      'posts_per_page' => -1,
      'suppress_filters' => false
    );

    // Get all FAQ posts
    $faqs = get_posts($args);

    // Filter out unwanted fields so we don't clutter the REST API responses
    $filtered_faqs = array_map(function ($post) {
      return (object) array(
        'id' => $post->ID,
        'question' => $post->post_title,
        'answer' => $post->post_content,
        // Add more fields you want to keep
      );
    }, $faqs);

    $formatted_block['data']['faqs'] = $filtered_faqs;
    return $formatted_block;
  });
