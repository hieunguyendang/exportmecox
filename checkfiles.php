<?php
$image_folder_copy = getcwd().'/images/mecox/copy/';
$image_folder = getcwd().'/images/mecox/mecox/';

echo $image_folder.'<br>';

$products = fopen('db_export_products_2018-03-19.csv', 'r');
$i = 0;
while (($product = fgetcsv($products)) !== FALSE) {
  // echo '<pre>';
  // var_dump($product);
  // die();
  //$line is an array of the csv elements
  // $product['images'] = $product[3];
  $product['index'] = $product[0];
  //$product['sku'] = $product[1];
  // $product['gallery'] = $product[4];
  // $product_images = explode('|', $product['images']);
  // echo '<pre>';
  // var_dump($product_images);
  // die();
  $image_product_folder = $image_folder.$product['index'].'/';
  $image_product_folder_copy = $image_folder_copy.$product['index'].'/';
 // echo '<pre>';
 //  var_dump($image_product_folder_copy);
  if (dir_is_empty($image_product_folder) ) {
    // echo '<br>';
    // echo $product['index'];
    echo $i.',';
  //   mkdir($image_product_folder_copy, 0777);
  //   foreach ($product_images as $key => $product_image) {
  //     $image_type = get_file_extension($product_image);
  //     $replace_path = $image_product_folder_copy.$product['sku'].'_'.$key.'.'.$image_type;
  //      // $replace_path = $image_product_folder_copy.$product['gallery'][$key];
  // //      echo '<pre>';
  // // var_dump($replace_path);
  //     download($product['index'].'/'.$product_image, $replace_path);
  //   }
  }

  $i++;
}
fclose($products);



// foreach ($products as $product) {

// }

function dir_is_empty($dir) {
    $handle = opendir($dir);
    if ($handle) {
       while (false !== ($entry = readdir($handle))) {
      if ($entry != "." && $entry != "..") {
        return FALSE;
      }

    }
   }
    return TRUE;

}

function get_file_extension($file_name) {
  return substr(strrchr($file_name,'.'),1);
}

function download($file_image, $file_target) {
    $file_source = 'http://mecox.com/images/uploads/pets/'.$file_image;
    $rh = fopen($file_source, 'rb');
    if (!$rh) {
      echo $file_source;
      $file_source = 'http://mecox.com/images/catalog/'.$file_image;
      $rh = fopen($file_source, 'rb');
    }
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
