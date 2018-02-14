<?php
require_once "../config.php";

use \Tsugi\Util\U;
use \Tsugi\Core\LTIX;
use \Tsugi\Grades\GradeUtil;
use \Tsugi\Core\Settings;
use \Tsugi\UI\SettingsForm;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;
$displayname = $USER->displayname;

// Handle the incoming post saving the settings form
if ( SettingsForm::handleSettingsPost() ) {
    $_SESSION['debug_log'] = $LAUNCH->link->settingsDebug();
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// Handle our own manual set of the manual_key setting
if ( isset($_POST['manual_key']) ) {
    $LAUNCH->link->settingsSet('manual_key', $_POST['manual_key']);
    $_SESSION['debug_log'] = $LAUNCH->link->settingsDebug();
    $_SESSION['success'] = "Setting updated";
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// Start of the output
$OUTPUT->header();

// Start of the view
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();

// Place the settings button in the upper right.
if ( $USER->instructor ) SettingsForm::button(true);

// Put out the hidden settings form using predefined UI routines
if ( $USER->instructor ) {
    SettingsForm::start();
    echo('<label for="sample_key">Please enter a string for "sample_key" below.<br/>'."\n");
    SettingsForm::text('sample_key');
    echo("</label>\n");
    SettingsForm::end();
}

echo("<h1>Settings Test Harness</h1>\n");
$OUTPUT->welcomeUserCourse();

if ( $USER->instructor ) {
    echo("<p>Press the settings button in the upper left to change the settings.</p>\n");
}

// Load the old values for the settings
$sk = $LAUNCH->link->settingsGet('sample_key');
echo("<p>The current setting for sample_key is: <b>".htmlent_utf8($sk)."</b></p>\n");

$mk = $LAUNCH->link->settingsGet('manual_key');
echo("<p>The current setting for manual_key is: <b>".htmlent_utf8($mk)."</b></p>\n");

// Lets show how to set a setting in our own code
if ( $USER->instructor ) {
?>
<form method="post">
Enter value for 'manual_key' setting:
<input type="text" name="manual_key" size="40" value="<?= htmlent_utf8($mk)?>"><br/>
<input type="submit" name="send" value="Update 'manual_key' setting">
</form>
<hr/>
<?php
}

if ( isset($_SESSION['debug_log']) && count($_SESSION['debug_log']) > 0) {
    echo("<p>Debug output from setting send:</p>\n");
    $OUTPUT->dumpDebugArray($_SESSION['debug_log']);
}
unset($_SESSION['debug_log']);

// Check the GetAll and Set operations by putting some fun into the context
$allSettings = $LAUNCH->context->settingsGetAll();
$fun = U::get($allSettings, 'fun', 0) + 1;
$LAUNCH->context->settingsSet('fun', $fun);
$allSettings = $LAUNCH->context->settingsGetAll();

echo("\n<pre>\n");
echo("Context Settings Log:\n");
$OUTPUT->dumpDebugArray($LAUNCH->context->settingsDebug());
echo("Context Settings Data:\n");
var_dump($allSettings);
echo("Global Tsugi Objects:\n");
var_dump($USER);
var_dump($CONTEXT);
var_dump($LINK);

echo("\n<hr/>\n");
echo("Session data (low level):\n");
echo($OUTPUT->safe_var_dump($_SESSION));

$OUTPUT->footer();

