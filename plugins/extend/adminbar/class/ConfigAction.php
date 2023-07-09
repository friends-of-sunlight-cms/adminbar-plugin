<?php

namespace SunlightExtend\Adminbar;

use Fosc\Feature\Plugin\Config\FieldGenerator;
use Sunlight\Plugin\Action\ConfigAction as BaseConfigAction;
use Sunlight\User;
use Sunlight\Util\ConfigurationFile;

class ConfigAction extends BaseConfigAction
{
    protected function getFields(): array
    {
        $langPrefix = "%p:adminbar.config";

        $gen = new FieldGenerator($this->plugin);
        $gen->generateField('bar_position', $langPrefix, '%select', [
            'class' => 'inputsmall',
            'select_options' => [
                'before' => _lang('adminbar.config.bar_position.before'),
                'after' => _lang('adminbar.config.bar_position.after'),
            ],
        ])
            ->generateField('min_level', $langPrefix, '%number', ['class' => 'inputsmall']);

        return $gen->getFields();
    }

    protected function mapSubmittedValue(ConfigurationFile $config, string $key, array $field, $value): ?string
    {
        $config = $this->plugin->getConfig();
        switch ($key) {
            case 'bar_position':
                $config[$key] = $value ?? $config['bar_position'];
                return null;

            case 'min_level':
                if ($value < 1) {
                    $value = 1;
                } elseif ($value > User::MAX_ASSIGNABLE_LEVEL) {
                    $value = User::MAX_ASSIGNABLE_LEVEL;
                }
                $config[$key] = (int)$value;
                return null;
        }

        return parent::mapSubmittedValue($config, $key, $field, $value);
    }
}