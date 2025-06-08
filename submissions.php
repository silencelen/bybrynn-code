<?php
// admin.php
// Full error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug log file
$debugLog = __DIR__ . '/admin_debug.log';
function logd($msg) {
    global $debugLog;
    file_put_contents($debugLog, date('c') . ' ' . $msg . PHP_EOL, FILE_APPEND);
}

// Helper to respond and exit (also logs)
function respond($msg) {
    logd('RESPOND: ' . $msg);
    echo '<p>' . htmlspecialchars($msg) . '</p>';
    exit;
}

logd('=== Script Start ===');

$entriesFile = __DIR__ . '/art/entries.json';
$indexFile   = __DIR__ . '/art/index.html';
$imagesDir   = __DIR__ . '/art/images';

// 1. Validate environment
if (!is_dir($imagesDir)) {
    if (!mkdir($imagesDir, 0755, true)) {
        respond('Error: Cannot create images directory.');
    }
    logd('Created images directory');
}
if (!file_exists($entriesFile)) {
    if (file_put_contents($entriesFile, "{}") === false) {
        respond('Error: Cannot create entries.json.');
    }
    logd('Created entries.json');
}
if (!is_readable($entriesFile) || !is_writable($entriesFile)) {
    respond('Error: entries.json not readable or writable.');
}
if (!file_exists($indexFile) || !is_writable($indexFile)) {
    respond('Error: index.html missing or not writable.');
}
logd('Environment validated');

// 2. Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        logd('Handling POST');
        // Gather inputs
        $title       = trim($_POST['title'] ?? '');
        $medium      = trim($_POST['medium'] ?? '');
        $dimensions  = trim($_POST['dimensions'] ?? '');
        $year        = trim($_POST['year'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date        = $_POST['date'] ?? date('Y-m-d');
        logd("Inputs: title='$title', medium='$medium', dimensions='$dimensions', year='$year', date='$date'");

        if (!$title || !$medium || !$dimensions || !$year) {
            respond('Error: Title, medium, dimensions, and year are required.');
        }

        // Derive slug
        $slug = preg_replace('/[^a-z0-9]/', '', strtolower($title));
        if (!$slug) respond('Error: Invalid title for slug.');
        logd("Slug: $slug");

        // Compose fields
        $subheading = "$medium - $dimensions - $year";
        $metaTitle  = "Art byBrynn - $title - Portfolio works";
        $onionUrl   = "http://artbybryndkmgb6ach4uqhrhsfkqbtcf3vrptfkljhclc3bxk74giwid.onion/T/art/$slug";

        // Handle thumbnail (required)
        if (empty($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
            respond('Error: Thumbnail upload required.');
        }
        if ($_FILES['thumbnail']['type'] !== 'image/webp') {
            respond('Error: Thumbnail must be .webp.');
        }
        $thumbName = "$slug-thumbnail.webp";
        $thumbDest = "$imagesDir/$thumbName";
        if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbDest)) {
            respond('Error: Cannot save thumbnail.');
        }
        $thumbPath = "/art/images/$thumbName";
        logd("Thumbnail saved: $thumbDest");

        // Handle high-res (optional)
        $highresPath = '';
        if (!empty($_FILES['highres']) && $_FILES['highres']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['highres']['type'] === 'image/webp') {
                $highName = "$slug-highres.webp";
                $highDest = "$imagesDir/$highName";
                if (move_uploaded_file($_FILES['highres']['tmp_name'], $highDest)) {
                    $highresPath = "/art/images/$highName";
                    logd("Highres saved: $highDest");
                }
            }
        }

        // Load and parse entries.json
        $raw = file_get_contents($entriesFile);
        if ($raw === false) respond('Error: Unable to read entries.json.');
        $decoded   = json_decode($raw, true);
        $jsonValid = json_last_error() === JSON_ERROR_NONE;
        if ($jsonValid) {
            $entries = $decoded;
            logd('Loaded entries.json count=' . count($entries));
        } else {
            logd('JSON decode error: ' . json_last_error_msg());
            $entries = null;  // will fallback to textual append
        }

        // Build new entry
        $newEntry = [
            'subheading'  => $subheading,
            'metaTitle'   => $metaTitle,
            'title'       => $title,
            'description' => $description,
            'onion'       => $onionUrl,
            'image'       => $highresPath,
            'prev'        => '',
            'next'        => ''
        ];

        if ($jsonValid) {
            // JSON-valid path: update PHP array
            $prev = '';
            if (!empty($entries)) {
                $keys = array_keys($entries);
                $prev = end($keys);
                $entries[$prev]['next'] = $slug;
                logd("Set next of prev=$prev to $slug");
            }
            $newEntry['prev']     = $prev;
            $entries[$slug]       = $newEntry;
            $newJson              = json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (json_last_error() !== JSON_ERROR_NONE) {
                respond('Error: JSON encode failed.');
            }
            if (file_put_contents($entriesFile, $newJson, LOCK_EX) === false) {
                respond('Error: Could not write entries.json.');
            }
            logd('Updated entries.json count=' . count($entries));
        } else {
            // Fallback: textual append
            $frag = json_encode([$slug => $newEntry], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $frag = trim($frag);
            $frag = substr($frag, 1, -1); // strip outer {}
            $pos    = strrpos($raw, '}');
            if ($pos === false) {
                respond('Error: Cannot find closing brace in entries.json');
            }
            $prefix = substr($raw, 0, $pos);
            if (!preg_match('/,\s*$/', $prefix)) {
                $prefix = rtrim($prefix) . ',';
            }
            $newRaw = $prefix . "\n" . $frag . "\n}";
            if (file_put_contents($entriesFile, $newRaw, LOCK_EX) === false) {
                respond('Error: Could not append to entries.json.');
            }
            logd('Appended entry textually');
        }

        // Normalize line endings and insert gallery snippet
        $html = file_get_contents($indexFile);
        if ($html === false) respond('Error: Cannot read index.html');
        $html = str_replace(["\r\n", "\r"], "\n", $html);

        // New marker: the closing of gallery and opening of footer
        $marker = "        </div>\n    </div>\n    <footer id=fh5co-footer role=contentinfo>";
        $pos    = strpos($html, $marker);
        if ($pos === false) {
            respond('Error: Gallery closing marker not found.');
        }

        // Build the new project block
        $block  = "            <div class=\"fh5co-project masonry-brick\" data-date=\"$date\">\n";
        $block .= "                <a href=\"page.html?art=$slug\">\n";
        $block .= "                    <img src=\"$thumbPath\" loading=\"lazy\" alt=\"$slug\">\n";
        $block .= "                </a>\n";
        $block .= "            </div>\n";

        // Insert it immediately before the footer marker
        $newHtml = substr($html, 0, $pos)
                 . $block
                 . substr($html, $pos);

        // Write back, converting LFs to system EOLs
        if (file_put_contents($indexFile, str_replace("\n", PHP_EOL, $newHtml)) === false) {
            respond('Error: Failed to update index.html');
        }
        logd('Updated index.html');

        respond("Entry '$title' added successfully.");
    } catch (Throwable $e) {
        respond('Unexpected error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Submission Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville&display=swap" rel=stylesheet>
    <link rel=stylesheet href=/cssrepo/bootstrap.css>
    <link rel=stylesheet href=/cssrepo/art_style.css>    
    <link rel=stylesheet href=/cssrepo/submissions_style.css>
    <style>
        a.fixed {
            position: fixed;
            right: 0;
            top: 0;
            max-width: 60px
        }
    </style>
    <a class=fixed href=https://www.instagram.com/bybrynnm/ target=_blank><img src=/images/insta.png></a>

</head>

<body>

        <header id=fh5co-header role=banner>
        <div class="container text-center">
            <div id="fh5co-logo">
                <a href="/"><img src="/images/logo.webp" alt="Home-Art_by_brynn"></a>
            </div>
            <nav>
                <ul>
                    <li><a href=/about>about.</a></li>
                    <li><a href=/commissions>commissions.</a></li>
                    <li><a href=/shop>shop.</a></li>
                    <li><a href=/portfolio>portfolio.</a></li>
            </nav>
        </div>
        </br>
    </header>
    <center>
    <h1>Submissions Portal</h1>
    </br></br>

    <form method="post" enctype="multipart/form-data">
        <div class="inputbox">        Title of the Artwork:   <input type="text" name="title" placeholder="Exactly as it will appear" required><br></div>
        <div class="inputbox">        Medium:   <input type="text" name="medium" placeholder="Medium" required><br></div>
        <div class="inputbox">        Dimensions:   <input type="text" name="dimensions" placeholder="Dimensions" required><br></div>
        <div class="inputbox">        Year Finished:   <input type="text" name="year" placeholder="20xx" required><br></div>
        <div class="inputbox">        Artwork description:   <textarea name="description" placeholder="Accompaning Information below images"></textarea><br></div>
        <div class="inputbox">        Current Date:   <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>"><br></div>
        <div class="inputbox"><label> Thumbnail (.webp): <input type="file" name="thumbnail" accept="image/webp" required></label><br></div>
        <div class="inputbox"><label> High-res (.webp): <input type="file" name="highres" accept="image/webp"></label><br></div>
        <div class="inputbox"><button type="submit">Add Entry</button></div>    
    </form>
    </center>
</body>
</html>
