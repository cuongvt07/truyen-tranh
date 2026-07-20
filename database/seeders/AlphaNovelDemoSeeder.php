<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Author;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Genre;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AlphaNovelDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrNew(['email' => 'demo.reader@example.com']);
        $user->forceFill([
            'username' => 'demo_reader',
            'name' => 'Demo Reader',
            'password' => $user->password ?: Hash::make('password'),
            'avatar' => $user->avatar ?: '/static/account/images/no-ava.jpg',
            'description' => 'Temporary account used for UI preview content.',
            'email_verified_at' => $user->email_verified_at ?: now(),
        ])->save();

        $genres = collect($this->genres())->mapWithKeys(function (array $genre) {
            $model = Genre::updateOrCreate(
                ['name' => $genre['name']],
                [
                    'description' => $genre['description'],
                    'cover_image' => $genre['cover_image'] ?? null,
                    'is_hot' => $genre['is_hot'] ?? false,
                ]
            );

            return [$genre['name'] => $model];
        });

        $authors = collect($this->authors())->mapWithKeys(function (string $name) {
            return [$name => Author::updateOrCreate(
                ['name' => $name],
                ['description' => 'Demo author for Romane auf Deutsch UI preview.']
            )];
        });

        $tags = collect($this->tags())->mapWithKeys(function (string $name) {
            return [$name => Tag::firstOrCreate(['name' => $name])];
        });

        $articleIds = [];

        foreach ($this->books() as $index => $book) {
            $article = Article::withoutGlobalScopes()->firstOrNew(['title' => $book['title']]);
            $article->forceFill([
                'user_id' => $user->id,
                'alt_title' => $book['alt_title'] ?? null,
                'illustrator' => $book['illustrator'] ?? null,
                'description' => $book['description'],
                'cover_image' => $book['cover_image'],
                'background_image' => $book['background_image'] ?? null,
                'novel_type' => 0,
                'is_adult' => $book['is_adult'] ?? false,
                'year_of_release' => $book['year'] ?? 2026,
                'country' => $book['country'] ?? 1,
                'is_completed' => $book['is_completed'],
                'rating' => $book['rating'],
                'rating_count' => $book['rating_count'],
                'view' => $book['view'],
                'status' => ArticleStatus::APPROVED->value,
                'is_user_submitted' => $book['exclusive'] ?? false,
                'credit_start_chapter' => $book['credit_start_chapter'] ?? null,
                'credit_per_chapter' => $book['credit_per_chapter'] ?? 0,
            ])->save();

            $articleIds[] = $article->id;

            $article->authors()->sync([$authors[$book['author']]->id]);
            $article->genres()->sync(collect($book['genres'])->map(fn (string $name) => $genres[$name]->id)->all());
            $article->tags()->sync(collect($book['tags'])->map(fn (string $name) => $tags[$name]->id)->all());

            foreach ($this->chapterTitles($book['tone']) as $number => $title) {
                $chapter = Chapter::withoutGlobalScopes()->firstOrNew([
                    'article_id' => $article->id,
                    'number' => $number + 1,
                ]);

                $chapter->forceFill([
                    'title' => $title,
                    'content' => $this->chapterContent($book['title'], $number + 1, $book['tone']),
                    'view' => max(20, $book['view'] - (($number + 1) * 37)),
                    'credit_cost' => (($book['credit_start_chapter'] ?? null) && ($number + 1) >= $book['credit_start_chapter'])
                        ? ($book['credit_per_chapter'] ?? 0)
                        : 0,
                    'published_at' => now()->subDays(12 - $number),
                ])->save();
            }

            $this->seedComments($article, $user, $index);
        }

        foreach (Article::withoutGlobalScopes()->whereIn('id', $articleIds)->get() as $article) {
            $similar = collect($articleIds)
                ->reject(fn (int $id) => $id === $article->id)
                ->shuffle()
                ->take(8)
                ->values()
                ->all();

            $article->forceFill([
                'similar_article_ids' => $similar,
                'translation_request_article_ids' => array_slice($similar, 0, 4),
                'related_genre_ids' => $article->genres()->pluck('genres.id')->values()->all(),
            ])->save();
        }
    }

    private function genres(): array
    {
        return [
            ['name' => 'Romance', 'description' => 'Love stories, arranged marriages, second chances, and slow burn tension.', 'cover_image' => '/media/genres/romance.jpg', 'is_hot' => true],
            ['name' => 'Billionaire', 'description' => 'Powerful CEOs, contracts, luxury, secrets, and boardroom romance.', 'is_hot' => true],
            ['name' => 'Werewolf', 'description' => 'Mate bonds, packs, alphas, rival clans, and supernatural drama.', 'is_hot' => true],
            ['name' => 'Fantasy', 'description' => 'Magic kingdoms, dragon courts, academy trials, and epic stakes.', 'cover_image' => '/media/genres/fantasy.jpg', 'is_hot' => true],
            ['name' => 'Paranormal', 'description' => 'Ghosts, curses, hidden powers, and dark family legacies.', 'is_hot' => true],
            ['name' => 'Young Adult', 'description' => 'Campus drama, coming of age, first love, and friendship.', 'is_hot' => false],
            ['name' => 'Drama', 'description' => 'High emotion, family secrets, betrayal, and personal conflict.', 'is_hot' => true],
            ['name' => 'Comedy', 'description' => 'Lighthearted romance, mistaken identities, and chaotic friendships.', 'cover_image' => '/media/genres/comedy.jpg', 'is_hot' => false],
            ['name' => 'Action', 'description' => 'Danger, chase scenes, revenge plots, and sharp confrontations.', 'cover_image' => '/media/genres/action.jpg', 'is_hot' => false],
            ['name' => 'Adventure', 'description' => 'Journeys, quests, new worlds, and high-risk discoveries.', 'is_hot' => false],
            ['name' => 'LGBTQ+', 'description' => 'Queer romance, identity, chosen family, and heartfelt stories.', 'is_hot' => false],
            ['name' => 'Contemporary', 'description' => 'Modern relationships, workplace conflict, and real-life choices.', 'is_hot' => true],
            ['name' => 'Historical', 'description' => 'Court politics, arranged alliances, old kingdoms, and grand romance.', 'is_hot' => false],
        ];
    }

    private function authors(): array
    {
        return [
            'Margarette Grey',
            'Page Hunter',
            'Lucy Avi',
            'Krystal Key',
            'Safiya Abdulmumin',
            'Bella Silva',
            'Red Butterfly',
            'Moon Quill',
            'Mira Stone',
            'C. A. Knight',
        ];
    }

    private function tags(): array
    {
        return [
            'Workplace Romance',
            'Friends to Lovers',
            'Arranged Marriage',
            'Enemies to Lovers',
            'Second Chance',
            'Secret Baby',
            'Forbidden Love',
            'Betrayal',
            'Coming of Age',
            'Strong Female Lead',
            'Alpha Male',
            'Contract Relationship',
            'Hidden Identity',
            'Lighthearted',
            'Reverse Harem',
            'Royalty',
            'Dragon',
            'Abuse',
            'Arrogant Boss',
            'GxG',
            'Slow Burn',
        ];
    }

    private function books(): array
    {
        return [
            [
                'title' => 'Mr. Untouchable: Bad Boy Billionaires',
                'alt_title' => 'Bad Boy Billionaires Book 1',
                'author' => 'Margarette Grey',
                'genres' => ['Billionaire', 'Romance', 'Contemporary'],
                'tags' => ['Workplace Romance', 'Arrogant Boss', 'Enemies to Lovers', 'Contract Relationship'],
                'tone' => 'billionaire',
                'description' => 'A guarded hotel heir hires the one woman who refuses to flatter him. Their contract begins as damage control, then turns into a dangerous negotiation between pride, desire, and the truth he has spent years hiding.',
                'cover_image' => '/images/articles/novelight/covers/a-wicked-husband-1srnxgi-f32b0534c3.jpg',
                'background_image' => '/images/articles/novelight/backgrounds/accidental-baby-1b3b0c7d26.jpg',
                'view' => 128430,
                'rating' => 9.4,
                'rating_count' => 2187,
                'is_completed' => false,
                'exclusive' => true,
                'credit_start_chapter' => 5,
                'credit_per_chapter' => 3,
            ],
            [
                'title' => 'De Mejor Amigo a Prometido',
                'alt_title' => 'From Best Friend to Fiance',
                'author' => 'Page Hunter',
                'genres' => ['Romance', 'Comedy', 'Contemporary'],
                'tags' => ['Friends to Lovers', 'Lighthearted', 'Contract Relationship', 'Slow Burn'],
                'tone' => 'romance',
                'description' => 'Her best friend volunteers as her fake fiance for one family dinner. One dinner becomes a weekend, the weekend becomes a lie everyone believes, and the line between performance and confession starts to disappear.',
                'cover_image' => '/images/articles/novelight/covers/a-game-to-make-him-fall-sdqrboo-c18340ba59.jpg',
                'background_image' => '/images/articles/novelight/backgrounds/a-barbaric-proposal-a19e3f8447.jpg',
                'view' => 98220,
                'rating' => 9.1,
                'rating_count' => 1760,
                'is_completed' => true,
            ],
            [
                'title' => 'Accidental Baby, Reluctant Bride',
                'author' => 'Lucy Avi',
                'genres' => ['Romance', 'Drama', 'Billionaire'],
                'tags' => ['Secret Baby', 'Second Chance', 'Betrayal', 'Strong Female Lead'],
                'tone' => 'drama',
                'description' => 'After one reckless night, Elena leaves the city with a secret. Three years later the father of her child returns as her company owner, and he wants answers she cannot afford to give.',
                'cover_image' => '/images/articles/novelight/covers/accidental-baby-nwt2hfi-1dc873770c.jpg',
                'background_image' => '/images/articles/novelight/backgrounds/accidental-baby-1b3b0c7d26.jpg',
                'view' => 87410,
                'rating' => 8.9,
                'rating_count' => 1421,
                'is_completed' => false,
                'credit_start_chapter' => 6,
                'credit_per_chapter' => 2,
            ],
            [
                'title' => 'The Alpha Who Remembered Me',
                'author' => 'Krystal Key',
                'genres' => ['Werewolf', 'Romance', 'Paranormal'],
                'tags' => ['Alpha Male', 'Hidden Identity', 'Second Chance', 'Forbidden Love'],
                'tone' => 'werewolf',
                'description' => 'The pack erased Mira from its records, but not from the memory of the alpha who once chose her. When a moon curse calls her home, their bond becomes the only weapon strong enough to stop a war.',
                'cover_image' => '/images/articles/novelight/covers/a-barbaric-proposal-18msmcr-06f63228d6.jpg',
                'background_image' => '/images/articles/novelight/backgrounds/a-barbaric-proposal-a19e3f8447.jpg',
                'view' => 110540,
                'rating' => 9.5,
                'rating_count' => 2344,
                'is_completed' => false,
                'exclusive' => true,
            ],
            [
                'title' => 'Dragon Court Assistant',
                'author' => 'Safiya Abdulmumin',
                'genres' => ['Fantasy', 'Adventure', 'Romance'],
                'tags' => ['Dragon', 'Royalty', 'Hidden Identity', 'Strong Female Lead'],
                'tone' => 'fantasy',
                'description' => 'Nara applies for a palace clerk job and accidentally becomes assistant to the dragon prince. Between court politics, enchanted ledgers, and a crown that chooses its own ruler, paperwork has never been so deadly.',
                'cover_image' => '/images/articles/novelight/covers/a-fortunetelling-princess-sxqvduh-22fc327bb3.jpg',
                'background_image' => '/images/articles/novelight/backgrounds/after-crossdressing-and-provoking-long-aotian-b5bd811a8f.jpg',
                'view' => 76590,
                'rating' => 9.0,
                'rating_count' => 1184,
                'is_completed' => false,
            ],
            [
                'title' => 'After the Last Confession',
                'author' => 'Bella Silva',
                'genres' => ['Young Adult', 'Romance', 'Drama'],
                'tags' => ['Coming of Age', 'Slow Burn', 'Betrayal', 'GxG'],
                'tone' => 'campus',
                'description' => 'A school broadcast accidentally exposes a love letter never meant to be heard. Now two rivals must share the same club room while the whole campus waits for the next confession.',
                'cover_image' => '/images/articles/novelight/covers/after-the-th-confession-the-cold-school-beautys-personality-collapsed-74c8a7dee9.jpg',
                'background_image' => null,
                'view' => 65300,
                'rating' => 8.8,
                'rating_count' => 934,
                'is_completed' => true,
            ],
            [
                'title' => 'A Secretly Capable Child Is Seeking Her Dad',
                'author' => 'Red Butterfly',
                'genres' => ['Drama', 'Comedy', 'Contemporary'],
                'tags' => ['Secret Baby', 'Hidden Identity', 'Lighthearted', 'Strong Female Lead'],
                'tone' => 'family',
                'description' => 'Six-year-old Lili is done waiting for adults to fix her family. Armed with a tablet, a fake email account, and terrifying negotiation skills, she starts interviewing candidates for the father she has never met.',
                'cover_image' => '/images/articles/novelight/covers/a-secretly-capable-child-is-seeking-for-her-dad-71e85daf61.jpg',
                'background_image' => null,
                'view' => 54880,
                'rating' => 8.7,
                'rating_count' => 811,
                'is_completed' => false,
            ],
            [
                'title' => 'The CEO Hates Pretty Lies',
                'author' => 'Mira Stone',
                'genres' => ['Billionaire', 'Drama', 'Romance'],
                'tags' => ['Workplace Romance', 'Betrayal', 'Arrogant Boss', 'Enemies to Lovers'],
                'tone' => 'billionaire',
                'description' => 'A PR crisis forces a cold CEO and a whistleblower designer into the same penthouse. Every lie they tell the press makes the truth between them harder to ignore.',
                'cover_image' => '/images/articles/novelight/covers/a-mastermind-no-im-just-the-livein-soninlaw-gni62bg-10c95969fd.jpg',
                'background_image' => null,
                'view' => 70230,
                'rating' => 8.6,
                'rating_count' => 1033,
                'is_completed' => false,
                'credit_start_chapter' => 5,
                'credit_per_chapter' => 3,
            ],
            [
                'title' => 'Moonlit Pack Promise',
                'author' => 'Moon Quill',
                'genres' => ['Werewolf', 'Paranormal', 'Drama'],
                'tags' => ['Alpha Male', 'Abuse', 'Forbidden Love', 'Second Chance'],
                'tone' => 'werewolf',
                'description' => 'Born without a wolf, Sera was promised to a rival pack as payment for peace. But the night before the ceremony, her missing wolf wakes and chooses the one alpha she was forbidden to love.',
                'cover_image' => '/images/articles/novelight/covers/a-knight-who-eternally-regresses-6a987361ad.jpg',
                'background_image' => null,
                'view' => 83670,
                'rating' => 9.2,
                'rating_count' => 1504,
                'is_completed' => false,
            ],
            [
                'title' => 'Fake Job at the Magic Academy',
                'author' => 'C. A. Knight',
                'genres' => ['Fantasy', 'Comedy', 'Adventure'],
                'tags' => ['Hidden Identity', 'Lighthearted', 'Royalty', 'Dragon'],
                'tone' => 'fantasy',
                'description' => 'Tobin lies his way into a teaching job at the empire academy. Unfortunately, the students are geniuses, the headmaster sees everything, and one tiny dragon has decided he smells like destiny.',
                'cover_image' => '/images/articles/novelight/covers/i-got-a-fake-job-at-the-academy-68ea1a358f.jpg',
                'background_image' => null,
                'view' => 60190,
                'rating' => 8.5,
                'rating_count' => 760,
                'is_completed' => false,
            ],
            [
                'title' => 'A Barbaric Proposal',
                'author' => 'Bella Silva',
                'genres' => ['Romance', 'Drama', 'Historical'],
                'tags' => ['Arranged Marriage', 'Royalty', 'Forbidden Love', 'Strong Female Lead'],
                'tone' => 'drama',
                'description' => 'Princess Elara accepts a warlord husband to save her city, expecting chains and conquest. Instead she finds a man who offers her a throne, a knife, and the choice to rule beside him.',
                'cover_image' => '/images/articles/novelight/covers/a-barbaric-proposal-18msmcr-06f63228d6.jpg',
                'background_image' => '/images/articles/novelight/backgrounds/a-barbaric-proposal-a19e3f8447.jpg',
                'view' => 92540,
                'rating' => 9.3,
                'rating_count' => 1972,
                'is_completed' => true,
            ],
            [
                'title' => 'Secretly Rich Genius',
                'author' => 'Safiya Abdulmumin',
                'genres' => ['Contemporary', 'Comedy', 'Romance'],
                'tags' => ['Hidden Identity', 'Lighthearted', 'Coming of Age', 'Workplace Romance'],
                'tone' => 'romance',
                'description' => 'Everyone at the startup treats intern June like the coffee runner. Nobody knows she owns half the patent portfolio, and she plans to keep it that way until one honest engineer ruins her perfect disguise.',
                'cover_image' => '/images/articles/novelight/covers/a-transmigrators-privilege-rs4jh3p-e7d559dcb9.jpg',
                'background_image' => null,
                'view' => 43870,
                'rating' => 8.4,
                'rating_count' => 522,
                'is_completed' => false,
            ],
            [
                'title' => 'Reverse Harem at Midnight Office',
                'author' => 'Page Hunter',
                'genres' => ['Paranormal', 'Romance', 'Comedy'],
                'tags' => ['Reverse Harem', 'Workplace Romance', 'Hidden Identity', 'Lighthearted'],
                'tone' => 'paranormal',
                'description' => 'Mina takes a night shift at a haunted law firm and discovers every partner is cursed, handsome, and desperate for her help. The employee handbook did not mention supernatural attachment issues.',
                'cover_image' => '/images/articles/novelight/covers/oclock-marionette-1640051357-rfo6d65-989a1b0fe6.jpg',
                'background_image' => null,
                'view' => 51220,
                'rating' => 8.9,
                'rating_count' => 689,
                'is_completed' => false,
                'is_adult' => true,
            ],
            [
                'title' => 'The Knight Who Eternally Regresses',
                'author' => 'C. A. Knight',
                'genres' => ['Action', 'Fantasy', 'Adventure'],
                'tags' => ['Betrayal', 'Royalty', 'Dragon', 'Strong Female Lead'],
                'tone' => 'action',
                'description' => 'A defeated knight wakes at the morning of his first battle with every scar still burning in memory. This time he will save the kingdom, expose the traitor, and refuse the fate written for him.',
                'cover_image' => '/images/articles/novelight/covers/a-knight-who-eternally-regresses-6a987361ad.jpg',
                'background_image' => null,
                'view' => 119300,
                'rating' => 9.6,
                'rating_count' => 2450,
                'is_completed' => false,
            ],
            [
                'title' => 'Getting Back Together in Paris',
                'author' => 'Lucy Avi',
                'genres' => ['Romance', 'Contemporary', 'Drama'],
                'tags' => ['Second Chance', 'Slow Burn', 'Betrayal', 'Forbidden Love'],
                'tone' => 'romance',
                'description' => 'A divorced pastry chef and her ex-husband inherit the same Paris bakery. To sell it, they must reopen for one summer and survive the recipes, memories, and customers who still believe in them.',
                'cover_image' => '/images/articles/novelight/covers/after-rebirth-the-real-young-master-began-to-maintain-his-health-b0f8f4b90a.jpg',
                'background_image' => null,
                'view' => 68920,
                'rating' => 9.0,
                'rating_count' => 1210,
                'is_completed' => true,
            ],
        ];
    }

    private function chapterTitles(string $tone): array
    {
        return match ($tone) {
            'billionaire' => [
                'Book I - Chapter 1: The Contract',
                'Chapter 2: Terms and Conditions',
                'Chapter 3: A Public Lie',
                'Chapter 4: The Penthouse Rule',
                'Chapter 5: Damage Control',
                'Chapter 6: What Money Cannot Buy',
            ],
            'werewolf' => [
                'Book I - Chapter 1: Under the Silver Moon',
                'Chapter 2: The Scent of Home',
                'Chapter 3: Pack Law',
                'Chapter 4: The Mark That Burned',
                'Chapter 5: Rival Claws',
                'Chapter 6: Mate or Enemy',
            ],
            'fantasy' => [
                'Book I - Chapter 1: The Wrong Door',
                'Chapter 2: Ink, Fire, and Oaths',
                'Chapter 3: The Crown Ledger',
                'Chapter 4: Trial by Flame',
                'Chapter 5: The Hidden Wing',
                'Chapter 6: A Dragon Signs First',
            ],
            'action' => [
                'Book I - Chapter 1: The Day He Died',
                'Chapter 2: Blade Memory',
                'Chapter 3: The Captain Lies',
                'Chapter 4: Siege Bells',
                'Chapter 5: A Map of Betrayal',
                'Chapter 6: One More Dawn',
            ],
            default => [
                'Book I - Chapter 1: The Promise',
                'Chapter 2: Almost Honest',
                'Chapter 3: A Message at Midnight',
                'Chapter 4: The Dinner Everyone Misread',
                'Chapter 5: Close Enough to Hurt',
                'Chapter 6: The Choice',
            ],
        };
    }

    private function chapterContent(string $title, int $chapter, string $tone): string
    {
        $opening = match ($tone) {
            'billionaire' => 'The elevator doors opened onto a floor so quiet it felt staged. Glass walls reflected the city below, and every assistant in the room pretended not to watch the meeting that was about to ruin someone.',
            'werewolf' => 'The forest knew before she did. Every branch turned silver under the moon, every shadow held its breath, and somewhere beyond the ridge a wolf answered a name she had tried to forget.',
            'fantasy' => 'The palace archive smelled of dust, ink, and sleeping fire. On the desk in front of her, a contract unfolded by itself and added a final line in red: accepted by royal command.',
            'campus' => 'By third period, everyone had heard the confession twice. Phones glowed under desks, whispers chased her down the hallway, and the only person not laughing was the one who mattered.',
            'action' => 'The battlefield was exactly as he remembered it: mud, smoke, and the captain giving the order that would kill them all. This time, he stepped out of formation before the horn sounded.',
            default => 'Morning arrived too brightly for people keeping secrets. She stood at the kitchen counter, reheating coffee and pretending the message on her phone had not changed the shape of the day.',
        };

        return implode("\n", [
            '<p>'.$opening.'</p>',
            '<p>In <strong>'.$title.'</strong>, chapter '.$chapter.' pushes the relationship forward while keeping enough tension for the next scroll. The scene is written as demo content so the detail page can show real paragraph spacing, long text rhythm, and inline chapter reading states.</p>',
            '<p>Dialogue moved faster than either of them expected. A promise was offered, refused, and then quietly accepted in the pause that followed. That pause said more than the argument did.</p>',
            '<p>By the end of the chapter, one secret had become harder to protect and one choice had become impossible to delay. The next chapter begins where this one leaves the reader wanting one more page.</p>',
        ]);
    }

    private function seedComments(Article $article, User $user, int $index): void
    {
        $comments = [
            'The first chapter layout is easy to read and the pacing feels strong.',
            'I like this cover and the tags make it clear what kind of story this is.',
            'Adding this to my library. The tension between the leads works well.',
        ];

        foreach ($comments as $offset => $content) {
            Comment::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'article_id' => $article->id,
                    'content' => $content,
                ],
                [
                    'parent_id' => null,
                    'score' => 8 - $offset + ($index % 3),
                    'is_hidden' => false,
                ]
            );
        }
    }
}
