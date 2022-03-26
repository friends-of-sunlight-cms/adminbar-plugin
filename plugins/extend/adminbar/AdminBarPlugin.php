<?php

namespace SunlightExtend\Adminbar;

use Sunlight\Extend;
use Sunlight\Page\Page;
use Sunlight\Plugin\Action\PluginAction;
use Sunlight\Plugin\ExtendPlugin;
use Sunlight\Router;
use Sunlight\Template;
use Sunlight\User;

class AdminBarPlugin extends ExtendPlugin
{
    public function initialize(): void
    {
        parent::initialize();

        $config = $this->getConfig();
        Extend::regm([
            'tpl.head' => [$this, 'onHead'],
            'page.all.' . $config['bar_position'] => [$this, 'onToolbarRender'],
        ]);
    }

    /**
     * @param array $args
     */
    public function onHead(array $args): void
    {
        $args['css'][] = $this->getWebPath() . '/Resources/css/adminbar.css';
    }

    /**
     * @param array $args
     */
    public function onToolbarRender(array $args): void
    {
        $config = $this->getConfig();

        if (!User::isLoggedIn() || (User::getLevel() < $config['min_level'])) {
            return;
        }

        $types = Page::getTypes();
        $type = $args['page']['type'];
        $currentType = $types[$type];

        $map = [
            _lang('global.type') => ['value' => $currentType, 'is_link' => false],
            _lang('global.id') => ['value' => $args['page']['id'], 'is_link' => false],
        ];

        // all page - edit (without article and topic)
        if (
            !Template::currentIsArticle()
            && !Template::currentIsTopic()
            && User::hasPrivilege('admin' . $currentType)
        ) {
            $map[_lang('global.edit')] = [
                'value' => Router::admin('content-edit' . $currentType, ['query' => ['id' => $args['page']['id']]]),
                'is_link' => true
            ];
        }

        // page gallery - manage images
        if (
            $type == Page::GALLERY
            && User::hasPrivilege('admingallery')
        ) {
            $map[_lang('adminbar.manage.images')] = [
                'value' => Router::admin('content-manageimgs', ['query' => ['g' => $args['page']['id']]]),
                'is_link' => true
            ];
        }

        // page category|article
        if (
            $type == Page::CATEGORY
            && User::hasPrivilege('adminart')
        ) {
            if (!Template::currentIsArticle()) { // 'add article'
                $map[_lang('adminbar.add.article')] = [
                    'value' => Router::admin('content-articles-edit', ['query' => ['new_cat' => $args['page']['id']]]),
                    'is_link' => true
                ];
            } else { // 'edit' article
                $currentType = 'article'; // update current type

                $map[_lang('global.type')] = [
                    'value' => 'article',
                ];
                $map[_lang('global.id')] = [
                    'value' => $args['article']['id'],
                ];
                $map[_lang('global.edit')] = [
                    'value' => Router::admin('content-articles-edit', ['query' => ['id' => $args['article']['id'], 'returnid' => 'load', 'returnpage' => 1,]]),
                    'is_link' => true
                ];
            }
        }

        // only topic
        if (Template::currentIsTopic()) {
            $currentType = 'topic'; // update current type

            $map[_lang('global.type')] = [
                'value' => 'topic',
            ];
            $map[_lang('global.id')] = [
                'value' => $GLOBALS['segment'],
            ];
        }

        Extend::call('adminbar.items', ['items' => &$map, 'page_type' => $currentType]);

        // render toolbar
        $args['output'] .= "<div id='adminbar-toolbar'>
                                <div class='adminbar-section'>[AdminBar]</div>";

        foreach ($map as $label => $val) {
            $isLink = $val['is_link'] ?? false;

            $args['output'] .= "<div class='adminbar-section" . ($isLink ? '-link right' : '') . "'>";
            if ($isLink) {
                $args['output'] .= '<a href="' . _e($val['value']) . '" target="_blank">' . $label . '</a>';
            } else {
                $args['output'] .= $label . ($val['value'] !== null ? ': ' . $val['value'] : '');
            }
            $args['output'] .= "</div>";
        }

        $args['output'] .= "<div class='cleaner'></div>";
        $args['output'] .= "</div>";
    }

    /**
     * ============================================================================
     *  EXTEND CONFIGURATION
     * ============================================================================
     */
    protected function getConfigDefaults(): array
    {
        return [
            'min_level' => 1000,
            'bar_position' => 'after'
        ];
    }

    public function getAction(string $name): ?PluginAction
    {
        if ($name === 'config') {
            return new Configuration($this);
        }
        return parent::getAction($name);
    }

}
