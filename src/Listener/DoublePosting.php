<?php

namespace TheTurk\NoDP\Listener;

use Carbon\Carbon;
use Flarum\Post\Event\Saving as PostSaving;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\User;

class DoublePosting
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param PostSaving $actor
     * @throws PermissionDeniedException if the user is double posting
     */
    public function handle(PostSaving $event)
    {
        $post = $event->post;

        // can't double post while editing
        if ($post->exists) return;

        $lastPost = $post->discussion->lastPost;

        if (!$lastPost) return;

        if ($this->willDoublePost($event->actor, $lastPost)) throw new PermissionDeniedException();
    }

    /**
     * @param User $actor
     * @param Post $lastPost
     * @return bool
     */
    public function willDoublePost(User $actor, Post $lastPost)
    {
        if ($lastPost instanceof CommentPost) {
            if ($actor->hasPermission('discussion.doublePost')) return false;

            // Prevent users from by-passing the double-posting check
            // by soft-deleting and restoring their previous post.
            $hiddenLastPost = Post::where('user_id', $actor->id)
                ->where('discussion_id', $lastPost->discussion->id)
                ->where('created_at', '>=', $lastPost->created_at)
                ->whereNotNull('hidden_at')
                ->whereColumn('hidden_user_id', 'user_id')
                ->orderBy('hidden_at', 'desc')
                ->first();

            $lastPost = $hiddenLastPost === null ? $lastPost : $hiddenLastPost;

            if ($actor->cannot('edit', $lastPost)) return false;

            if ($actor->id == $lastPost->user_id) {
                $timeLimit = $this->settings->get('the-turk-nodp.time_limit');

                $isExpired = Carbon::parse($lastPost->created_at)->addMinutes($timeLimit)->isPast();

                if ($timeLimit == 0 || !$isExpired) return true;
            }
        }

        return false;
    }
}
