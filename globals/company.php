<?php

// Please see the documentation for more information about registering an option page using ACF:
// https://www.advancedcustomfields.com/resources/acf_add_options_page/

use Extended\ACF\Fields\Accordion;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\WysiwygEditor;
use Extended\ACF\Location;

acf_add_options_page([
  'icon_url' => 'dashicons-admin-settings', // https://developer.wordpress.org/resource/dashicons/
  'menu_slug' => 'company',
  'page_title' => 'Company Info',
  'position' => 21,
]);

register_extended_field_group([
  'title' => 'Company Settings',
  'fields' => [
    Text::make('Company Name')
      ->required(),
    Image::make('Logo')
      ->instructions('For use on light backgrounds')
      ->column(50),
    Image::make('Logo on dark')
      ->instructions('For use on dark backgrounds')
      ->wrapper(['class' => 'dark-bg'])
      ->column(50),
    WysiwygEditor::make('Tagline')
      ->instructions('Short company tagline, for use in footer.')
      ->mediaUpload(false)
      ->toolbar(['bold', 'italic', 'link', 'underline'])
      ->tabs('visual'),
    Accordion::make('Social Links'),
    Text::make('Facebook'),
    Text::make('Instagram'),
    Text::make('Twitter'),
    Text::make('LinkedIn'),
    Text::make('YouTube'),
    Accordion::make('Endpoint')
      ->endpoint()
      ->multiExpand(),
  ],
  'location' => [
    Location::where('options_page', 'company'),
  ],
]);
