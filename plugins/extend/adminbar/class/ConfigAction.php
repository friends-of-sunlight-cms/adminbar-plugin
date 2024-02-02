<?php

namespace SunlightExtend\Adminbar;

use Sunlight\Util\Form;
use Sunlight\Plugin\Action\ConfigAction as BaseConfigAction;
use Sunlight\User;
use Sunlight\Util\ConfigurationFile;
use Sunlight\Util\Request;

class ConfigAction extends BaseConfigAction
{
    protected function getFields(): array
    {
        $config = $this->plugin->getConfig();

        return [
            'bar_position' => [
                'label' => _lang('adminbar.config.bar_position'),
                'input' => Form::select('config[bar_position]', [
                    'before' => _lang('adminbar.config.bar_position.before'),
                    'after' => _lang('adminbar.config.bar_position.after'),
                ], $config['bar_position'], ['class' => 'inputsmall']),
            ],
            'min_level' => [
                'label' => _lang('adminbar.config.min_level'),
                'input' => Form::input('number', 'config[min_level]', Request::post('min_level', $config['min_level']), ['checked' => Form::loadCheckbox('config', $config['min_level'], 'min_level'), 'min' => -1, 'max' => User::MAX_ASSIGNABLE_LEVEL, 'class' => 'inputsmall']),
            ],
        ];
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
