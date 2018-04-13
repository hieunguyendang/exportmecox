<?php

$image_folder_copy = getcwd().'/images/mecox/copy/';
$image_folder = getcwd().'/images/mecox/mecox/';

echo $image_folder.'<br>';

$products = fopen('db_export_products_2018-03-19.csv', 'r');
$g_products = [];
while (($product = fgetcsv($products)) !== FALSE) {
  $g_products[] =$product;
}

$empties = '214,378,845,889,1305,1847,2238,2509,3088,4193,5679,5718,5719,5720,5721,5722,5723,5724,5725,5726,5727,5728,5729,5730,5731,5732,5733,5734,5735,5736,5737,5738,5739,5740,5741,5742,5743,5744,5745,5746,5747,5748,5749,5750,5751,5752,5753,5754,5755,5756,5757,5758,5759,5760,5761,5762,5763,5764,5765,5766,5767,5768,5769,5770,5771,5772,5773,5774,5775,5776,5777,5778,5779,5780,5781,5782,5783,5784,5785,5786,5787,5788,5789,5790,5791,5792,5793,5794,5795,5796,5797,5798,5799,5800,5801,5802,5803';
$empties= explode(',', $empties);

foreach ($empties as $id) {
  $product = $g_products[$id];
  // echo '<pre>';
  // var_dump($product);
  // die();
  $product['images'] = $product[3];
  $product['legacy'] = $product[2];
  $product['index'] = $product[0];
  $product['sku'] = $product[1];
  // $product['gallery'] = $product[4];
  $product_images = explode('|', $product['images']);
  // echo '<pre>';
  // var_dump($product_images);
  // die();
  // $image_product_folder = $image_folder.$product['index'].'/';
  $image_product_folder_copy = $image_folder_copy.$product['index'].'/';
 // echo '<pre>';
 //  var_dump($image_product_folder_copy);
  // if (dir_is_empty($image_product_folder) ) {
  //   echo '<br>';
  //   echo $product['index'];
    mkdir($image_product_folder_copy, 0777);
    foreach ($product_images as $key => $product_image) {
      $image_type = get_file_extension($product_image);
      $replace_path = $image_product_folder_copy.$product['sku'].'_'.$key.'.'.$image_type;
       // $replace_path = $image_product_folder_copy.$product['gallery'][$key];
  //      echo '<pre>';
  // var_dump($replace_path);
      if ($product['legacy'] == 'yes') {
        $file_source = 'http://mecox.com/images/catalog/'.$product_image;
      } else {
        $file_source = 'http://mecox.com/images/uploads/pets/'.$product['index'].'/'.$product_image;
      }
      download($file_source, $replace_path);
    }
  // }

}

/*
while (($product = fgetcsv($products)) !== FALSE) {
  // echo '<pre>';
  // var_dump($product);
  // die();
  //$line is an array of the csv elements
  $product['images'] = $product[3];
  $product['index'] = $product[0];
  $product['sku'] = $product[1];
  // $product['gallery'] = $product[4];
  $product_images = explode('|', $product['images']);
  // echo '<pre>';
  // var_dump($product_images);
  // die();
  $image_product_folder = $image_folder.$product['index'].'/';
  $image_product_folder_copy = $image_folder_copy.$product['index'].'/';
 // echo '<pre>';
 //  var_dump($image_product_folder_copy);
  if (dir_is_empty($image_product_folder) ) {
    echo '<br>';
    echo $product['index'];
    mkdir($image_product_folder_copy, 0777);
    foreach ($product_images as $key => $product_image) {
      $image_type = get_file_extension($product_image);
      $replace_path = $image_product_folder_copy.$product['sku'].'_'.$key.'.'.$image_type;
       // $replace_path = $image_product_folder_copy.$product['gallery'][$key];
  //      echo '<pre>';
  // var_dump($replace_path);
      download($product['index'].'/'.$product_image, $replace_path);
    }
  }


}
*/

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
