<?php
$twitterUser = 'TWITTER_USER_NAME';
$tweetCount = 10; // gösterilecek tweet sayısı
$twitterURL = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$oauth_access_token = "TWITTER_ACCESS_TOKEN";
$oauth_access_token_secret = "TWITTER_ACCESS_TOKEN_SECRET";
$consumer_key = "TWITTER_CONSUMER_KEY";
$consumer_secret = "TWITTER_CONSUMER_SECRET";

$oauth = array(
    'oauth_consumer_key' => $consumer_key,
    'oauth_nonce' => time(),
    'oauth_signature_method' => 'HMAC-SHA1',
    'oauth_token' => $oauth_access_token,
    'oauth_timestamp' => time(),
    'oauth_version' => '1.0'
);

$base_info = buildBaseString($twitterURL, 'GET', $oauth);
$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
$oauth['oauth_signature'] = $oauth_signature;

$header = array(buildAuthorizationHeader($oauth), 'Expect:');
$options = array(
    CURLOPT_HTTPHEADER => $header,
    CURLOPT_HEADER => false,
    CURLOPT_URL => $twitterURL . '?screen_name=' . $twitterUser . '&count=' . $tweetCount,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false
);

$feed = curl_init();
curl_setopt_array($feed, $options);
$json = curl_exec($feed);
curl_close($feed);

$tweets = json_decode($json);
foreach($tweets as $tweet){
    echo '<div class="tweet">';
    echo '<div class="tweet-text">' . $tweet->text . '</div>';
    echo '<div class="tweet-date">' . date('F j, Y, g:i a', strtotime($tweet->created_at)) . '</div>';
    echo '</div>';
}

function buildBaseString($baseURI, $method, $params) {
    $r = array();
    ksort($params);
    foreach($params as $key=>$value){
        $r[] = "$key=" . rawurlencode($value);
    }
    return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
}

function buildAuthorizationHeader($oauth) {
    $r = 'Authorization: OAuth ';
    $values = array();
    foreach($oauth as $key=>$value)
        $values[] = "$key=\"" . rawurlencode($value) . "\"";
    $r .= implode(', ', $values);
    return $r;
}

?>