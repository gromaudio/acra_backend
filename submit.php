<?php

include "mysql.php";
include "crashes.php";
include "alphaID.php";
include "version.php";

/**
 * Parses a raw data string to extract the CPU serial number and build version.
 *
 * @param string $customData The multiline string containing the device data.
 * @return array An associative array with 'serial' and 'version', or null for values not found.
 */
function parseCustomData(string $customData): array
{
    $result = [
        'serial' => null,
        'version' => null,
    ];

    // Handle data that may have been escaped (e.g., by mysqli_real_escape_string).
    // This replaces literal '\n' and '\r' strings with actual newline characters.
    $customData = str_replace(['\\r\\n', '\\n', '\\r'], ["\n", "\n", "\n"], $customData);

    // Use a regular expression to split by any common newline sequence (\n, \r\n, or \r).
    $lines = preg_split('/\r\n?|\n/', $customData);

    foreach ($lines as $line) {
        // Trim whitespace from the line.
        $line = trim($line);

        // --- Extract Serial Number ---
        // Check if the line starts with 'vl.sys.cpu_sn='
        if (strpos($line, 'vl.sys.cpu_sn=') === 0) {
            // Extract the substring that comes after 'vl.sys.cpu_sn='
            $result['serial'] = substr($line, strlen('vl.sys.cpu_sn='));
        }

        // --- Extract Version Number (Corrected Logic) ---
        // Check if the line starts with 'ro.build.id='
        if (strpos($line, 'ro.build.id=') === 0) {
            // First, get the full build ID string.
            $buildId = substr($line, strlen('ro.build.id='));

            // Use a regex to find the position of the first digit that is
            // immediately followed by a period. This correctly identifies the
            // start of the version number in cases like 'V2SC0.1.1.0'.
            if (preg_match('/[0-9]\./', $buildId, $matches, PREG_OFFSET_CAPTURE)) {
                // $matches[0][1] gives the starting position of the match (e.g., of "0.")
                $startPosition = $matches[0][1];
                // The version is the substring from that starting position to the end.
                $result['version'] = substr($buildId, $startPosition);
            }
        }
    }

    return $result;
}

$f = fopen("log", "a");

$f = fopen("last_access", "w");
fputs($f, "access on ".date("d/M/Y G:i:s")."\n");
fclose($f);

ob_start();

fputs($f, "1\n");

global $VERSION_RELEASE, $VERSION_RELEASE_FULL, $VERSION_FILTERED, $VERSION_RELEASE_A12, $VERSION_RELEASE_FULL_A12, $VERSION_FILTERED_A12,
$VERSION_RELEASE_A7, $VERSION_RELEASE_FULL_A7, $VERSION_FILTERED_A7;

$BUG4787_VER = 'V2OVL3.71.1.0'; //vk

fputs($f, "2\n");

if (!isset($_GET['key'])) {
  echo "no Key";
        log_to_file("No key");
  die();
}

fputs($f, "3\n");

// Check _POST
if (count($_POST) == 0) {
  echo "empty post";
    log_to_file("Empty _POST query");
    die();
}

fputs($f, "4\n");

foreach($_POST as $k => $v) {
    if (array_search(strtolower($k), $values) === FALSE) {
        continue;
    }

    $object[strtolower($k)] = mysqli_real_escape_string($mysql, $v);
}

fputs($f, "5\n");


// Add custom data
$object['appid'] = $_GET['key'];
$object['added_date'] = time();
$object['issue_id'] = issue_id($object['stack_trace'], $object['package_name']);

if (isset($object['user_ip'])) {
  if(!(trim($object['user_ip']) === '') && $object['package_name'] != "com.gromaudio.vlineservice" && $object['package_name'] != "com.gromaudio.vlite2")
    $object['appid'] = $object['user_ip'];
  unset($object['user_ip']); 
}

fputs($f, "6\n");

$appid = $object['appid'];
if ($appid === "n7yjvztxh97d76jy4ek5ax4uc3d9cgx7" || $appid === "f5ar7wfpkdmda852krjpwmt8iunu4d9f") {
  $applog = $object['application_log'];
  if (/*strpos($applog, "rk3288:8.1.0/" . $VERSION_FILTERED) === false && 
  		strpos($applog, 'VL2-8.1') === false */
  		/*&& strpos($applog, 'crash_dump failed') === false && */ 
  		strpos($applog, 'V2SC0.1') === false && 
      strpos($applog, $VERSION_FILTERED_A7) === false &&
      strpos($applog, 'LITE_N_VL1.1.1.0') === false && 
      strpos($applog, 'LITE_N_VL0.0') === false && 
  		strpos($applog, $BUG4787_VER) === false &&
  		strpos($applog, "rk3288_Android12:12/" . $VERSION_FILTERED_A12) === false) {
      echo "Old version";
      return;
  }
} 
else if ($appid === "72gym8mf5juqjwxk43y8m47ygq3nnab8" || $appid === "95wjw673hkkiw37rcumqarrwiczcqpk3" || $appid === "5ztxh97ax4uc3n7yjvd76jy4ekd9cgx7" || $appid === "77wjw673hkkiw37rcumqarrwiczcqpk3" || $appid === "72gym8mf5juqjwxk43y8m47ygq3nnab8") {
  $applog = $object['custom_data'];
    if (/*strpos($applog, $VERSION_FILTERED) === false && 
    	strpos($applog, 'VL2-8.1') === false && */
    	strpos($applog, 'V2SC0.1') === false && 
      strpos($applog, 'LITE_N_VL1.1.1.0') === false && 
      strpos($applog, 'LITE_N_VL0.0') === false && 
    	strpos($applog, $VERSION_FILTERED_A7) === false && 
    	strpos($applog, $BUG4787_VER) === false &&
    	strpos($applog, $VERSION_FILTERED_A12) === false) {
      echo "Old version";
      return;
  }
}

fputs($f, "7\n");

$sql = "SELECT `status` FROM `crashes` WHERE `issue_id` = '" . $object['issue_id'] . "'";
$res = mysqli_query($mysql,$sql);
mysqli_data_seek($res,  0); 
$status = mysqli_fetch_array($res)[0];

if ($status == 0) {
  $object['status'] = STATE_NEW;

} else {
  $object['status'] = $status;
}

fputs($f, "8\n");

// get logcat and application_log and unset them from the object
$application_log = $object['logcat'];
$logcat = $object['application_log'];

$application_log_escaped = $application_log;//mysqli_real_escape_string($mysql, $application_log);
$logcat_escaped = $logcat;//mysqli_real_escape_string($mysql, $logcat);

unset($object['logcat']);
unset($object['application_log']);


$object['shared_preferences'] = "";
$object['environment'] = "";
$object['device_features'] = "";
$object['display'] = "";
$object['crash_configuration'] = "";
$object['initial_configuration'] = "";
$object['build'] = "";

$parsedData = parseCustomData($object['custom_data']);
$object['user_email'] = $parsedData['serial'];
$object['android_version'] = $parsedData['version'];

// Save to DB
$sql = create_mysql_insert($object);
$success = mysqli_query($mysql,$sql);

if ($success != TRUE) {
  log_to_file("Unable to save record: ".mysqli_error($mysql));
  log_to_file("Query was: ".$sql);
}

$last_id = mysqli_insert_id($mysql);
$sql = "INSERT INTO crash_detail (id, application_log, logcat)
        VALUES ($last_id, '$application_log_escaped', '$logcat_escaped')";

$success = mysqli_query($mysql, $sql);
if ($success != TRUE) {
    log_to_file("Unable to save record: ".mysqli_error($mysql));
    log_to_file("Query was: ".$sql);
}

fputs($f, "9: $last_id, " . $object['user_email'] . ", " . $object['android_version'] . "\n");

// Close MySQL
mysqli_close($mysql);

fputs($f, "10\n");


fputs($f, "Output of ".date("d/M/Y G:i:s").":\n".ob_get_clean());
fclose($f);

echo "What are you doing here?";

?> 