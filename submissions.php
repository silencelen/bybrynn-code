<?php

session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'domain'   => '.bybrynn.com',
  'secure'   => true,
  'httponly' => true,
  'samesite' => 'Lax',
]);

session_start();

file_put_contents(
  __DIR__ . '/admin_debug.log',
  date('c') . " SESSION ID   : " . session_id() . "\n" .
  date('c') . " COOKIE ARRAY : " . print_r($_COOKIE, true) . "\n" .
  date('c') . " GET          : " . print_r($_GET, true) . "\n" .
  date('c') . " SESS         : " . print_r($_SESSION, true) . "\n\n",
  FILE_APPEND
);

require __DIR__ . '/vendor/autoload.php';
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $clientId     = getenv('MICROSOFT_OAUTH_CLIENT_ID');
    $clientSecret = getenv('MICROSOFT_OAUTH_CLIENT_SECRET');
    $tenantId     = 'cd47551c-33c7-4b7f-87a9-df19f9169121';
    $redirectUri  = 'https://bybrynn.com/submissions';

    if (! $clientId || ! $clientSecret) {
        exit('OAuth client credentials not configured.');
    }

    $provider = new Microsoft([
        'clientId'                => $clientId,
        'clientSecret'            => $clientSecret,
        'redirectUri'             => $redirectUri,
        'urlAuthorize'            => "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize",
        'urlAccessToken'          => "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
        'urlResourceOwnerDetails' => 'https://graph.microsoft.com/oidc/userinfo',
    ]);

    if (isset($_GET['error'])) {
        exit('Azure error: ' . htmlspecialchars(urldecode($_GET['error_description'] ?? $_GET['error'])));
    }

    if (! isset($_GET['code'])) {
        $authUrl = $provider->getAuthorizationUrl([
            'scope'  => ['User.Read'],
            'prompt' => 'select_account',
        ]);
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    if (empty($_GET['state']) || ($_GET['state'] !== ($_SESSION['oauth2state'] ?? null))) {
        unset($_SESSION['oauth2state']);
        exit('Invalid OAuth state');
    }

    try {
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);
    } catch (IdentityProviderException $e) {
        exit('Error fetching access token: ' . $e->getMessage());
    }

    try {
        $owner = $provider->getResourceOwner($token);
    } catch (Exception $e) {
    }
}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$debugLog = __DIR__ . '/admin_debug.log';
function logd($msg) {
    global $debugLog;
    file_put_contents($debugLog, date('c') . ' ' . $msg . PHP_EOL, FILE_APPEND);
}
function respond($msg) {
    logd('RESPOND: ' . $msg);
    echo '<p>' . htmlspecialchars($msg) . '</p>';
    exit;
}

logd('=== Script Start ===');

$entriesFile = __DIR__ . '/art/entries.json';
$indexFile   = __DIR__ . '/art/index.php';
$imagesDir   = __DIR__ . '/art/images';

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
    respond('Error: index.php missing or not writable.');
}
logd('Environment validated');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        logd('Handling POST');
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

        $slug = preg_replace('/[^a-z0-9]/', '', strtolower($title));
        if (!$slug) respond('Error: Invalid title for slug.');
        logd("Slug: $slug");


        $highresPath = '';
        if (!empty($_FILES['highres']) && $_FILES['highres']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['highres']['type'] === 'image/webp') {
                $highName = "$slug.webp";
                $highDest = "$imagesDir/$highName";
                if (move_uploaded_file($_FILES['highres']['tmp_name'], $highDest)) {
                    $highresPath = "/art/images/$highName";
                    logd("Highres saved: $highDest");
                }
            }
        }

        $secondaryPath = '';
        if (!empty($_FILES['secondary']) && $_FILES['secondary']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['secondary']['type'] === 'image/webp') {
                $secondaryName = "{$slug}-secondary.webp";
                $secondaryDest = "{$imagesDir}/{$secondaryName}";
                if (move_uploaded_file($_FILES['secondary']['tmp_name'], $secondaryDest)) {
                    $secondaryPath = "/art/images/{$secondaryName}";
                    logd("Secondary image saved: {$secondaryDest}");
                }
            }
        }

        $raw = file_get_contents($entriesFile);
        if ($raw === false) respond('Error: Unable to read entries.json.');
        $decoded   = json_decode($raw, true);
        $jsonValid = json_last_error() === JSON_ERROR_NONE;
        if ($jsonValid) {
            $entries = $decoded;
            logd('Loaded entries.json count=' . count($entries));
        } else {
            logd('JSON decode error: ' . json_last_error_msg());
            $entries = null;
        }

        $newEntry = [
            'subheading'  => "$medium - $dimensions - $year",
            'metaTitle'   => "Art by Brynn - $title - Portfolio works",
            'title'       => $title,
            'description' => $description,
            'onion'       => "http://artbybryndkmgb6ach4uqhrhsfkqbtcf3vrptfkljhclc3bxk74giwid.onion/T/art/$slug",
            'image'       => $highresPath,
            'secondary'   => $secondaryPath,
            'prev'        => '',
            'next'        => ''
        ];

        if ($jsonValid) {
            $prev = '';
            if (!empty($entries)) {
                $keys = array_keys($entries);
                $prev = end($keys);
                $entries[$prev]['next'] = $slug;
                logd("Set next of prev=$prev to $slug");
            }
            $newEntry['prev'] = $prev;
            $entries[$slug]   = $newEntry;
            $newJson          = json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (json_last_error() !== JSON_ERROR_NONE) {
                respond('Error: JSON encode failed.');
            }
            if (file_put_contents($entriesFile, $newJson, LOCK_EX) === false) {
                respond('Error: Could not write entries.json.');
            }
            logd('Updated entries.json count=' . count($entries));
        } else {
            $frag = json_encode([$slug => $newEntry], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $frag = trim($frag);
            $frag = substr($frag, 1, -1);
            $pos  = strrpos($raw, '}');
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

        $html = file_get_contents($indexFile);
        if ($html === false) respond('Error: Cannot read index.php');
        $html = str_replace(["\r\n", "\r"], "\n", $html);

        $marker = "];\n\nforeach (\$items as \$item):";
        $pos    = strpos($html, $marker);
        if ($pos === false) {
            respond('Error: Gallery closing marker not found.');
        }

        $block = "['slug' => '$slug', 'date' => '$date'],\n";

        $newHtml = substr($html, 0, $pos)
                 . $block
                 . substr($html, $pos);

        if (file_put_contents($indexFile, str_replace("\n", PHP_EOL, $newHtml)) === false) {
            respond('Error: Failed to update index.php');
        }
        logd('Updated index.php');

        header('Location: /art#bottom', true, 302);
        exit;

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
    <meta name="description" content="Art by Brynn (Bybrynn) - Submit your own artwork to Art by Brynn using this secure portal.">
    <meta property="og:title" content="Submission Portal">
    <meta property="og:description" content="Art by Brynn (Bybrynn) - Submit your own artwork to Art by Brynn using this secure portal.">
    <meta name="twitter:title" content="Submission Portal">
    <meta name="twitter:description" content="Art by Brynn (Bybrynn) - Submit your own artwork to Art by Brynn using this secure portal.">
    <meta name="twitter:card" content="summary">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville&display=swap" rel=stylesheet>
    <link rel=stylesheet href=/cssrepo/bootstraps/bootstrap.css>
    <link rel="stylesheet" href="/cssrepo/global_style.css">
    <link rel=stylesheet href=/cssrepo/ind/about_style.css>    
    <link rel=stylesheet href=/cssrepo/ind/submissions_style.css>
    <style>
        a.fixed {
            position: fixed;
            right: 0;
            top: 0;
            max-width: 60px;
        }
    </style>
    <a class=fixed href=https://www.instagram.com/bybrynnm/ target=_blank><img src=/images/insta.png alt="Instagram"></a>
    <link rel="canonical" href="https://bybrynn.com/submissions.php" />
</head>
<body class="fade">
    <header id=fh5co-header role=banner>
        <div class="container text-center">
            <div id="fh5co-logo">
                <a href="/"><img src="/images/logo.webp" alt="Art by Brynn logo"></a>
            </div>
            <nav>
                <ul>
                    <li><a href=/about>about.</a></li>
                    <li><a href=/commissions>commissions.</a></li>
                    <li><a href=/shop>shop.</a></li>
                    <li><a href=/portfolio>portfolio.</a></li>
                </ul>
            </nav>
        </div>
        <br>
    </header>
    <center>
        <h1>Submissions Portal</h1>
        <br><br>

        <form class="art-form" method="post" enctype="multipart/form-data">
          <div class="inputbox">
            <span class="label-text">Title of the Artwork:</span>
            <input type="text" name="title" placeholder="Exactly as it will appear" required>
          </div>

          <div class="inputbox">
            <span class="label-text">Medium:</span>
            <input type="text" name="medium" placeholder="Watercolor, pencil, etc" required>
          </div>

          <div class="inputbox">
            <span class="label-text">Dimensions:</span>
            <input type="text" name="dimensions" placeholder="10x12in" required>
          </div>

          <div class="inputbox">
            <span class="label-text">Year Finished:</span>
            <input type="text" name="year" placeholder="20xx" required>
          </div>

          <div class="inputbox">
            <span class="label-text">Artwork description:</span>
            <textarea name="description" placeholder="Accompanying information below images"></textarea>
          </div>

          <div class="inputbox">
            <span class="label-text">Current Date:</span>
            <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>">
          </div>

          <div class="inputbox">
            <span class="label-text">High-res watermarked .webp image:</span>
            <input type="file" name="highres" accept="image/webp">
          </div>

          <div class="inputbox">
            <span class="label-text">Secondary/Framed watermarked .webp image:</span>
            <input type="file" name="secondary" accept="image/webp">
          </div>

          <div class="inputbox submitbox">
            <button type="submit">Add Entry</button>
          </div>
        </form>
    </center>
    <script src="/jsrepo/fade.js"></script>
</body>
</html>