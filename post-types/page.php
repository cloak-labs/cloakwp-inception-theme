<?php

use CloakWP\Content\PostType;
use CloakWP\Utils;

return PostType::make('page')
  ->afterChange(function ($data) {
    // add some custom ISR stuff on top of default post revalidation
    Utils::write_log('Save page ##');
  });