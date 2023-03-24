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
        /**
         * @var ?Post
         */
        $lastPost = $discussion->posts()
            ->where('type', 'comment')
            ->latest()
            ->limit(1)
            ->first();

        if ($actor->hasPermission('discussion.doublePost')) return true;

        // what is this ?!
        // if ($actor->cannot('edit', $lastPost)) return true;
        
        if ($actor->id == $lastPost->user_id) {
            $timeLimit = $this->settings->get('the-turk-nodp.time_limit');

            $isExpired = $lastPost->created_at->addMinutes($timeLimit)->isPast();

            if ($timeLimit == 0 || !$isExpired) return false;
        }

        return true;
    }
}
