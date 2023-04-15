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

    public function doublePost(User $actor, Discussion $discussion)
    {
        return $this->canDoublePost($actor, $discussion);
    }

    private function canDoublePost(User $actor, Discussion $discussion)
    {
        if ($actor->hasPermission('discussion.doublePost')) return $this->forceAllow();

        /**
         * @var Post
         */
        $lastPost = $discussion->posts()
            ->where('type', 'comment')
            ->latest()
            ->first();

        if ($actor->id !== $lastPost->user_id) return $this->forceAllow();

        if ($actor->cannot('edit', $lastPost)) return $this->forceAllow();

        $timeLimit = (int) $this->settings->get('the-turk-nodp.time_limit');

        if ($timeLimit === 0) return $this->forceDeny();

        return $lastPost->created_at->addMinutes($timeLimit)->isPast() ? $this->forceAllow() : $this->forceDeny();
    }
}
