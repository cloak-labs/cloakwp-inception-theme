<?php
use CloakWP\CloakWP;
use CloakWP\ACF\FieldGroup;
use CloakWP\Content\PostType;
use Extended\ACF\Fields\Text;

$CloakWP = CloakWP::getInstance();

return PostType::make('testimonial')
  ->menuIcon('dashicons-format-quote')
  ->public(false)
  ->showUi(true)
  ->showInRest(true)
  ->blockEditor(false)
  ->titlePlaceholder("Person's name")
  ->featuredImageLabel("Person's Headshot")
  ->publiclyQueryable(false)
  ->adminCols([
    // A featured image column:
    'featured_image' => array(
      'title' => 'Headshot',
      'featured_image' => 'thumbnail',
      'width' => 60,
      'height' => 60,
    ),
    // The default Title column:
    'title',
    // A meta field column:
    'last_modified' => array(
      'title' => 'Last Modified',
      'post_field' => 'post_modified',
      'date_format' => 'd/m/Y g:i A'
    ),
    'published' => array(
      'title' => 'Published',
      'post_field' => 'post_date',
      'date_format' => 'd/m/Y g:i A'
    ),
  ])
  ->fieldGroups([
    FieldGroup::make('Testimonial Fields')
      ->fields([
        Text::make('Company')
          ->helperText("The company this person works for."),
        Text::make('Position')
          ->helperText("The person's title/position at their company."),
      ])
  ])
  ->afterChange(function ($postId) use ($CloakWP) {
    // when a testimonial post is created/updated, we also rebuild the /testimonials listing page:
    $CloakWP->getActiveFrontend()->revalidatePages([$postId, '/testimonials']);
  });