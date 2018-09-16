<?php
// don't get configs if run from cron job:
if (!defined('STDIN')) {
 include_once "../../_admin/config.php";
}
  ?>

<!-- {{prevNextLinks}} -->

</div>

<footer>
  <small style="color: #ccc;">&copy; <?php echo $copyright; ?> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a style="color: #ccc;" href="<?php echo $siteUrl; ?>/humans.txt">humans.txt</a> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a style="color: #ccc;" href="<?php echo $siteUrl; ?>/sitemap.xml">sitemap.xml</a> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a style="color: #ccc;" href="https://adrian3.github.io/whisper/">powered by a whisper</a></small>
</footer>

<script type="text/javascript">
  // {{customJavascript}}
</script>
</body>
</html>
