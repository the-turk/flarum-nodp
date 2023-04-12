<?php

namespace TheTurk\NoDP\Discussion\Access;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
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
        return $this->canDoublePost($user, $discussion);
    }

    private function canDoublePost(User $actor, Discussion $discussion): bool
    {
        if ($actor->hasPermission('discussion.doublePost')) return true;

        /**
         * @var Post
         */
        $lastPost = $discussion->posts()
            ->where('type', 'comment')
            ->latest()
            ->limit(1)
            ->first();

        if ($actor->id !== $lastPost->user_id) return true;

        if ($actor->cannot('edit', $lastPost)) return true;

        $timeLimit = (int) $this->settings->get('the-turk-nodp.time_limit');

        if ($timeLimit === 0) return false;

        return $lastPost->created_at->addMinutes($timeLimit)->isPast();
    }
}
