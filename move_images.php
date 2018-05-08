<?php
$image_folder_copy = getcwd().'/images/copy/';
$image_folder = getcwd().'/images/mecox/';


$products = fopen('products_2018-04-13.csv', 'r');
$i = 0;
while (($product = fgetcsv($products)) !== FALSE) {

  $product['index'] = $product[0];
  $product['sku'] = $product[1];
  $product['legacy'] = $product[2];
  $product['images'] = $product[3];
  $product['product_gallery'] = $product[4];
  $product['featured_image'] = $product[5];

  $product_images = explode('|', $product['images']);
  $product_gallery = explode('|', $product['product_gallery']);
  if (!$product_gallery) $product_gallery = [];
  $product_gallery[] = $product['featured_image'];

  $image_product_folder = $image_folder.$product['index'].'/';
  echo $image_product_folder.'<br>';

  foreach ($product_gallery as $key => $product_image) {
    $image_path = $image_product_folder.$product_image;
    $image_path_copy = $image_folder_copy.$product_image;
    if (file_exists($image_path_copy)) continue;
    if (!copy($image_path,$image_path_copy)) {
      echo $product['index']."\n";
      $origine_image = str_replace(' ',"%20", $product_images[$key]);
      if ($product['legacy'] == 'yes') {
        $file_source = 'http://mecox.com/images/catalog/'.$origine_image;

      } else {
        $file_source = 'http://mecox.com/images/uploads/pets/'.$product['index'].'/'.$origine_image;
      }
      echo $file_source. '<br>';
      download($file_source, $image_path_copy);
    }
  }

  $i++;
}
fclose($products);



// foreach ($products as $product) {

// }

function dir_is_empty($dir) {
  $handle = opendir($dir);
  while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != "..") {
      return FALSE;
    }
  }
  return TRUE;
}

function get_file_extension($file_name) {
  return substr(strrchr($file_name,'.'),1);
}

function download($file_source, $file_target) {
    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'w+b');
    if (!$rh || !$wh) {
        return false;
    }

    while (!feof($rh)) {
        if (fwrite($wh, fread($rh, 4096)) === FALSE) {
            return false;
        }
        echo ' ';
        flush();
    }

    fclose($rh);
    fclose($wh);

    return true;
}
?>
