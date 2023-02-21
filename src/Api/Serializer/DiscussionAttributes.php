<?php

namespace TheTurk\NoDP\Api\Serializer;

use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;

class DiscussionAttributes
{
    public function __invoke(DiscussionSerializer $serializer, Discussion $discussion, array $attributes)
    {
        $attributes['canDoublePost'] = $serializer->getActor()->can('doublePost', $discussion);

        return $attributes;
    }
}
