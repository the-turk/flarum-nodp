<?php

namespace TheTurk\NoDP\Listener;

use Flarum\Post\Event\Saving as PostSaving;
use Flarum\User\Exception\PermissionDeniedException;

class DoublePosting
{
    /**
     * @param PostSaving $actor
     * @throws PermissionDeniedException if the user is double posting
     */
    public function handle(PostSaving $event)
    {
        $post = $event->post;

        // new discussion
        if (is_null($post->discussion->first_post_id)) return;

        // can't double post while editing
        if ($post->exists) return;

        $event->actor->assertCan('doublePost', $post->discussion);
    }
}
