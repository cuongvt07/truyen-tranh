const MobileWidth = 768;


const LockScrollType = {
    lock: 0,
    unlock: 1,
    toggle: 2
}
let mobileMenuActive = false;
/**
 * Lock window scrolling
 * @param {string} LockScrollType
 * @param {bool} isMobile
 */
function lockScroll(LockScroll, isMobileMenu = false) {
    switch (LockScroll) {
        case LockScrollType.lock:
            document.documentElement.classList.add("lock");
            break;
        case LockScrollType.unlock:
            if (!mobileMenuActive || isMobileMenu)
                document.documentElement.classList.remove("lock");
            break;
        case LockScrollType.toggle:
            if (!mobileMenuActive || isMobileMenu)
                document.documentElement.classList.toggle("lock");
            break;
    }
    if (isMobileMenu) {
        if (document.documentElement.classList.contains("lock"))
            mobileMenuActive = true
        else
            mobileMenuActive = false
    }
}


function burgerMenuOpenClose(e) {
    e.currentTarget.querySelector("i").classList.toggle("fa-bars");
    e.currentTarget.querySelector("i").classList.toggle("fa-close");
}

// Open Close elements by target on btn
// <a href="#" p-target="some-panel1" p-targetClass="active" p-event="someFunction">Click</a>
let openCloseBtns = document.querySelectorAll(".open-close");
if (openCloseBtns) {
    openCloseBtns.forEach(element => {
        element.addEventListener("click", function (e) {
            e.preventDefault();
            let targetID = element.getAttribute("p-target");
            let target = document.getElementById(targetID);
            let targetClass = "hide";
            let isNoLock = element.hasAttribute("nolock");

            if (element.hasAttribute("p-target-class"))
                targetClass = element.getAttribute("p-target-class");

            target.classList.toggle(targetClass);

            console.log(isNoLock);
            if (!isNoLock) {
                if (targetID == "fullscreen-mobile-menu")
                    lockScroll(LockScrollType.toggle, true);
                else
                    lockScroll(LockScrollType.toggle);
            }
            if (element.hasAttribute("p-event")) {
                eventName = element.getAttribute("p-event");
                window[eventName](e);
            }
        });
    });
}

class PoppupWindow {
    constructor({ title,
        textInfo,
        hasCancel = true,
        agreeEvent = () => { },
        cancelEvent = () => { },
        agreeText = "OK",
        cancelText = "Cancel" }) {

        this.title = title;
        this.textInfo = textInfo;

        this.hasCancel = hasCancel;
        this.agreeText = agreeText;
        this.cancelText = cancelText;

        this.onAgree = agreeEvent;
        this.onCancel = cancelEvent;
    }
    show() {
        let fullscreen = document.createElement("div");
        fullscreen.classList.add("fullscreen", "fullscreen-poppup");

        let poppupWindow = document.createElement("div");
        poppupWindow.classList.add("block", "poppup-window");

        poppupWindow.append(this.getTitleElement(this.title));
        poppupWindow.append(this.getContentElement(this.textInfo));

        let btns = document.createElement("div");
        btns.classList.add("control-btns");
        if (this.hasCancel) {
            let cancelBtn = document.createElement("button");
            cancelBtn.classList.add("btn", "btn-invincible");
            cancelBtn.textContent = this.cancelText;
            cancelBtn.addEventListener("click", (e) => {
                fullscreen.remove();
                lockScroll(LockScrollType.unlock);
                this.onCancelBtn();
            })
            btns.append(cancelBtn);
        }

        if (this.agreeText) {
            let agreeBtn = document.createElement("button");
            agreeBtn.classList.add("btn");
            agreeBtn.textContent = this.agreeText;
            agreeBtn.addEventListener("click", (e) => {
                this.onAgreeBtn();
                fullscreen.remove();
                lockScroll(LockScrollType.unlock);
            });
            btns.append(agreeBtn);
        }

        poppupWindow.append(btns);
        fullscreen.append(poppupWindow);
        document.body.append(fullscreen);
        lockScroll(LockScrollType.lock);

        this.poppumWindow = poppupWindow;
    }
    onAgreeBtn() {
        this.onAgree();
    }
    onCancelBtn() {
        this.onCancel();
    }
    getTitleElement(title) {
        let titleElement = document.createElement('div');
        titleElement.classList.add('title');
        titleElement.textContent = title;

        return titleElement;
    }
    getContentElement(textInfo) {
        let contentElement = document.createElement('div');
        contentElement.classList.add('text-info');
        contentElement.innerHTML = textInfo;

        return contentElement;
    }

}
class PoppupWindowTextarea extends PoppupWindow {
    constructor({ title,
        textInfo,
        hasCancel = true,
        agreeEvent = (text) => { },
        agreeText = "OK",
        placeholder = "Your submission..." }) {

        super({
            'title': title,
            'textInfo': textInfo,
            'hasCancel': hasCancel,
            'agreeText': agreeText,
            'agreeEvent': agreeEvent
        });

        this.placeholder = placeholder;
    }
    getContentElement(textInfo) {
        let textElement = super.getContentElement(textInfo);

        let textInput = document.createElement('div');
        textInput.classList.add('text-input');

        let textareaElement = document.createElement('textarea');
        textareaElement.setAttribute('placeholder', this.placeholder);
        textInput.append(textareaElement);
        this.textareaElem = textareaElement;

        let container = document.createElement('div');
        container.append(textElement);
        container.append(textInput);

        return container;
    }
    onAgreeBtn() {
        let value = this.textareaElem.value;
        this.onAgree(value);
    }
}

function reportWindow({ title, additional, textInfo = 'Do you want to really report this?' }) {
    new PoppupWindowTextarea({
        'title': 'Report',
        'textInfo': textInfo,
        'hasCancel': true,
        'agreeText': 'Send',
        'agreeEvent': (value) => {
            $.ajax({
                type: "POST",
                url: "/user/ajax/report",
                data: {
                    "csrfmiddlewaretoken": window.CSRF_TOKEN,
                    "additional": additional,
                    "content": value,
                    'title': title
                },
                success: function (response) {
                    sendNotification(response.message, NotificationType.success);
                },
                error: function (jqXHR, exception) {
                    sendNotification(jqXHR.responseText, NotificationType.error)
                }
            });
        }
    }).show();
}


class TippyMenu {
    isOnceActivated = false;
    background = undefined;

    /**
     * 
     * @param {string | HTMLElement} element HTML Element or selector 
     * @param {string | HTMLElement} content HTML content or Element
     * @param {*} onLoaded Event after show
     */
    constructor({ element,
        content,
        position = "bottom-start",
        onLoaded = function (i) { }, onShown = function (i) { }, byOuterHTML = false }) {

        if (byOuterHTML)
            content = content.outerHTML

        this.tippyObj = tippy(element, {
            content: content,
            arrow: true,
            theme: 'light',
            allowHTML: true,
            placement: position,
            interactive: true,
            appendTo: () => document.body,

            trigger: 'click',

            onShow(instance) {
                if (window.screen.width <= MobileWidth) {
                    this.background = document.createElement("div");
                    this.background.classList.add("fullscreen", "fullscreen-tippy");

                    // Nút X đóng popup (thấy rõ trên mobile thay vì chỉ bấm nền)
                    let closeBtn = document.createElement("button");
                    closeBtn.type = "button";
                    closeBtn.className = "fullscreen-tippy__close";
                    closeBtn.setAttribute("aria-label", "Close");
                    closeBtn.innerHTML = "&times;";
                    closeBtn.addEventListener("click", function (e) {
                        e.stopPropagation();
                        instance.hide();
                    });
                    this.background.append(closeBtn);

                    document.body.append(this.background);
                    this.background.addEventListener("click", function (e) {
                        instance.hide();
                    });

                    lockScroll(LockScrollType.lock)
                }
            },
            onShown(instance) {
                if (!this.isOnceActivated)
                    onLoaded(instance);

                onShown(instance);
                this.isOnceActivated = true;
            },
            onHide(instance) {
                this.background?.classList.add("closing");
            },
            onHidden(instance) {
                this.background?.remove();
                if (window.screen.width <= MobileWidth)
                    lockScroll(LockScrollType.unlock)
            }
        })
    }
}

try {
    let browseBtns = document.querySelectorAll('.tippy-browse');
    let profileBtns = document.querySelectorAll('.tippy-profile');
    let addItemBtn = document.getElementById('add-item-btn');

    browseBtns.forEach(browseBtn => {
        new TippyMenu({
            'element': browseBtn,
            'content': document.getElementById("header-browse-list"),
            'byOuterHTML': true,
        });
    });
    profileBtns.forEach(profileBtn => {
        new TippyMenu({
            'element': profileBtn,
            'content': document.getElementById("header-user-list"),
            'position': 'bottom-end',
            'byOuterHTML': true
        });
    });

    new TippyMenu({
        'element': addItemBtn,
        'content': document.getElementById("header-add-list"),
        'position': 'bottom-end',
    });
}
catch {
    console.log("Can't call tippy-browse and tippy-profile")
}

// Spoiler
let spoilersText = document.getElementsByClassName("spoiler");
if (spoilersText) {
    for (let i = 0; i < spoilersText.length; i++) {
        const element = spoilersText[i];
        element.addEventListener("click", function (e) {
            element.classList.remove("active");
        });
    }
}
// Notifications
const NotificationType = {
    success: "success",
    note: "note",
    error: "error"
}
function sendNotification(text, type = NotificationType.note) {
    Toastify({
        text: text,
        close: true,
        duration: 5000,
        className: type,
        gravity: "bottom"
    }).showToast();
}

// Search
let searchBaseAjax = document.getElementById('ajax-base-search');
let searchContainer = document.querySelector('.searches-container');
let iconSeach = document.querySelector('#ajax-submit-search i');

let ajaxSearch;
let pingTimer;

searchBaseAjax?.addEventListener('input', (e) => {
    let val = searchBaseAjax.value;
    if (ajaxSearch != undefined || ajaxSearch != null) {
        ajaxSearch.abort();
    }
    if (pingTimer != undefined || pingTimer != null) {
        clearTimeout(pingTimer);
    }

    if (val.length > 3) {
        iconSeach.classList.add('fa-spinner');
        iconSeach.classList.remove('fa-search');

        let data = {
            csrfmiddlewaretoken: window.CSRF_TOKEN,
            search: val
        };
        searchContainer.innerHTML = '';

        pingTimer = setTimeout(() => {
            ajaxSearch = $.ajax({
                type: "GET",
                url: "/ajax/search-live",
                data: data,
                success: function (response) {
                    iconSeach.classList.remove('fa-spinner');
                    iconSeach.classList.add('fa-search');

                    searchContainer.innerHTML = response.html;
                }
            });
        }, 1000)
    }
    else {
        iconSeach.classList.remove('fa-spinner');
        iconSeach.classList.add('fa-search');
    }
})

let listBtns = document.querySelectorAll(".list-names .btn");
let pLists = document.querySelectorAll(".p-list");
let nothingElem = document.getElementById("nothing");

if (listBtns) {
    for (let i = 0; i < listBtns.length; i++) {
        const btn = listBtns[i];
        let target = btn.getAttribute("p-target");

        btn.addEventListener("click", (e) => {
            pLists?.forEach(list => {
                if (target == "all") {
                    list.classList.remove("hide");
                    nothingElem.classList.add("hide");
                }
                else {
                    let targetElem = document.getElementById(target);
                    list.classList.add("hide");

                    targetElem?.classList.remove("hide");
                    if (targetElem == null)
                        nothingElem?.classList.remove("hide");
                    else
                        nothingElem?.classList.add("hide");
                }
            });
            listBtns.forEach(btn => {
                btn.classList.add("btn-invincible");
            });
            btn.classList.remove("btn-invincible");

            if (!pLists) {
                nothingElem.classList.remove("hide");
            }
        })

    }
}

// Subscription btn
let subscriptionBtn = document.getElementById('subscription-btn');
subscriptionBtn?.addEventListener('click', (e) => {
    let btnText = subscriptionBtn.querySelector('span');

    let message = subscriptionBtn.getAttribute('data-message');
    let content_type_id = subscriptionBtn.getAttribute('data-content-type');
    let object_id = subscriptionBtn.getAttribute('data-content-id');
    let activeText = subscriptionBtn.getAttribute('data-active-text');
    let inactiveText = subscriptionBtn.getAttribute('data-inactive-text');

    $.ajax({
        type: "POST",
        url: "/user/ajax/set-subscribe",
        data: {
            "csrfmiddlewaretoken": window.CSRF_TOKEN,
            'content_type': content_type_id,
            'object_id': object_id
        },
        beforeSend: function () {
            subscriptionBtn.disabled = true;
        },
        success: function (response) {
            sendNotification(message, NotificationType.note);
            if (response.added) {
                btnText.textContent = activeText;
            }
            else {
                btnText.textContent = inactiveText;
            }
            subscriptionBtn.disabled = false;
        }
    });
});

const daily_reward_shown = window.DAILY_REWARD_CLAIMED;
const rewardPoppup = new PoppupWindow({
    title: 'Premium daily reward',
    textInfo: `
<div style="text-align: center;">
    <h2>+10 <i class="fas fa-coins"></i></h2>
    <p>Here's your daily login reward</p>
    <p>Days left: ${window.PREMIUM_DAYS_REMAINING}</p>
</div>
    `,
    agreeText: 'Ok',
    hasCancel: false
})
if (daily_reward_shown) {
    rewardPoppup.show();
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 },
        zIndex: 9999
    });
}

const lazyImages = document.querySelectorAll('.lazy-image');

lazyImages.forEach((img) => {
    const setLoaded = () => {
        img.classList.add('loaded');
        const parent = img.closest('.lazy-load-bg');
        if (parent) {
            parent.classList.remove('lazy-load-bg');
        }
    }
    if (img.complete) {
        setLoaded();
    }
    else {
        img.addEventListener('load', setLoaded, { once: true });
    }
});

//Disable scroll effect on number inputs
document.addEventListener('wheel', function (event) {
    if (document.activeElement.type === 'number') {
        event.preventDefault();
    }
}, { passive: false });