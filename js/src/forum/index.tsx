import { extend, override } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import Alert from 'flarum/common/components/Alert';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import EditPostComposer from 'flarum/forum/components/EditPostComposer';
import Model from 'flarum/common/Model';
import User from 'flarum/common/models/User';
import Post from 'flarum/common/models/Post';

app.initializers.add(
  'the-turk-nodp',
  () => {
    User.prototype.canDoublePost = Model.attribute('canDoublePost');

    const isDoublePosting = (post: Post, user: User) => {
      if (user.canDoublePost()) return false;

      const postUser = post.user();
      const postCreatedAt = post.createdAt();

      const timeLimit: number = app.forum.attribute('nodp.time_limit'); // in minutes
      const isExpired: boolean = dayjs(postCreatedAt).add(timeLimit, 'minute').isBefore(dayjs());

      return postUser === user && !isExpired;
    };

    // Add a warning message.
    extend(EditPostComposer.prototype, 'headerItems', function (items) {
      if (!this.attrs.nodp) return;

      const title = app.translator.trans('the-turk-nodp.forum.composer_edit.double_posting_warning_title');
      const description = app.translator.trans('the-turk-nodp.forum.composer_edit.double_posting_warning_description');

      items.add(
        'nodp',
        <Alert dismissible={false} title={title} type="warning">
          {description}
        </Alert>
      );
    });

    // We need to override replyAction directly to support `flarum/mentions`.
    override(DiscussionControls, 'replyAction', function (original, goToLast, forceRefresh) {
      const user = app.session.user;

      if (!user) return original(goToLast, forceRefresh);

      return new Promise((resolve, reject) => {
        const posts: Array<Post> = app.current.get('stream').posts();
        const post = posts[posts.length - 1];

        if (!isDoublePosting(post, user)) return original(goToLast, forceRefresh);

        if (post && post.contentType() === 'comment' && post.canEdit()) {
          app.composer.load(EditPostComposer, { post, nodp: true });
          app.composer.show();

          return resolve(app.composer);
        }

        // user can't edit their post
        // and not allowed to double post.
        app.alerts.show(Alert, { type: 'error' }, app.translator.trans('the-turk-nodp.forum.discussion.cannot_reply_alert_message'));

        return reject();
      });
    });
  },
  -10
);
