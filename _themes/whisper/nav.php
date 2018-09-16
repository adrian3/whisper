<?php
// don't get configs if run from cron job:
if (!defined('STDIN')) {
 include_once "../../_admin/config.php";
}
  ?>

  <header>
    <a class="logo" href="<?php echo $siteUrl; ?>/index.html"><?php echo $siteTitle; ?></a>

    <ul class="nav">
      <li><a href="<?php echo $siteUrl; ?>/setup.html">Setup</a></li>
      <li><a href="<?php echo $siteUrl; ?>/dropbox.html">Dropbox</a></li>
      <li><a href="<?php echo $siteUrl; ?>/archive.html">Blog</a></li>
    </ul>
  </header>
