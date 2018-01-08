<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

// Sanity checks
$LAUNCH = LTIX::requireData();

if ( ! $USER->instructor ) die("Must be instructor");

$id = $_REQUEST['id'];
if ( strlen($id) < 1 ) {
    die("File not found");
}

$p = $CFG->dbprefix;
$stmt = $PDOX->prepare("SELECT file_name, path FROM {$p}blob_file
            WHERE file_id = :ID AND context_id = :CID");
$stmt->execute(array(":ID" => $id, ":CID" => $CONTEXT->id));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    die("File not found");
}

$fn = $row['file_name'];
$path = $row['path'];

if ( isset($_POST["doDelete"]) ) {
    $stmt = $PDOX->prepare("DELETE FROM {$p}blob_file
            WHERE file_id = :ID AND context_id = :CID");
    $stmt->execute(array(":ID" => $id, ":CID" => $CONTEXT->id));
    $_SESSION['success'] = 'File deleted';
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// Switch to view / controller
$OUTPUT->header();
$OUTPUT->flashMessages();

if ( strlen($path) > 0 ) {
    echo '<h4 style="color:orange">Note this will not delete the blob on disk at: ' .htmlent_utf8($path). "</h4>\n";
}
echo '<h4 style="color:red">Are you sure you want to delete: ' .htmlent_utf8($fn). "</h4>\n";
?>
<form name=myform enctype="multipart/form-data" method="post">
<input type=hidden name="id" value="<?php echo $_REQUEST['id']; ?>">
<p><input type=submit name=doCancel onclick="location='<?php echo(addSession('index.php'));?>'; return false;" value="Cancel">
<input type=submit name=doDelete value="Delete"></p>
</form>
<?php

$OUTPUT->footer();
