new Swiper('.popular .swiper-container', {
    slidesPerView: 2,
    slidesPerGroup: 1,
    spaceBetween: 10,

    breakpoints:
    {
        1040:
        {
            slidesPerView: 6,
        },
        550:
        {
            slidesPerView: 4,
        },
        350:
        {
            slidesPerView: 3,
        },
    }
})

new Swiper('.translation-requests .swiper-container', {
    slidesPerView: 2,
    slidesPerGroup: 1,
    spaceBetween: 10,

    breakpoints:
    {
        1040:
        {
            slidesPerView: 8,
        },
        550:
        {
            slidesPerView: 5,
        },
        320:
        {
            slidesPerView: 3,
        },
    }
})

new Swiper('.new-realeses .swiper-container', {
    slidesPerView: 1,
    slidesPerGroup: 1,
    spaceBetween: 10,
})