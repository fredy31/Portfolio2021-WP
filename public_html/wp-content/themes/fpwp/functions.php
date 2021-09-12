<?php
//https://wp.fredericpilon.com/?rest_route=/wp/v2/posts/2
    $box = tr_meta_box('Contents');
    $box->addPostType('page');
    $box->setCallback(function() {
        $form = tr_form()->useRest();;
        echo $form->wpEditor('intro')->setLabel('Intro');
        echo $form->repeater('abilities')->setLabel('Abiletés')->setFields(
            $form->row(
                $form->text('Nom'),
                $form->select('Niveau')->setOptions([1,2,3,4,5]),
                $form->color('Couleur'),
                $form->image('Logo')
            )
        );
        echo $form->repeater('projects')->setLabel('Projets')->setFields(
            $form->text('Nom'),
            $form->wpEditor('Texte'),
            $form->image('Image')
        );
        echo $form->repeater('experience')->setLabel('Expérience')->setFields(
            $form->text('Nom'),
            $form->text('Années'),
            $form->image('Image')
        );
        echo $form->repeater('formation')->setLabel('Formation')->setFields(
            $form->text('Nom'),
            $form->text('Années'),
            $form->image('Image')
        );
        echo $form->wpEditor('Contact');
    });

    add_action( 'rest_api_init',  'register_custom_fields' );
    function register_custom_fields() {
        register_rest_field('page','trmeta',['get_callback'=>function($object){
            $meta = searchReplaceArray(get_post_meta($object['id']));
            //$meta = get_post_meta($object['id']);
            return $meta;
        }]);
        /*register_rest_field('page','argh',['get_callback'=>function($object){
            return $object['id'];
        }]);*/
    };
    function searchReplaceArray($array){
        $arrOut = [];
        foreach($array as $k=>$a){
            $data = @unserialize($a);
            if(is_array($a)){
                $arrOut[$k] = searchReplaceArray($a);
            }elseif($data !== false){
                $arrOut[$k] = json_encode(searchReplaceArray($data));
            }elseif(!is_nan($a)){
                $arrOut[$k] = $a;
                $img = wp_get_attachment_image_url($a,'large');
                if($img){
                    $arrOut[$k.'_src'] = $img;
                }
            }else{
                $arrOut[$k] = $a;
            }
            //$arrOut[$k] = $a;
            //$arrOut[$k.'_data'] = $data;
        }
        return $arrOut;
    }