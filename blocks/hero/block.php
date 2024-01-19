<?php

use CloakWP\ACF\Block;
use CloakWP\ACF\Fields\Alignment;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\RadioButton;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Fields\Image;

return Block::make(__DIR__ . '/block.json')
  ->fields([
    RadioButton::make('Hero Style')
      ->helperText('Select the style of the hero section.')
      ->choices([
        'image_right' => 'Image Right',
        'bg_image' => 'Background Image',
        'no_image' => 'No image',
      ])
      ->default('image_right')
      ->layout('horizontal')
      ->column(50),
    Image::make('Image')
      ->helperText('Upload/select an image, shown as either the background or to the right depending on your Hero Style choice.')
      ->previewSize('medium')
      ->column(50)
      ->conditionalLogic([
        ConditionalLogic::where('hero_style', '!=', 'no_image') // available operators: ==, !=, >, <, ==pattern, ==contains, ==empty, !=empty
      ])
      ->format('id')
      ->required(),
    Alignment::make('Content Alignment')
      ->include(['left', 'center'])
      ->default('left')
      ->column(50)
      ->conditionalLogic([
        ConditionalLogic::where('hero_style', '!=', 'image_right') // available operators: ==, !=, >, <, ==pattern, ==contains, ==empty, !=empty
      ]),
    RadioButton::make('Inner Content Width')
      ->choices([
        'default' => 'Content',
        'wide' => 'Wide',
      ])
      ->default('default')
      ->layout('horizontal')
      ->column(50),
    Text::make('Eyebrow')
      ->helperText('1-3 words above H1.')
      ->column(50),
    Text::make('H1')
      ->helperText('Main title of page.'),
    Textarea::make('Subtitle')
      ->helperText('1-3 sentences below the H1.'),
    Link::make('CTA Button')
      ->column(50)
      ->helperText("Select a page to link to, followed by the CTA button's text.")
  ]);
