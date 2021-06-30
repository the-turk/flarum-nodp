<?php

namespace TheTurk\NoDP\Listener;

use Carbon\Carbon;
use Flarum\Extension\ExtensionManager;
use Flarum\Post\Event\Saving as PostSaving;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\PermissionDeniedException;

class CheckDoublePosting
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
    
    public function handle(PostSaving $event)
    {
        $actor = $event->actor;
        $post = $event->post;

        if ($post->exists || $actor->hasPermission('discussion.doublePost')) return;

        $lastPostedAt = $post->discussion->last_posted_at;
        $lastPostedUserId = $post->discussion->last_posted_user_id;

        $timeLimit = $this->settings->get('the-turk-nodp.time_limit', 1440);

        $isExpired = Carbon::parse($lastPostedAt)->addMinutes($timeLimit)->isPast();

        if ($timeLimit == 0 || (!$isExpired && $actor->id == $lastPostedUserId)) throw new PermissionDeniedException();
    }
}