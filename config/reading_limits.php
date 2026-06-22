<?php

return [
    'guest_articles_per_day' => (int) env('GUEST_ARTICLES_PER_DAY', 5),
    'guest_chapters_per_day' => (int) env('GUEST_CHAPTERS_PER_DAY', 10),
    'unpaid_user_chapters' => (int) env('UNPAID_USER_CHAPTERS', 100),
];
