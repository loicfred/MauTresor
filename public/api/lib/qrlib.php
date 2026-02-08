<?php
define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);

class QRcode
{
    public static function png($text, $outfile = false, $level = QR_ECLEVEL_H, $size = 6, $margin = 4)
    {

        $qr = new QR_Encoder($text, $level);
        $matrix = $qr->getMatrix();

        $count = count($matrix);
        $imgSize = ($count + 2 * $margin) * $size;

        $im = imagecreatetruecolor($imgSize, $imgSize);
        imagesavealpha($im, true);

        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        imagefill($im, 0, 0, $white);

        for ($y = 0; $y < $count; $y++) {
            for ($x = 0; $x < $count; $x++) {
                if ($matrix[$y][$x]) {
                    $px = ($x + $margin) * $size;
                    $py = ($y + $margin) * $size;
                    imagefilledrectangle($im, $px, $py, $px + $size - 1, $py + $size - 1, $black);
                }
            }
        }

        if ($outfile === false) {
            imagepng($im);
        } else {
            imagepng($im, $outfile);
        }

        imagedestroy($im);
    }
}

class QR_Encoder
{
    private string $text;
    private int $level;

    public function __construct(string $text, int $level)
    {
        $this->text = $text;
        $this->level = $level;
    }

    public function getMatrix(): array
    {

        $version = 3;
        $size = 21 + 4 * ($version - 1);

        $matrix = array_fill(0, $size, array_fill(0, $size, null));
        $this->placeFinderPatterns($matrix);
        $this->placeTimingPatterns($matrix);
        $this->placeDarkModule($matrix, $version);

        // Encode data (byte mode)
        $bits = $this->encodeByteMode($this->text, $version);

        // Place data bits (simple zigzag)
        $this->placeData($matrix, $bits);

        // Apply a simple mask (mask 0)
        $this->applyMask0($matrix);

        // Convert null to white
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($matrix[$y][$x] === null) $matrix[$y][$x] = false;
            }
        }

        return $matrix;
    }

    private function placeFinderPatterns(array &$m): void
    {
        $this->placeFinder($m, 0, 0);
        $this->placeFinder($m, 0, count($m) - 7);
        $this->placeFinder($m, count($m) - 7, 0);
    }

    private function placeFinder(array &$m, int $y, int $x): void
    {
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $yy = $y + $r;
                $xx = $x + $c;
                if ($yy < 0 || $xx < 0 || $yy >= count($m) || $xx >= count($m)) continue;

                $isBorder = ($r === -1 || $r === 7 || $c === -1 || $c === 7);
                $isInnerBorder = ($r === 0 || $r === 6 || $c === 0 || $c === 6);
                $isCenter = ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4);

                if ($isBorder) $m[$yy][$xx] = false;
                else if ($isInnerBorder || $isCenter) $m[$yy][$xx] = true;
                else $m[$yy][$xx] = false;
            }
        }
    }

    private function placeTimingPatterns(array &$m): void
    {
        $n = count($m);
        for ($i = 8; $i < $n - 8; $i++) {
            $m[6][$i] = ($i % 2 === 0);
            $m[$i][6] = ($i % 2 === 0);
        }
    }

    private function placeDarkModule(array &$m, int $version): void
    {

        $m[4 * $version + 9][8] = true;
    }

    private function encodeByteMode(string $text, int $version): array
    {

        $data = [];
        $data = array_merge($data, [0,1,0,0]);

        $len = strlen($text);
        $countBits = $this->toBits($len, 8);
        $data = array_merge($data, $countBits);

        for ($i = 0; $i < $len; $i++) {
            $data = array_merge($data, $this->toBits(ord($text[$i]), 8));
        }

        // terminator
        $data = array_merge($data, [0,0,0,0]);

        // pad to byte
        while (count($data) % 8 !== 0) $data[] = 0;


        $targetBytes = 70;
        $bytes = [];
        for ($i=0; $i<count($data); $i+=8) {
            $b = 0;
            for ($j=0; $j<8; $j++) $b = ($b<<1) | $data[$i+$j];
            $bytes[] = $b;
        }
        $pad = [0xEC, 0x11];
        $p=0;
        while (count($bytes) < $targetBytes) {
            $bytes[] = $pad[$p%2];
            $p++;
        }

        // return as bits
        $out = [];
        foreach ($bytes as $b) $out = array_merge($out, $this->toBits($b, 8));
        return $out;
    }

    private function toBits(int $value, int $width): array
    {
        $bits = [];
        for ($i = $width - 1; $i >= 0; $i--) $bits[] = (($value >> $i) & 1) ? 1 : 0;
        return $bits;
    }

    private function isReserved(array &$m, int $y, int $x): bool
    {
        return $m[$y][$x] !== null;
    }

    private function placeData(array &$m, array $bits): void
    {
        $n = count($m);
        $dirUp = true;
        $bitIndex = 0;

        for ($x = $n - 1; $x > 0; $x -= 2) {
            if ($x === 6) $x--; // skip timing column

            for ($i = 0; $i < $n; $i++) {
                $y = $dirUp ? ($n - 1 - $i) : $i;

                for ($col = 0; $col < 2; $col++) {
                    $xx = $x - $col;
                    if ($this->isReserved($m, $y, $xx)) continue;

                    $m[$y][$xx] = ($bitIndex < count($bits)) ? ($bits[$bitIndex++] === 1) : false;
                }
            }
            $dirUp = !$dirUp;
        }
    }

    private function applyMask0(array &$m): void
    {
        $n = count($m);
        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                if ($this->inFinderZone($n, $y, $x) || $y === 6 || $x === 6) continue;

                // Mask 0: (row + col) % 2 == 0
                if ((($y + $x) % 2) === 0) {
                    $m[$y][$x] = !$m[$y][$x];
                }
            }
        }
    }

    private function inFinderZone(int $n, int $y, int $x): bool
    {
        $inTL = ($y < 9 && $x < 9);
        $inTR = ($y < 9 && $x >= $n - 8);
        $inBL = ($y >= $n - 8 && $x < 9);
        return $inTL || $inTR || $inBL;
    }
}
