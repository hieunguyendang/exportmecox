<?php
// $empties = '1592,1594,1595,1602,1603,1604,1605,1607,1608,2608,2609,2610,2612,2614,2615,2620,2621,2623,2625,2626,2629,2630,2635,2639,2641,2642,2643,2645,2647,2649,2654,2655,2656,2658,2659,2661,2663,2666,2670,2672,2675,2678,2683,2684,2685,2686,2687,2688,2690,2692,2693,2696,2699,2700,2702,2706,2707,2709,2711,2713,2714,2716,2720,2722,2726,2727,2728,2730,2733,2734,2735,2740,2741,2742,2743,2745,2747,2748,2753,2756,2762,2767,2768,2769,2771,2773,2776,2777,2779,2780,2784,2786,2792,2793,2795,2797,2803,2805,2811,2813,2814,2815,2816,2817,2819,2820,2824,2825,2827,2829,2830,2833,2835,2840,2842,2846,2848,2852,2853,2855,2857,2858,2861,2862,2863,2865,2866,2871,2872,2873,2876,2881,2882,2883,2888,2890,2893,2897,2898,2901,2902,2904,2908,2910,2913,2914,2922,2923,2924,2928,2929,2930,2932,2934,2936,2938,2939,2940,2941,2945,2946,2947,2948,2949,2951,2954,2955,2956,2957,2960,2961,2962,2964,2965,2967,2974,2975,2977,2978,2980,2982,2983,2984,2985,2986,2987,2989,2990,2991,2992,2995,2996,3002,3003,3004,3006,3008,3011,3012,3015,3017,3018,3019,3021,3022,3025,3026,3031,3032,3034,3035,3036,3037,3042,3046,3047,3049,3050,3051,3052,3054,3056,3057,3063,3066,3067,3070,3072,3073,3074,3076,3079,3082,3083,3086,3089,3092,3095,3100,3102,3104,3105,3107,3109,3111,3119,3122,3123,3125,3128,3129,3130,3132,3135,3137,3139,3140,3142,3143,3144,3145,3146,3147,3148,3150,3151,3154,3155,3157,3158,3164,3165,3167,3168,3174,3177,3178,3179,3180,3181,3183,3184,3187,3189,3190,3195,3198,3202,3204,3206,3207,3208,3209,3213,3217,3218,3220,3225,3226,3228,3230,3231,3232,3233,3236,3237,3238,3241,3243,3248,3250,3251,3252';
// $empties= explode(',', $empties);
// var_dump(count($empties));


// die();

$image_folder_copy = getcwd().'/images/mecox/copy/';
$image_folder = getcwd().'/images/mecox/mecox/';

$products = fopen('products_final.csv', 'r');
$i = 0;
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
  // if($product['index']== 27249) {
  //   echo '<pre>';
  // var_dump($product);
  // die();
  // }
  // echo '<pre>';
  // var_dump($product_images);
  // die();
  $image_product_folder = $image_folder.$product['index'];
  //$image_product_folder_copy = $image_folder_copy.$product['index'].'/';
  $fi = new FilesystemIterator($image_product_folder, FilesystemIterator::SKIP_DOTS);
  $num_of_images = iterator_count($fi);
  // $num_of_images = count_files($image_product_folder);
  if (count($product_images) != $num_of_images) {
     echo $i.',';
  }
 // echo '<pre>';
 //  var_dump($image_product_folder_copy);
  //if (dir_is_empty($image_product_folder) ) {
    // echo '<br>';
    // echo $product['index'];
  //  echo $i.',';
  //   mkdir($image_product_folder_copy, 0777);
  //   foreach ($product_images as $key => $product_image) {
  //     $image_type = get_file_extension($product_image);
  //     $replace_path = $image_product_folder_copy.$product['sku'].'_'.$key.'.'.$image_type;
  //      // $replace_path = $image_product_folder_copy.$product['gallery'][$key];
  // //      echo '<pre>';
  // // var_dump($replace_path);
  //     download($product['index'].'/'.$product_image, $replace_path);
  //   }
  //}

  $i++;
}
fclose($products);


function count_files($dir) {
  $fi = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
  return iterator_count($fi);
}
?>
