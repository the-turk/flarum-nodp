<?php

namespace TheTurk\NoDP\Discussion\Access;

use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
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
        $lastPost = $discussion->lastPost;

        if ($lastPost instanceof CommentPost) {
            if ($actor->hasPermission('discussion.doublePost')) return true;

            /**
             * Prevent users from by-passing the double-posting check
             * by soft-deleting and restoring their previous post.
             * 
             * @var ?Post
             */
            $hiddenLastPost = Post::where('user_id', $actor->id)
                ->where('discussion_id', $lastPost->discussion->id)
                ->where('created_at', '>=', $lastPost->created_at)
                ->whereNotNull('hidden_at')
                ->whereColumn('hidden_user_id', 'user_id')
                ->orderBy('hidden_at', 'desc')
                ->first();

            if (!is_null($hiddenLastPost)) {
                $lastPost = $hiddenLastPost;
            }

            // what is this ?!
            // if ($actor->cannot('edit', $lastPost)) return true;
            
            if ($actor->id == $lastPost->user_id) {
                $timeLimit = $this->settings->get('the-turk-nodp.time_limit');

                $isExpired = $lastPost->created_at->addMinutes($timeLimit)->isPast();

                if ($timeLimit == 0 || !$isExpired) return false;
            }
        }

        return true;
    }
}
