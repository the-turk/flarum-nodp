module.exports=function(o){var e={};function t(r){if(e[r])return e[r].exports;var n=e[r]={i:r,l:!1,exports:{}};return o[r].call(n.exports,n,n.exports,t),n.l=!0,n.exports}return t.m=o,t.c=e,t.d=function(o,e,r){t.o(o,e)||Object.defineProperty(o,e,{enumerable:!0,get:r})},t.r=function(o){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(o,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(o,"__esModule",{value:!0})},t.t=function(o,e){if(1&e&&(o=t(o)),8&e)return o;if(4&e&&"object"==typeof o&&o&&o.__esModule)return o;var r=Object.create(null);if(t.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:o}),2&e&&"string"!=typeof o)for(var n in o)t.d(r,n,function(e){return o[e]}.bind(null,n));return r},t.n=function(o){var e=o&&o.__esModule?function(){return o.default}:function(){return o};return t.d(e,"a",e),e},t.o=function(o,e){return Object.prototype.hasOwnProperty.call(o,e)},t.p="",t(t.s=10)}([function(o,e){o.exports=flarum.core.compat["forum/app"]},function(o,e){o.exports=flarum.core.compat["common/extend"]},function(o,e){o.exports=flarum.core.compat["components/EditPostComposer"]},function(o,e){o.exports=flarum.core.compat["utils/DiscussionControls"]},function(o,e){o.exports=flarum.core.compat["components/LogInModal"]},function(o,e){o.exports=flarum.core.compat.Model},function(o,e){o.exports=flarum.core.compat["components/ReplyComposer"]},function(o,e){o.exports=flarum.core.compat["models/User"]},,function(o,e){o.exports=flarum.core.compat["components/DiscussionPage"]},function(o,e,t){"use strict";t.r(e);var r=t(1),n=t(0),s=t.n(n),a=t(3),u=t.n(a),i=(t(9),t(2)),c=t.n(i),p=t(4),l=t.n(p),d=t(5),f=t.n(d),b=t(6),y=t.n(b),g=t(7),x=t.n(g);s.a.initializers.add("the-turk-nodp",(function(){x.a.prototype.canDoublePost=f.a.attribute("canDoublePost");var o=function(o,e){if(!o||!e||e.canDoublePost())return!1;var t=o.lastPostedUser(),r=o.lastPostedAt(),n=s.a.forum.attribute("nodp.time_limit"),a=dayjs(r.getTime()).add(n,"minute").isBefore(dayjs());return 0==n||!a&&t==e};Object(r.extend)(c.a.prototype,"headerItems",(function(e){o(this.attrs.post.discussion(),s.a.session.user)&&e.add("nodp",m("div",{className:"Alert"},m("div",{className:"Alert-body"},m("h4",null,s.a.translator.trans("the-turk-nodp.forum.composer_edit.double_posting_warning_title")),m("p",null,s.a.translator.trans("the-turk-nodp.forum.composer_edit.double_posting_warning_description")))))})),Object(r.override)(u.a,"replyAction",(function(e,t){var r=this,n=s.a.session.user;return new Promise((function(a,u){return n?o(r,n)?(s.a.composer.load(c.a,{post:r.lastPost()}),s.a.composer.show(),$("body").find(".item-nodp").css("display","block"),a(s.a.composer)):r.canReply()?(s.a.composer.composingReplyTo(r)&&!t||s.a.composer.load(y.a,{user:n,discussion:r}),s.a.composer.show(),e&&s.a.viewingDiscussion(r)&&!s.a.composer.isFullScreen()&&s.a.current.get("stream").goToNumber("reply"),a(s.a.composer)):u():(s.a.modal.show(l.a),u())}))}))}),-10)}]);
//# sourceMappingURL=forum.js.map