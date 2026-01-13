<?php
declare(strict_types=1);

session_start();

spl_autoload_register(function (string $className): void {
    $file = __DIR__ . '/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

if (empty($_SESSION['resume_data'])) {
    header('Location: index.php');
    exit;
}

$data     = $_SESSION['resume_data'];
$resume   = new Resume($data);

// Determine template from query or session
$templateId = 1;
if (isset($_GET['template'])) {
    $candidate = (int)$_GET['template'];
    if ($candidate === 1 || $candidate === 2) {
        $templateId = $candidate;
        $_SESSION['resume_template'] = $templateId;
    }
} elseif (isset($_SESSION['resume_template'])) {
    $templateId = (int)$_SESSION['resume_template'];
}

// Simple helper to make sure URLs for images are safe
function safeProfilePicturePath(?string $path): ?string
{
    if ($path === null || $path === '') {
        return null;
    }

    // Only allow images from the uploads directory
    if (str_starts_with($path, 'uploads/')) {
        return htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    }

    return null;
}

$profilePicture = safeProfilePicturePath($resume->profilePicture);

/**
 * Render the chosen resume template as HTML.
 */
function renderResumeTemplate(Resume $resume, int $templateId, ?string $profilePicturePath): string
{
    ob_start();
    ?>
    <div class="resume-template resume-template-<?= (int)$templateId; ?>">
        <?php if ($templateId === 1): ?>
            <div class="row">
                <div class="col-md-9">
                    <h1 class="resume-name"><?= $resume->name; ?></h1>
                    <p class="resume-contact">
                        <?= $resume->email; ?> | <?= $resume->phone; ?>
                        <?php if ($resume->address !== ''): ?>
                            | <?= $resume->address; ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-3 text-md-end text-center">
                    <?php if ($profilePicturePath !== null): ?>
                        <img src="<?= $profilePicturePath; ?>" alt="Profile Picture" class="resume-photo mb-2">
                    <?php endif; ?>
                </div>
            </div>

            <hr>

            <section class="resume-section">
                <h2 class="resume-section-title">Education</h2>
                <p class="mb-0"><strong><?= $resume->educationDegree; ?></strong></p>
                <p class="mb-0"><?= $resume->educationSchool; ?></p>
                <p class="text-muted"><?= $resume->educationYear; ?></p>
            </section>

            <section class="resume-section">
                <h2 class="resume-section-title">Work Experience</h2>
                <p class="mb-0"><strong><?= $resume->workPosition; ?></strong> - <?= $resume->workCompany; ?></p>
                <p class="text-muted mb-1"><?= $resume->workYears; ?></p>
            </section>

            <?php $skills = $resume->getSkillsList(); ?>
            <?php if (!empty($skills)): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Skills</h2>
                    <ul class="resume-skills-list">
                        <?php foreach ($skills as $skill): ?>
                            <li><?= htmlspecialchars($skill, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

        <?php else: ?>
            <!-- Template 2: Modern layout -->
            <div class="resume-header-modern d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="resume-name mb-1"><?= $resume->name; ?></h1>
                    <p class="resume-contact mb-0">
                        <?= $resume->email; ?> | <?= $resume->phone; ?>
                    </p>
                    <?php if ($resume->address !== ''): ?>
                        <p class="resume-contact mb-0"><?= $resume->address; ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($profilePicturePath !== null): ?>
                    <div>
                        <img src="<?= $profilePicturePath; ?>" alt="Profile Picture" class="resume-photo-modern">
                    </div>
                <?php endif; ?>
            </div>

            <div class="resume-body-modern">
                <div class="row">
                    <div class="col-md-7">
                        <section class="resume-section">
                            <h2 class="resume-section-title">Work Experience</h2>
                            <p class="mb-0"><strong><?= $resume->workPosition; ?></strong></p>
                            <p class="mb-0"><?= $resume->workCompany; ?></p>
                            <p class="text-muted mb-1"><?= $resume->workYears; ?></p>
                        </section>

                        <section class="resume-section">
                            <h2 class="resume-section-title">Education</h2>
                            <p class="mb-0"><strong><?= $resume->educationDegree; ?></strong></p>
                            <p class="mb-0"><?= $resume->educationSchool; ?></p>
                            <p class="text-muted mb-1"><?= $resume->educationYear; ?></p>
                        </section>
                    </div>
                    <div class="col-md-5">
                        <?php $skills = $resume->getSkillsList(); ?>
                        <?php if (!empty($skills)): ?>
                            <section class="resume-section">
                                <h2 class="resume-section-title">Skills</h2>
                                <ul class="resume-skills-list-modern">
                                    <?php foreach ($skills as $skill): ?>
                                        <li><?= htmlspecialchars($skill, ENT_QUOTES, 'UTF-8'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return (string)ob_get_clean();
}

$resumeHtml = renderResumeTemplate($resume, $templateId, $profilePicture);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resume Builder - Preview</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">PHP Resume Builder</a>
    </div>
</nav>

<div class="container">
    <h1 class="mb-3">Live Resume Preview</h1>
    <p class="text-muted mb-4">
        This is how your resume will look. You can go back to edit your details or change the template.
    </p>

    <div class="mb-3 d-flex justify-content-between flex-wrap gap-2">
        <div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">Edit Details</a>
            <a href="template.php" class="btn btn-outline-secondary btn-sm">Change Template</a>
        </div>
        <div>
            <a href="generate.php?template=<?= (int)$templateId; ?>" class="btn btn-primary btn-sm">
                Download as PDF
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <?= $resumeHtml; ?>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS (optional for components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>