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
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Post\Event\Saving as PostSaving;
use TheTurk\NoDP\Api\Serializer\DiscussionAttributes;
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
        ->attributes(DiscussionAttributes::class),

    (new Extend\Settings())
        ->serializeToForum('nodp.time_limit', 'the-turk-nodp.time_limit'),

    (new Extend\Policy)
        ->modelPolicy(Discussion::class, DiscussionDoublePostPolicy::class)
];
