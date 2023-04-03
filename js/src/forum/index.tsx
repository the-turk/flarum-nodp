import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import Alert from 'flarum/common/components/Alert';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import EditPostComposer from 'flarum/forum/components/EditPostComposer';
import Model from 'flarum/common/Model';
import Post from 'flarum/common/models/Post';
import ComposerBody from 'flarum/forum/components/ComposerBody';
import Discussion from 'flarum/common/models/Discussion';

app.initializers.add(
  'the-turk-nodp',
  () => {
    Discussion.prototype.canDoublePost = Model.attribute('canDoublePost');

    // Add a warning message.
    extend(ComposerBody.prototype, 'headerItems', function (items) {
      if (!this.attrs.nodp) return;

      const title = app.translator.trans('the-turk-nodp.forum.composer_edit.double_posting_warning_title');
      const description = app.translator.trans('the-turk-nodp.forum.composer_edit.double_posting_warning_description');

      items.add(
        'nodp',
        <Alert dismissible={false} title={title} type="warning">
          {description}
        </Alert>,
        -10
      );
    });

    extend(DiscussionControls, 'replyAction', function () {
      const user = app.session.user;

      if (!user) return;

      const stream = app.current.get('stream');

      if (stream.discussion.canDoublePost()) return;

      const posts: Array<Post> = stream.posts()
        .filter((post: Post) => {
          return post.contentType() === "comment" && post.user().id() === user.id()
        });

      if (!posts.length) return;

      // last post
      const post = posts[posts.length - 1];

      if (post?.canEdit()) {
        app.composer.load(EditPostComposer, { post, nodp: true });
        app.composer.show();
      } else {
        // user can't edit their post
        // and not allowed to double post.
        app.alerts.show(Alert, { type: 'error' }, app.translator.trans('the-turk-nodp.forum.discussion.cannot_reply_alert_message'));
        app.composer.close();
      }
    });
  },
  -10
);
