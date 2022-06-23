<?php

// Load SBMailer Class
require_once ( __DIR__ . '/../SBMailer.php' );

// Import the configurations
require_once ( __DIR__ . '/configuration.php' );

// require_once ( __DIR__ . '/../SBMailer.php' );

// /**
//  * Clean up the path to be used in html and adds domain if needed
//  * @param path string original path
//  * @return string Path fixed
//  */
// function fixPath ($path, $addDomain = false) {
//    $fixedPath = $path;

//    // Remove the beggining if found
//    if (substr($path, 0, 3) === '../') {
//        $fixedPath = substr($fixedPath, 3);
//    }

//    // OPTION 1 --------------------------------------------------------------
//    // You could just to that: Replace the spaces to %20
//    $fixedPath = str_replace(' ', '%20', $fixedPath);
//    // end OPTION 1 ----------------------------------------------------------

//    // // OPTION 2 --------------------------------------------------------------
//    // // Extract the filename from path, and apply the rawurlencode on it
//    // // It uses SBMailerUtils from SBMailer
//    // // By the Way, This function (fixPath) could be added to SBMailerUtils
//    // // And you could use it like that:
//    // // $bundlepiclg = SBMailerUtils::fixPath($bundlepiclg, true);
//    // $filename = (string) SBMailerUtils::mb_pathinfo($path, PATHINFO_BASENAME);
//    // // Replaces the path with the new filename
//    // $fixedPath = str_replace($filename, rawurlencode($filename), $fixedPath);
//    // // end OPTION 2 ----------------------------------------------------------

//    // Add the domain if true
//    if ($addDomain) {
//        $fixedPath = 'https://www.stonebasyx.com/' . $fixedPath;
//    }

//    return $fixedPath;
// }

// // Using it
// $bundlepiclg = "../_siteadmin2015/bundlepics/lg/113-229-5-281 Slab No _ 39.jpeg";
// $bundlepiclg = fixPath($bundlepiclg, true);

// echo $bundlepiclg;

// die;

$result = "";

// Creates the default mailer instance as configurations
$mailer = SBMailer::createDefault();
if ($mailer->send ()) {
   $result = "Email has been sent.";
} else {
   $result = $mailer->getErrorInfo();
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    function getInput($field, $enableHtml = false) {
        if (isset($_POST[$field])) {
            $data = trim($_POST[$field]);
            $data = stripslashes($data);
            if (!$enableHtml) {
                $data = htmlspecialchars($data);
            }
            return $data;
        }
        return "";
     }

    // Creates the default mailer instance as configurations
    $mailer = SBMailer::createDefault();

    // Defining true would enable Exceptions
    // $mailer = SBMailer::createDefault(true);

    // Set the From fields of email
    $mailer->setFrom(getInput("from"), getInput("fromName"));
    
    $replyTo = getInput("replyTo");
    if (!empty($replyTo)) {
        $mailer->addReplyTo($replyTo, getInput("replyToName"));
    }

    // Add recipients
    $mailer->addAddress (getInput("to"), getInput("toName"));
    
    // CC
    $cc = getInput("cc");
    if (!empty($cc)) {
        $mailer->addCC($cc, getInput("ccName"));
    }
    
    // BCC
    $bcc = getInput("bcc");
    if (!empty($bcc)) {
        $mailer->addBcc($bcc, getInput("bccName"));
    }

    // Add attachments
    if (isset($_FILES['attach']) && !empty($_FILES['attach']["tmp_name"])) {
        $mailer->addAttachment( $_FILES['attach']['tmp_name'], $_FILES['attach']['name']);
    }

    // Set the subject and the email body
    // Always HTML body
    $mailer->setSubject(getInput("subject"));
    //$mailer->Subject = (getInput("subject")); // PHPMailer compatibility

    //$mailer->isHTML(false); // We use HTML by default. Use it if you need text/plain
    $mailer->setBody(getInput("body", true));
    //$mailer->Body = getInput("body", true); // PHPMailer compatibility

    //$mailer->setAltBody("Alternative Body when reader does not support HTML");
    //$mailer->AltBody = "Alternative Body when reader does not support HTML"; // PHPMailer compatibility

    // Sends the email
    if ($mailer->send ()) {
        $result = "Email has been sent.";
    } else {
        $result = $mailer->getErrorInfo();
    }

    // // When exceptions enabled
    // try {
    //     $mailer->send ();
    //     echo "Email sent.";
    // } catch (Exception $e) {
    //     echo $e->getMessage();
    // }

}
?><html>
   
<head>
   <style>
      .error {color: #FF0000;}
      table {
        width: 100%;
      }
      table tr td.header {
        background-color: #eeeeee;
        text-align: right;
      }
      input, textarea {
        width: 100%;
      }
      input.button {
        width: 100px;
        font-weight: bold;
      }
   </style>
</head>

<body> 
     
   <h2>Test New Email</h2>
   
   <p><span class = "error"><?php echo $result; ?></span></p>
   
   <form method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
      <table>
         <tr>
            <td class="header">From:</td>
            <td><input type="email" name="from" required placeholder="Email"></td>
            <td><input type="text" name="fromName" placeholder="Name"></td>
         </tr>
         <tr>
            <td class="header">Reply To:</td>
            <td><input type="email" name="replyTo" placeholder="Email"></td>
            <td><input type="text" name="replyToName" placeholder="Name"></td>
         </tr>
         <tr>
            <td class="header">To:</td>
            <td><input type="email" name="to" required placeholder="Email"></td>
            <td><input type="text" name="toName" placeholder="Name"></td>
         </tr>
         <tr>
            <td class="header">CC:</td>
            <td><input type="email" name="cc" placeholder="Email"></td>
            <td><input type="text" name="ccName" placeholder="Name"></td>
         </tr>
         <tr>
            <td class="header">BCC:</td>
            <td><input type="email" name="bcc" placeholder="Email"></td>
            <td><input type="text" name="bccName" placeholder="Name"></td>
         </tr>
         <tr>
            <td class="header">Subject:</td>
            <td colspan="2"><input type="text" name="subject" required></td>
         </tr>
         <tr>
            <td class="header">Html Body:</td>
            <td colspan="2"><textarea rows="5" name="body"></textarea></td>
         </tr>
         <tr>
            <td class="header">Attachment:</td>
            <td colspan="2">
                <input type="hidden" name="MAX_FILE_SIZE" value="10000" />
                <input name="attach" type="file" />
            </td>
         </tr>
         <tr>
            <td colspan="3">
               <input class="button" type="submit" name="submit" value="Send"> 
            </td>
         </tr>
      </table>
   </form>
   
</body>
</html>