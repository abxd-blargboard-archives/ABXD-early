<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
  $qMisc = "select * from misc";
  $rMisc = Query($qMisc);
  $misc = Fetch($rMisc);
?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>
    <title>
      AcmlmBoard XD<?php print ($title ? " - ".$title : "") ?>
    </title>
    <meta http-equiv="Content-Type" content="text/html; CHARSET=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=8" />
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="css/common.css" />
    <link rel="stylesheet" type="text/css" href="css/<?php print $theme; ?>.css" />
  </head>
  <body>
    <div class="border width100">
      <table>
        <tr>
          <td colspan="3" class="cell0">
            <!-- Board header goes here -->
            <table>
              <tr>
                <td style="border: 0px none;">
                  <img src="<?php print $logopic; ?>" alt="board?" />
                </td>
                <td style="border: 0px none;">
                  <div class="border PoRT">
                    <table class="PoRT">
                      <tr class="header1">
                        <th>
                          Points of Required Attention
                        </th>
                      </tr>
                      <tr>
                        <td class="cell0">
                          <?php print $misc['porabox']; ?>
                        </td>
                      </tr>
                    </table>
                  </div>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr class="smallFonts cell1">
          <td rowspan="2" style="text-align: center; width: 10%;">
            Views: <?php print $misc['views']."\n"; ?>
          </td>
          <td style="text-align: center; width: 80%;">
            <?php
              if($loguser['powerlevel'] == 3)
                print "<a href=\"admin.php\">Admin</a> | ";
            ?>
            <a href="index.php">Main</a> |
            <a href="faq.php">FAQ</a> |
            <a href="memberlist.php">Member list</a> |
            <a href="avatarlibrary.php">Avatars</a> |
            <a href="online.php">Online users</a>
          </td>
          <td rowspan="2" style="text-align: center; width: 10%;">
            <?php print date($dateformat)."\n"; ?>
          </td>
        </tr>
        <tr class="smallFonts cell2">
          <td style="text-align: center">
            <?php
              if($loguserid)
              {
                print UserLink($loguser).": \n";
            ?>
            <a href="login.php?action=logout">Log out</a> |
            <a href="editprofile.php">Edit profile</a> |
            <a href="private.php">Private messages</a>
            <?php
                if(!isset($_POST['id']) && isset($_GET['id']))
                  $_POST['id'] = (int)$_GET['id'];
                if(strpos($_SERVER['SCRIPT_NAME'], "forum.php"))
                  print " | <a href=\"index.php?fid=".$_POST['id']."&amp;action=markasread\">Mark forum read</a>";
                elseif(strpos($_SERVER['SCRIPT_NAME'], "index.php"))
                  print " | <a href=\"index.php?action=markallread\">Mark all forums read</a>";
                //print " (".$_SERVER['SCRIPT_NAME'].")";
              } else
              {
            ?>
            <a href="register.php">Register</a> |
            <a href="login.php">Log in</a><?php
              }
            ?></td>
        </tr>
      </table>
    </div>
<?php
  $timeStart = usectime();
?>
