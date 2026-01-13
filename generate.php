<?php
declare(strict_types=1);

session_start();

spl_autoload_register(function (string $className): void {
    $file = __DIR__ . '/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Ensure resume data exists
if (empty($_SESSION['resume_data'])) {
    header('Location: index.php');
    exit;
}

// Validate template selection
$templateId = 1;
if (isset($_GET['template'])) {
    $candidate = (int)$_GET['template'];
    if ($candidate === 1 || $candidate === 2) {
        $templateId = $candidate;
    }
}

$data   = $_SESSION['resume_data'];
$resume = new Resume($data);

/**
 * For PDF generation, we generate minimal CSS and markup
 * compatible with TCPDF's HTML rendering.
 *
 * In a larger project you might extract this render function into its own class.
 */
function renderPdfResume(Resume $resume, int $templateId): string
{
    $skills = $resume->getSkillsList();

    ob_start();
    ?>
    <style>
        body {
            font-family: helvetica, sans-serif;
            font-size: 11pt;
        }
        .resume-name {
            font-size: 20pt;
            font-weight: bold;
        }
        .section-title {
            font-size: 13pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 4px;
            border-bottom: 1px solid #333333;
        }
        .small-muted {
            color: #555555;
            font-size: 9pt;
        }
        .skills-list li {
            margin-bottom: 2px;
        }
    </style>
    <div>
        <?php if ($templateId === 1): ?>
            <div>
                <span class="resume-name"><?= $resume->name; ?></span><br>
                <span><?= $resume->email; ?> | <?= $resume->phone; ?></span><br>
                <?php if ($resume->address !== ''): ?>
                    <span><?= $resume->address; ?></span><br>
                <?php endif; ?>
            </div>

            <div class="section-title">Education</div>
            <div>
                <strong><?= $resume->educationDegree; ?></strong><br>
                <span><?= $resume->educationSchool; ?></span><br>
                <span class="small-muted"><?= $resume->educationYear; ?></span>
            </div>

            <div class="section-title">Work Experience</div>
            <div>
                <strong><?= $resume->workPosition; ?></strong><br>
                <span><?= $resume->workCompany; ?></span><br>
                <span class="small-muted"><?= $resume->workYears; ?></span>
            </div>

            <?php if (!empty($skills)): ?>
                <div class="section-title">Skills</div>
                <ul class="skills-list">
                    <?php foreach ($skills as $skill): ?>
                        <li><?= htmlspecialchars($skill, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        <?php else: ?>
            <!-- Template 2: Modern-ish look in PDF -->
            <div style="background-color:#222222; color:#ffffff; padding:10px;">
                <span class="resume-name"><?= $resume->name; ?></span><br>
                <span><?= $resume->email; ?> | <?= $resume->phone; ?></span><br>
                <?php if ($resume->address !== ''): ?>
                    <span><?= $resume->address; ?></span><br>
                <?php endif; ?>
            </div>

            <div style="margin-top:10px;">
                <table cellspacing="0" cellpadding="4" border="0" width="100%">
                    <tr>
                        <td width="60%" valign="top">
                            <div class="section-title">Work Experience</div>
                            <strong><?= $resume->workPosition; ?></strong><br>
                            <span><?= $resume->workCompany; ?></span><br>
                            <span class="small-muted"><?= $resume->workYears; ?></span>

                            <div class="section-title">Education</div>
                            <strong><?= $resume->educationDegree; ?></strong><br>
                            <span><?= $resume->educationSchool; ?></span><br>
                            <span class="small-muted"><?= $resume->educationYear; ?></span>
                        </td>
                        <td width="40%" valign="top">
                            <?php if (!empty($skills)): ?>
                                <div class="section-title">Skills</div>
                                <ul class="skills-list">
                                    <?php foreach ($skills as $skill): ?>
                                        <li><?= htmlspecialchars($skill, ENT_QUOTES, 'UTF-8'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php

    return (string)ob_get_clean();
}

// Prepare HTML content for TCPDF
$pdfHtml = renderPdfResume($resume, $templateId);

// Load TCPDF (make sure you have the TCPDF library in your project)
require_once __DIR__ . '/tcpdf_min/tcpdf.php';

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Document information
$pdf->SetCreator('PHP Resume Builder');
$pdf->SetAuthor($resume->name);
$pdf->SetTitle('Resume - ' . $resume->name);
$pdf->SetSubject('Generated Resume');

// Set default header data (we disable it for a cleaner look)
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Write content
$pdf->writeHTML($pdfHtml, true, false, true, false, '');

// Output PDF to browser
$fileName = 'resume_' . preg_replace('/\s+/', '_', strtolower($resume->name)) . '.pdf';

// Force download
$pdf->Output($fileName, 'D');
exit;