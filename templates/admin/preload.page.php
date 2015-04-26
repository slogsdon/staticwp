<div class="wrap" id="staticwp-preload">
  <h2>StaticWP Preload</h2>

  <nav>
    <?php \StaticWP\View::template('admin/menu', 'include'); ?>
    <br />
    <br />
  </nav>

  <h3>What's "preload" mean?</h3>
  <p>
    Preloading is way for StaticWP to generate static HTML for all of your 
    supported content at once. It is a great idea to preload after 
    activating StaticWP.
  </p>
  
  <form action="" method="post">
    <input type="hidden" name="staticwp-action" value="preload" />
    <?php wp_nonce_field('staticwp'); ?>

    <p>Start preloading?</p>
    <input type="submit" value="Go for it!" class="button action" />
  </form>
</div>