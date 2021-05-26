<?php

    $box = tr_meta_box('Contents');
    $box->addPostType('page');
    $box->setCallback(function() {
        $form = tr_form();
        echo $form->wpEditor('intro')->setLabel('Intro');
        echo $form->repeater('abilities')->setLabel('Abiletés')->setFields(
            $form->row(
                $form->text('Nom'),
                $form->select('Niveau')->setOptions([1,2,3,4]),
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