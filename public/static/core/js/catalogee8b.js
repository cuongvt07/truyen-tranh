let displayed_ordering_field = document.querySelector('.page-title__catalog #id_ordering');
let ordering_field = document.querySelector('.filter-container #id_ordering');
let filterForm = document.querySelector('.filter-container');

displayed_ordering_field.addEventListener('change', (e) => {
    ordering_field.value = displayed_ordering_field.value;
    filterForm.submit();
});