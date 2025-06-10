<?php
header('Content-Type: text/plain');
echo "MICROSOFT_OAUTH_CLIENT_ID=" . getenv('MICROSOFT_OAUTH_CLIENT_ID') . "\n";
echo "MICROSOFT_OAUTH_CLIENT_SECRET=" . getenv('MICROSOFT_OAUTH_CLIENT_SECRET') . "\n";
