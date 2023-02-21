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

        // can't double post while editing
        if ($post->exists) return;

        if (is_null($post->discussion->lastPost)) return;

        if ($event->actor->cannot('doublePost', $post->discussion)) throw new PermissionDeniedException();
    }
}
