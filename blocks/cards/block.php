<?php

use CloakWP\ACF\Block;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\PostObject;
use Extended\ACF\Fields\RadioButton;
use Extended\ACF\Fields\Text;

return Block::make(__DIR__ . '/block.json')
  ->fields([
    Number::make('# of Columns', 'num_columns')
      ->default(3)
      ->column(50)
      ->min(1)
      ->max(4),
    RadioButton::make('CTA Strategy')
      ->choices(['None', 'Shared', 'Individual'])
      ->column(50)
      ->default('none')
      ->layout('horizontal'),
    Text::make('Shared CTA')
      ->helperText('Shared CTA text for all cards.')
      ->placeholder('eg. Read More')
      ->conditionalLogic([
        ConditionalLogic::where('cta_strategy', '==', 'shared')
      ]),
    Repeater::make('Cards')
      ->fields([
        // InnerBlocks::make('Card Inner Blocks'),
        TrueFalse::make('Select page?', 'is_page')
          ->helperText('Choose an existing page/post to auto-populate this card.')
          ->stylized(),
        PostObject::make('Page')
          ->postTypes(['page', 'post'])
          ->required()
          ->conditionalLogic([
            ConditionalLogic::where('is_page', '==', 1)
          ]),
        Text::make('Individual CTA')
          ->helperText('CTA text unique to this card.')
          ->placeholder('eg. Read More')
          ->conditionalLogic([
            ConditionalLogic::where('is_page', '==', 1)->and('cta_strategy', '==', 'individual')
          ]),
        Message::make('Subtitle')
          ->body("To add a small subtitle below each card title, you must edit each selected post's 'excerpt' field.")
          ->escapeHtml()
          ->conditionalLogic([
            ConditionalLogic::where('is_page', '==', 1)
          ]),
        Group::make('Card Data')
          ->fields([
            Image::make('Image')
              ->helperText('Choose an image to display at the top of the card.')
              ->previewSize('medium')
              ->column(50)
              ->required(),
            Text::make('Title')
              ->column(50)
              ->required(),
            Link::make('Link')
              ->column(50)
              ->helperText('Optionally link this card to another page/website.'),
            Textarea::make('Excerpt')
              ->column(50)
              ->rows(4),
            Text::make('Individual CTA')
              ->helperText('CTA text unique to this card.')
              ->placeholder('eg. Read More')
              ->conditionalLogic([
                ConditionalLogic::where('cta_strategy', '==', 'individual')
              ]),
          ])
          ->conditionalLogic([
            ConditionalLogic::where('is_page', '==', 0)
          ]),
      ])
      ->minRows(1)
      ->button('Add card')
      ->layout('block')
      ->required(),
  ]);
