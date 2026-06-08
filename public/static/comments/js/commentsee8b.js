let replySendData = {
    replyId: null,
    replyElem: null,
    isReply: false
}
let currentCommentId = null;

let commentForm = document.getElementById('comments-form');
let submitComment = document.getElementById('submit-comment');

let commentMainAppendBtn = document.querySelector('.comment-append-btn[comment-target="main"]');

let commentsState = {
    page: 1,
    loading: false,
    finished: false
};


showForm(commentMainAppendBtn)

let commentsSection = document.querySelector('.main-comments');
const commentsSections = document.querySelectorAll('.main-comments');

// Submit comment
let sending = false
submitComment.addEventListener('click', (e) => {
    e.preventDefault();
    let csrf = commentForm.querySelector('input[name="csrfmiddlewaretoken"]').value;
    let content = commentForm.querySelector('textarea').value;

    if (sending)
        return;

    sending = true;

    $.ajax({
        type: "POST",
        url: "/comments/ajax/comment-reply",
        data: {
            "csrfmiddlewaretoken": csrf,
            'content': content,
            'content_type': CONTENT_TYPE,
            'object_id': OBJECT_BY_COMMENT,
            'replyId': replySendData.replyId,
            'isReply': replySendData.isReply
        },
        success: function (response) {
            const comment = createComment(response.id, response.content);

            if (replySendData.isReply && replySendData.replyElem) {
                replySendData.replyElem.append(comment);
                replySendData.replyElem.removeAttribute('hidden');
            } else {
                activeCommentsSection.append(comment);
            }

            commentForm.reset();
            commentForm.setAttribute('hidden', '');
            commentMainAppendBtn.removeAttribute('hidden');

            sending = false;
        },
        error: function (jqXHR, exception) {
            sendNotification(jqXHR.responseText, NotificationType.error)
            sending = false;
        }
    });
});

let commentTemplateUser = document.getElementById('comment-template-user');
function createComment(commentId, commentContent) {
    let newComment = commentTemplateUser.cloneNode(true);
    newComment.removeAttribute('hidden');
    newComment.removeAttribute('id');
    newComment.setAttribute('comment-id', commentId)

    newComment.querySelector('.content').textContent = commentContent;
    newComment.innerHTML = newComment.innerHTML.replace("[[ id ]]", commentId).replace("[[ id ]]", commentId);

    tippyOnComment(newComment);
    showForm(newComment.querySelector('.comment-append-btn'), newComment);
    commentLikes(newComment);

    return newComment;
}

function tippyOnComment(comment) {
    let additional = comment.querySelector('.additional');

    let tippyObject = new TippyMenu({
        'element': additional,
        'content': document.getElementById("comment-additional"),
        'position': 'bottom-start',
        'onLoaded': (i) => {
            let tippyPanel = tippyObject.tippyObj.popper;
            let btns = tippyPanel.querySelectorAll('.additional-comment-btn');

            btns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    let type = btn.getAttribute('data-type');
                    setCommentState(comment, type);

                    i.hide();
                });
            });

        },
        'byOuterHTML': true
    });

}

async function setCommentState(comment, type) {
    const commentId = comment.getAttribute('comment-id');
    let valueReturn = true;
    await $.ajax({
        type: "POST",
        url: "/comments/ajax/comment-change",
        data: {
            "csrfmiddlewaretoken": window.CSRF_TOKEN,
            'comment_id': commentId,
            'type': type
        },
        success: function (response) {
            switch (type) {
                case 'remove':
                    comment.remove();
                    sendNotification(response.message, NotificationType.success)
                    break;

                case 'report':
                    reportWindow({
                        'title': 'Report comment',
                        'additional': `comment id: ${comment.id}`,
                        'textInfo': 'Do you want to really report this comment?'
                    })
                    break;

                case 'like':
                case 'dislike':
                    valueReturn = response.rating;
                    break;

                default:
                    break;
            }
        },
        error: function (jqXHR, exception) {
            sendNotification(jqXHR.responseText, NotificationType.error)
            return false;
        }
    });

    return valueReturn;
}

function showForm(appendBtn, comment = null, container = document) {
    appendBtn.addEventListener('click', (e) => {
        e.preventDefault();
        commentForm.removeAttribute('hidden');

        let target = appendBtn.getAttribute('comment-target');
        let appendElem = container.querySelector(`.comment-append-to[comment-append="${target}"]`);

        appendElem.appendChild(commentForm);

        if (appendBtn == commentMainAppendBtn) {
            replySendData.isReply = false;
            commentMainAppendBtn.setAttribute('hidden', '')
        }
        else {
            replySendData.isReply = true;
            replySendData.replyId = comment.getAttribute('comment-id');
            replySendData.replyElem = comment.querySelector('.comments');

            commentMainAppendBtn.removeAttribute('hidden')
        }
    })
}

/**
 * @param {Element} comment
 */
function commentLikes(comment) {
    let like = comment.querySelector('.like');
    let dislike = comment.querySelector('.dislike');
    let text = comment.querySelector('.right span');


    like.addEventListener('click', async (e) => {
        e.preventDefault();
        if (like.classList.contains('active'))
            return;
        like.classList.add('active');
        dislike.classList.remove('active');

        currentCommentId = comment.getAttribute('comment-id');
        res = await setCommentState(comment, 'like');

        text.textContent = res
    });

    dislike.addEventListener('click', async (e) => {
        e.preventDefault();
        if (dislike.classList.contains('active'))
            return;
        dislike.classList.add('active');
        like.classList.remove('active');

        currentCommentId = comment.getAttribute('comment-id');
        res = await setCommentState(comment, 'dislike');

        text.textContent = res
    });
}


function attachShowRepliesBtn(comment, commentData) {
    const hasReplies = commentData.has_replies;

    if (!hasReplies) return;

    const repliesList = comment.querySelector('.comments-reply');
    const controls = comment.querySelector('.comment-controls .left');

    const btn = document.createElement('a');
    btn.href = '#';
    btn.className = 'show-replies-btn';
    btn.textContent = 'Show replies';

    controls.appendChild(btn);

    btn.addEventListener('click', async (e) => {
        e.preventDefault();

        btn.textContent = 'Loading...';
        btn.style.pointerEvents = 'none';

        const data = await loadComments({
            content_type: CONTENT_TYPE,
            object_id: OBJECT_BY_COMMENT,
            page: 1,
            limit: 100,
            parent: commentData.id
        });

        if (data && data.results) {
            repliesList.innerHTML = '';
            data.results.forEach(replyData => {
                const replyElem = renderComment(replyData);
                repliesList.appendChild(replyElem);
            });
            repliesList.removeAttribute('hidden');
        }

        btn.remove();
    });
}

const commentContainers = document.querySelectorAll('.comments-container');

commentContainers.forEach((container) => {
    let commentsState = {
        page: 1,
        loading: false,
        finished: false
    };
    const loadMoreBtn = container.querySelector('.load-more-comments');
    const commentsList = container.querySelector('.comments');
    const noCommentsText = container.querySelector('.no-comments');

    function renderCommentsPage(data) {

        data.results.forEach(commentData => {
            const comment = renderComment(commentData);
            commentsList.appendChild(comment);
        });

        if (data.results.length === 0 && commentsState.page === 1) {
            noCommentsText.hidden = false;
            noCommentsText.textContent = "No comments. Be the first!";
        } else {
            noCommentsText.hidden = true;
        }

        if (commentsState.page >= data.total_pages) {
            commentsState.finished = true;
            loadMoreBtn.hidden = true;
        } else {
            loadMoreBtn.hidden = false;
        }
    }

    async function loadInitialComments() {
        const data = await loadComments({
            content_type: CONTENT_TYPE,
            object_id: OBJECT_BY_COMMENT,
            page: 1,
            limit: 20,
            parent: null
        });

        if (!data) return;

        commentsState.page = 1;
        renderCommentsPage(data);
    }


    document.addEventListener("DOMContentLoaded", () => {
        const section = document.querySelector('.main-comments');
        if (!section) return;

        activeCommentsSection = section;
        loadInitialComments();
    });

    loadMoreBtn.addEventListener("click", async () => {
        if (commentsState.loading || commentsState.finished) return;

        commentsState.loading = true;
        commentsState.page += 1;

        loadMoreBtn.disabled = true;

        const data = await loadComments({
            content_type: CONTENT_TYPE,
            object_id: OBJECT_BY_COMMENT,
            page: commentsState.page,
            limit: 20,
            parent: null
        });

        loadMoreBtn.disabled = false;

        renderCommentsPage(data, false);

        commentsState.loading = false;
    });


})

async function loadComments({
    content_type,
    limit = 20,
    object_id,
    page = 1,
    parent = null
}) {
    try {
        const response = await $.ajax({
            type: "GET",
            url: "/api/comments",
            data: {
                content_type,
                limit,
                object_id,
                page,
                parent: parent || undefined
            }
        });

        return response;
    } catch (jqXHR) {
        sendNotification(jqXHR.responseText, NotificationType.error);
        return null;
    }
}

const commentTemplateRegular =
    document.getElementById('comment-template-regular');

function renderComment(commentData) {
    const comment = commentTemplateRegular.cloneNode(true);

    comment.removeAttribute('hidden');
    comment.removeAttribute('id');

    comment.setAttribute('comment-id', commentData.id);

    const profileUrl = `/user/${commentData.user_object.pk}`;

    const avatar = comment.querySelector('.comment-header__ava');
    const username = comment.querySelector('.comment-header__username');

    avatar.href = profileUrl;
    username.href = profileUrl;

    username.textContent = commentData.user_object.username;

    comment.querySelector('.avatar-image').src =
        commentData.user_object.avatar ?? '/static/account/images/no-ava.jpg';

    comment.querySelector('.date').textContent =
        new Date(commentData.time_created).toLocaleDateString();

    comment.querySelector('.content').textContent =
        commentData.content;

    comment
        .querySelector('.comment-append-btn')
        .setAttribute('comment-target', commentData.id);

    comment
        .querySelector('.comment-append-to')
        .setAttribute('comment-append', commentData.id);

    comment.querySelector('.right span').textContent =
        commentData.rating;

    if (commentData.user_rate === true) {
        comment.querySelector('.like').classList.add('active');
    }

    if (commentData.user_rate === false) {
        comment.querySelector('.dislike').classList.add('active');
    }

    tippyOnComment(comment);

    showForm(
        comment.querySelector('.comment-append-btn'),
        comment,
        comment
    );

    commentLikes(comment);

    attachShowRepliesBtn(comment, commentData);

    return comment;
}
