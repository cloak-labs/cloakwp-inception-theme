<?php

use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\TrueFalse;

return [
  Number::make('Slides in view')
    ->min(1)
    ->max(3)
    ->default(2)
    ->column(33.33),
  TrueFalse::make('Infinite Loop', 'loop')
    ->stylized('Yes', 'No')
    ->column(33.33),
  TrueFalse::make('Blend Effect', 'blend')
    ->stylized('Yes', 'No')
    ->column(33.33),
  TrueFalse::make('Autoplay')
    ->stylized('Yes', 'No')
    ->column(33.33),
  Number::make('Autoplay Interval', 'interval')
    ->suffix('sec')
    ->default(4)
    ->column(33.33)
    ->min(1)
    ->conditionalLogic([
      ConditionalLogic::where('autoplay', '==', 1)
    ])
];
