import { extend, override } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import Alert from 'flarum/common/components/Alert';
import DiscussionControls from 'flarum/forum/utils/DiscussionControls';
import EditPostComposer from 'flarum/forum/components/EditPostComposer';
import LogInModal from 'flarum/forum/components/LogInModal';
import Model from 'flarum/common/Model';
import ReplyComposer from 'flarum/forum/components/ReplyComposer';
import User from 'flarum/common/models/User';
import Discussion from 'flarum/common/models/Discussion';

app.initializers.add(
  'the-turk-nodp',
  () => {
    User.prototype.canDoublePost = Model.attribute('canDoublePost');

    const isDoublePosting = (discussion: Discussion, user: User) => {
      if (!discussion || !user || user.canDoublePost()) return false;

      const lastPostedUser = discussion.lastPostedUser();
      const lastPostedAt = discussion.lastPostedAt();

      const timeLimit = app.forum.attribute('nodp.time_limit'); // in minutes
      const isExpired = dayjs(lastPostedAt.getTime()).add(timeLimit, 'minute').isBefore(dayjs());

      return timeLimit == 0 || (!isExpired && lastPostedUser == user) ? true : false;
    };

    // Add a warning message.
    extend(EditPostComposer.prototype, 'headerItems', function (items) {
      items.add(
        'nodp',
        <div className="Alert">
          <div className="Alert-body">
            <h4>{app.translator.trans('the-turk-nodp.forum.composer_edit.double_posting_warning_title')}</h4>
            <p>{app.translator.trans('the-turk-nodp.forum.composer_edit.double_posting_warning_description')}</p>
          </div>
        </div>
      );
    });

    // We need to override replyAction directly to support `flarum/mentions`.
    override(DiscussionControls, 'replyAction', function (goToLast, forceRefresh) {
      const user = app.session.user;

      return new Promise((resolve, reject) => {
        if (user) {
          if (isDoublePosting(this, user)) {
            const post = this.lastPost();

            if (post.contentType() === 'comment' && post.canEdit()) {
              app.composer.load(EditPostComposer, { post });
              app.composer.show();

              // showing the composer message like this because what will happen
              // if you're just editing a post instead of attempting double posting?
              $('body').find('.item-nodp').css('display', 'block');

              return resolve(app.composer);
            }

            // user can't edit their post
            // and not allowed to double post.
            app.alerts.show(Alert, { type: 'error' }, app.translator.trans('the-turk-nodp.forum.discussion.cannot_reply_alert_message'));

            return reject();
          }

          if (this.canReply()) {
            if (!app.composer.composingReplyTo(this) || forceRefresh) {
              app.composer.load(ReplyComposer, {
                user,
                discussion: this,
              });
            }
            app.composer.show();

            if (goToLast && app.viewingDiscussion(this) && !app.composer.isFullScreen()) {
              app.current.get('stream').goToNumber('reply');
            }

            return resolve(app.composer);
          } else {
            return reject();
          }
        }

        app.modal.show(LogInModal);

        return reject();
      });
    });
  },
  -10
);
