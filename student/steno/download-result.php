<?php
require_once __DIR__ . '/../includes/auth_helper.php';

// ── Access Check ─────────────────────────────────────────────────────────────
if (!$auth->isLogged()) {
    header('Location: ../login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die("Invalid Result ID");
}

try {
    // Fetch result data
    $stmt = $pdo->prepare("SELECT r.*, t.title as test_title, t.content as original_content, t.language, t.level, 
                                  s.student_name, s.father_name, s.roll_no, s.image as student_image
                           FROM steno_results r
                           JOIN steno_tests t ON r.test_id = t.id
                           JOIN students s ON r.student_id = s.id
                           WHERE r.id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();

    if (!$result) {
        die("Result not found.");
    }
    
    // Authorization check: student can only download their own results
    if ($result['student_id'] != $auth->getSessionUID($auth->getSessionHash())) {
        die("Unauthorized access.");
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// ── Evaluation Logic (Same as steno-result.php) ──────────────────────────────
function cleanStenoText($text) {
    $text = strip_tags(html_entity_decode($text));
    $text = str_replace(["\r", "\n", "\t", "&nbsp;", "\xc2\xa0"], ' ', $text);
    return preg_replace('/\s+/', ' ', trim($text));
}

$originalTxt = cleanStenoText($result['original_content']);
$transcribedTxt = cleanStenoText($result['transcribed_text']);

$origArr = explode(' ', $originalTxt);
$tranArr = explode(' ', $transcribedTxt);

if(empty($origArr[0]) && count($origArr) === 1) $origArr = [];
if(empty($tranArr[0]) && count($tranArr) === 1) $tranArr = [];

$origHtml = '';
$tranHtml = '';
$i = 0; $j = 0;

while ($i < count($origArr) || $j < count($tranArr)) {
    if ($i >= count($origArr)) {
        $tranHtml .= "<del style='color:#dc3545; text-decoration:line-through;'>{$tranArr[$j]}</del> ";
        $j++; continue;
    }
    if ($j >= count($tranArr)) {
        $origHtml .= "<span style='color:#fd7e14; border-bottom: 1px dotted #fd7e14;'>{$origArr[$i]}</span> ";
        $i++; continue;
    }
    
    if (trim($origArr[$i]) === trim($tranArr[$j])) {
        $origHtml .= "<span style='color:#28a745;'>{$origArr[$i]}</span> ";
        $tranHtml .= "<span style='color:#28a745;'>{$tranArr[$j]}</span> ";
        $i++; $j++;
    } else {
        $foundMatch = false;
        for ($lookahead = 1; $lookahead <= 6; $lookahead++) {
            if (($i + $lookahead) < count($origArr) && trim($origArr[$i + $lookahead]) === trim($tranArr[$j])) {
                for ($k = 0; $k < $lookahead; $k++) {
                    $origHtml .= "<span style='color:#fd7e14; border-bottom: 1px dotted #fd7e14;'>{$origArr[$i]}</span> ";
                    $i++;
                }
                $foundMatch = true; break;
            }
            if (($j + $lookahead) < count($tranArr) && trim($origArr[$i]) === trim($tranArr[$j + $lookahead])) {
                for ($k = 0; $k < $lookahead; $k++) {
                    $tranHtml .= "<del style='color:#dc3545; text-decoration:line-through;'>{$tranArr[$j]}</del> ";
                    $j++;
                }
                $foundMatch = true; break;
            }
        }
        if (!$foundMatch) {
            $origHtml .= "<span style='color:#dc3545;'>{$origArr[$i]}</span> ";
            $tranHtml .= "<span style='color:#dc3545; border-bottom: 1px solid #dc3545;'>{$tranArr[$j]}</span> ";
            $i++; $j++;
        }
    }
}

// ── PDF Generation with mPDF ────────────────────────────────────────────────
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'margin_header' => 10,
        'margin_footer' => 10,
    ]);

    // Setup Fonts
    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf->fontdata['krutidev'] = [
        'R' => 'krutidev-010-hindi-font-download.ttf',
    ];
    // Note: We need the actual font file in the mPDF font directory or use AddFont
    // For simplicity, we assume fonts are configured in vendor or we use custom CSS @font-face if possible (mPDF prefers direct registration)

    $logo_path = __DIR__ . '/../src/images/prayag-computer-logo.png';
    $student_image_path = !empty($result['student_image']) ? __DIR__ . '/../../admin/' . $result['student_image'] : __DIR__ . '/../src/images/user-avtar.png';

    $isHindi = $result['language'] === 'Hindi';
    $fontFamily = $isHindi ? 'krutidev, DejaVu Sans' : 'Inter, DejaVu Sans';
    $fontSize = $isHindi ? '14pt' : '11pt';

    $html = "
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header-table { width: 100%; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-bottom: 20px; }
        .center-name { font-size: 24pt; font-weight: bold; color: #28a745; text-align: center; }
        .center-sub { font-size: 10pt; text-align: center; color: #666; }
        .report-title { font-size: 18pt; font-weight: bold; text-align: center; margin: 20px 0; background: #f8f9fa; padding: 10px; border-radius: 5px; }
        
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 8px; border: 1px solid #eee; font-size: 10pt; }
        .label { font-weight: bold; color: #555; width: 30%; background: #fafafa; }
        
        .stats-table { width: 100%; margin: 20px 0; border-collapse: collapse; }
        .stats-table td { width: 25%; text-align: center; padding: 15px; border: 1px solid #ddd; }
        .stat-val { font-size: 18pt; font-weight: bold; color: #2c3e50; display: block; }
        .stat-lbl { font-size: 8pt; color: #888; text-transform: uppercase; letter-spacing: 1px; }
        
        .content-table { width: 100%; table-layout: fixed; }
        .content-block { padding: 15px; border: 1px solid #eee; background: #fff; vertical-align: top; width: 48%; }
        .block-title { font-weight: bold; font-size: 10pt; margin-bottom: 10px; color: #28a745; border-bottom: 1px solid #28a745; padding-bottom: 5px; }
        .diff-text { line-height: 1.8; font-size: $fontSize; }
        " . ($isHindi ? ".diff-text { font-family: krutidev !important; }" : "") . "
        
        .footer { text-align: center; font-size: 8pt; color: #999; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>

    <div class='header-table'>
        <table width='100%'>
            <tr>
                <td width='20%'><img src='$logo_path' width='80'></td>
                <td width='60%' align='center'>
                    <div class='center-name'>PRAYAG COMPUTER</div>
                    <div class='center-sub'>Certified Computer Education & Skill Development Center</div>
                </td>
                <td width='20%' align='right'><img src='$student_image_path' width='70' style='border-radius:5px;'></td>
            </tr>
        </table>
    </div>

    <div class='report-title'>STENO EXAMINATION RESULT</div>

    <table class='info-table'>
        <tr>
            <td class='label'>Student Name:</td><td>" . htmlspecialchars($result['student_name']) . "</td>
            <td class='label'>Roll No:</td><td>" . htmlspecialchars($result['roll_no']) . "</td>
        </tr>
        <tr>
            <td class='label'>Father's Name:</td><td>" . htmlspecialchars($result['father_name']) . "</td>
            <td class='label'>Test Language:</td><td>" . htmlspecialchars($result['language']) . "</td>
        </tr>
        <tr>
            <td class='label'>Test Name:</td><td colspan='3'>" . htmlspecialchars($result['test_title']) . "</td>
        </tr>
        <tr>
            <td class='label'>Exam Date:</td><td>" . date('d M Y, h:i A', strtotime($result['test_date'])) . "</td>
            <td class='label'>Level:</td><td>" . htmlspecialchars($result['level']) . "</td>
        </tr>
    </table>

    <table class='stats-table'>
        <tr>
            <td>
                <span class='stat-val'>{$result['wpm']}</span>
                <span class='stat-lbl'>Gross WPM</span>
            </td>
            <td>
                <span class='stat-val' style='color: " . ($result['accuracy'] >= 90 ? '#28a745' : ($result['accuracy'] >= 75 ? '#ffc107' : '#dc3545')) . ";'>{$result['accuracy']}%</span>
                <span class='stat-lbl'>Accuracy</span>
            </td>
            <td>
                <span class='stat-val' style='color: #dc3545;'>{$result['errors']}</span>
                <span class='stat-lbl'>Total Mistakes</span>
            </td>
            <td>
                <span class='stat-val' style='color: #17a2b8;'>{$result['correct_words']}</span>
                <span class='stat-lbl'>Correct Words</span>
            </td>
        </tr>
    </table>

    <div style='margin-top: 30px;'>
        <table width='100%' cellpadding='10' cellspacing='0'>
            <tr>
                <td class='content-block'>
                    <div class='block-title'>ORIGINAL DICTATION CONTENT</div>
                    <div class='diff-text'>$origHtml</div>
                </td>
                <td width='4%'></td>
                <td class='content-block'>
                    <div class='block-title'>YOUR TRANSCRIBED CONTENT</div>
                    <div class='diff-text'>$tranHtml</div>
                </td>
            </tr>
        </table>
    </div>

    <div style='margin-top: 40px; border: 1px solid #ddd; padding: 15px; background: #fffbe6;'>
        <div style='font-weight: bold; margin-bottom: 5px; font-size: 10pt;'>Result Analysis Legend:</div>
        <table width='100%' style='font-size: 9pt;'>
            <tr>
                <td><span style='color:#28a745; font-weight:bold;'>■ Correct Word</span></td>
                <td><span style='color:#dc3545; font-weight:bold; text-decoration:underline;'>■ Misspelled/Error</span></td>
                <td><span style='color:#fd7e14; font-weight:bold; border-bottom: 1px dotted #fd7e14;'>■ Omitted/Skipped</span></td>
                <td><span style='color:#dc3545; font-weight:bold; text-decoration:line-through;'>■ Extra Word</span></td>
            </tr>
        </table>
    </div>

    <div class='footer'>
        This is an electronically generated result report via Prayag Computer Steno Software.<br>
        Verified on " . date('Y-m-d H:i:s') . "
    </div>
    ";

    $mpdf->WriteHTML($html);
    $filename = "Steno_Result_" . str_replace(' ', '_', $result['student_name']) . "_" . date('Ymd_His') . ".pdf";
    $mpdf->Output($filename, 'I'); // Inline view

} catch (\Mpdf\MpdfException $e) {
    echo "PDF Generation Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
