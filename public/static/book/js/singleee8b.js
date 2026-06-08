// Swipers
let swiperSections = document.getElementsByClassName("swp-single")
if (swiperSections) {
    for (let i = 0; i < swiperSections.length; i++) {
        let swiperContainer = swiperSections[i].querySelector(".swiper-container");

        new Swiper(swiperContainer, {
            slidesPerView: 1.2,
            slidesPerGroup: 1,

            spaceBetween: 15,

            navigation: {
                nextEl: swiperSections[i].querySelector(".swiper-right"),
                prevEl: swiperSections[i].querySelector(".swiper-left")
            },

            breakpoints:
            {
                1040:
                {
                    slidesPerView: 3,
                    slidesPerGroup: 1,
                },
                480:
                {
                    slidesPerView: 2,
                    slidesPerGroup: 1,
                },
            }
        })
    }
}

class Section {
    currentSection = undefined;
    onLoad = undefined;

    isLoad = true;

    static sections = document.querySelectorAll(".main-section");
    static sectionBtns = document.querySelectorAll(".section-select a");

    /**
     * 
     * @param {HTMLElement} section 
     * @param {Function} onLoad 
     */
    constructor(section, onLoad) {
        this.currentSection = section;
        this.onLoad = onLoad;

        if (!section.classList.contains("hide")) {
            this.onLoad();
            this.isLoad = true;
        }
    }
    displaySection() {
        for (let i = 0; i < Section.sections.length; i++) {
            Section.sections[i].classList.add("hide");

            if (Section.sectionBtns[i].getAttribute("section-target") == this.currentSection.id)
                Section.sectionBtns[i].classList.add("active");
            else
                Section.sectionBtns[i].classList.remove("active");
        }
        this.currentSection.classList.remove("hide");

        if (this.isLoad)
            this.onLoad();

        if (window.screen.width > MobileWidth) {
            window.scrollTo(0, 0);
        }

    }
}
function onLoadInformation() {
}
function onLoadChapters() { }
let commentsSectionAll = document.querySelector('.main-comments-all');

async function onLoadComments() {
    const section = document.getElementById("comments");
    const commentsList = section?.querySelector('.main-comments');

    if (!commentsList) return;

    if (typeof loadInitialComments === 'function') {
        activeCommentsSection = commentsList;
        await loadInitialComments(commentsList);
    }
}

let sects = {
    "information": new Section(document.getElementById("information"), onLoadInformation),
    "chapters": new Section(document.getElementById("chapters"), onLoadChapters),
    "comments": new Section(document.getElementById("comments"), onLoadComments)
}

// Buttons for change Sections
Section.sectionBtns.forEach(btn => {
    btn.addEventListener("click", function (e) {
        e.preventDefault()

        target = btn.getAttribute("section-target")
        sects[target].displaySection();
    })
})
document.getElementById("show-all-comments")?.addEventListener("click", function (e) {
    e.preventDefault();
    sects["comments"].displaySection();
})
document.getElementById("show-all-chapters")?.addEventListener("click", function (e) {
    e.preventDefault();
    sects["chapters"].displaySection();
})

let chapterList = document.getElementById("all-chapters-list")
document.getElementById("chapter-sort-btn")?.addEventListener("click", function (e) {
    chapterList.classList.toggle("chapters-reverse")
})

// Chapter pagination
let pagination = document.getElementById("select-pagination-chapter");
let paginationPages = new Map();
pagination?.addEventListener('change', (e) => {
    const page = pagination.value;

    // Cache
    htmlChapters = paginationPages.get(page);
    if (htmlChapters) {
        chapterList.innerHTML = "";
        chapterList.innerHTML = htmlChapters;
        return;
    }
    // Collect ajax data
    let data = {
        csrfmiddlewaretoken: window.CSRF_TOKEN,
        book_id: BOOK_ID,
        page: page
    };
    if (BOOKMARK_CH)
        data.bookmark_chapter = BOOKMARK_CH;

    $.ajax({
        type: "GET",
        url: "/book/ajax/chapter-pagination",
        data: data,
        success: function (response) {
            chapterList.innerHTML = "";
            chapterList.innerHTML = response.html;
            paginationPages.set(page, response.html);
        }
    });
});

function addToListEvent(instance) {
    let addToListBtns = document.querySelectorAll('.add-to-list-btn');
    let addToListText = document.querySelector('.text-add-to-list');
    addToListBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            let listData = btn.getAttribute('data-list');

            $.ajax({
                type: "POST",
                url: "/book/ajax/booklist",
                data: {
                    csrfmiddlewaretoken: window.CSRF_TOKEN,
                    status: listData,
                    book: BOOK_ID
                },
                success: function (response) {
                    sendNotification(response.message, NotificationType.success)

                    if (listData == 'remove')
                        addToListText.textContent = "Add to List"
                    else
                        addToListText.textContent = btn.textContent
                },
                error: function (jqXHR, exception) {
                    sendNotification(jqXHR.responseText, NotificationType.error)
                }
            });
            instance.hide();
        })
    });

}
let btnAddtoList = document.querySelector('.btn-add-to-list');
let addToListContent = document.querySelector('.add-to-list__content');
new TippyMenu({
    'element': btnAddtoList,
    'content': addToListContent,
    'position': 'bottom',
    'onLoaded': addToListEvent
})

function appreciateEvent(instance) {
    let rates = document.querySelectorAll(".tippy-rate");
    let rateTextElem = document.querySelector('.appreciate .your')

    rates.forEach(rateBtn => {
        const rateNum = rateBtn.getAttribute("rate");
        rateBtn.addEventListener("click", (e) => {

            $.ajax({
                type: "POST",
                url: RATE_LINK,
                data: {
                    csrfmiddlewaretoken: window.CSRF_TOKEN,
                    rate: rateNum
                },
                success: function (response) {
                    if (!response.rate) {
                        sendNotification(response.message, NotificationType.note);
                        return;
                    }
                    else
                        sendNotification(response.message, NotificationType.success);

                    if (rateNum > 0)
                        rateTextElem.textContent = `Your rate: ${rateNum}`
                    else
                        rateTextElem.textContent = 'Rate it'
                },
                error: function (jqXHR, exception) {
                    sendNotification(jqXHR.responseText, NotificationType.error)
                }
            });

            instance.hide();
        });

    });
}
let appreciateBtn = document.querySelector('.appreciate');
let appreciateContent = document.querySelector('.appreciate__content');
new TippyMenu({
    'element': appreciateBtn,
    'content': appreciateContent,
    'position': 'bottom',
    'onLoaded': appreciateEvent
})

let reportBook = document.getElementById('report-book');
reportBook?.addEventListener('click', (e) => {
    e.preventDefault();
    let bookId = reportBook.getAttribute('data-id');

    reportWindow({
        'title': `Report "${BOOK_TITLE}"`,
        'additional': `\n\nBook id: ${bookId}`,
        'textInfo': 'Do you want to really report this book?'
    });
})

// Request to translate book
let requestTranslateBtn = document.getElementById('request-to-translate');
requestTranslateBtn?.addEventListener('click', (e) => {
    $.ajax({
        type: "POST",
        url: "/book/ajax/request-translate",
        data: {
            csrfmiddlewaretoken: window.CSRF_TOKEN,
            book: BOOK_ID
        },
        success: function (response) {
            sendNotification(response.message, NotificationType.note)
        },
        error: function (jqXHR, exception) {
            sendNotification(jqXHR.responseText, NotificationType.error)
        }
    });
});
