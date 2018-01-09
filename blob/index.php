<?php
require_once "../config.php";

use \Tsugi\Util\LTI;
use \Tsugi\UI\Output;
use \Tsugi\Core\LTIX;
use \Tsugi\Blob\BlobUtil;

$p = $CFG->dbprefix;

// Sometimes, if the maxUpload_SIZE is exceeded, it deletes all of $_POST
// Thus losing our session :(
if ( $_SERVER['REQUEST_METHOD'] == 'POST' && count($_POST) == 0 ) {
    die('Error: Maximum size of '.BlobUtil::maxUpload().'MB exceeded.');
}

// Sanity checks
$LAUNCH = LTIX::requireData(array(LTIX::CONTEXT, LTIX::LINK));

if ( ! $USER->instructor ) die("Must be instructor");

// Other times, we see an error indication on bad upload that does not delete all the $_POST
if( isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] == 1) {
    $_SESSION['error'] = 'Error: Maximum size of '.BlobUtil::maxUpload().'MB exceeded.';
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

if( isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] == 0)
{
    $fdes = $_FILES['uploaded_file'];
    $filename = isset($fdes['name']) ? basename($fdes['name']) : false;

    // Sanity-check the file
    $safety = BlobUtil::validateUpload($fdes);
    if ( $safety !== true ) {
        $_SESSION['error'] = "Error: ".$safety;
        error_log("Upload Error: ".$safety);
        header( 'Location: '.addSession('index') ) ;
        return;
    }

    $blob_id = BlobUtil::uploadFileToBlob($fdes);
    if ( $blob_id === false ) {
        $_SESSION['error'] = 'Problem storing file in server: '.$filename;
        header( 'Location: '.addSession('index') ) ;
        return;
    }

    $_SESSION['success'] = 'File uploaded';
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// If we got a post but no file...
if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    $_SESSION['error'] = 'Please choose a file to upload';
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

// View
$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
$OUTPUT->welcomeUserCourse();

// TODO: Make this a method in BlobUtil
$stmt = $PDOX->prepare("SELECT file_id, file_name FROM {$p}blob_file
        WHERE context_id = :CI");
$stmt->execute(array(":CI" => $CONTEXT->id));

echo("<ul>\n");
$count = 0;
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    $id = $row['file_id'];
    $fn = $row['file_name'];
    $serve = BlobUtil::getAccessUrlForBlob($id);
    echo '<li><a href="'.addSession($serve).'" target="_blank">'.htmlentities($fn).'</a>';
    if ( $USER->instructor ) {
        echo ' (<a href="blob_delete.php?id='.$id.'">Delete</a>)';
    }
    echo '</li>';
    $count++;
}

if ( $count == 0 ) echo "<p>No Files Found</p>\n";

echo("</ul>\n");

if ( $USER->instructor ) { ?>
<h4>Upload file (max <?php echo(BlobUtil::maxUpload());?>MB)</h4>
<form name="myform" enctype="multipart/form-data" method="post" action="<?php addSession('index.php');?>">
<p>Upload File: <input name="uploaded_file" type="file">
   <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo(BlobUtil::maxUpload());?>000000" />
   <input type="submit" name="submit" value="Upload"></p>
</form>
<!-- A little debug output: -->
<?php
    if ( isset($CFG->dataroot) && $CFG->dataroot ) {
        if ( is_writeable($CFG->dataroot) ) {
            echo("<p>Note: Storing blobs in the file system at ");
            echo(htmlentities($CFG->dataroot));
            echo(".");
        } else {
            echo('<p style="background: pink;">Warning: Storing blobs in the database as because ');
            echo(htmlentities($CFG->dataroot));
            echo(" is not writeable.");
        }
    } else {
        echo("<p>Note: Storing blobs in the database.\n");
    }
    echo("</p>\n");
}

$OUTPUT->footer();
