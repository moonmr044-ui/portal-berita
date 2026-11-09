<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EditorialPickSlot;
use App\Enums\PostStatus;
use App\Enums\RoleName;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\EditorialPick;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NewsPortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan Seeder Role & Permission dulu
        $this->call(RolePermissionSeeder::class);

        // 2. Buat Akun Pengguna / Kru Redaksi
        $admin = User::firstOrCreate(
            ['email' => 'ahmadarifaie64@gmail.com'],
            [
                'name' => 'Ahmad Arifaie (Super Admin)',
                'password' => Hash::make('password'),
                'is_crew' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles([RoleName::SUPER_ADMIN->value]);

        $pemred = User::firstOrCreate(
            ['email' => 'pemred@portalberita.test'],
            [
                'name' => 'Bambang Soediro (Pemred)',
                'password' => Hash::make('password'),
                'is_crew' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $pemred->syncRoles([RoleName::PEMRED->value]);

        $editor = User::firstOrCreate(
            ['email' => 'editor@portalberita.test'],
            [
                'name' => 'Dewi Lestari (Editor Desk)',
                'password' => Hash::make('password'),
                'is_crew' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $editor->syncRoles([RoleName::DESK_EDITOR->value]);

        $reporter1 = User::firstOrCreate(
            ['email' => 'budi.reporter@portalberita.test'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'is_crew' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $reporter1->syncRoles([RoleName::REPORTER->value]);

        $reporter2 = User::firstOrCreate(
            ['email' => 'siti.reporter@portalberita.test'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => Hash::make('password'),
                'is_crew' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $reporter2->syncRoles([RoleName::REPORTER->value]);

        $reader = User::firstOrCreate(
            ['email' => 'pembaca@portalberita.test'],
            [
                'name' => 'Ahmad Pembaca Setia',
                'password' => Hash::make('password'),
                'is_crew' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $reader->syncRoles([RoleName::READER->value]);

        // 3. Kategori Rubrik Berita
        $categoriesData = [
            ['name' => 'Nasional', 'slug' => 'nasional', 'badge_color' => '#dc2626', 'sort_order' => 1],
            ['name' => 'Megapolitan', 'slug' => 'megapolitan', 'badge_color' => '#ea580c', 'sort_order' => 2],
            ['name' => 'Ekonomi & Bisnis', 'slug' => 'ekonomi', 'badge_color' => '#059669', 'sort_order' => 3],
            ['name' => 'Bola & Olahraga', 'slug' => 'bola', 'badge_color' => '#2563eb', 'sort_order' => 4],
            ['name' => 'Teknologi & Sains', 'slug' => 'tekno', 'badge_color' => '#7c3aed', 'sort_order' => 5],
            ['name' => 'Otomotif', 'slug' => 'otomotif', 'badge_color' => '#475569', 'sort_order' => 6],
            ['name' => 'Tren & Lifestyle', 'slug' => 'tren', 'badge_color' => '#db2777', 'sort_order' => 7],
            ['name' => 'Edukasi & Humaniora', 'slug' => 'edukasi', 'badge_color' => '#0891b2', 'sort_order' => 8],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'badge_color' => $cat['badge_color'],
                    'sort_order' => $cat['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // Hubungkan editor dengan kategori
        $editor->assignedCategories()->sync([
            $categories['nasional']->id,
            $categories['tekno']->id,
            $categories['ekonomi']->id,
        ]);

        // 4. Tags
        $tagsData = [
            'IKN Nusantara',
            'Timnas Indonesia',
            'Piala Dunia 2026',
            'Kecerdasan Buatan',
            'Mobil Listrik',
            'Bursa Saham IHSG',
            'Bank Indonesia',
            'Gadget Terbaru',
            'TransJakarta',
            'Kuliner Legendaris',
        ];

        $tags = [];
        foreach ($tagsData as $tagName) {
            $tags[$tagName] = Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            );
        }

        // 5. Sample Media Gambar
        $mediaList = [];
        $sampleImages = [
            ['path' => 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Proyek pembangunan infrastruktur strategis nasional.', 'credit' => 'Dok. Antara Foto'],
            ['path' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Aksi pemain Timnas Indonesia saat berlaga di stadion internasional.', 'credit' => 'PSSI Media'],
            ['path' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Ilustrasi transformasi kecerdasan buatan dan pusat data modern.', 'credit' => 'Unsplash / DeepMind'],
            ['path' => 'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Layar pergerakan indeks harga saham gabungan di Bursa Efek Indonesia.', 'credit' => 'Bursa Efek Indonesia'],
            ['path' => 'https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Uji ketahanan armada kendaraan listrik ramah lingkungan di jalan tol.', 'credit' => 'Kompas Media'],
            ['path' => 'https://images.unsplash.com/photo-1514565131-fce0801e5785?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Pemandangan malam kawasan metropolitan Jakarta dari Bundaran HI.', 'credit' => 'Dok. Redaksi'],
            ['path' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80', 'caption' => 'Para mahasiswa berdiskusi riset teknologi dalam pameran riset inovasi kampus.', 'credit' => 'Humas Dikti'],
        ];

        foreach ($sampleImages as $img) {
            $mediaList[] = Media::firstOrCreate(
                ['caption' => $img['caption']],
                [
                    'uploader_id' => $editor->id,
                    'disk' => 'public',
                    'path' => $img['path'],
                    'width' => 1200,
                    'height' => 675,
                    'mime_type' => 'image/jpeg',
                    'size_bytes' => 245000,
                    'alt_text' => $img['caption'],
                    'credit' => $img['credit'],
                ]
            );
        }

        // 6. Breaking News Ticker
        BreakingNews::firstOrCreate(
            ['title' => 'BREAKING NEWS: Gempa Magnitudo 5.2 Guncang Pesisir Banten, Getaran Terasa hingga Jakarta Selatan, Tidak Berpotensi Tsunami.'],
            [
                'url' => '#',
                'starts_at' => now()->subHours(2),
                'ends_at' => now()->addHours(6),
                'is_active' => true,
                'created_by' => $editor->id,
            ]
        );

        BreakingNews::firstOrCreate(
            ['title' => 'KURS RUPIAH: Menguat ke Level Rp 15.620 per Dolar AS Pasca Pengumuman Cadangan Devisa Terkini.'],
            [
                'url' => '#',
                'starts_at' => now()->subHour(),
                'ends_at' => now()->addHours(4),
                'is_active' => true,
                'created_by' => $editor->id,
            ]
        );

        // 7. Artikel Berita Lengkap
        $articles = [
            [
                'category' => 'nasional',
                'title' => 'Pemerintah Resmikan Istana Garuda IKN, Siap Digunakan untuk Agenda Kenegaraan Skala Penuh',
                'excerpt' => 'Pembangunan kompleks Istana Kepresidenan dan Istana Garuda di Ibu Kota Nusantara (IKN) dinyatakan rampung dan siap menjadi pusat gravitasi pemerintahan baru.',
                'body' => '<p><strong>NUSANTARA, KOMPAS</strong> — Pemerintah secara resmi mengumumkan penyelesaian tahap final arsitektur dan konstruksi Istana Garuda di Kawasan Inti Pusat Pemerintahan (KIPP) Ibu Kota Nusantara (IKN), Kalimantan Timur.</p><p>Gedung megah bernuansa sayap garuda karya pematung kenamaan Nyoman Nuarta ini telah melalui serangkaian uji kelayakan struktural, sistem keamanan terintegrasi, dan efisiensi energi hijau berbasis panel surya serta ventilasi aerodinamis.</p><p>"Hari ini kita menyaksikan simbol kedaulatan baru bangsa. Bukan sekadar megah fisiknya, tetapi perwujudan komitmen peradaban baru yang cerdas, ramah lingkungan, dan inklusif," ujar juru bicara kepresidenan dalam konferensi pers di Nusantara.</p><p>Kawasan KIPP kini juga telah dilengkapi sistem transportasi publik tanpa emisi (Autonomous Rail Rapid Transit/ART) dan pasokan air bersih berstandar konsumsi langsung minum (tap water) di seluruh distrik pemerintahan.</p>',
                'media_idx' => 0,
                'views' => 18450,
                'published_at' => now()->subMinutes(25),
                'tags' => ['IKN Nusantara'],
            ],
            [
                'category' => 'bola',
                'title' => 'Daftar 27 Pemain Timnas Indonesia untuk Putaran Lanjutan Kualifikasi Piala Dunia 2026',
                'excerpt' => 'Pelatih memanggil komposisi terbaik termasuk pemain muda potensial dari Liga 1 dan amunisi pengalaman dari kompetisi elit Eropa demi target poin krusial.',
                'body' => '<p><strong>JAKARTA</strong> — Tim Nasional Sepak Bola Indonesia resmi merilis daftar 27 pemain yang dipersiapkan melakoni dua pertandingan krusial fase ketiga Kualifikasi Piala Dunia 2026 zona Asia pekan mendatang.</p><p>Kombinasi solid di lini pertahanan kembali diperkuat dengan kehadiran pemain-pemain tangguh yang berkompetisi di kompetisi utama benua Eropa, dipadukan dengan performa impresif pilar-pilar lokal yang bersinar sepanjang paruh musim kompetisi nasional.</p><p>Pelatih kepala menegaskan bahwa fokus utama pada sesi pemusatan latihan (TC) adalah kedisiplinan transisi bertahan ke menyerang serta efektivitas penyelesaian akhir dalam skema bola mati.</p><p>"Kami menargetkan poin maksimal di laga kandang. Dukungan penuh puluhan ribu suporter di Gelora Bung Karno akan menjadi suntikan motivasi ekstra bagi para pemain untuk mencatatkan sejarah baru sepak bola Tanah Air," ungkap asisten pelatih.</p>',
                'media_idx' => 1,
                'views' => 15200,
                'published_at' => now()->subMinutes(50),
                'tags' => ['Timnas Indonesia', 'Piala Dunia 2026'],
            ],
            [
                'category' => 'tekno',
                'title' => 'Adopsi Kecerdasan Buatan Generatif di Sektor Publik Melonjak, Efisiensi Pelayanan Meningkat Drastis',
                'excerpt' => 'Implementasi model AI lokal berdaulat membantu percepatan verifikasi data kependudukan, pengaduan masyarakat, serta pemantauan tata ruang daerah secara presisi.',
                'body' => '<p><strong>JAKARTA</strong> — Riset terbaru kolaborasi lembaga riset informatika dan kementerian terkait mencatat peningkatan signifikan adopsi teknologi kecerdasan buatan (Artificial Intelligence) pada sektor pelayanan publik di berbagai daerah di Indonesia.</p><p>Integrasi Large Language Model (LLM) berbahasa Indonesia yang dirancang khusus mampu memangkas waktu tunggu layanan administrasi publik dari hitungan hari menjadi hanya beberapa detik saja.</p><p>Selain layanan kependudukan, pemanfaatan computer vision berbasis AI juga diterapkan untuk mendeteksi potensi kemacetan lalu lintas perkotaan dan respons cepat terhadap genangan air saat curah hujan tinggi.</p><p>Para pakar siber mengingatkan pentingnya penerapan pagar pembatas etika (AI Guardrails) dan kepatuhan penuh terhadap Undang-Undang Perlindungan Data Pribadi (UU PDP) demi menjaga kepercayaan masyarakat terhadap sistem otomatisasi cerdas ini.</p>',
                'media_idx' => 2,
                'views' => 12890,
                'published_at' => now()->subHours(2),
                'tags' => ['Kecerdasan Buatan', 'Gadget Terbaru'],
            ],
            [
                'category' => 'ekonomi',
                'title' => 'IHSG Tembus Rekor Baru 7.850, Arus Modal Asing Deras Mengalir ke Saham Perbankan dan Transisi Energi',
                'excerpt' => 'Sentimen positif pasar modal ditopang oleh kinerja solid laba bersih emiten perbankan nasional, stabilitas inflasi, dan kepastian regulasi investasi energi hijau.',
                'body' => '<p><strong>JAKARTA</strong> — Indeks Harga Saham Gabungan (IHSG) Bursa Efek Indonesia kembali mencetak rekor penutupan tertinggi sepanjang sejarah pada perdagangan tengah pekan ini, ditutup melonjak di atas level psikologis 7.850.</p><p>Aksi beli bersih (net buy) oleh investor institusi asing tercatat menembus angka triliunan rupiah dalam sepekan terakhir, dipimpin oleh saham-saham perbankan buku IV dan korporasi yang aktif bertransformasi ke ekosistem energi baru terbarukan (EBT).</p><p>Analis pasar modal menilai bahwa fundamental makroekonomi domestik yang tangguh serta neraca perdagangan yang konsisten surplus menjadi daya pikat utama bagi dana global di tengah fluktuasi geopolitik dunia.</p><p>"Investor melihat prospek pertumbuhan ekonomi Indonesia tetap solid di kisaran 5 persen, didukung konsumsi domestik yang kuat dan belanja infrastruktur yang berkesinambungan," papar kepala riset sekuritas.</p>',
                'media_idx' => 3,
                'views' => 9780,
                'published_at' => now()->subHours(3),
                'tags' => ['Bursa Saham IHSG', 'Bank Indonesia'],
            ],
            [
                'category' => 'otomotif',
                'title' => 'Uji Tempuh 1.000 Km Mobil Listrik Lintas Trans Jawa: Hemat Biaya 70 Persen Dibanding Bahan Bakar Minyak',
                'excerpt' => 'Kesiapan fasilitas Stasiun Pengisian Kendaraan Listrik Umum (SPKLU) Fast Charging di sepanjang rest area membuat mobilitas antarprovinsi semakin nyaman dan terjangkau.',
                'body' => '<p><strong>SURABAYA</strong> — Ekspedisi jelajah Trans Jawa menggunakan mobil listrik keluarga sejauh lebih dari 1.000 kilometer dari Jakarta hingga Surabaya membuktikan keandalan dan efisiensi kendaraan elektrifikasi modern.</p><p>Total biaya energi yang dikeluarkan selama perjalanan hanya berkisar Rp 230.000, atau hemat hingga lebih dari 70 persen jika dibandingkan dengan kendaraan konvensional bermesin pembakaran dalam (ICE) dengan jarak tempuh setara.</p><p>Dukungan SPKLU tipe Ultra Fast Charging berkekuatan 200 kW di titik-titik rest area strategis memungkinkan pengisian daya baterai dari 20 persen hingga 80 persen diselesaikan dalam waktu kurang dari 25 menit.</p><p>Pemerintah menargetkan penambahan ratusan titik pengisian daya baru di koridor jalan tol dan pusat perbelanjaan hingga akhir tahun demi mempercepat adopsi massal kendaraan tanpa polusi.</p>',
                'media_idx' => 4,
                'views' => 8420,
                'published_at' => now()->subHours(5),
                'tags' => ['Mobil Listrik'],
            ],
            [
                'category' => 'megapolitan',
                'title' => 'Perluasan Rute TransJakarta dan Integrasi Moda LRT Mengurangi Kemacetan Koridor Sudirman-Thamrin',
                'excerpt' => 'Sistem tiket integrasi JakLingko dan penambahan armada bus listrik ramah lingkungan berhasil menaikkan angka penumpang harian transportasi publik ibukota.',
                'body' => '<p><strong>JAKARTA</strong> — Dinas Perhubungan DKI Jakarta mengumumkan keberhasilan program integrasi antarmoda transportasi massal yang menghubungkan jaringan bus TransJakarta, LRT Jabodebek, dan MRT Jakarta.</p><p>Data sensor traffic monitoring menunjukkan penurunan waktu tempuh kendaraan pribadi pada jam-jam sibuk pagi dan sore hari di sepanjang koridor utama Thamrin hingga Bundaran Senayan sebesar 18 persen.</p><p>Masyarakat mengapresiasi fasilitas halte integrasi ramah disabilitas, fasilitas parkir sepeda, serta kemudahan satu kartu untuk seluruh jaringan moda transportasi darat.</p>',
                'media_idx' => 5,
                'views' => 7150,
                'published_at' => now()->subHours(7),
                'tags' => ['TransJakarta'],
            ],
            [
                'category' => 'edukasi',
                'title' => 'Kampus-Kampus Nasional Buka Program Studi Baru Keamanan Siber dan Rekayasa Kecerdasan Buatan',
                'excerpt' => 'Langkah adaptif institusi pendidikan tinggi menjawab tingginya kebutuhan talenta digital nasional untuk memperkuat kedaulatan data dan riset terapan masa depan.',
                'body' => '<p><strong>YOGYAKARTA</strong> — Merespons perkembangan pesat teknologi digital dan kebutuhan pasar tenaga kerja global, sejumlah perguruan tinggi negeri dan swasta terkemuka serentak membuka kurikulum spesialisasi Cybersecurity dan AI Engineering.</p><p>Program ini dirancang dengan metode magang industri intensif selama satu tahun penuh bekerja sama dengan pelaku teknologi terdepan di dalam dan luar negeri.</p><p>"Mahasiswa tidak hanya dibekali teori pemrograman dasar, melainkan dilatih langsung memecahkan studi kasus nyata seperti proteksi infrastruktur kritis dan mitigasi kebocoran data," ungkap dekan fakultas ilmu komputer.</p>',
                'media_idx' => 6,
                'views' => 5300,
                'published_at' => now()->subHours(9),
                'tags' => ['Kecerdasan Buatan'],
            ],
            [
                'category' => 'tren',
                'title' => 'Eksplorasi Kuliner Nusantara: Dari Masakan Tradisional Autentik hingga Sentuhan Modern Gastronomi',
                'excerpt' => 'Generasi muda kian bangga melestarikan resep rempah warisan leluhur dengan penyajian elegan berkelas internasional yang memikat wisatawan mancanegara.',
                'body' => '<p><strong>BANDUNG</strong> — Kekayaan kuliner tradisional Nusantara tengah mengalami gelombang kebangkitan berkat sentuhan kreativitas juru masak muda yang memadukan resep otentik warisan rempah dengan teknik kuliner modern.</p><p>Festival kuliner yang diselenggarakan akhir pekan ini di berbagai kota besar ramai diserbu pengunjung yang antre mencicipi aneka kreasi sambal nusantara, olahan daging panggang asap tradisional, hingga minuman rempah rempah hangat berkasiat herbal.</p>',
                'media_idx' => 0,
                'views' => 4190,
                'published_at' => now()->subHours(12),
                'tags' => ['Kuliner Legendaris'],
            ],
        ];

        $createdPosts = [];
        foreach ($articles as $index => $item) {
            $cat = $categories[$item['category']];
            $media = $mediaList[$item['media_idx']] ?? $mediaList[0];

            $post = Post::firstOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'category_id' => $cat->id,
                    'author_id' => $reporter1->id,
                    'editor_id' => $editor->id,
                    'featured_media_id' => $media->id,
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'body' => $item['body'],
                    'status' => PostStatus::PUBLISHED,
                    'published_at' => $item['published_at'],
                    'views_count' => $item['views'],
                    'seo_title' => $item['title'],
                    'seo_description' => $item['excerpt'],
                ]
            );

            // Pasang Tags
            $tagIds = [];
            foreach ($item['tags'] as $tName) {
                if (isset($tags[$tName])) {
                    $tagIds[] = $tags[$tName]->id;
                }
            }
            if (! empty($tagIds)) {
                $post->tags()->sync($tagIds);
            }

            $createdPosts[] = $post;
        }

        // 8. Editorial Picks (1 Headline + 4 Picks) ala Kompas.com
        if (count($createdPosts) >= 5) {
            $pickSlots = [
                EditorialPickSlot::HEADLINE->value => $createdPosts[0]->id,
                EditorialPickSlot::PICK_1->value => $createdPosts[1]->id,
                EditorialPickSlot::PICK_2->value => $createdPosts[2]->id,
                EditorialPickSlot::PICK_3->value => $createdPosts[3]->id,
                EditorialPickSlot::PICK_4->value => $createdPosts[4]->id,
            ];

            foreach ($pickSlots as $slotVal => $postId) {
                EditorialPick::updateOrCreate(
                    ['slot' => $slotVal],
                    [
                        'post_id' => $postId,
                        'updated_by' => $pemred->id,
                    ]
                );
            }
        }
    }
}
