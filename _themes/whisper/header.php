<?php include_once "../_admin/config.php"; ?>

<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>

    <link rel="alternate" type="application/rss+xml"  title="XML Feed for <?php echo $siteTitle; ?>" href="<?php echo $siteUrl; ?>/rss.xml" />
    <link rel="alternate" title="JSON Feed for <?php echo $siteTitle; ?>" type="application/json" href="<?php echo $siteUrl; ?>/feed.json" />

    <link href="https://fonts.googleapis.com/css?family=Vollkorn:400,400i,600,600i" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $siteUrl; ?>/_themes/whisper/css/whisper.css"/>

    <!-- {{jquery}} -->

</head>

<body class="{{bodyclass}}">
  <?php include $prefix."_themes/".$theme."/nav.php"; ?>
  <div class="container">
