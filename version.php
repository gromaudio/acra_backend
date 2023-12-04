<?php
function getHtml($url, $post = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    if(!empty($post)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    } 
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

$data = getHtml('https://g-auth.net/ota/ota.php?action=get_version&m=VLINE2_O&type=release');
//echo $data . "<br/>";
$prefix = "V2OVL";
$position = strpos($data, $prefix);
//echo $position . "<br/>";
$VERSION_RELEASE = substr($data, $position + 5);
//echo "VERSION_RELEASE: " . $VERSION_RELEASE . "<br/>";
$VERSION_RELEASE_FULL = $prefix . $VERSION_RELEASE;
//echo "VERSION_RELEASE_FULL: " . $VERSION_RELEASE_FULL . "<br/>";
$explodedVersion = explode('.', $VERSION_RELEASE);
//var_dump($explodedVersion);
$VERSION_FILTERED = $prefix . $explodedVersion[0] . "." . $explodedVersion[1];
//echo "VERSION_FILTERED: " . $VERSION_FILTERED . "<br/>";
?>
