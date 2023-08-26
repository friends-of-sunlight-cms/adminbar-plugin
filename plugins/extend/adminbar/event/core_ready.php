<?php

use Sunlight\Extend;
use Sunlight\User;
use SunlightExtend\Adminbar\ToolbarRenderer;

return function (array $args) {
    $config = $this->getConfig();
    if (!User::isLoggedIn() || (User::getLevel() < $config['min_level'])) {
        return;
    }
    $this->enableEventGroup('adminbar');

    $renderer = new ToolbarRenderer();
    Extend::regm([
        'page.all.' . _e($config['bar_position']) => [$renderer, 'render'],
        'mod.all.' . _e($config['bar_position']) => [$renderer, 'render'],
    ]);
};
