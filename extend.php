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

use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Extend;
use Flarum\Discussion\Discussion;
use Flarum\Post\Event\Saving as PostSaving;
use TheTurk\NoDP\Discussion\Access\DiscussionDoublePostPolicy;
use TheTurk\NoDP\Listener;

return [
    (new Extend\Frontend('forum'))
        ->css(__DIR__.'/less/forum.less')
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Locales(__DIR__.'/locale')),

    (new Extend\Event())
        ->listen(PostSaving::class, Listener\DoublePosting::class),

    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->attributes(function (DiscussionSerializer $serializer, Discussion $discussion, array $attributes) {
            $attributes['canDoublePost'] = $serializer->getActor()->can('doublePost', $discussion);

            return $attributes;
        }),

    (new Extend\Settings())
        ->default('the-turk-nodp.time_limit', 1440)
        ->serializeToForum('nodp.time_limit', 'the-turk-nodp.time_limit'),

    (new Extend\Policy)
        ->modelPolicy(Discussion::class, DiscussionDoublePostPolicy::class)
];
