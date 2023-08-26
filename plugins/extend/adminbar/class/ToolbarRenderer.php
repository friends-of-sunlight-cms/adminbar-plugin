<?php

namespace SunlightExtend\Adminbar;

use Sunlight\Extend;
use Sunlight\Page\Page;
use Sunlight\Router;
use Sunlight\Template;
use Sunlight\User;
use Sunlight\Util\Request;
use Sunlight\WebState;

class ToolbarRenderer
{
    /** @var array */
    private $map = [];

    public function render(array $args): void
    {
        $this->dispatch($args);

        // render toolbar
        $args['output'] .= '<div id="adminbar-toolbar">
                                <div class="adminbar-section">[AdminBar]</div>';

        foreach ($this->map as $label => $val) {
            $isLink = $val['is_link'] ?? false;

            $args['output'] .= '<div class="adminbar-section' . ($isLink ? '-link right' : '') . '">';
            if ($isLink) {
                $args['output'] .= '<a href="' . _e($val['value']) . '" target="_blank">' . _e($label) . '</a>';
            } else {
                $args['output'] .= _e($label) . _e($val['value'] !== null ? ': ' . $val['value'] : '');
            }
            $args['output'] .= '</div>';
        }

        $args['output'] .= '<div class="cleaner"></div>';
        $args['output'] .= '</div>';
    }

    private function dispatch(array $args): void
    {
        global $_index;

        if ($_index->type > WebState::MODULE) {
            return;
        }

        if (Template::currentIsModule()) {
            $type = 'module';
            $currentType = 'module';
            $moduleAction = Request::get('action');
            $pageId = $args['id'] . ($moduleAction !== null ? '-' . _e($moduleAction) : '');
        } else {
            $type = $args['page']['type'];
            $currentType = Page::TYPES[$type];
            $pageId = $args['page']['id'];
        }

        $this->map = [
            _lang('global.type') => ['value' => $currentType, 'is_link' => false],
            _lang('global.id') => ['value' => $pageId, 'is_link' => false],
        ];

        // all page - edit (without article and topic)
        if (!Template::currentIsArticle() && !Template::currentIsTopic() && User::hasPrivilege('admin' . $currentType)) {
            $this->pageActions($currentType, $args);
        }

        // page gallery - manage images
        if ($type == Page::GALLERY && User::hasPrivilege('admingallery')) {
            $this->galleryActions($args);
        }

        // page category|article
        if ($type == Page::CATEGORY && User::hasPrivilege('adminart')) {
            if (!Template::currentIsArticle()) { // 'add article'
                $this->categoryActions($args);
            } else { // 'edit' article
                $currentType = 'article'; // update current type
                $this->articleActions($args);
            }
        }

        // only topic
        if (Template::currentIsTopic()) {
            $currentType = 'topic'; // update current type
            $this->topicActions($args);
        }

        // event
        Extend::call('adminbar.items', ['items' => &$this->map, 'page_type' => $currentType]);
    }

    private function pageActions(string $currentType, array $args): void
    {
        $this->map[_lang('global.edit')] = [
            'value' => Router::admin('content-edit' . $currentType, [
                'query' => ['id' => $args['page']['id']]
            ]),
            'is_link' => true
        ];
    }

    private function galleryActions(array $args): void
    {
        $this->map[_lang('adminbar.manage.images')] = [
            'value' => Router::admin('content-manageimgs', [
                'query' => ['g' => $args['page']['id']]
            ]),
            'is_link' => true
        ];
    }

    private function categoryActions(array $args): void
    {
        $this->map[_lang('adminbar.add.article')] = [
            'value' => Router::admin('content-articles-edit', [
                'query' => ['new_cat' => $args['page']['id']]
            ]),
            'is_link' => true
        ];
    }

    private function articleActions(array $args): void
    {
        $this->map[_lang('global.type')] = [
            'value' => 'article',
        ];
        $this->map[_lang('global.id')] = [
            'value' => $args['article']['id'],
        ];
        $this->map[_lang('global.edit')] = [
            'value' => Router::admin('content-articles-edit', [
                'query' => ['id' => $args['article']['id'], 'returnid' => 'load', 'returnpage' => 1,]
            ]),
            'is_link' => true
        ];
    }

    private function topicActions(array $args): void
    {
        // replace current type and id
        $this->map[_lang('global.type')] = [
            'value' => 'topic',
        ];
        $this->map[_lang('global.id')] = [
            'value' => $GLOBALS['segment'],
        ];
    }
}
