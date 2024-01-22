<?php
use CloakWP\CloakWP;
use CloakWP\Content\PostType;

$CloakWP = CloakWP::getInstance();

return PostType::make('faq')
  ->menuIcon('dashicons-format-chat')
  ->public(false)
  ->showUi(true)
  ->showInRest(true)
  ->blockEditor(false)
  ->supports(['editor', 'title', 'thumbnail' => false])
  ->titlePlaceholder("Question")
  ->publiclyQueryable(false)
  ->afterChange(function ($postId) use ($CloakWP) {
    // when an FAQ post is created/updated, we rebuild the /faqs listing page:
    $CloakWP->getActiveFrontend()->revalidatePages([$postId, '/faqs']);
  });