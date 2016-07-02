<?php

/*
 *  @file
 *  Contain \Drupal\mod_field_zoom\Plugin\Field\FieldFormatter;
 */
namespace Drupal\mod_field_zoom\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;

/**
 * Plugin for responsive image formatter.
 *
 * @FieldFormatter(
 *   id = "zoom_formatter",
 *   label = @Translation("Zoom Gallery"),
 *   field_types = {
 *     "image",
 *   }
 * )
 */

class ZoomFieldFormatter extends  ImageFormatterBase implements  ContainerFactoryPluginInterface{


    /**
     * The image style entity storage.
     * @var \Drupal\Core\Entity\EntityStorageInterface
     */
    protected $imageStyleStorage;


    /**
     * The current User
     * @var \Drupal\Core\Session\AccountInterface
     */
    protected $currentUser;


    /**
     * @var \Drupal\Core\Utility\LinkGeneratorInterface
     */
    protected $linkGenerator;


    /**
     * ZoomFieldFormatter constructor.
     * @param string $plugin_id
     * @param mixed $plugin_definition
     * @param FieldDefinitionInterface $field_definition
     * @param array $settings
     * @param string $label
     * @param string $view_mode
     * @param array $third_party_settings
     * @param EntityStorageInterface $image_style_storage
     * @param LinkGeneratorInterface $link_generator
     * @param AccountInterface $current_user
     */
    public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityStorageInterface $image_style_storage, LinkGeneratorInterface $link_generator, AccountInterface $current_user)
    {
        parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

        $this->imageStyleStorage = $image_style_storage;
        $this->linkGenerator = $link_generator;
        $this->currentUser = $current_user;
    }


    /**
     * Creates an instance of the plugin.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *   The container to pull out services used in the plugin.
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin ID for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     *
     * @return static
     *   Returns an instance of this plugin.
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static (
            $plugin_id,
            $plugin_definition,
            $configuration['field_definition'],
            $configuration['settings'],
            $configuration['label'],
            $configuration['view_mode'],
            $configuration['third_party_settings'],
            $container->get('entity.manager')->getStorage('image_style'),
            $container->get('link_generator'),
            $container->get('current_user')
        );
    }

    public static function zoom_settings_info(){

       $settings = array(
           'settings' => array(
               'form_type' => 'fieldset',
               'title' => 'Settings Zoom',
               'description' => '',
               'default_value' => false,
           ),
           'zoomType' => array(
               'form_type' => 'select',
               'title' => 'Zoom type',
               'description' => 'Possible Values: Lens, Window, Inner',
               'options' => array('lens' => 'Lens','window' => 'Window' ,'inner' => 'Inner'),
               'attributes' => array('class' => array('zoomType_sel')),
               'fieldset' => 'settings',
               'default_value'  => 'lens',
           ),

           'cursor' => array(
               'form_type' => 'select',
               'title'  => 'Cursor',
               'description' => 'The default cursor is usually the arrow, if using a lightbox, then set the cursor to pointer so it looks clickable - Options are default, cursor, crosshair',
               'options' => array('cursor' => 'Cursor','crosshair' => 'Crosshair'),
               'fieldset' => 'settings',
               'default_value'  => 'cursor',
               'fieldset' => 'settings',
           ),
           'responsive' => array(
               'form_type' => 'checkbox',
               'title' => t('Responsive'),
               'description' => 'Set to true to activate responsivenes. If you have a theme which changes size, or tablets which change orientation this is needed to be on',
               'default_value' => false,
               'fieldset' => 'settings'
           ),

           'containLensZoom' => array(
               'form_type' => 'checkbox',
               'title' => 'ContainLensZoom',
               'description' => 'For use with the Lens Zoom Type. This makes sure the lens does not fall outside the outside of the image',
               'default_value' => true,
               'fieldset' => 'settings'
           ),
           'easing' => array(
               'form_type' => 'checkbox',
               'title' => 'easing',
               'description' => 'Set to true to activate easing',
               'default_value' => false,
               'fieldset' => 'settings'
           ),
           'easingDuration' => array(
               'form_type' => 'textfield',
               'title' => 'easingDuration',
               'description' => 'Set as a number e.g 200 for speed duration',
               'default_value' => '200',
               'fieldset' => 'settings'
           ),
           'scrollzoom' => array(
               'form_type' =>  'checkbox',
               'title' => 'Scroll Zoom',
               'description' => 'Set to true to activate zoom on mouse scroll.',
               'default_value' => false,
               'fieldset' => 'settings'
           ),
           'borderColour' => array(
               'form_type' => 'textfield',
               'title' => 'Border Colour',
               'description' => 'Border Colour',
               'default_value' => '#888',
               'fieldset' => 'settings'
           ),
           'borderSize' => array(
               'form_type' => 'textfield',
               'title' => 'Border size',
               'description' => 'Border Size of the ZoomBox - Must be set here as border taken into account for plugin calculations',
               'default_value' => '4',
               'fieldset' => 'settings'
           ),
           // Setting for ZoomType Lens
           'lensFadeIn' => array(
               'form_type'  => 'textfield',
               'title' => 'Lens FadeIn',
               'description' => 'Set as a number e.g 200 for speed of Lens fadeIn',
               'default_value' => '500',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'lens'),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           'lensFadeOut' => array(
               'form_type' => 'textfield',
               'title' => 'Lens FadeOut',
               'description' => 'Set as a number e.g 200 for speed of Lens fadeOut',
               'default_value' => '750',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'lens'),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           'lensShape' => array(
               'form_type' => 'select',
               'title' => 'Lens Shape',
               'description' => 'Can also be round (note that only modern browsers support round, will default to square in older browsers)',
               'options' => array('round' => 'Round' , 'square' => 'Square'),
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'lens'),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           'lensSize' => array(
               'form_type' => 'textfield',
               'title' => 'Lens Size',
               'description' => 'used when zoomType set to lens, when zoom type is set to window, then the lens size is auto calculated',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'lens'),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           'lensOpacity' => array(
               'form_type' => 'textfield',
               'title' => 'Lens Opacity',
               'description' => 'used in combination with lensColour to make the lens see through. When using tint, this is overrided to 0',
               'default_value' => '0.4',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'lens'),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           'lensColour' => array(
               'form_type' => 'textfield',
               'title' => 'Lens Colour',
               'description' => 'Colour of the lens background',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'lens'),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           // Settings for Zoomtype : window
           'zoomWindowFadeIn' => array(
               'form_type' => 'textfield',
               'title' => 'zoomWindowFadeIn',
               'description' => 'Set as a number e.g 200 for speed of Window fadeIn',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                   ),
               ),
               'default_value' => '500',
               'fieldset' => 'settings',
           ),
           'zoomWindowFadeOut' => array(
               'form_type' => 'textfield',
               'title' => 'zoomWindowFadeOut',
               'description' => 'Set as a number e.g 200 for speed of Window fadeout',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                   ),
               ),
               'default_value' => '750',
               'fieldset' => 'settings',
           ),
           'zoomWindowWidth' => array(
               'form_type' => 'textfield',
               'title' => 'zoomWindowWidth',
               'description' => 'Width of the zoomWindow (Note: zoomType must be "window")',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                   ),
               ),
               'default_value' => '400',
               'fieldset' => 'settings',
           ),
           'zoomWindowHeight' => array(
               'form_type' => 'textfield',
               'title' => 'zoomWindowHeight',
               'description' => 'Height of the zoomWindow (Note: zoomType must be "window")',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                   ),
               ),
               'default_value' => '400',
               'fieldset' => 'settings',
           ),
           'zoomWindowOffetx' => array(
               'form_type' => 'textfield',
               'title' => 'zoomWindowOffetx',
               'description' => 'x-axis offset of the zoom window',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                   ),
               ),
               'default_value' => 0,
               'fieldset' => 'settings',
           ),
           'zoomWindowOffety' => array(
               'form_type' => 'textfield',
               'title' => 'zoomWindowOffety',
               'description' => 'y-axis offset of the zoom window',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                   ),
               ),
               'default_value' => 0,
               'fieldset' => 'settings',
           ),
           'tint' => array(
               'form_type' => 'checkbox',
               'title' => 'Tint',
               'description' => 'Enable a tint overlay, other options: true',
               'attributes' => array('class' => array('zoomwindow_tint')),
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           'tintOpacity' => array(
               'form_type' => 'textfield',
               'title' =>' Tint Opacity',
               'description' => 'Opacity of the tint',
               'default_value' => '0.4',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                       '.zoomwindow_tint' => array('checked' => true),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           'tintColour' => array(
               'form_type' => 'textfield',
               'title' => 'Tint Colour',
               'description' => 'Colour of the tint, can be hex, word (red, blue), or rgb(x, x, x)',
               'default_value' => '333',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                       '.zoomwindow_tint' => array('checked' => true),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           'zoomTintFadeIn' => array(
               'form_type' => 'textfield',
               'title' => 'zoomTintFadeIn',
               'description' => '	Set as a number e.g 200 for speed of Tint fadeIn',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                       '.zoomwindow_tint' => array('checked' => true),
                   ),
               ),
               'fieldset' => 'settings',
           ),
           'zoomTintFadeOut' => array(
               'form_type' => 'textfield',
               'title' => 'zoomTintFadeOut',
               'description' => '	Set as a number e.g 200 for speed of Tint fadeOut',
               'states' => array(
                   'visible' => array(
                       '.zoomType_sel' => array('value' => 'window'),
                       '.zoomwindow_tint' => array('checked' => true),
                   ),
               ),
               'fieldset' => 'settings',
           ),

       );

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    public static function defaultSettings()
    {   $zoom_settings = self::zoom_settings_info();
       
        $default_settings = array();
        // Return a single depth array with the given key as value.
        foreach ($zoom_settings as $key => $setting) {
            if (isset($setting['fieldset'])) {
                $default_settings[$setting['fieldset']][$key] = isset($setting['default_value']) ? $setting['default_value'] : '';
            }
            else {
                $default_settings[$key] = isset($setting['default_value']) ? $setting['default_value'] : '';
            }
        }

        return array(
            'image_style' => '',
            'zoom_image_style' => '',
            'format_gallery' => '',
        )+ $default_settings; // TODO: Change the autogenerated stub

    }

    /**
     * {@inheritdoc}
     */
    public function settingsForm(array $form, FormStateInterface $form_state)
    {
        $image_options = array();
        $image_styles = $this->imageStyleStorage->loadMultiple();
        if ($image_styles && !empty($image_styles)) {
            foreach ($image_styles as $machine_name => $image_style) {
                $image_options[$machine_name] = $image_style->label();
            }
        }
        $settings = $this->getSettings();
        //\Kint::dump($settings);die();
        $zoom_settings = self::zoom_settings_info();

        $form['image_style'] = array(
            '#title' => t('Image style'),
            '#description' => t('Format style image '),
            '#type' => 'select',
            '#default_value' => $this->getSetting('image_style'),
            '#required' => TRUE,
            '#options' => $image_options,
        );
        $form['zoom_image_style'] = array(
            '#title' => t('Zoom Image style'),
            '#description' => t('Format style image Zoom'),
            '#type' => 'select',
            '#default_value' => $this->getSetting('zoom_image_style'),
            '#required' => TRUE,
            '#options' => $image_options,
        );
        $form['format_gallery'] = array(
            '#title' => t('Gallery style'),
            '#description' => t('Format style gallery'),
            '#type' => 'select',
            '#default_value' => $this->getSetting('format_gallery'),
            '#required' => TRUE,
            '#options' => $image_options,
        );

        // Settings

        foreach($zoom_settings as $key => $form_element){


            if ($form_element['form_type'] == 'fieldset') {
                $form[$key] = array(
                    '#type' => $form_element['form_type'],
                    '#title' => $form_element['title'],
                    '#description' => $form_element['description'],
                    '#collapsible' => TRUE,
                    '#collapsed' => TRUE,
                );
            }
            else{
                $default_value = empty($form_element['fieldset']) ? $settings[$key] : $settings[$form_element['fieldset']][$key];

                //\Kint::dump($attributes);
                $form_settings = array(
                    '#type' => $form_element['form_type'],
                    '#title' => $form_element['title'],
                    '#default_value' => isset($default_value) ? $default_value : '',
                    '#description' => $form_element['description'],
                    '#states' => isset($form_element['states']) ? $form_element['states'] : '',
                    '#attributes' => isset($form_element['attributes']) ? $form_element['attributes'] : '',
                );
                if ($form_element['form_type'] == 'select') {
                    if (isset($form_element['options'])) {
                        $form_settings['#options'] = $form_element['options'];
                    }
                }
                // Add element to fieldset or to main form.
                if (!empty($form_element['fieldset'])) {
                    $form[$form_element['fieldset']][$key] = $form_settings;
                }
                else {
                    $form[$key] = $form_settings;
                }
            }
        }

        return $form;
    }




    /**
     * {@inheritdoc}
     */
    public function settingsSummary()
    {
        return array('The Zoom gallery settings');
    }


    /**
     * Builds a renderable array for a field value.
     *
     * @param \Drupal\Core\Field\FieldItemListInterface $items
     *   The field values to be rendered.
     * @param string $langcode
     *   The language that should be used to render the field.
     *
     * @return array
     *   A renderable array for $items, as an array of child elements keyed by
     *   consecutive numeric indexes starting from 0.
     */
    public function viewElements(FieldItemListInterface $items, $langcode)
    {
        $elements = array();

        $entity  = $items->getEntity();

        $field_instance = $items->getFieldDefinition(); // Return instance field

        $entity_type_id = $entity->getEntityTypeId();

        $entity_id = $entity->id();

        $field_name = $field_instance->getName();

        $display_name = $this->viewMode;

        $files = $this->getEntitiesToView($items, $langcode);

        $settings = $this->getSettings();



        // If the field is empty
        if(empty($files)){
            return $elements;
        }

        $url = NULL;
        $image_link_setting = $this->getSetting('image_link');
        // Check if the formatter involves a link.
        if ($image_link_setting == 'content') {
            $entity = $items->getEntity();
            if (!$entity->isNew()) {
                $url = $entity->urlInfo();
            }
        }
        elseif ($image_link_setting == 'file') {
            $link_file = TRUE;
        }

        $image_style_setting = $this->getSetting('image_style');

        // Collect cache tags to be added for each item in the field.
        $cache_tags = array();
        if (!empty($image_style_setting)) {
            $image_style = $this->imageStyleStorage->load($image_style_setting);
            $cache_tags = $image_style->getCacheTags();
        }

        foreach ($files as $delta => $file) {
            if (isset($link_file)) {
                $image_uri = $file->getFileUri();
                $url = Url::fromUri(file_create_url($image_uri));
            }
            $cache_tags = Cache::mergeTags($cache_tags, $file->getCacheTags());

            // Extract field item attributes for the theme function, and unset them
            // from the $item so that the field template does not re-render them.
            $item = $file->_referringItem;
            $item_attributes = $item->_attributes;
            unset($item->_attributes);

            if($delta == 0) {
                $image_zoom[$delta] = array(
                    '#theme' => 'zoom_formatter',
                    '#item' => $item,
                    '#image_style' => $image_style_setting,
                    '#zoom_image_style' => $this->getSetting('zoom_image_style'),
                );
            }
        }

        $image_zoom = $image_zoom[0];

        $gallery = array(
            '#theme' => 'gallery_formatter',
            '#items' => $items,
            '#image_style'   => $this->getSetting('image_style'),
            '#gallery_style' => $this->getSetting('format_gallery'),
            '#zoom_image_style' => $this->getSetting('zoom_image_style'),
            '#attributes' => array(
                'class' => array('list-img-zoom'),
                'id' => array('zoom-gallery'),
            ),
        );

        $elements['image_zoom'] = $image_zoom;
        $elements['gallery']    = $gallery;

        $container = array(
            '#theme' => 'zoom_wrapper',
            '#children'=> $elements,
            '#attributes' => array(
                'class' => array('zoom-container'),
            ),
        );

        // Attach Library
        $container['#attached']['library'][] = 'mod_field_zoom/jquery.elevatezoom';
        $container['#attached']['library'][] = 'mod_field_zoom/jquery.fancybox';
        
        // Attach settings.

        $container['#attached']['drupalSettings']['views']['ZoomViews'] = $this->getSetting('settings'); ;
        return $container;
    }


}