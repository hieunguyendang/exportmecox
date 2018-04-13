<?php
$hostname = "172.22.0.1";
$user = 'root';
$password = '123456';
$database = 'mecox_production_932018';
$csv_filename = 'db_export_products_'.date('Y-m-d').'.csv';
$mysqli = new mysqli($hostname, $user, $password,$database);

//////////////////////////////////////////


// /* check connection */
if ($mysqli->connect_error) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}
if (!$mysqli->set_charset("utf8")) {
    //printf("Error loading character set utf8: %s\n", $mysqli->error);
    exit();
} else {
    //printf("Current character set: %s\n", $mysqli->character_set_name());
}

$product_sql = 'SELECT ev.version_data, ct.title, ct.status, cd.entry_id as id,  cd.field_id_1 as av, cd.field_id_2 as description, cd.field_id_3 as sku, cd.field_id_5 as info_dimensions,
cd.field_id_7 as tags, sp.regular_price as regular_price, cd.field_id_6 as lecacy_image, cd.field_id_9 as lecacy_product_id


FROM exp_channel_data cd
LEft join exp_store_products sp on cd.entry_id = sp.entry_id
LEft join exp_channel_titles ct on cd.entry_id = ct.entry_id
Left join (select old.*
from exp_entry_versioning as old
   left outer join exp_entry_versioning as older on older.entry_id = old.entry_id and older.version_id > old.version_id
where older.version_id is null) ev  On cd.entry_id = ev.entry_id
where cd.channel_id = 1 and ct.status = "open"
';



$result = $mysqli->query($product_sql);

$products = $result->fetch_all(MYSQLI_ASSOC);

$products = parse_versions_data($products, $mysqli);
// echo '<pre>';
// var_dump($products);
// die();

// die();

// $active_products = array_filter($products, function($product) {
//     return $product['post_status'] == 'publish';
// });
// echo '<pre>';
// var_dump($products);
// die();
// $products = array_column($products, 'tags');

// echo '<pre>';
// var_dump($products);
// die();
// // create line with field names
// $attr = product-type, location, shape, material, style

//header info for browser
// header("Content-Type: application/xls");
// header("Content-Disposition: attachment; filename=".$csv_filename."");
// header("Pragma: no-cache");
// header("Expires: 0");
$sep = ",";
$titles =['index', 'sku', 'post_title',
//'post_status', 'lecacy_image', 'images', 'product_gallery', 'featured_image', 'regular_price', 'sale_price', 'category',
//'tag', 'location', 'shape', 'material', 'style', 'color',
'dimensions', 'width', 'height', 'length', 'seat_height', 'additional_dimension',
//'post_content'
];
foreach ($titles as  $title) {
   $schema_insert.= $title.$sep;
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$csv_filename);
ob_end_clean();

$schema_insert = trim($schema_insert);
echo $schema_insert;
print "\n";
// newline (seems to work both on Linux & Windows servers)

// loop through database query and fill export variable
foreach ($products as $product) {

    $schema_insert = "";
    $schema_insert = trim($product['id']).$sep;
    $schema_insert.= trim($product['sku']).$sep;
    $schema_insert.= $product['title'].$sep;
    // $schema_insert.= $product['status'].$sep;
    // $schema_insert.= $product['lecacy_image'].$sep;
    // $schema_insert.= json_encode($product['images']).$sep;
    // $schema_insert.= json_encode($product['product_gallery']).$sep;
    // $schema_insert.= json_encode($product['featured_image']).$sep;
    // $schema_insert.= $product['regular_price'].$sep;
    // $schema_insert.= $product['sale_price'].$sep;
    // $schema_insert.= json_encode($product['category']).$sep;
    // $schema_insert.= json_encode($product['tag']).$sep;
    // $schema_insert.= json_encode($product['location']).$sep;
    // $schema_insert.= json_encode($product['shape']).$sep;
    // $schema_insert.= json_encode($product['material']).$sep;
    // $schema_insert.= json_encode($product['style']).$sep;
    // $schema_insert.= json_encode($product['color']).$sep;

    $schema_insert.= $product['info_dimensions'].$sep;
    $schema_insert.= $product['dimensions']['width'].$sep;
    $schema_insert.= $product['dimensions']['height'].$sep;
    $schema_insert.= $product['dimensions']['length'].$sep;
    $schema_insert.= $product['dimensions']['seat_height'].$sep;
    $schema_insert.= $product['dimensions']['additional_dimension'].$sep;

//   $schema_insert.= json_encode($product['description']).$sep;
    print($schema_insert);
    print "\n";
}

function filter_string($string) {
  return preg_replace("/\r\n|\n\r|\n|\r/", " ", $string);
}
function filter_description($string) {
  if(substr($string, -1) == '"' || substr($string, -1) == '”') {
    $string.='.';
  }
  return $string;
}

function parse_versions_data($products, $mysqli) {
  $results = [];
  $r_tags = [];
  foreach ($products as $value) {
    if (!empty($value["version_data"])) {
      $value['version_data'] = unserialize($value['version_data']);
      $value['description'] = filter_description(rtrim(strip_tags($value["version_data"]['field_id_2'])));
      //$value['description'] = rtrim(strip_tags($value["version_data"]['field_id_2']));
      $value['title'] = $value["version_data"]['title'];
      $value['sku'] = $value["version_data"]['field_id_3'];
      $value['info_dimensions'] = $value["version_data"]['field_id_5'];
      $value['lecacy_image'] = $value["version_data"]['field_id_6'];
      $value['lecacy_product_id'] = $value["version_data"]['field_id_9'];
      $value['tags'] = $value["version_data"]['field_id_7'];
      $value["category"] = $value["version_data"]["category"];
      $value["regular_price"] = $value["version_data"]['store_product_field']["regular_price"];
      $value["sale_price"] = $value["version_data"]['store_product_field']["sale_price"];
    }

    // $value['title'] = json_encode(preg_replace('/"/', '' ,$value['title']));
    $value['title'] = json_encode($value['title']);

    $status = $value['status'];
    $value['status'] = handle_status($value['status']);
    // if ($value['status'] != 'publish') continue;
    $value['featured'] = ($status =='featured') ? 'yes' : 'no';
    $value['dimensions'] = handle_dimensions($value['info_dimensions']);
    //handle_categories($value, $value["category"], $mysqli);
    //handle_images($value, $mysqli);
    //handle_tags_to_custom_taxonomies($value);

    // unset($value["version_data"]);
    $results[] = handle_product_attr($value);
    // $tags = explode('|',$value['tags']);
    // foreach ($tags as $tag) {
    //   $r_tags[] = $tag;
    // }

    // echo '<pre>';
    // var_dump($value);
    // die();
  }

  // $r_tags  = array_unique($r_tags);
  // $r_tags = join(', ',$r_tags);
  // echo '<pre>';
  // var_dump($r_tags);
  // die();
  return $results;
}


function handle_product_attr($product) {
  $attrs =['index', 'sku', 'title','status', 'images', 'product_gallery', 'featured_image', 'lecacy_image', 'regular_price', 'sale_price', 'tag', 'category', 'info_dimensions','dimensions','description', 'location', 'shape', 'material', 'style', 'color'];
  $dimensions = ['width', 'height', 'length', 'depth', 'length', 'seat_height','additional_dimension'];
  foreach ($attrs as $attr) {
    if ($attr == 'dimensions') {
      foreach ($dimensions as $dimension) {
        $product[$attr][$dimension] = $product[$attr][$dimension] ? $product[$attr][$dimension] : '';
      }
    } else {
      $product[$attr] = ($product[$attr]) ? $product[$attr] : '';
    }

  }
  return $product;
}

function handle_status($status) {
  switch ($status) {
    case 'open':
      $result = 'publish';
      break;
    case 'closed':
      $result = 'pending';
      break;
    case 'featured':
      $result = 'publish';
      break;
    default:
      $result = 'pending';
      break;
  }
  return $result;
}

function handle_images(&$product, $mysqli) {
  $entry_id = $product['id'];
  $legacy_image = $product['lecacy_image'];

  if($legacy_image == 'yes') {
    $product_id = $product['lecacy_product_id'];
    //$images_sql = 'SELECT GROUP_CONCAT( DISTINCT CONCAT(PHOTOFILENAME) SEPARATOR "|") as images FROM exp_tablePhotos where productid ='.$product_id;
    $images_sql = 'SELECT PHOTOFILENAME as file_name FROM exp_tablePhotos where productid ='.$product_id. ' order by MAINPHOTO DESC';

  } else {
    //$images_sql = 'SELECT GROUP_CONCAT( DISTINCT CONCAT(ci.filename) SEPARATOR "|") as images FROM  exp_channel_images ci
    //where entry_id = '.$entry_id.' group by ci.entry_id';

    $images_sql = 'SELECT filename FROM  exp_channel_images ci
    where entry_id = '.$entry_id.' order by image_order';
  }

  $result = $mysqli->query($images_sql);

  $images = $result->fetch_all(MYSQLI_ASSOC);
  $product['images'] = $images ? $images : '';
  // echo '<pre>';
  // var_dump($product);
  // die();
  _rename_images($product);
}

function _rename_images(&$product) {
  $images = $product['images'];
  $renamed_images = [];
  $image_tmp =[];
  foreach ($images as $key => $product_image) {
    $image_type = get_file_extension($product_image['file_name']);
    $renamed_images[] = trim($product['sku']).'_'.$key.'.'.$image_type;
    $image_tmp[] = $product_image['file_name'];
  }
  $product['images'] = ($image_tmp) ? join('|',$image_tmp) : '';
  $product['featured_image'] = $renamed_images[0];
  unset($renamed_images[0]);
  $product['product_gallery'] = ($renamed_images) ? join('|',$renamed_images) : '';
  // echo '<pre>';
  // var_dump($product);
  // die();
}

function get_file_extension($file_name) {
  return substr(strrchr($file_name,'.'),1);
}

function handle_categories(&$product, $categories, $mysqli) {
  // $sql = "SELECT GROUP_CONCAT( DISTINCT CONCAT(cat_name) SEPARATOR ', ') as categories FROM exp_categories where cat_id IN". "('".implode("','", $categories)."')";
  $sep ='|';
  $child = '->';
  $sql = "SELECT cat_id, parent_id, cat_name FROM exp_categories_2 where cat_id IN". "('".implode("','", $categories)."')";
  $categories = $mysqli->query($sql);
  $categories = $categories->fetch_all(MYSQLI_ASSOC);
  $results = '';
  // $parent_cats = [];
  foreach ($categories as $cat) {
    if((int)$cat['parent_id'] == 0) {
      if ($cat['cat_name'] != 'Sale') {
        // $parent_cats[] = $cat['cat_name'];
        // $results .= $cat['cat_name'].$sep;
        foreach ($categories as $value) {
          if((int)$value['parent_id'] == (int)$cat['cat_id']) {
            $results .= $cat['cat_name'].$child;
            $results.=$value['cat_name'].$sep;
          }
        }
      } else {
        $product["sale_price"] = $product["regular_price"];
        $product["regular_price"] = get_regular_price_in_description($product['description']);
      }

    }
  }
  $product['category'] = $results;
  // if (preg_match('/antique/', $product['product_type'])) {
  //   $results .= 'Antiques';
  //   foreach ($parent_cats as $cat) {
  //     $results .= '|Antiques'.$child.$cat;
  //   }
  // }
  // return $results;
}


function get_regular_price_in_description($str) {
  //$str = 'ORIGINAL PRICE: $033.33 Account Information: Some text here';
  preg_match('/ORIGINAL PRICE:\s\$(\d+(,+\d+)?(\.\d{1,2})?)/', $str, $matches);
  return toInt($matches[1]);
}
function toInt($str)
{
    return preg_replace("/([^0-9\\.])/i", "", $str);
}

function handle_dimensions($dimensions) {
  $dimensions = preg_split("/x/i", $dimensions);
  //  var_dump($dimensions);
  // die();

  $results = [];
  $results['additional_dimension'] = '';
  foreach ($dimensions as $value) {
    // $value1 = trim($value);
    $value_trim = preg_replace("/\r\n|\n\r|\n|\r| /", "", trim($value));
    $int = (float) filter_var( $value_trim, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
    if (preg_match('/"W/',$value_trim) || preg_match('/”W/',$value_trim)) {
      $results['width'] =  $int;
    } else if (preg_match('/"H/',$value_trim) || preg_match('/”H/',$value_trim)) {
      $results['height'] = $int;
    } else if (preg_match('/"D/',$value_trim) || preg_match('/”D/',$value_trim)) {
      $results['depth'] = $int;
    } else if (preg_match('/"L/',$value_trim) || preg_match('/”L/',$value_trim)) {
      $results['length'] = $int;
    }  else if (preg_match('/"SH/', $value_trim) || preg_match('/”SH/',$value_trim)) {
      $results['seat_height'] = $int;
    } else {
      $results['additional_dimension'] .= ($results['additional_dimension']) ? ' x '.$value : $value;
    }

  }
  if ($results['depth']) {
    if (!isset($results['length']) || empty($results['length'])) {
      $results['length'] = $results['depth'];
    } else if (!isset($results['width']) || empty($results['width'])) {
      $results['width'] = $results['depth'];
    }
  }

  // var_dump($results);
  // die();

  //    else if (preg_match('/"AH/', $value)) {
  //     $results['a_h'] = $int;
  //   } else if (preg_match('/"SD/', $value)) {
  //     $results['s_d'] = $int;
  //   }
  // }
  return $results;
}

$product_types = [];


function handle_tags_to_custom_taxonomies(&$product) {

  $g_locations = ['dallas', 'houston', 'los angeles', 'new york city', 'palm beach', 'pittsburgh', 'southampton',
'east hampton' => 'southhampton', 'new york' => 'new york city'
];


$g_shapes = ['clover', 'kidney', 'octagonal', 'oval', 'rectangle', 'round', 'square',
'oval','scalloped','hexagonal','rectangular','waterfall','triangular','hourglass','spindle',
'sqaure' => 'square',
];

$g_materials = ['acrylic', 'grasscloth raffia', 'leather', 'metal', 'shagreen', 'stone concrete', 'wood', 'zinc', 'teak', 'shell', 'brass', 'mirror', 'cowhide',
'ceramic','glass','stone','concrete','marble','linen','resin','rattan','faux shagreen','rope','lucite','velvet', 'lacquered', 'mirrored', 'nail heads', 'eglomise',
'raffia','vellum','grasscloth','seagrass', 'wicker','parchment','mohair','bamboo','copper', 'elmwood',
'zinc top' => 'zinc', 'upholstered' => 'upholstered', 'uphosltered' => 'upholstered', 'nickel' => 'metal',
'teak' => 'wood', 'oak' => 'wood', 'reclaimed wood' => 'wood', 'mahogany' => 'wood', 'pine' => 'wood', 'walnut' => 'wood', 'burlwood' => 'wood',
'rosewood' => 'wood', 'cherry wood' => 'wood', 'cherry' => 'wood', 'chestnut' => 'wood', 'maple' => 'wood', 'acacia' => 'wood', 'fruitwood' => 'wood',
'poplar' => 'wood', 'carved wood' => 'wood', 'limed oak' => 'wood', 'quartz' => 'stone', 'cast stone' => 'stone', 'cement' => 'stone', 'stone / concrete' => 'stone',
'stone concrete' => 'stone|concrete', 'travertine' => 'stone', 'bluestone' => 'stone', 'granite' => 'stone', 'limestone' => 'stone', 'stone top' => 'stone',
'stainless steel' => 'stainless steel', 'stainless' => 'stainless steel', 'penshell' => 'shell', 'shell mirror' => 'shell', 'nickle' => 'metal',
'navy lacquer' => 'lacquered', 'nail head' => 'nail heads', 'aluminum' => 'metal', 'wrought iron' => 'metal', 'lacquer' => 'lacquered', 'high gloss' => 'lacquered',
'lacqer' => 'lacquered', 'elm' => 'elmwood', 'elm wood' => 'elmwood', 'cearmic' => 'ceramic', 'porcelain' => 'ceramic', 'aged brass'=> 'aged brass', 'acylic' => 'acrylic',
'acylric' => 'acrylic', 'acyrlic' => 'acrylic', 'iron' => 'metal', 'steel' => 'metal', 'cypress' => 'wood', 'dark oak' => 'wood', 'faux bamboo' => 'bamboo',
'hide' => 'cowhide', 'white lacquer' => 'lacquered', 'chrome' => 'metal', 'faux raffia' => 'grasscloth raffia', 'faux shell' => 'shell'
];


$g_styles = ['18th 19th century', 'americana', 'art deco', 'contemporary', 'early 20th century', 'english country', 'european', 'folk art', 'french belgian antiques', 'giacometti', 'italian', 'mid-century', 'primitive rustic', 'reclaimed', 'scandanavian',
'modern', 'asian', 'rustic', 'french', 'beachy', 'faux bois', 'industrial', 'belgian',
'hollywood regency', 'mid century', 'campaign', 'chinoiserie', 'english', 'deco', 'american', 'art nouveau', 'baroque', 'traditional',
'spool'=>'spindle','spanish'=>'spanish','spanish console'=>'spanish','neoclassic'=>'neoclassical','geometric'=>'geometric','fau shagreen'=>'faux shagreen','18th / 19th century'=>'18th 19th Century',
'zebra' => 'animal print'
];

$g_colors = ['bronze', 'silver', 'gold', 'white', 'black', 'brown', 'grey', 'green', 'ivory', 'blue', 'red', 'yellow', 'pink', 'purple', 'orange',
'yellowv'=> 'yellow', 'silver leaf'=> 'silver', 'silverleafed'=> 'silver', 'silver gilt'=> 'silver',
'grey wash'=> 'grey', 'gold leaf'=> 'gold', 'gilt'=> 'gold', 'giled'=> 'gold',
'balck'=> 'black', 'whitewash'=> 'white', 'antique white'=> 'white', 'antique silver'=> 'silver', 'blue and white'=> 'blue|white',
'cream' => 'ivory', 'aqua' => 'blue|green', 'antiqued gold' => 'gold', 'celadon' => 'blue|green', 'turquoise' => 'blue|green', 'navy' => 'blue',
'aged brass' => 'gold', 'brass' => 'gold'
];

$g_tags = ['com' => 'customizable', 'custom'=> 'customizable', 'customizable'=> 'customizable', 'customize'=> 'customizable', 'antique', 'customizable', 'indoor outdoor', 'mecox exclusive'];

$g_tags_categories = ['antique white' => 'Antiques', 'antique silver' => 'Antiques', 'antiqued brass' => 'Antiques', 'antiqued gold' => 'Antiques', 'antiqued' => 'Antiques',
'bedroom' => 'Bedroom', 'chaise' => 'Bedroom->Bedroom Chairs', 'chaise lounge' => 'Bedroom->Bedroom Chairs', 'vanity chair' => 'Bedroom->Bedroom Chairs', 'bedroom chair' => 'Bedroom->Bedroom Chairs', 'daybed'=>'Bedroom->Beds and Headboards', 'bed'=>'Bedroom -> Beds and Headboards', 'headboard'=>'Bedroom -> Beds and Headboards',
'dresser'=>'Bedroom->Dressers and Chests', 'chest'=>'Bedroom->Dressers and Chests',
'night stand'=>'Bedroom -> Nightstands and Bedside Tables', 'nighstand'=>'Bedroom->Nightstands and Bedside Tables', 'bedside tables'=>'Bedroom->Nightstands and Bedside Tables', 'nightstand'=>'Bedroom->Nightstands and Bedside Tables', 'vanity'=>'Bedroom->Nightstands and Bedside Tables',
'art'=>'Decor and Art', 'wall art'=>'Decor and Art',
'accessories'=>'Decor and Art', 'carpet'=>'Decor and Art', 'rug'=>'Decor and Art', 'bookends'=>'Decor and Art',
'basket'=>'Decor and Art->Baskets', 'firescreen'=>'Decor and Art->Fireplace', 'original art' => 'Decor and Art->Original Art',
'photograph'=>'Decor and Art->Reproduction Art', 'print'=>'Decor and Art->Reproduction Art',
'sculptural'=>'Decor and Art->Sculptural Art', 'scul'=>'Decor and Art->Sculptural Art', 'sculpture'=>'Decor and Art->Sculptural Art',
'counter stool'=>'Dining->Bar and Counter Stools', 'bar stool'=>'Dining->Bar and Counter Stools', 'bar'=>'Dining->Bar and Counter Stools', 'bar cart'=>'Dining->Bar Carts',
'sideboard'=>'Dining->Buffets and Sideboards', 'commode'=>'Dining->Buffets and Sideboards', 'credenza'=>'Dining->Buffets and Sideboards', 'buffet'=>'Dining->Buffets and Sideboards', 'cabinet'=>'Dining->Buffets and Sideboards',
'dining chair'=>'Dining->Dining Chairs', 'dining chairs'=>'Dining->Dining Chairs', 'trestle'=>'Dining->Dining Tables', 'trestle base'=>'Dining->Dining Tables', 'farm table'=>'Dining->Dining Tables', 'dining table'=>'Dining->Dining Tables', 'kitchen island'=>'Dining->Dining Tables',
'chandelier' => 'Lighting->Chandeliers',
'christopher sitzmiller' => 'Lighting->Christopher Spitzmiller Lamps', 'christopher spitzmiller' =>'Lighting->Christopher Spitzmiller Lamps',
'floor lamp'=>'Lighting->Floor Lamps', 'floor lamp'=>'Lighting->Floor Lamps', 'lamp'=>'Lighting->Floor Lamps',
'flush mount' => 'Lighting->Flush Mounts',
'hurricane'=>'Lighting->Hurricanes', 'lantern'=>'Lighting->Hurricanes', 'paul schneider lamps'=>'Lighting->Paul Schneider Lamps',
'sconce' =>'Lighting->Sconces', 'sconces' =>'Lighting->Sconces', 'table lamps'=>'Lighting->Table Lamps', 'table lamp'=>'Lighting->Table Lamps',
'living room'=>'Living', 'bench' => 'Living->Benches','etagere'=>'Living->Bookcases','shelf'=>'Living->Bookcases','bookshelf'=>'Living->Bookcases','bookcase'=>'Living->Bookcases',
'swivel chair'=>'Living->Chairs','swivel'=>'Living->Chairs','side chair'=>'Living->Chairs','armchairs'=>'Living->Chairs','slipper chair'=>'Living->Chairs','leather club chair'=>'Living->Chairs','chair'=>'Living->Chairs','club chair'=>'Living->Chairs','wing back chair'=>'Living->Chairs',
'console'=>'Living->Consoles', 'demi lune'=>'Living->Demi Lunes', 'demilunes'=>'Living->Demi Lunes','media cabinet'=>'Living->Entertainment Centers','entertainment center'=> 'Living->Entertainment Centers', 'ottomam'=>'Living->Ottomans','ottoman'=>'Living->Ottomans',
'condo sofa'=>'Living->Sofas and Loveseats','sette'=>'Living->Sofas and Loveseats','love seat'=>'Living->Sofas and Loveseats','sofa'=>'Living->Sofas and Loveseats','loveseat'=>'Living->Sofas and Loveseats','settee'=>'Living->Sofas and Loveseats','sectional'=>'Living->Sofas and Loveseats',
'stoo' =>'Living->Stools','stool'=>'Living->Stools',
'accent table'=>'Living->Tables','side table'=>'Living->Tables',
'nesting'=>'Living->Tables','coffee table'=>'Living->Tables','table'=>'Living->Tables','hall tables'=>'Living->Tables','hall table'=>'Living->Tables','nesting table'=>'Living->Tables','game table'=>'Living->Tables','drinks table'=>'Living->Tables','game tables'=>'Living->Tables',
'coffee tables'=> 'Living->Tables->Coffee Tables', 'nesting tables' => 'Living->Tables->Nesting Tables', 'side tables' => 'Living->Tables->Side Tables',
'end / side tables'=>'Living -> Tables -> Side Tables',
'shell mirror' => 'Mirrors', 'mirror'=>'Mirrors', 'mirrored'=>'Mirrors',
'floor mirror' => 'Mirrors->Floor Mirrors','wall mirror' => 'Mirrors->Wall Mirrors','desk chair'=> 'Office -> Desk Chairs','secretary'=> 'Office -> Desks','desk'=> 'Office -> Desks',
'indoor outdoor'=>'Outdoor','outdoor'=>'Outdoor','fountain'=>'Outdoor->Fountains','garden stool'=>'Outdoor->Garden Stools','lounge chair'=>'Outdoor->Patio Furniture','pottery'=>'Outdoor->Pottery',
];

  $tags = explode(',', $product['tags']);

  $r_locations = [];
  $r_shapes = [];
  $r_materials = [];
  $r_styles = [];
  $r_colors = [];
  $r_tags = [];
  $r_catetories = [];
  foreach ($tags as $tag) {
    $tag = strtolower($tag);

    if (in_array($tag, $g_tags_categories)) {
      $r_catetories[] = $tag;
    } else if (array_key_exists($tag, $g_tags_categories)) {
      $r_catetories[] = $g_tags_categories[$tag];
    }

    if (in_array($tag, $g_tags)) {
      $r_tags[] = $tag;
    } else if (array_key_exists($tag, $g_tags)) {
      $r_tags[] = $g_tags[$tag];
    }

    if (in_array($tag, $g_locations)) {
      $r_locations[] = $tag;
    } else if (array_key_exists($tag, $g_locations)) {
      $r_locations[] = $g_locations[$tag];
    }

    if (in_array($tag, $g_shapes)) {
      $r_shapes[] = $tag;
    } else if (array_key_exists($tag, $g_shapes)) {
      $r_shapes[] = $g_shapes[$tag];
    }

    if (in_array($tag, $g_materials)) {
      $r_materials[] = $tag;
    } else if (array_key_exists($tag, $g_materials)) {
      $r_materials[] = $materials[$tag];
    }

    if (in_array($tag, $g_styles)) {
      $r_styles[] = $tag;
    } else if (array_key_exists($tag, $g_styles)) {
      $r_styles[] = $styles[$tag];
    }

    if (in_array($tag, $g_colors)){
      $r_colors[] = $tag;
    } else if (array_key_exists($tag, $g_colors)) {
      $r_colors[] = $g_colors[$tag];
    }

  }
  $product['color'] = ($r_colors) ? join('|',$r_colors) : '';
  $product['location'] = ($r_locations) ? join('|',$r_locations) : '';
  $product['shape'] = ($r_shapes) ? join('|',$r_shapes) : '';
  $product['material'] = ($r_materials) ? join('|',$r_materials) : '';
  $product['style'] = ($r_styles) ? join('|',$r_styles) : '';
  $product['tag'] = ($r_tags) ? join('|',$r_tags) : '';
  $product['category'] .= ($r_catetories) ? join('|',$r_catetories) : '';
  // $attr = '';
  // if($r_product_types) $attr.='product-type|';
  // if($r_locations) $attr.='location|';
  // if($r_shapes) $attr.='shape|';
  // if($r_materials) $attr.='material|';
  // if($r_styles) $attr.='style|';

  // $attr.='product-type|';
  // $attr.='location|';
  // $attr.='shape|';
  // $attr.='material|';
  // $attr.='style';
  // return [$r_product_types, $r_locations, $r_shapes, $r_materials, $r_styles, $r_tags];
}




// function handle_tags_to_custom_taxonomies($tags) {
//   $tags = explode(',', $tags);
//   $product_types = ['antique', 'customizable', 'indoor outdoor', 'mecox exclusive'];
//   $locations = ['dallas', 'houston', 'los angeles', 'new york', 'palm beach', 'pittsburgh', 'southampton'];
//   $shapes = ['clover', 'kidney', 'octagonal', 'oval', 'rectangle', 'round', 'square'];
//   $materials = ['acrylic', 'grasscloth raffia', 'leather', 'metal', 'shagreen', 'stone concrete', 'wood'];
//   $styles = ['18th 19th Century', 'americana', 'art deco', 'contemporary', 'early 20th century', 'english country', 'european', 'folk art', 'french belgian antiques', 'giacometti', 'italian', 'mid-century', 'primitive rustic', 'reclaimed', 'scandanavian'];

//   $custom_attributes = '';

//   $r_product_types = [];
//   $r_locations = [];
//   $r_shapes = [];
//   $r_materials = [];
//   $r_styles = [];
//   $r_tags = [];
//   foreach ($tags as $tag) {
//     if (in_array($tag, $product_types)) {
//       $r_product_types[] = 'type->'.$tag;
//     } else if (in_array($tag, $locations)) {
//       $r_locations[] = 'location->'.$tag;
//     } else if (in_array($tag, $shapes)) {
//       $r_shapes[] = 'shape->'.$tag;
//     } else if (in_array($tag, $materials)) {
//       $r_materials[] = 'material->'.$tag;
//     } else if (in_array($tag, $styles)) {
//       $r_styles[] = 'style->'.$tag;
//     } else {
//       $r_tags[] = $tag;
//     }
//   }
//   $r_tags = ($r_tags) ? join('|',$r_tags) : '';

//   $custom_attributes .= ($r_product_types) ? 'type|'.join('|',$r_product_types).'|' : '';
//   $custom_attributes .= ($r_locations) ? 'location|'.join('|',$r_locations).'|' : '';
//   $custom_attributes .= ($r_shapes) ? 'shape|'.join('|',$r_shapes).'|' : '';
//   $custom_attributes .= ($r_materials) ? 'material|'.join('|',$r_materials).'|' : '';
//   $custom_attributes .= ($r_styles) ? 'style|'.join('|',$r_styles).'|' : '';

//   return [$custom_attributes, $r_tags];
// }

// $attr = product-type, location, shape, material, style
// function handle_tags_to_custom_taxonomies($tags) {
//   $tags = explode(',', $tags);
//   $product_types = ['antique', 'customizable', 'indoor outdoor', 'mecox exclusive'];
//   $locations = ['dallas', 'houston', 'los angeles', 'new york', 'palm beach', 'pittsburgh', 'southampton'];
//   $shapes = ['clover', 'kidney', 'octagonal', 'oval', 'rectangle', 'round', 'square'];
//   $materials = ['acrylic', 'grasscloth raffia', 'leather', 'metal', 'shagreen', 'stone concrete', 'wood'];
//   $styles = ['18th 19th Century', 'americana', 'art deco', 'contemporary', 'early 20th century', 'english country', 'european', 'folk art', 'french belgian antiques', 'giacometti', 'italian', 'mid-century', 'primitive rustic', 'reclaimed', 'scandanavian'];
//   $r_product_types = [];
//   $r_locations = [];
//   $r_shapes = [];
//   $r_materials = [];
//   $r_styles = [];
//   $r_tags = [];
//   foreach ($tags as $tag) {
//     if (in_array($tag, $product_types)) {
//       $r_product_types[] = preg_replace("/\r\n|\n\r|\n|\r| /", "-", $tag);
//     } else if (in_array($tag, $locations)) {
//       $r_locations[] = preg_replace("/\r\n|\n\r|\n|\r| /", "-", $tag);
//     } else if (in_array($tag, $shapes)) {
//       $r_shapes[] = preg_replace("/\r\n|\n\r|\n|\r| /", "-", $tag);
//     } else if (in_array($tag, $materials)) {
//       $r_materials[] = preg_replace("/\r\n|\n\r|\n|\r| /", "-", $tag);
//     } else if (in_array($tag, $styles)) {
//       $r_styles[] = preg_replace("/\r\n|\n\r|\n|\r| /", "-", $tag);
//     } else {
//       $r_tags[] = preg_replace("/\r\n|\n\r|\n|\r| /", "-", $tag);
//     }
//   }
//   $r_product_types = ($r_product_types) ? join('|',$r_product_types) : '';
//   $r_locations = ($r_locations) ? join('|',$r_locations) : '';
//   $r_shapes = ($r_shapes) ? join('|',$r_shapes) : '';
//   $r_materials = ($r_materials) ? join('|',$r_materials) : '';
//   $r_styles = ($r_styles) ? join('|',$r_styles) : '';
//   $r_tags = ($r_tags) ? join('|',$r_tags) : '';
//   // $attr = '';
//   // if($r_product_types) $attr.='product-type|';
//   // if($r_locations) $attr.='location|';
//   // if($r_shapes) $attr.='shape|';
//   // if($r_materials) $attr.='material|';
//   // if($r_styles) $attr.='style|';

//   // $attr.='product-type|';
//   // $attr.='location|';
//   // $attr.='shape|';
//   // $attr.='material|';
//   // $attr.='style';
//   return [$r_product_types, $r_locations, $r_shapes, $r_materials, $r_styles, $r_tags];
// }


?>

