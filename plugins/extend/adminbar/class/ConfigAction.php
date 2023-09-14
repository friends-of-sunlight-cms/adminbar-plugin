<?php

namespace SunlightExtend\Adminbar;

use Sunlight\Util\Form;
use Sunlight\Plugin\Action\ConfigAction as BaseConfigAction;
use Sunlight\User;
use Sunlight\Util\ConfigurationFile;

class ConfigAction extends BaseConfigAction
{
    protected function getFields(): array
    {
        $config = $this->plugin->getConfig();

        return [
            'bar_position' => [
                'label' => _lang('adminbar.config.bar_position'),
                'input' => _buffer(function () use ($config) { ?>
                    <select name="config[bar_position]" class="inputsmall">
                        <option value="before" <?= Form::selectOption($config['bar_position'] === 'before') ?>><?= _lang('adminbar.config.bar_position.before') ?></option>
                        <option value="after" <?= Form::selectOption($config['bar_position'] === 'after') ?>><?= _lang('adminbar.config.bar_position.after') ?></option>
                    </select>
                <?php }),
            ],
            'min_level' => [
                'label' => _lang('adminbar.config.min_level'),
                'input' => '<input type="number" name="config[min_level]" min="1" max="' . User::MAX_ASSIGNABLE_LEVEL . '" value="' . Form::restorePostValue('min_level', $config['min_level'], false) . '" class="inputsmall">',
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
