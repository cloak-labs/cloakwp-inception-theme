<?php

use CloakWP\CloakWP;
use CloakWP\Content\PostType;

$CloakWP = CloakWP::getInstance();

return PostType::make('post')
  ->rewrite([
    'permastruct' => '/blog/%post%'
  ])
  ->afterChange(function ($data) use ($CloakWP) {
    /**
     * Saving a post will only revalidate its own single page by default, so below we manually 
     * revalidate the root "blog" page to ensure the post list updates as well:
     */
    $CloakWP->getActiveFrontend()->revalidatePages(['/blog']);
  });