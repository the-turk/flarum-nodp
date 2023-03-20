<?php

namespace TheTurk\NoDP\Discussion\Access;

use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class DiscussionDoublePostPolicy extends AbstractPolicy
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

    protected function doublePost(User $user, Discussion $discussion)
    {
        return $user->hasPermission('discussion.doublePost')
            || $this->canDoublePost($user, $discussion);
    }

    private function canDoublePost(User $user, Discussion $discussion): bool
    {
        $lastPost = $discussion->lastPost;

        if (!($lastPost instanceof CommentPost)) return true;

        /**
         * Prevent users from by-passing the double-posting check
         * by soft-deleting and restoring their previous post.
         * 
         * @var \Flarum\Post\Post
         */
        $hiddenPost = $discussion->posts
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $discussion->last_posted_at)
            ->whereNotNull('hidden_at')
            ->whereColumn('hidden_user_id', 'user_id')
            ->orderBy('hidden_at', 'desc')
            ->first();

        if (!is_null($hiddenPost)) {
            $lastPost = $hiddenPost;
        }

        if ($lastPost->user_id != $user->id) return true;

        if ($user->cannot('edit', $lastPost)) return false;

        $timeLimit = (int) $this->settings->get('the-turk-nodp.time_limit');

        if ($timeLimit == 0) return false;

        return $lastPost->created_at->addMinutes($timeLimit)->isPast();
    }
}
