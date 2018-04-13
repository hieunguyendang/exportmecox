<?php

try {
    $phar = new PharData('wp-content/uploads/mecox.tar.gz');
    $phar->extractTo('wp-content/uploads/products/'); // extract all files
} catch (Exception $e) {
    var_dump($e);
}

  // $unzip = exec("tar -xvzf products.csv.tar.gz test/");
  // echo 'Unzip command executed';
?>
