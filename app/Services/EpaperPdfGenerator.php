<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EditorialPickSlot;
use App\Models\EditorialPick;
use App\Models\Epaper;
use App\Models\Post;
use Illuminate\Support\Collection;

class EpaperPdfGenerator
{
    /**
     * Hasilkan dokumen PDF multi-halaman asli dari artikel-artikel terbitan di database.
     */
    public function generate(?Epaper $epaper = null): string
    {
        $picks = EditorialPick::query()
            ->with(['post' => fn ($q) => $q->withListRelations()])
            ->get()
            ->keyBy(fn (EditorialPick $pick) => $pick->slot->value);

        $headline = $picks->get(EditorialPickSlot::HEADLINE->value)?->post
            ?? Post::published()->latestPublished()->withListRelations()->first();

        $posts = Post::published()
            ->latestPublished()
            ->withListRelations()
            ->take(12)
            ->get();

        if ($headline && ! $posts->contains('id', $headline->id)) {
            $posts->prepend($headline);
        }

        $editionNo = $epaper?->edition_number ?? ('EDISI #'.now()->format('ymd'));
        $editionDate = ($epaper?->edition_date ?? now())->translatedFormat('l, d F Y');

        return $this->buildPdfDocument($editionNo, $editionDate, $headline, $posts);
    }

    /**
     * @param  Collection<int, Post>  $posts
     */
    private function buildPdfDocument(string $editionNo, string $editionDate, ?Post $headline, Collection $posts): string
    {
        $pages = [];
        $currentStream = '';
        $cursorY = 790.0;

        $startNewPage = function (bool $isFirstPage = false) use (&$pages, &$currentStream, &$cursorY, $editionNo, $editionDate): void {
            if ($currentStream !== '') {
                $pages[] = $currentStream;
            }
            $currentStream = '';

            if ($isFirstPage) {
                // Top Red Accent Bar
                $currentStream .= "0.898 0.035 0.078 rg\n";
                $currentStream .= "40 800 515 6 re f\n";

                // Masthead Title: RUANG DATA
                $currentStream .= "0 0 0 rg\n";
                $currentStream .= $this->pdfText('F2', 32, 200, 768, 'RUANG DATA');
                $currentStream .= "0.35 0.38 0.42 rg\n";
                $currentStream .= $this->pdfText('F1', 8.5, 162, 753, 'INDEPENDENT JOURNALISM  -  DATA PERSPECTIVES  -  EDISI CETAK DIGITAL');

                // Edition Metadata Double Rule Bar
                $currentStream .= "0 0 0 RG 1.2 w 40 743 m 555 743 l S\n";
                $currentStream .= "0.1 0.1 0.1 rg\n";
                $currentStream .= $this->pdfText('F2', 8.5, 42, 731, $this->cleanText($editionNo.'   |   '.strtoupper($editionDate)));
                $currentStream .= $this->pdfText('F2', 8.5, 420, 731, 'AKSES PUBLIK TERBUKA & GRATIS');
                $currentStream .= "0 0 0 RG 0.6 w 40 725 m 555 725 l S\n";

                $cursorY = 702.0;
            } else {
                // Running Header for Continuation Pages
                $currentStream .= "0.898 0.035 0.078 rg\n";
                $currentStream .= "40 802 515 3 re f\n";
                $currentStream .= "0.15 0.15 0.15 rg\n";
                $currentStream .= $this->pdfText('F2', 9.5, 40, 787, 'RUANG DATA  -  KORAN DIGITAL TERVERIFIKASI');
                $currentStream .= $this->pdfText('F1', 8.5, 380, 787, $this->cleanText($editionNo.' | '.$editionDate));
                $currentStream .= "0.7 0.7 0.7 RG 0.6 w 40 780 m 555 780 l S\n";

                $cursorY = 758.0;
            }
        };

        $ensureSpace = function (float $neededHeight) use (&$cursorY, $startNewPage): void {
            if ($cursorY - $neededHeight < 60.0) {
                $startNewPage(false);
            }
        };

        $startNewPage(true);

        if ($headline === null && $posts->isEmpty()) {
            $currentStream .= "0 0 0 rg\n";
            $currentStream .= $this->pdfText('F2', 14, 40, $cursorY, 'Belum ada berita yang diterbitkan untuk edisi cetak hari ini.');
        } else {
            // 1. LAPORAN UTAMA (HEADLINE)
            if ($headline) {
                $catName = strtoupper($this->cleanText($headline->category?->name ?? 'UTAMA'));
                $badgeText = 'HEADLINE: '.$catName;
                $badgeWidth = max(110.0, strlen($badgeText) * 5.1 + 14.0);
                $currentStream .= "0.898 0.035 0.078 rg\n";
                $currentStream .= sprintf("40 %.2f %.2f 15 re f\n", $cursorY - 3, $badgeWidth);
                $currentStream .= "1 1 1 rg\n";
                $currentStream .= $this->pdfText('F2', 8.5, 46, $cursorY + 1, $badgeText);
                $cursorY -= 24.0;

                // Headline Title wrapped
                $currentStream .= "0 0 0 rg\n";
                $titleLines = $this->wrapText($this->cleanText($headline->title), 54);
                foreach ($titleLines as $line) {
                    $ensureSpace(22.0);
                    $currentStream .= $this->pdfText('F2', 18, 40, $cursorY, $line);
                    $cursorY -= 21.0;
                }

                // Meta
                $metaLine = sprintf(
                    'Oleh %s   |   Diterbitkan: %s WIB   |   %s Pembaca',
                    $this->cleanText($headline->authorDisplayName()),
                    $headline->published_at?->translatedFormat('d M Y H:i') ?? now()->format('d M Y H:i'),
                    number_format((int) $headline->views_count)
                );
                $currentStream .= "0.4 0.42 0.45 rg\n";
                $currentStream .= $this->pdfText('F1', 8.5, 40, $cursorY, $metaLine);
                $cursorY -= 16.0;

                // Excerpt
                if (! empty($headline->excerpt)) {
                    $currentStream .= "0.15 0.15 0.15 rg\n";
                    foreach ($this->wrapText($this->cleanText($headline->excerpt), 85) as $line) {
                        $ensureSpace(14.0);
                        $currentStream .= $this->pdfText('F3', 10, 40, $cursorY, $line);
                        $cursorY -= 13.5;
                    }
                    $cursorY -= 6.0;
                }

                // Body Text of Headline
                $bodyText = $this->cleanText(strip_tags((string) $headline->body));
                if ($bodyText !== '') {
                    $currentStream .= "0.1 0.1 0.1 rg\n";
                    foreach ($this->wrapText($bodyText, 92) as $line) {
                        $ensureSpace(13.0);
                        $currentStream .= $this->pdfText('F1', 9.5, 40, $cursorY, $line);
                        $cursorY -= 12.5;
                    }
                }

                $cursorY -= 12.0;
                $currentStream .= "0.15 0.15 0.15 RG 1.0 w 40 {$cursorY} m 555 {$cursorY} l S\n";
                $cursorY -= 20.0;
            }

            // 2. INDEKS LAPORAN & PILIHAN REDAKSI LAINNYA
            $otherPosts = $posts->filter(fn (Post $p) => $p->id !== $headline?->id)->values();
            if ($otherPosts->isNotEmpty()) {
                $ensureSpace(40.0);
                $currentStream .= "0 0 0 rg\n";
                $currentStream .= $this->pdfText('F2', 12, 40, $cursorY, 'SOROTAN REDAKSI & BERITA TERKINI');
                $cursorY -= 8.0;
                $currentStream .= "0.898 0.035 0.078 RG 1.5 w 40 {$cursorY} m 230 {$cursorY} l S\n";
                $cursorY -= 18.0;

                foreach ($otherPosts as $idx => $post) {
                    $ensureSpace(85.0);

                    $catBadge = strtoupper($this->cleanText($post->category?->name ?? 'BERITA'));
                    $dateStr = $post->published_at?->translatedFormat('d M Y, H:i') ?? '-';
                    $authorStr = $this->cleanText($post->authorDisplayName());

                    // Category & Number
                    $currentStream .= "0.898 0.035 0.078 rg\n";
                    $currentStream .= $this->pdfText('F2', 8.5, 40, $cursorY, sprintf('[%02d] %s', $idx + 1, $catBadge));
                    $currentStream .= "0.45 0.45 0.45 rg\n";
                    $currentStream .= $this->pdfText('F1', 8, 170, $cursorY, "Oleh {$authorStr}  |  {$dateStr} WIB  |  {$post->views_count} pembaca");
                    $cursorY -= 14.0;

                    // Post Title
                    $currentStream .= "0 0 0 rg\n";
                    foreach ($this->wrapText($this->cleanText($post->title), 74) as $tLine) {
                        $ensureSpace(15.0);
                        $currentStream .= $this->pdfText('F2', 11.5, 40, $cursorY, $tLine);
                        $cursorY -= 14.0;
                    }

                    // Post Excerpt + Body Summary
                    $summaryText = trim($this->cleanText(($post->excerpt ? $post->excerpt.' — ' : '').strip_tags((string) $post->body)));
                    if ($summaryText !== '') {
                        $currentStream .= "0.2 0.2 0.2 rg\n";
                        $lines = array_slice($this->wrapText($summaryText, 92), 0, 6);
                        foreach ($lines as $sLine) {
                            $ensureSpace(13.0);
                            $currentStream .= $this->pdfText('F1', 9.2, 40, $cursorY, $sLine);
                            $cursorY -= 12.0;
                        }
                    }

                    $cursorY -= 6.0;
                    $currentStream .= "0.85 0.85 0.85 RG 0.5 w 40 {$cursorY} m 555 {$cursorY} l S\n";
                    $cursorY -= 16.0;
                }
            }
        }

        if ($currentStream !== '') {
            $pages[] = $currentStream;
        }

        // Append Page Footers
        $totalPages = count($pages);
        foreach ($pages as $i => $stream) {
            $pageNum = $i + 1;
            $footer = "0.75 0.75 0.75 RG 0.5 w 40 42 m 555 42 l S\n";
            $footer .= "0.45 0.45 0.45 rg\n";
            $footer .= $this->pdfText('F1', 8, 40, 30, 'Diterbitkan oleh Redaksi Ruang Data (Independent Journalism & Data Perspectives) - Dokumen Resmi E-Paper');
            $footer .= $this->pdfText('F2', 8, 495, 30, "Halaman {$pageNum} / {$totalPages}");
            $pages[$i] = $stream.$footer;
        }

        return $this->assemblePdfObjects($pages);
    }

    /**
     * @param  list<string>  $pageStreams
     */
    private function assemblePdfObjects(array $pageStreams): string
    {
        $objects = [];
        // 1: Catalog, 2: Pages, 3: Font Regular, 4: Font Bold, 5: Font Italic
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >>';

        $kids = [];
        $nextId = 6;

        foreach ($pageStreams as $stream) {
            $pageObjId = $nextId++;
            $contentObjId = $nextId++;
            $kids[] = "{$pageObjId} 0 R";

            $objects[$pageObjId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> >> /Contents %d 0 R >>',
                $contentObjId
            );
            $len = strlen($stream);
            $objects[$contentObjId] = "<< /Length {$len} >>\nstream\n{$stream}endstream";
        }

        $objects[2] = sprintf(
            '<< /Type /Pages /Kids [%s] /Count %d >>',
            implode(' ', $kids),
            count($kids)
        );

        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxId = max(array_keys($objects));
        $pdf .= "xref\n0 ".($maxId + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxId; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size ".($maxId + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }

    private function pdfText(string $font, float $size, float $x, float $y, string $text): string
    {
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);

        return sprintf("BT /%s %.1f Tf %.2f %.2f Td (%s) Tj ET\n", $font, $size, $x, $y, $escaped);
    }

    /**
     * @return list<string>
     */
    private function wrapText(string $text, int $maxChars): array
    {
        $paragraphs = preg_split('/\R+/', trim($text)) ?: [];
        $lines = [];

        foreach ($paragraphs as $para) {
            $para = trim(preg_replace('/\s+/', ' ', $para) ?? '');
            if ($para === '') {
                continue;
            }
            $wrapped = explode("\n", wordwrap($para, $maxChars, "\n", true));
            foreach ($wrapped as $wLine) {
                $lines[] = $wLine;
            }
        }

        return $lines ?: [''];
    }

    private function cleanText(string $text): string
    {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $replacements = [
            "\xE2\x80\x93" => '-',
            "\xE2\x80\x94" => '--',
            "\xE2\x80\x98" => "'",
            "\xE2\x80\x99" => "'",
            "\xE2\x80\x9C" => '"',
            "\xE2\x80\x9D" => '"',
            "\xE2\x80\xA2" => '*',
            "\xE2\x80\xA6" => '...',
        ];
        $decoded = strtr($decoded, $replacements);
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $decoded);

        return $converted !== false ? $converted : preg_replace('/[^\x20-\x7E]/', '', $decoded);
    }
}
