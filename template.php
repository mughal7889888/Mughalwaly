<?php
declare(strict_types=1);

session_start();

if (empty($_SESSION['resume_data'])) {
    // No data in session, redirect back to form
    header('Location: index.php');
    exit;
}

// Set default template
$currentTemplate = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $templateChoice = (int)($_POST['template'] ?? 1);
    if ($templateChoice !== 1 && $templateChoice !== 2) {
        $templateChoice = 1;
    }
    $currentTemplate = $templateChoice;

    $_SESSION['resume_template'] = $currentTemplate;
    // Redirect to preview for live display
    header('Location: preview.php?template=' . $currentTemplate);
    exit;
} else {
    // If a template is already stored, use it
    if (isset($_SESSION['resume_template'])) {
        $currentTemplate = (int)$_SESSION['resume_template'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resume Builder - Choose Template</title>
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
    <h1 class="mb-3">Choose a Resume Template</h1>
    <p class="text-muted mb-4">Select one of the templates below, then preview and download your resume as a PDF.</p>

    <form action="template.php" method="post" class="mb-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card template-card <?= $currentTemplate === 1 ? 'border-primary' : ''; ?>">
                    <div class="card-body">
                        <h2 class="h5">Template 1 - Classic</h2>
                        <p class="text-muted small">
                            A clean, classic layout with clear headings and left-aligned details.
                        </p>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="template"
                                id="template1"
                                value="1"
                                <?= $currentTemplate === 1 ? 'checked' : ''; ?>
                            >
                            <label class="form-check-label" for="template1">
                                Use Template 1
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card template-card <?= $currentTemplate === 2 ? 'border-primary' : ''; ?>">
                    <div class="card-body">
                        <h2 class="h5">Template 2 - Modern</h2>
                        <p class="text-muted small">
                            A modern layout with colored header and stronger visual hierarchy.
                        </p>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="template"
                                id="template2"
                                value="2"
                                <?= $currentTemplate === 2 ? 'checked' : ''; ?>
                            >
                            <label class="form-check-label" for="template2">
                                Use Template 2
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="index.php" class="btn btn-outline-secondary">
                Back to Edit Details
            </a>
            <button type="submit" class="btn btn-primary">
                Continue to Live Preview
            </button>
        </div>
    </form>
</div>

<!-- Bootstrap Bundle JS (optional for components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>