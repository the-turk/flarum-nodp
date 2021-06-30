<?php

/*
 * This file is part of the-turk/flarum-nodp.
 *
 * Copyright (c) 2021 Hasan Özbey
 *
 * LICENSE: For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace TheTurk\NoDP;

use Flarum\Extend;
use Flarum\Api\Serializer\CurrentUserSerializer;
use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Post\Event\Saving as PostSaving;
use TheTurk\NoDP\Listener;

return [
    (new Extend\Frontend('forum'))
        ->css(__DIR__.'/less/forum.less')
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Locales(__DIR__.'/locale')),

    (new Extend\Event())
        ->listen(PostSaving::class, Listener\CheckDoublePosting::class),

    (new Extend\ApiSerializer(CurrentUserSerializer::class))
        ->attributes(function (CurrentUserSerializer $serializer) {
            $attributes['canDoublePost'] = $serializer->getActor()->hasPermission('discussion.doublePost');

            return $attributes;
        }),

    (new Extend\ApiController(ShowDiscussionController::class))
        ->addInclude('lastPost')
        ->addInclude('lastPostedUser'),     

    (new Extend\Settings())
        ->serializeToForum('nodp.time_limit', 'the-turk-nodp.time_limit')
];
