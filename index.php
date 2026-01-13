<?php
declare(strict_types=1);

session_start();

// Simple autoload for classes (adjust if you add more classes later)
spl_autoload_register(function (string $className): void {
    $file = __DIR__ . '/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize variables
$errors = [];
$oldInput = [
    'name'              => '',
    'email'             => '',
    'phone'             => '',
    'address'           => '',
    'education_school'  => '',
    'education_degree'  => '',
    'education_year'    => '',
    'work_company'      => '',
    'work_position'     => '',
    'work_years'        => '',
    'skills'            => '',
];

// If we already have data in the session, prefill the form
if (isset($_SESSION['resume_data']) && is_array($_SESSION['resume_data'])) {
    $oldInput = array_merge($oldInput, $_SESSION['resume_data']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic trim and fetch
    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $educationSchool  = trim($_POST['education_school'] ?? '');
    $educationDegree  = trim($_POST['education_degree'] ?? '');
    $educationYear    = trim($_POST['education_year'] ?? '');
    $workCompany      = trim($_POST['work_company'] ?? '');
    $workPosition     = trim($_POST['work_position'] ?? '');
    $workYears        = trim($_POST['work_years'] ?? '');
    $skillsRaw        = trim($_POST['skills'] ?? '');

    // Persist for re-display in case of errors
    $oldInput = [
        'name'              => $name,
        'email'             => $email,
        'phone'             => $phone,
        'address'           => $address,
        'education_school'  => $educationSchool,
        'education_degree'  => $educationDegree,
        'education_year'    => $educationYear,
        'work_company'      => $workCompany,
        'work_position'     => $workPosition,
        'work_years'        => $workYears,
        'skills'            => $skillsRaw,
    ];

    // Basic validation
    if ($name === '') {
        $errors['name'] = 'Name is required.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($phone === '') {
        $errors['phone'] = 'Phone number is required.';
    }

    if ($educationSchool === '') {
        $errors['education_school'] = 'School name is required.';
    }

    if ($educationDegree === '') {
        $errors['education_degree'] = 'Degree is required.';
    }

    if ($educationYear === '') {
        $errors['education_year'] = 'Graduation year is required.';
    }

    if ($workCompany === '') {
        $errors['work_company'] = 'Company name is required.';
    }

    if ($workPosition === '') {
        $errors['work_position'] = 'Position is required.';
    }

    if ($workYears === '') {
        $errors['work_years'] = 'Years of experience are required.';
    }

    // Handle profile picture upload (optional)
    $profilePicturePath = $_SESSION['resume_data']['profile_picture'] ?? null;

    if (!empty($_FILES['profile_picture']['name'])) {
        $uploadDir = __DIR__ . '/uploads';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES['profile_picture']['tmp_name'] ?? '';
        $fileName    = basename($_FILES['profile_picture']['name'] ?? '');
        $fileSize    = (int)($_FILES['profile_picture']['size'] ?? 0);
        $fileError   = (int)($_FILES['profile_picture']['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($fileError === UPLOAD_ERR_OK && $fileTmpPath !== '') {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileMimeType     = mime_content_type($fileTmpPath);

            if (!in_array($fileMimeType, $allowedMimeTypes, true)) {
                $errors['profile_picture'] = 'Only JPG, PNG, and GIF images are allowed.';
            } elseif ($fileSize > 2 * 1024 * 1024) {
                // Limit file size to 2 MB
                $errors['profile_picture'] = 'Profile picture must be smaller than 2 MB.';
            } else {
                // Generate a unique filename to avoid collisions
                $extension          = pathinfo($fileName, PATHINFO_EXTENSION);
                $safeFileName       = 'profile_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                $destination        = $uploadDir . '/' . $safeFileName;
                $relativePath       = 'uploads/' . $safeFileName;

                if (!move_uploaded_file($fileTmpPath, $destination)) {
                    $errors['profile_picture'] = 'Failed to upload profile picture.';
                } else {
                    $profilePicturePath = $relativePath;
                }
            }
        } elseif ($fileError !== UPLOAD_ERR_NO_FILE) {
            $errors['profile_picture'] = 'An error occurred while uploading the profile picture.';
        }
    }

    // If no errors, store in session and redirect to template selection
    if (empty($errors)) {
        // Sanitize values for output later (XSS protection)
        $resumeData = [
            'name'              => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'email'             => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            'phone'             => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
            'address'           => htmlspecialchars($address, ENT_QUOTES, 'UTF-8'),
            'education_school'  => htmlspecialchars($educationSchool, ENT_QUOTES, 'UTF-8'),
            'education_degree'  => htmlspecialchars($educationDegree, ENT_QUOTES, 'UTF-8'),
            'education_year'    => htmlspecialchars($educationYear, ENT_QUOTES, 'UTF-8'),
            'work_company'      => htmlspecialchars($workCompany, ENT_QUOTES, 'UTF-8'),
            'work_position'     => htmlspecialchars($workPosition, ENT_QUOTES, 'UTF-8'),
            'work_years'        => htmlspecialchars($workYears, ENT_QUOTES, 'UTF-8'),
            'skills'            => htmlspecialchars($skillsRaw, ENT_QUOTES, 'UTF-8'),
            'profile_picture'   => $profilePicturePath,
        ];

        $_SESSION['resume_data'] = $resumeData;

        header('Location: template.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resume Builder - Home</title>
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
    <h1 class="mb-3">Create Your Resume</h1>
    <p class="text-muted mb-4">Fill in your details below. You can preview and download your resume as a PDF in the next steps.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>There were some problems with your input:</strong>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="index.php" method="post" enctype="multipart/form-data" class="card shadow-sm p-4 mb-5">
        <h2 class="h5 mb-3">Personal Information</h2>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control <?= isset($errors['name']) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($oldInput['name'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($oldInput['email'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Phone *</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="form-control <?= isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($oldInput['phone'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
            <div class="col-md-6 mb-3">
                <label for="address" class="form-label">Address</label>
                <input
                    type="text"
                    id="address"
                    name="address"
                    class="form-control"
                    value="<?= htmlspecialchars($oldInput['address'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>
        </div>

        <div class="mb-3">
            <label for="profile_picture" class="form-label">Profile Picture (Optional)</label>
            <input
                type="file"
                id="profile_picture"
                name="profile_picture"
                class="form-control <?= isset($errors['profile_picture']) ? 'is-invalid' : ''; ?>"
                accept="image/*"
            >
            <div class="form-text">Maximum size: 2 MB. Allowed types: JPG, PNG, GIF.</div>
        </div>

        <hr class="my-4">

        <h2 class="h5 mb-3">Education</h2>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="education_school" class="form-label">School / University *</label>
                <input
                    type="text"
                    id="education_school"
                    name="education_school"
                    class="form-control <?= isset($errors['education_school']) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($oldInput['education_school'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
            <div class="col-md-4 mb-3">
                <label for="education_degree" class="form-label">Degree *</label>
                <input
                    type="text"
                    id="education_degree"
                    name="education_degree"
                    class="form-control <?= isset($errors['education_degree']) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($oldInput['education_degree'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
            <div class="col-md-4 mb-3">
                <label for="education_year" class="form-label">Graduation Year *</label>
                <input
                    type="text"
                    id="education_year"
                    name="education_year"
                    class="form-control <?= isset($errors['education_year']) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($oldInput['education_year'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
        </div>

        <hr class="my-4">

        <h2 class="h5 mb-3">Work Experience</h2>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="work_company" class="form-label">Company *</label>
                <input
                    type="text"
                    id="work_company"
                    name="work_company"
                    class="form-control <?= isset($errors['work_company']) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($oldInput['work_company'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
            <div class="col-md-4 mb-3">
                <label for="work_position" class="form-label">Position *</label>
                <input
                    type="text"
                    id="work_position"
                    name="work_position"
                    class="form-control <?= isset($errors['work_position']) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($oldInput['work_position'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
            <div class="col-md-4 mb-3">
                <label for="work_years" class="form-label">Years *</label>
                <input
                    type="text"
                    id="work_years"
                    name="work_years"
                    class="form-control <?= isset($errors['work_years']) ? 'is-invalid' : ''; ?>"
                    value="<?= htmlspecialchars($oldInput['work_years'], ENT_QUOTES, 'UTF-8'); ?>"
                    required
                >
            </div>
        </div>

        <hr class="my-4">

        <h2 class="h5 mb-3">Skills</h2>
        <div class="mb-3">
            <label for="skills" class="form-label">List your skills (comma-separated)</label>
            <textarea
                id="skills"
                name="skills"
                rows="3"
                class="form-control"
            ><?= htmlspecialchars($oldInput['skills'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            <div class="form-text">Example: PHP, JavaScript, MySQL, HTML, CSS</div>
        </div>

        <button type="submit" class="btn btn-primary">
            Continue to Template Selection
        </button>
    </form>
</div>

<!-- Bootstrap Bundle JS (optional for components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>