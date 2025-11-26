<?php include('config.php'); ?>
<?php
$recordCssPath = __DIR__ . '/../css/record_style.css';
$recordCssVersion = file_exists($recordCssPath) ? filemtime($recordCssPath) : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Recorded Calls</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/record_style.css?v=<?php echo $recordCssVersion; ?>">
</head>
<body>
  <div class="app-shell">
    <header class="app-header">
      <div class="app-header__inner">
        <h1 class="app-header__title">Recorded Calls</h1>
        <p class="app-header__subtitle">Review, monitor, and download conversations.</p>
        <nav class="app-nav" aria-label="Primary">
          <span class="app-nav__welcome">Welcome</span>
          <span class="app-nav__status"><span class="app-nav__status-dot" aria-hidden="true"></span>Secure Workspace</span>
          <a class="logout-link" href="logout.php">Logout</a>
        </nav>
      </div>
    </header>
    <main class="app-main">
