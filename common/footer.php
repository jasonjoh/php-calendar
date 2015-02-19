<!-- footer begins -->

<pre class="debug-dump">
SESSION VARIABLES:
  <?php
    echo "Session ID: ".session_id()."\n";
    foreach ($_SESSION as $key=>$value) 
    {
      echo "  ".$key.": ".$value;
      echo "\n";
    }
  ?>
</pre>
</body>
</html>