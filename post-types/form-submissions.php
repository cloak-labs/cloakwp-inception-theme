<?php
use CloakWP\ACF\FieldGroup;
use CloakWP\Content\PostType;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Fields\TrueFalse;

return PostType::make('form-submission')
  ->menuIcon('dashicons-admin-users')
  ->public(false)
  ->showUi(true)
  ->showInRest(true)
  ->blockEditor(false)
  ->supports(['title', 'editor' => false])
  ->publiclyQueryable(false)
  ->menuPosition(9)
  ->adminCols([
    // The default Title column:
    'title',
    // A meta field column:
    'published' => array(
      'title' => 'Date submitted',
      'post_field' => 'post_date',
      'date_format' => 'd/m/Y g:i A'
    ),
  ])
  ->fieldGroups([
    FieldGroup::make('Contact Form Submission')
      ->fields([
        Text::make('First Name', 'first_name'),
        Text::make('Last Name', 'last_name'),
        Text::make('Email', 'company'),
        Text::make('Phone', 'email'),
        Text::make('Company', 'phone'),
        Textarea::make('Message', 'message'),
        Text::make('Referral Source', 'referral_source'),
        TrueFalse::make('Subscribed to newsletter', 'subscribe'),
      ])
  ]);