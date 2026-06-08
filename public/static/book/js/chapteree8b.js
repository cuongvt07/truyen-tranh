const body = document.querySelector("body")
const defaultReaderSettings = {
    "theme": "none",
    "font-size": 18,
    "line-height": 1.3,
    "margin-bottom": 19
}
let currentSettings = localStorage.getItem("reader-settings");
let cls = 'chapter-text';

if(currentSettings)
    currentSettings = JSON.parse(currentSettings);
else
    currentSettings = defaultReaderSettings;

function setReaderOption(key, value) {
    currentSettings[key] = value;
    localStorage.setItem("reader-settings", JSON.stringify(currentSettings));
    console.log(currentSettings);
}
// Set options from cache
let styles = document.querySelector("style[data-chater-options-stylesheet]");
body.classList.add(currentSettings["theme"])
function setFontStyles() {
    styles.innerHTML = `
    .chapter-text {
        font-size: ${currentSettings["font-size"]}px;
    }
    .${cls} {
        display: block !important;
    }
    .chapter-text > * {
        line-height: ${currentSettings["line-height"]};
        margin-bottom: ${currentSettings["margin-bottom"]}px;
    }
    `;
}
setFontStyles();

let paragraphs = [];
// Load content
$.ajax({
    type: "GET",
    url: "/book/ajax/read-chapter/" + CHAPTER_ID,
    success: function (response) {
        let placeElement = document.querySelector('.chapter-text__place');
        if(placeElement) {
            placeElement.insertAdjacentHTML('afterend', response.content);
            placeElement.remove();
        }
        
        paragraphs = document.querySelectorAll(`.${response.class} > div`);
        cls = response.class;

        // Run scripts in the content
        const scripts = document.querySelectorAll(`.${cls} script`);
        scripts?.forEach(oldScript => {
            const newScript = document.createElement('script');
        
            // Set same attributes
            [...oldScript.attributes]?.forEach(attr => newScript.setAttribute(attr.name, attr.value));

            // Place code
            if (oldScript.textContent) {
                newScript.textContent = oldScript.textContent;
            }
        
            oldScript.replaceWith(newScript); // Replace old script on new
        });
        
        setParagraphs();
        setFontStyles();
    },
    error: function (jqXHR, exception) {
        sendNotification('The chapter cannot be uploaded', NotificationType.error)
}});

// Settings Panel
let fullscreenSettings = document.getElementById("chapter-settings");
let btnsTheme = document.getElementsByClassName("btn-theme");
for (let i = 0; i < btnsTheme.length; i++) {
    const element = btnsTheme[i];
    element.addEventListener("click", function(e) {
        let themeName = element.getAttribute("theme");
        body.classList = "";
        body.classList.add(themeName);
        setReaderOption("theme", themeName);
    });
}
let fields = document.querySelectorAll("#chapter-settings .field");
for (let i = 0; i < fields.length; i++) {
    const field = fields[i];
    const span = field.querySelector("span");
    const min = Number(field.getAttribute("min"));
    const max = Number(field.getAttribute("max"));
    const step = Number(field.getAttribute("step"));
    span.textContent = currentSettings[field.id];

    field.querySelector(".btn-lower").addEventListener("click", (e) => {
        let val = currentSettings[field.id];
        val -= step;
        if(val < min)
            val = min;
        val = Math.round(val * 10) / 10;
        span.textContent = val;
        setReaderOption(field.id, val);
        setFontStyles();
    });
    field.querySelector(".btn-upper").addEventListener("click", (e) => {
        let val = currentSettings[field.id];
        val += step;
        if(val > max)
            val = max;
        val = Math.round(val * 10) / 10;
        span.textContent = val;
        setReaderOption(field.id, val);
        setFontStyles();
    });
}

// Hide Header by scroll
let scrollCheckpoint = 0;
window.addEventListener("scroll", function (e) {
    if(window.scrollY >= scrollCheckpoint) {
        body.classList.add("scrolled");
        scrollCheckpoint = window.scrollY;
    }
    else if (window.screenY < scrollCheckpoint) {
        body.classList.remove("scrolled");
        scrollCheckpoint = window.scrollY;
    }
});
document.documentElement.addEventListener("click", function(e) {
    body.classList.remove("scrolled");
});

function addToBooklist(paragraph_num = 0) {
    $.ajax({
        type: "POST",
        url: "/book/ajax/booklist",
        data: {
            csrfmiddlewaretoken: window.CSRF_TOKEN,
            chapter: CHAPTER_ID,
            book: BOOK_ID,
            chapter_ph: paragraph_num
        },
        success: function (response) {
            sendNotification(response.message, NotificationType.success);
            

            // Paragraph point save
            paragraphs.forEach((ph, key) => {
                key++;
                console.log(paragraph_num);
                if(key == paragraph_num)
                    ph.classList.add('bookmark-p');
                else
                    ph.classList.remove('bookmark-p');
                
            });
        },
        error: function (jqXHR, exception) {
            sendNotification(jqXHR.responseText, NotificationType.error);
        }
    });
}

// Bookmark
let bookmarkBtns = document.querySelectorAll(".bookmark");
let reportBtn = document.getElementById('report-chapter-btn');
for (let i = 0; i < bookmarkBtns.length; i++) {
    const element = bookmarkBtns[i];

    element.addEventListener("click", function(e) {
        addToBooklist();
        element.classList.add('active');
    });
}
// Bookmark with paragraph
let bookmarkParagraphActive = false;
let bookmarkPhAlert = document.querySelector('.bookmark-ph-alert');

let bookmarkPhBtn = document.querySelector(".bookmark-paragraph");
bookmarkPhBtn.addEventListener('click', (e) => {
    body.classList.toggle('bookmark-ph-process');
    bookmarkParagraphActive = !bookmarkParagraphActive;
});

function setParagraphs() {
    paragraphs.forEach((ph, key)=> {    
        ph.addEventListener('click', (e) => {
            if(!bookmarkParagraphActive)
                return;
    
            body.classList.toggle('bookmark-ph-process');
            bookmarkParagraphActive = false;
    
            addToBooklist(key);
        });
        let chapterPhSaved = body.getAttribute('chapter_ph');
        if(chapterPhSaved && chapterPhSaved != "0") {
            chapterPhSaved = +chapterPhSaved;
            if(key == chapterPhSaved) {
                ph.classList.add('bookmark-p');
    
                ph.scrollIntoView({
                    behavior: 'smooth', // плавная прокрутка
                    block: 'center',
                    inline: 'nearest'
                })
            }
        }
    });
}

reportBtn?.addEventListener('click', (e) => {
    let chapterId = reportBtn.getAttribute('data-id');
    reportWindow({
        'title': REPORT_TITLE,
        'textInfo': 'Do you want to really report this chapter?',
        'additional': `chapter id: ${chapterId}`
    });
});


let chapterList = document.querySelector('.chapter-list');
let chaptersLoaded = false
function loadChapters(e) {
    if(chaptersLoaded)
        return

    $.ajax({
        type: "GET",
        url: "/book/ajax/chapter-pagination",
        data: {
            csrfmiddlewaretoken: window.CSRF_TOKEN,
            book_id: BOOK_ID,
            current_id: CHAPTER_ID,
            page: 1,
            pagination: 0
        },
        success: function (response) {
            chapterList.innerHTML = "";
            chapterList.innerHTML = response.html;
            chaptersLoaded = true;
        }
    });
}

// Buy chapter
let buyChapterBtn = document.getElementById('btn-buy-chapter');
buyChapterBtn?.addEventListener('click', (e) => {
    e.preventDefault();

    new PoppupWindow({
        'title': 'Purchasing a chapter',
        'textInfo': `Do you really want to purchase chapter: <strong>"${CHAPTER_TITLE}"</strong> from the book "${BOOK_TITLE}"<br><br>You have ${USER_MONEY} coupons`,
        'agreeText': `Buy (${PRICE} coupons)`,
        'agreeEvent': () => {
            $.ajax({
                type: "POST",
                url: "/book/ajax/chapter-purchase",
                data: {
                    csrfmiddlewaretoken: window.CSRF_TOKEN,
                    chapter_id: CHAPTER_ID
                },
                success: function (response) {
                    if(response.is_bought)
                        window.location.replace(window.location.href);
                    else
                        sendNotification(response.message, NotificationType.error)
                },
                error: function (jqXHR, exception) {
                    sendNotification(jqXHR.responseText, NotificationType.error)
                }
            });
        }
    }).show();
});

// Likes
let liked = false
document.getElementById("btn-like-chapter").addEventListener("click", function(e) {
    if(liked)
        return

    $.ajax({
        type: "POST",
        url: LIKE_LINK,
        data: {
            csrfmiddlewaretoken: window.CSRF_TOKEN
        },
        success: function (response) {
            likeCountElem = document.getElementById('likes-count');

            if(response.liked)
                likeCountElem.textContent = +likeCountElem.textContent + 1
            
            sendNotification(response.message, NotificationType.note)
            liked = true
        },
        error: function (jqXHR, exception) {
            sendNotification(jqXHR.responseText, NotificationType.error)
        }
    });
    
});