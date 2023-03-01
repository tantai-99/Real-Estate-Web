<?php
namespace App\Http\Form\TDK\Blog;
use  App\Http\Form\TDK\AbstractTDK;
use Library\Custom\Form;
    class Detail extends AbstractTDK {

        public function init() {

            parent::init();

            $this->removeElement('title');
        }
    }