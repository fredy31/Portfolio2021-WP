<?php
function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
}
add_action('init','add_cors_http_header');
//https://wp.fredericpilon.com/?rest_route=/wp/v2/posts/2
    $box = tr_meta_box('Contents');
    $box->addPostType('page');
    $box->setCallback(function() {
        $form = tr_form()->useRest();;
        echo $form->wpEditor('intro')->setLabel('Intro');
        echo $form->image('intro_img')->setLabel('Image Intro');
        echo $form->image('intro_img_light')->setLabel('Image Intro (Light)');
        echo $form->text('Titre Abiletés');
        echo $form->repeater('abilities')->setLabel('Abiletés')->setFields(
            $form->row(
                $form->text('Nom'),
                $form->textarea('Description'),
                $form->select('Niveau')->setOptions([1,2,3,4,5]),
                $form->color('Couleur'),
                $form->image('Logo')
            )
        );
        echo $form->text('Titre Projets');
        echo $form->repeater('projects')->setLabel('Projets')->setFields(
            $form->text('Nom'),
            $form->wpEditor('Texte'),
            $form->image('Image')
        );
        echo $form->text('Titre Logiciels');
        echo $form->wpEditor('Intro Logiciels');
        echo $form->repeater('logiciels')->setLabel('Logiciels')->setFields(
            $form->text('categorie')->setlabel('Catégorie'),
            $form->row(
                $form->text('prefere')->setLabel('Préféré'),
                $form->image('prefere_logo')->setLabel('Préféré Logo')
            ),
            $form->repeater('Autres')->setFields(
                $form->row(
                    $form->text('autre_text')->setLabel('Autre'),
                    $form->image('autre_logo')->setLabel('Autre Logo')
                )
            )
        );
        echo $form->text('Titre Expérience');
        echo $form->repeater('experience')->setLabel('Expérience')->setFields(
            $form->text('Nom'),
            $form->text('Années'),
            $form->image('Image'),
            $form->color('Couleur')
        );
        echo $form->text('Titre Formation');
        echo $form->repeater('formation')->setLabel('Formation')->setFields(
            $form->text('Nom'),
            $form->text('Années'),
            $form->image('Image'),
            $form->color('Couleur')
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
            if(is_string($a)){
                $data = @unserialize($a);
            }
            if(is_array($a)){
                $arrOut[$k] = searchReplaceArray($a);
            }elseif($data !== false){
                $arrOut[$k] = json_encode(searchReplaceArray($data));
            }elseif(is_numeric($a)){
                $arrOut[$k] = $a;
                $img = wp_get_attachment_image_url($a,'large');
                if($img){
                    $arrOut[$k.'_src'] = $img;
                    $webp = check_for_webp($img);
                    if($webp){
                        $arrOut[$k.'_src_webp'] = $webp;
                    }
                }
            }else{
                $arrOut[$k] = $a;
            }
            //$arrOut[$k] = $a;
            //$arrOut[$k.'_data'] = $data;
        }
        return $arrOut;
    }
    function check_for_webp($img){
        $transientname = 'img_'.str_replace(array('/','.',':'),'_',$img);
        $transient = get_transient($transientname);
        if(!$transient){
            $imgwebp = '';
            $pathinfo = pathinfo($img);
            $dirname = str_replace(array(site_url(),'https:','http:'),'',$pathinfo['dirname']);
            if(file_exists(ABSPATH.$dirname.'/'.$pathinfo['filename'].'.webp')){
                $imgwebp = $dirname.'/'.$pathinfo['filename'].'.webp';
            }
            //Check pour ceux uploadés, oû litespeed convertit fichier.png > fichier.png.webp
            if(file_exists(ABSPATH.$dirname.'/'.$pathinfo['filename'].'.'.$pathinfo['extension'].'.webp')){
                $imgwebp = $dirname.'/'.$pathinfo['filename'].'.'.$pathinfo['extension'].'.webp';
            }
            //set_transient($transientname.'_checked',ABSPATH.$dirname.'/'.$pathinfo['filename'].'.webp');
            //set_transient($transientname.'_checked2',ABSPATH.$dirname.'/'.$pathinfo['filename'].'.'.$pathinfo['extension'].'.webp');
    
            $dirname = str_replace('https:','',str_replace(WP_CONTENT_URL,'wp-content/',$pathinfo['dirname']));
            if(file_exists(ABSPATH.$dirname.'/'.$pathinfo['filename'].'.webp')){
                $imgwebp = $dirname.'/'.$pathinfo['filename'].'.webp';
            }
            //Check pour ceux uploadés, oû litespeed convertit fichier.png > fichier.png.webp
            if(file_exists(ABSPATH.$dirname.'/'.$pathinfo['filename'].'.'.$pathinfo['extension'].'.webp')){
                $imgwebp = $dirname.'/'.$pathinfo['filename'].'.'.$pathinfo['extension'].'.webp';
            }
            //set_transient('WP_CONTENT_URL',WP_CONTENT_URL);
            //set_transient('bloginfo_url',get_bloginfo('url'));
            //set_transient($transientname.'_checked3',ABSPATH.$dirname.'/'.$pathinfo['filename'].'.webp');
            //set_transient($transientname.'_checked4',ABSPATH.$dirname.'/'.$pathinfo['filename'].'.'.$pathinfo['extension'].'.webp');
            //set_transient($transientname.'_url',);
            //if(WP_CONTENT_URL != get_bloginfo('url')){
            $imgwebp = str_replace('wp-content/',WP_CONTENT_URL,$imgwebp);
            //}
            if(substr( $imgwebp, 0, 2 ) === "//"){
                $imgwebp = 'https:'.$imgwebp;
            }
            if($imgwebp != ''){
                set_transient($transientname,$imgwebp,0);
            }else{
                set_transient($transientname,$imgwebp,60*60*24);
            }
            $transient = $imgwebp;
        }
        if($transient){
            return $transient;
        }else{
            return $img;
        }
    }