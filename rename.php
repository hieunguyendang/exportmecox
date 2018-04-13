<?php
$image_folder = getcwd().'/images/mecox/public_html/images/uploads/pets/';
$image_folder_copy = getcwd().'/images/mecox/copy/';

echo $image_folder.'<br>';

$products = fopen('20850.csv', 'r');
while (($product = fgetcsv($products)) !== FALSE) {
  // echo '<pre>';
  // var_dump($product);
  // die();
  //$line is an array of the csv elements
  $product['product_gallery'] = $product[5];
  $product['index'] = $product[0];
  $product['sku'] = $product[1];
  $product_images = explode('|', $product['product_gallery']);
  $image_product_folder = $image_folder.$product['index'].'/';
  $image_product_folder_copy = $image_folder_copy.$product['index'].'/';
  mkdir($image_product_folder_copy, 0777);
  foreach ($product_images as $key => $product_image) {
    $image_path = $image_product_folder.$product_image;
    $image_type = get_file_extension($product_image);
    $replace_path = $image_product_folder_copy.$product['sku'].'_'.$key.'.'.$image_type;
    echo '<br>';
    echo $image_path;
    echo '<br>';
    echo $replace_path;
    if (!copy($image_path,$replace_path)) {
      echo "failed to copy $image_path...\n";
      $file_source = 'http://mecox.com/images/uploads/pets/'. $product['index'].'/'.$product_image;
      download($file_source, $replace_path);
    }
  }
}
fclose($products);



// foreach ($products as $product) {

// }

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
