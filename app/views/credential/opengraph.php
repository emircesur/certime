<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?></title>
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($credential['course_name']) ?> — Verified Credential">
    <meta property="og:description" content="<?= e($credential['student_name'] ?? '') ?> earned this credential from <?= e($credential['issuer_name'] ?? 'CertiMe') ?>. Verify it on CertiMe.">
    <meta property="og:image" content="<?= e($imageUrl) ?>">
    <meta property="og:url" content="<?= e($credUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="CertiMe">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($credential['course_name']) ?>">
    <meta name="twitter:description" content="Verified digital credential issued by <?= e($credential['issuer_name'] ?? 'CertiMe') ?>">
    <meta name="twitter:image" content="<?= e($imageUrl) ?>">
    
    <!-- Redirect to credential page -->
    <meta http-equiv="refresh" content="0;url=<?= e($credUrl) ?>">
</head>
<body>
    <p>Redirecting to <a href="<?= e($credUrl) ?>"><?= e($credential['course_name']) ?></a>...</p>
</body>
</html>
