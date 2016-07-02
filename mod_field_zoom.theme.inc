<?php
use Drupal\Component\Utility;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Url;
use Drupal\Core\GeneratedLink;
use Drupal\Core\Render;
use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;
use Drupal\image\Entity\ImageStyle;


define('FULL_URL',0);
define('ABSOLUTE_FILE_PATH',1);
define('Relative_FILE_PATH',2);


function template_preprocess_zoom_formatter(&$variables){
    //var_dump($variables);//die();
    $item = $variables['item'];
    $entity = $item->entity;

    $image_zoom = null;
    $url_zoom_large = ImageStyle::load($variables['zoom_image_style'])->buildUrl($item->entity->getFileUri());
    if ($variables['image_style']) {
        $variables['image'] = array(
            '#theme' => 'image_style',
            '#style_name' => $variables['image_style'],
        );
    }

    else {
        $variables['image'] = array(
            '#theme' => 'image',
        );
    }

    $variables['image']['#attributes']['id'] = Utility\Html::getId('zoom-formatter');

    // Do not output an empty 'title' attribute.
    if (Unicode::strlen($item->title) != 0) {
        $variables['image']['#title'] = $item->title;
    }

    if (empty($item->uri)) {
        $variables['image']['#uri'] = $entity->getFileUri();
        $variables['image']['#attributes']['data-zoom-image'] = $url_zoom_large;

    }
    else {
        $variables['image']['#uri'] = $item->uri;
        $variables['image']['#attributes']['data-zoom-image'] = $url_zoom_large;
    }

    foreach (array('width', 'height', 'alt') as $key) {
        $variables['image']["#$key"] = $item->$key;
    }

}

function template_preprocess_gallery_formatter(&$variables){

    $gallery = array();
    $items = $variables['items'];
        foreach($items as $key => $item){
            $entity = $item->entity;

            $image_style_url =  array(
                '#theme' => 'image_url_formatter',
                '#item'  => $item,
                '#image_style'=> 'product_zoom',
                '#url' => $item->uri,
                '#url_type' => FULL_URL,
            );

            $zoom_style_url =  array(
                '#theme' => 'image_url_formatter',
                '#item'  => $item,
                '#image_style'=> $variables['zoom_image_style'],
                '#url' => $item->uri,
                '#url_type' => FULL_URL,
            );

            $gallery[]= \Drupal::l(array(
                '#theme' => 'image_formatter',
                '#item'  => $item,
                '#image_style' => $variables['image_style'],
            ), Url::fromUserInput('#',array('attributes' => array('class' => array('item-link'),'data-image' => drupal_render($image_style_url),'data-zoom-image' => drupal_render($zoom_style_url)))));

        }
    $variables['attributes']['id'] = Utility\Html::getId('gallery-zoom');
    $variables['attributes']['class'][] = 'gallery-zoom';
    $variables['gallery'] = $gallery;
}

function template_preprocess_zoom_wrapper(&$variables){

    $element = $variables['element'];
    $element += array('#attributes' => array());
    $variables['image_zoom'] = $element['#children']['image_zoom'];
    $variables['gallery']    = $element['#children']['gallery'];
    $variables['attributes'] = $element['#attributes'];
}
