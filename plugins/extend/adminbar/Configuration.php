<?php

namespace SunlightExtend\Adminbar;

use Sunlight\Plugin\Action\ConfigAction;
use Sunlight\User;
use Sunlight\Util\ConfigurationFile;

class Configuration extends ConfigAction
{
    protected function getFields(): array
    {
        $config = $this->plugin->getConfig();

        return [
            'bar_position' => [
                'label' => _lang('adminbar.cfg.bar_position'),
                'input' => _buffer(function () use ($config) { ?>
                    <select name="config[bar_position]">
                        <option value="before" <?= $config['bar_position'] === 'before' ? ' selected' : '' ?>><?= _lang('adminbar.cfg.bar_position.before') ?></option>
                        <option value="after" <?= $config['bar_position'] === 'after' ? ' selected' : '' ?>><?= _lang('adminbar.cfg.bar_position.after') ?></option>
                    </select>
                <?php }),
            ],
            'min_level' => [
                'label' => _lang('adminbar.cfg.min_level'),
                'input' => '<input type="number" min="1" max="' . User::MAX_ASSIGNABLE_LEVEL . '" name="config[min_level]" value="' . $config['min_level'] . '" class="inputsmall"">',
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