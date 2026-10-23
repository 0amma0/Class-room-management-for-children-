<?php

namespace App\Actions\Guru;

use App\Exceptions\StoryIllustrationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateStoryStoryboard
{
    private const int MAX_SCENES = 6;

    public function generate(string $title, string $storyText): string
    {
        $scenes = $this->splitIntoScenes($storyText);
        $svg = $this->renderStoryboard($title, $scenes);
        $path = 'story-illustrations/'.Str::uuid().'.svg';

        if (! Storage::disk('local')->put($path, $svg)) {
            throw new StoryIllustrationException('Storyboard gagal disimpan. Periksa ruang penyimpanan lalu coba kembali.');
        }

        return $path;
    }

    /**
     * @return list<string>
     */
    private function splitIntoScenes(string $storyText): array
    {
        $scenes = preg_split('/(?:\R\s*\R+|(?<=[.!?])\s+)/u', trim($storyText), -1, PREG_SPLIT_NO_EMPTY);
        $scenes = is_array($scenes) ? array_values(array_filter(array_map('trim', $scenes))) : [];

        if (count($scenes) <= self::MAX_SCENES) {
            return $scenes;
        }

        $visibleScenes = array_slice($scenes, 0, self::MAX_SCENES - 1);
        $visibleScenes[] = implode(' ', array_slice($scenes, self::MAX_SCENES - 1));

        return $visibleScenes;
    }

    /**
     * @param  list<string>  $scenes
     */
    private function renderStoryboard(string $title, array $scenes): string
    {
        $width = 1040;
        $panelWidth = 480;
        $panelHeight = 300;
        $rows = (int) ceil(count($scenes) / 2);
        $height = 110 + ($rows * 320) + 30;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 '.$width.' '.$height.'">';
        $svg .= '<rect width="100%" height="100%" fill="#fffaf0"/>';
        $svg .= '<text x="30" y="48" fill="#17324d" font-family="Arial, sans-serif" font-size="28" font-weight="700">'.$this->escape($title).'</text>';
        $svg .= '<text x="30" y="78" fill="#637083" font-family="Arial, sans-serif" font-size="15">Storyboard cerita · ilustrasi lokal</text>';

        foreach ($scenes as $index => $scene) {
            $x = 30 + (($index % 2) * 500);
            $y = 110 + ((int) floor($index / 2) * 320);
            $svg .= $this->renderPanel($scene, $index, $x, $y, $panelWidth, $panelHeight);
        }

        return $svg.'</svg>';
    }

    private function renderPanel(string $scene, int $index, int $x, int $y, int $width, int $height): string
    {
        $backgrounds = ['#e5f3ff', '#fff0d5', '#e9f7e8', '#fbe8ef', '#e9e8ff', '#e2f5f1'];
        $background = $backgrounds[$index % count($backgrounds)];
        $illustration = $this->describeScene($scene);
        $svg = '<g class="storyboard-panel">';
        $svg .= '<rect x="'.$x.'" y="'.$y.'" width="'.$width.'" height="'.$height.'" rx="22" fill="#ffffff" stroke="#d9e1e8" stroke-width="2"/>';
        $svg .= '<g data-setting="'.$illustration['setting'].'" data-action="'.$illustration['action'].'" data-props="'.implode(',', $illustration['props']).'">';
        $svg .= '<rect x="'.($x + 1).'" y="'.($y + 1).'" width="'.($width - 2).'" height="165" rx="21" fill="'.$background.'"/>';
        $svg .= $this->renderSetting($illustration['setting'], $x, $y);
        $svg .= $this->renderProps($illustration['props'], $x, $y);
        $svg .= $this->renderCharacters($illustration['action'], $x, $y);
        $svg .= '</g>';
        $svg .= '<circle cx="'.($x + 35).'" cy="'.($y + 35).'" r="18" fill="#ffffff"/>';
        $svg .= '<text x="'.($x + 35).'" y="'.($y + 41).'" text-anchor="middle" fill="#17324d" font-family="Arial, sans-serif" font-size="16" font-weight="700">'.($index + 1).'</text>';
        $svg .= '<text x="'.($x + 22).'" y="'.($y + 195).'" fill="#17324d" font-family="Arial, sans-serif" font-size="14" font-weight="700">ADEGAN '.($index + 1).'</text>';

        foreach ($this->wrapText($scene, 54, 4) as $lineIndex => $line) {
            $svg .= '<text x="'.($x + 22).'" y="'.($y + 222 + ($lineIndex * 19)).'" fill="#39485a" font-family="Arial, sans-serif" font-size="15">'.$this->escape($line).'</text>';
        }

        return $svg.'</g>';
    }

    /**
     * @return array{setting: string, action: string, props: list<string>}
     */
    private function describeScene(string $scene): array
    {
        $setting = match (true) {
            $this->sceneHasAny($scene, ['sungai', 'laut', 'danau', 'kolam', 'pantai', 'hujan']) => 'water',
            $this->sceneHasAny($scene, ['sekolah', 'kelas', 'belajar', 'guru']) => 'school',
            $this->sceneHasAny($scene, ['rumah', 'keluarga', 'dapur']) => 'home',
            $this->sceneHasAny($scene, ['hutan', 'kebun', 'taman', 'pohon', 'bunga']) => 'garden',
            default => 'outdoor',
        };
        $action = match (true) {
            $this->sceneHasAny($scene, ['menolong', 'membantu', 'berbagi', 'membagikan', 'memberi']) => 'helping',
            $this->sceneHasAny($scene, ['membaca', 'buku', 'belajar', 'menulis']) => 'reading',
            $this->sceneHasAny($scene, ['bermain', 'berlari', 'bermain bola', 'tertawa']) => 'playing',
            $this->sceneHasAny($scene, ['menanam', 'menyiram']) => 'planting',
            $this->sceneHasAny($scene, ['berdoa', 'beribadah']) => 'praying',
            $this->sceneHasAny($scene, ['pergi', 'berjalan', 'datang', 'berangkat']) => 'walking',
            default => 'talking',
        };
        $props = [];

        foreach ([
            'tree' => ['pohon', 'hutan', 'kebun'],
            'flower' => ['bunga', 'mawar', 'melati'],
            'ball' => ['bola'],
            'book' => ['buku', 'membaca', 'belajar'],
            'fish' => ['ikan'],
            'bird' => ['burung'],
            'cat' => ['kucing'],
            'dog' => ['anjing'],
            'food' => ['makanan', 'roti', 'buah', 'nasi'],
        ] as $prop => $keywords) {
            if ($this->sceneHasAny($scene, $keywords)) {
                $props[] = $prop;
            }
        }

        return ['setting' => $setting, 'action' => $action, 'props' => $props];
    }

    /**
     * @param  list<string>  $keywords
     */
    private function sceneHasAny(string $scene, array $keywords): bool
    {
        $normalizedScene = Str::lower($scene);

        foreach ($keywords as $keyword) {
            $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($keyword, '/').'(?![\p{L}\p{N}])/u';
            if (preg_match($pattern, $normalizedScene) === 1) {
                return true;
            }
        }

        return false;
    }

    private function renderSetting(string $setting, int $x, int $y): string
    {
        $ground = '<path d="M'.($x + 1).' '.($y + 140).' Q'.($x + 100).' '.($y + 120).' '.($x + 190).' '.($y + 145).' T'.($x + 479).' '.($y + 135).' V'.($y + 166).' H'.($x + 1).'Z" fill="#b9dfb4"/>';

        return match ($setting) {
            'water' => '<path d="M'.($x + 1).' '.($y + 124).' Q'.($x + 100).' '.($y + 110).' '.($x + 190).' '.($y + 126).' T'.($x + 479).' '.($y + 120).' V'.($y + 166).' H'.($x + 1).'Z" fill="#75c5e5"/>'
                .'<path d="M'.($x + 270).' '.($y + 140).'q16 -9 32 0t32 0t32 0" fill="none" stroke="#e9f9ff" stroke-width="5" stroke-linecap="round"/>',
            'school' => $ground.$this->renderBuilding($x + 340, $y + 72, '#f5bd76'),
            'home' => $ground.$this->renderBuilding($x + 340, $y + 78, '#f2a989'),
            'garden' => $ground.$this->renderTree($x + 360, $y + 126),
            default => '<circle cx="'.($x + 390).'" cy="'.($y + 58).'" r="25" fill="#ffd36b"/>'.$ground,
        };
    }

    private function renderBuilding(int $x, int $y, string $wallColor): string
    {
        return '<path d="M'.$x.' '.($y + 38).'l44 -36 44 36v48h-88z" fill="'.$wallColor.'" stroke="#9a6846" stroke-width="3"/>'
            .'<path d="M'.($x - 8).' '.($y + 39).'l52 -44 52 44" fill="none" stroke="#b36d5c" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>'
            .'<rect x="'.($x + 34).'" y="'.($y + 52).'" width="20" height="34" rx="3" fill="#fff4dc"/>'
            .'<rect x="'.($x + 9).'" y="'.($y + 48).'" width="16" height="16" fill="#c8ebf5"/>'
            .'<rect x="'.($x + 63).'" y="'.($y + 48).'" width="16" height="16" fill="#c8ebf5"/>';
    }

    private function renderTree(int $x, int $y): string
    {
        return '<path d="M'.$x.' '.$y.'v-54" stroke="#80613b" stroke-width="12"/>'
            .'<circle cx="'.$x.'" cy="'.($y - 63).'" r="31" fill="#67ad69"/>'
            .'<circle cx="'.($x - 27).'" cy="'.($y - 48).'" r="22" fill="#80c77b"/>'
            .'<circle cx="'.($x + 26).'" cy="'.($y - 47).'" r="22" fill="#80c77b"/>';
    }

    /**
     * @param  list<string>  $props
     */
    private function renderProps(array $props, int $x, int $y): string
    {
        $svg = '';

        foreach (array_slice($props, 0, 2) as $index => $prop) {
            $propX = $x + ($index === 0 ? 90 : 420);
            $propY = $y + 143;
            $svg .= match ($prop) {
                'tree' => $this->renderTree($propX, $propY),
                'flower' => '<path d="M'.$propX.' '.$propY.'v-25" stroke="#478b4b" stroke-width="4"/>'
                    .'<circle cx="'.$propX.'" cy="'.($propY - 31).'" r="8" fill="#e681aa"/>'
                    .'<circle cx="'.($propX - 7).'" cy="'.($propY - 25).'" r="7" fill="#e681aa"/>'
                    .'<circle cx="'.($propX + 7).'" cy="'.($propY - 25).'" r="7" fill="#e681aa"/>',
                'ball' => '<circle cx="'.$propX.'" cy="'.($propY - 12).'" r="15" fill="#f17b58" stroke="#ffffff" stroke-width="3"/>'
                    .'<path d="M'.($propX - 13).' '.($propY - 12).'h26M'.$propX.' '.($propY - 26).'v28" stroke="#ffffff" stroke-width="2"/>',
                'book' => '<path d="M'.($propX - 20).' '.($propY - 32).'q10 -5 20 3q10 -8 20 -3v30q-10 -5 -20 3q-10 -8 -20 -3z" fill="#fff5d8" stroke="#5c7eb5" stroke-width="3"/>',
                'fish' => '<path d="M'.($propX - 21).' '.($propY - 12).'q20 -22 39 0q-19 22 -39 0z" fill="#59a9d0"/>'
                    .'<path d="M'.($propX + 17).' '.($propY - 12).'l15 -12v24z" fill="#59a9d0"/>'
                    .'<circle cx="'.($propX - 9).'" cy="'.($propY - 15).'" r="2" fill="#17324d"/>',
                'bird' => '<path d="M'.($propX - 22).' '.($propY - 5).'q15 -24 31 0q-16 20 -31 0z" fill="#f2bd54"/>'
                    .'<path d="M'.($propX + 8).' '.($propY - 7).'l13 5 -13 5" fill="#e68158"/>',
                'cat', 'dog' => '<circle cx="'.$propX.'" cy="'.($propY - 12).'" r="15" fill="#d99162"/>'
                    .'<path d="M'.($propX - 12).' '.($propY - 23).'l-8 -13 17 7M'.($propX + 12).' '.($propY - 23).'l8 -13 -17 7" fill="#d99162"/>'
                    .'<circle cx="'.($propX - 5).'" cy="'.($propY - 14).'" r="2" fill="#17324d"/>'
                    .'<circle cx="'.($propX + 5).'" cy="'.($propY - 14).'" r="2" fill="#17324d"/>',
                'food' => '<path d="M'.($propX - 15).' '.($propY - 5).'q-10 -20 7 -28q18 8 8 28z" fill="#ed765f"/>'
                    .'<path d="M'.$propX.' '.($propY - 32).'q7 -11 16 -7" fill="none" stroke="#518c50" stroke-width="4"/>',
                default => '',
            };
        }

        return $svg;
    }

    private function renderCharacters(string $action, int $x, int $y): string
    {
        $characters = '<circle cx="'.($x + 190).'" cy="'.($y + 94).'" r="20" fill="#f1c49b"/>'
            .'<path d="M'.($x + 166).' '.($y + 145).'q2 -34 24 -34t24 34z" fill="#5385c5"/>'
            .'<circle cx="'.($x + 250).'" cy="'.($y + 103).'" r="18" fill="#dfad83"/>'
            .'<path d="M'.($x + 228).' '.($y + 150).'q2 -31 22 -31t22 31z" fill="#e68a73"/>';

        return $characters.match ($action) {
            'helping' => '<path d="M'.($x + 207).' '.($y + 126).'l12 -8 12 8" fill="none" stroke="#f1c49b" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>',
            'reading' => '<path d="M'.($x + 208).' '.($y + 128).'q10 -6 18 0v18q-9 -5 -18 0z" fill="#fff5d8" stroke="#5c7eb5" stroke-width="2"/>',
            'playing' => '<path d="M'.($x + 210).' '.($y + 129).'l20 -12" stroke="#f1c49b" stroke-width="7" stroke-linecap="round"/>'
                .'<circle cx="'.($x + 242).'" cy="'.($y + 128).'" r="9" fill="#f17b58"/>',
            'planting' => '<path d="M'.($x + 214).' '.($y + 143).'v-18m0 9q-12 -13 -17 -5m17 0q12 -13 17 -5" fill="none" stroke="#478b4b" stroke-width="4"/>',
            'praying' => '<path d="M'.($x + 208).' '.($y + 126).'l9 13 9 -13" fill="none" stroke="#f1c49b" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>',
            'walking' => '<path d="M'.($x + 178).' '.($y + 143).'l-8 12m38 -12 8 12m35 -10 -6 12m23 -12 8 12" stroke="#17324d" stroke-width="4" stroke-linecap="round"/>',
            default => '<path d="M'.($x + 210).' '.($y + 127).'q12 -12 24 0" fill="none" stroke="#f1c49b" stroke-width="7" stroke-linecap="round"/>',
        };
    }

    /**
     * @return list<string>
     */
    private function wrapText(string $text, int $lineLength, int $maxLines): array
    {
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $candidate = $currentLine === '' ? $word : $currentLine.' '.$word;
            if ($currentLine !== '' && Str::length($candidate) > $lineLength) {
                $lines[] = $currentLine;
                $currentLine = $word;

                continue;
            }

            $currentLine = $candidate;
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = Str::limit($lines[$maxLines - 1], $lineLength - 1, '…');
        }

        return $lines;
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
