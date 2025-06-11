<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../thumb.php';
?>
<!DOCTYPE html>
<html class=no-js lang="en">

<head>
    <meta charset=utf-8>
    <title>Art byBrynn - Art - Portfolio works</title>
    <meta name="description" content="A collection of past painted works">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords"
        content="art, bybrynn, paintings, portfolio, shop, commissions, photography, artist, brynn, bybrynn">
    <meta name="author" content=BrynnMonahan>
    <meta http-equiv="onion-location"
        content="http://artbybryndkmgb6ach4uqhrhsfkqbtcf3vrptfkljhclc3bxk74giwid.onion/T/art">
    <link rel=icon href=/images/icon.ico>
    <link rel=preconnect href=https://fonts.googleapis.com>
    <link rel=preconnect href=https://fonts.gstatic.com crossorigin>
    <link rel=stylesheet href=/cssrepo/bootstrap.css>
    <link rel=stylesheet href=/cssrepo/art_style.css>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville&display=swap" rel=stylesheet>
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
                    <li><a href="/about">about.</a></li>
                    <li><a href="/commissions">commissions.</a></li>
                    <li><a href="/shop">shop.</a></li>
                    <li class="dropdown">
                        <a>portfolio.</a>
                        <ul class="dropdown-menu">
                            <li><a href="/art/">art.</a></li>
                            <li><a href="/photography/">photography.</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
        </br>
    </header>
    <center>
        <h1 class=mb0>Art</h1>
    </center>
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid pt70 pb70">
        <div class="container">
            <div class="sorting">
                <h6 style="font-size: 25px;" onclick="toggleDropdown()">
                    <ion-icon name="filter-outline"></ion-icon>
                    <span style="font-size: 15px; position: relative; top: -10px;">Sort By:
                    </span>
                </h6>
                <span id="selectedOption" onclick="resetSorting()">
                </span>
                <div id="sortingDropdown" class="dropdown-content">
                    <div class="sorting-content">
                        <span onclick="sortBy('Name')">Name</span>
                        <span onclick="sortBy('Newest')">Newest</span>
                        <span onclick="sortBy('Oldest')">Oldest</span>
                    </div>
                </div>
            </div>
        </div>
        <div id="fh5co-projects-feed" class="fh5co-projects-feed gallery clearfix" style="align-items: center;">
<?php
// 1) Build an array of your slugs + dates
$items = [
['slug' => 'harrysteyeles', 'date' => '2020-11-30'],
['slug' => 'ophelia', 'date' => '2021-02-30'],
['slug' => 'inthegallery', 'date' => '2021-04-30'],
['slug' => 'bentley-penelope', 'date' => '2021-04-30'],
['slug' => 'orlainorange', 'date' => '2020-11-29'],
['slug' => 'deborainthelaundromat', 'date' => '2021-05-29'],
['slug' => 'yelena', 'date' => '2021-07-25'],
['slug' => 'invisiblestring', 'date' => '2021-08-26'],
['slug' => 'billiex2', 'date' => '2021-09-20'],
['slug' => 'lookwhatyoumademedo', 'date' => '2020-09-30'],
['slug' => 'littlewomenbyjomarch', 'date' => '2020-11-30'],
['slug' => 'chadwickbozeman', 'date' => '2020-11-30'],
['slug' => 'spideyx3', 'date' => '2022-02-29'],
['slug' => 'holdmyhand', 'date' => '2022-02-28'],
['slug' => 'thisiswhatitfeelslike', 'date' => '2022-03-28'],
['slug' => 'letlightbelight', 'date' => '2022-03-28'],
['slug' => 'slotcanyonnv', 'date' => '2022-05-25'],
['slug' => 'ella', 'date' => '2022-05-25'],
['slug' => 'mady-jonathan', 'date' => '2022-09-25'],
['slug' => 'coolgirl', 'date' => '2022-04-25'],
['slug' => 'apromiseofhopeisenoughtofeelfree', 'date' => '2022-04-20'],
['slug' => 'grogu', 'date' => '2020-04-19'],
['slug' => 'latenightsocking', 'date' => '2022-10-19'],
['slug' => 'midnights', 'date' => '2022-11-25'],
['slug' => 'myheart-mylungs', 'date' => '2022-12-19'],
['slug' => 'myheart-mylungs', 'date' => '2022-12-19'],
['slug' => 'heartinmyhands', 'date' => '2023-01-19'],
['slug' => 'labyrinth', 'date' => '2023-02-19'],
['slug' => 'jess-gabriel', 'date' => '2023=02-19'],
['slug' => 'getyourheadouttheground', 'date' => '2023-03-17'],
['slug' => 'pluto', 'date' => '2020-10-17'],
['slug' => 'kitschycatclock', 'date' => '2022-08-25'],
['slug' => 'jo-laurie', 'date' => '2020-08-17'],
['slug' => 'walkonthebeach', 'date' => '2020-10-17'],
['slug' => 'aquietlife', 'date' => '2020-03-17'],
['slug' => 'beforethekiss', 'date' => '2020-03-17'],
['slug' => 'conan', 'date' => '2021-10-25'],
['slug' => 'aconcertsixmonthsfromnow', 'date' => '2021-11-17'],
['slug' => 'makenna', 'date' => '2020-11-17'],
['slug' => 'pinkyswear', 'date' => '2023-02-16'],
['slug' => 'snowonthebeach', 'date' => '2023-02-16'],
['slug' => 'gracie', 'date' => '2023-04-16'],
['slug' => 'imamirrorball', 'date' => '2023-05-16'],
['slug' => 'mirrorball', 'date' => '2023-05-16'],
['slug' => 'shiningjustforyou', 'date' => '2023-05-16'],
['slug' => 'maisie', 'date' => '2023-08-15'],
['slug' => 'withtheflowers', 'date' => '2023-07-15'],
['slug' => 'growntogether', 'date' => '2023-09-15'],
['slug' => 'backstabbed', 'date' => '2023-11-31'],
['slug' => 'felinefamilyportrait', 'date' => '2024-01-15'],
['slug' => 'rosehillcottage', 'date' => '2023-12-15'],

];

foreach ($items as $item):
    $origFs   = $_SERVER['DOCUMENT_ROOT'] . '/art/images/' . $item['slug'] . '.webp';
    $thumbUrl = get_thumb($origFs, 290);
?>
  <div class="fh5co-project masonry-brick" data-date="<?= $item['date'] ?>">
    <a href="page.html?art=<?= urlencode($item['slug']) ?>">
      <img
        src="<?= htmlspecialchars($thumbUrl) ?>"
        width="290"
        loading="lazy"
        alt="<?= htmlspecialchars($item['slug']) ?>">
    </a>
  </div>
<?php endforeach; ?>
</div>
    </div>
    <footer id=fh5co-footer role=contentinfo>
        <center>
            <p>
                <a href="/art" style="color:#000000; text-decoration:underline;">Back to top</a>
            </p>
        </center>
        <div class=container-fluid>
            <div class=footer-content>
                <div class=copyright><small>&copy; <span id="year"></span> - Brynn Monahan - All Rights
                        Reserved.</small></br><a href="mailto: contact@bybrynn.com">Contact me</a>
                </div>
            </div>
    </footer>
    <div id="bottom"></div>
    <script src=/jsrepo/touch_dropdown.js></script>
    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
    <script src=/jsrepo/gallery_sorter.js></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/isotope/3.0.6/isotope.pkgd.min.js"></script>
</body>

</html>