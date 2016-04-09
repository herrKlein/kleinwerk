<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">
  <xsl:import href="xhtmlconvert.xsl" />
  <xsl:output method="xml" omit-xml-declaration="yes" indent="yes" />
  <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" indent="yes"/>
  <xsl:template match="/">
    <html>
      <head>
        <title>
          <xsl:value-of select="page/contenttitle" />
        </title>
        
        <meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />
        <meta name="description" content="Illustraties en ander werk van Patrick de Klein" />
        <meta name="keywords" content="Illustraties, Illustrator, Opencanvas, schilderen" />
        <meta name="verify-v1" content="4KdCSBF09qdTUmdc3w6bay+Z6DVC2wE83cbq+4O+/sk=" />

        <script type="text/javascript">
          var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
          document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
        </script>
        <script type="text/javascript">
          try {
          var pageTracker = _gat._getTracker("UA-10028581-1");
          pageTracker._trackPageview();
          } catch(err) {}</script>

          <style>
          .item img{ border-radius: 5px; }
          .item{ margin-bottom: 10px;  }
          </style>

          <link rel="shortcut icon" href="favicon.ico" type="image/ico" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="assets/css/main.css" />
		<!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
		<!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
	</head>
        <body>
		<!-- Wrapper -->
			<div id="wrapper">

				<!-- Header -->
					<header id="header">
						<div class="inner">

							<!-- Logo -->
								<a href="index.php" class="logo">
									<span class="symbol"><img src="images/logo.svg" alt="" /></span><span class="title">Kleinwerk</span>
								</a>

							<!-- Nav -->
								<nav>
									<ul>
										<li><a href="#menu">Menu</a></li>
									</ul>
								</nav>

						</div>
					</header>


				<!-- Menu -->
					<nav id="menu">
						<h2>Menu</h2>
						<ul>
                <xsl:apply-templates select="page/websitemenu/*|text()"/>
                <xsl:apply-templates select="page/languageswitch/*|text()"/>
                            
						</ul>
					</nav>
          

          <!-- End Header and Nav -->

          <!-- Three-up Content Blocks -->

<div id="main">
    <div class="inner">
        <header>
            <h1>This is Phantom, a free, fully responsive site<br />
            template designed by <a href="http://html5up.net">HTML5 UP</a>.</h1>
            <p>Etiam quis viverra lorem, in semper lorem. Sed nisl arcu euismod sit amet nisi euismod sed cursus arcu elementum ipsum arcu vivamus quis venenatis orci lorem ipsum et magna feugiat veroeros aliquam. Lorem ipsum dolor sit amet nullam dolore.</p>
            <xsl:apply-templates select="page/content[0]/*|text()"/>            
        </header>

                <xsl:apply-templates select="page/functionality/*|text()"/>

                <span id="page-nav">      
                      <a href="/kleinwerk/index.php?page=scrollimages&amp;scrollpage='. $scrollpage .'">asdasfsdfdstest</a>
                </span>

        <!-- Footer -->
</div>
					</div>

				<!-- Footer -->
					<footer id="footer">
						<div class="inner">
							<section>
								<h2>Get in touch</h2>
								<form method="post" action="#">
									<div class="field half first">
										<input type="text" name="name" id="name" placeholder="Name" />
									</div>
									<div class="field half">
										<input type="email" name="email" id="email" placeholder="Email" />
									</div>
									<div class="field">
										<textarea name="message" id="message" placeholder="Message"></textarea>
									</div>
									<ul class="actions">
										<li><input type="submit" value="Send" class="special" /></li>
									</ul>
								</form>
							</section>
							<section>
								<h2>Follow</h2>
								<ul class="icons">
									<li><a href="#" class="icon style2 fa-twitter"><span class="label">Twitter</span></a></li>
									<li><a href="#" class="icon style2 fa-facebook"><span class="label">Facebook</span></a></li>
									<li><a href="#" class="icon style2 fa-instagram"><span class="label">Instagram</span></a></li>
									<li><a href="#" class="icon style2 fa-dribbble"><span class="label">Dribbble</span></a></li>
									<li><a href="#" class="icon style2 fa-github"><span class="label">GitHub</span></a></li>
									<li><a href="#" class="icon style2 fa-500px"><span class="label">500px</span></a></li>
									<li><a href="#" class="icon style2 fa-phone"><span class="label">Phone</span></a></li>
									<li><a href="#" class="icon style2 fa-envelope-o"><span class="label">Email</span></a></li>
								</ul>
							</section>
							<ul class="copyright">
								<li>(c)Untitled. All rights reserved</li><li>Design: <a href="http://html5up.net">HTML5 UP</a></li>
							</ul>
						</div>
					</footer>

			</div>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/skel.min.js"></script>
			<script src="assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="assets/js/main.js"></script>


          <script src="design/scripts/imagesloaded.js"></script>
          <script src="design/scripts/jquery.masonry.min.js"></script>
          <script src="design/scripts/jquery.infinitescroll.min.js"></script>
          <script src="design/scripts/scrollimages.js"></script>
            
    
    </body>
  </html>
</xsl:template>
</xsl:stylesheet>
