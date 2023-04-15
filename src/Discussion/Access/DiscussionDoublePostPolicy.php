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

    /**
     * This ends with 'Custom' because we don't want to override
     * the 'doublePost' policy, that runs some checks we need
     * like if we have a tag-scoped permission (flarum-tags).
     *
     * If you have a better name/approach, you are welcome.
     */
    public function doublePostCustom(User $actor, Discussion $discussion)
    {
        /**
         * @var Post
         */
        $lastPost = $discussion->posts()
            ->where('type', 'comment')
            ->latest()
            ->first();

        if ($actor->id !== $lastPost->user_id) return $this->allow();

        if ($actor->cannot('edit', $lastPost)) return $this->allow();

        $timeLimit = (int) $this->settings->get('the-turk-nodp.time_limit');

        if ($timeLimit === 0) return $this->deny();

        return $lastPost->created_at->addMinutes($timeLimit)->isPast() ? $this->allow() : $this->deny();
    }
}
