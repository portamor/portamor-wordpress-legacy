<?php 

// Plugin name: Vue plugin
// Description: para hacer vistas dinamicas
// version: 1.0
// Author: Jose Luis Barboza Gonzales
// AUthor URI: https://joseluisb.com


function estilos_plugin(){

  $estilos_url = plugin_dir_url(__FILE__);
  wp_enqueue_style('modo_oscuro', $estilos_url.'/assets/css/style.css', '', '1.1', 'all');

  wp_register_script('wpvue_vuejs', 'https://unpkg.com/vue@3/dist/vue.global.js');
  wp_register_script('my_vuecode', plugin_dir_url( __FILE__ ).'vuecode.js', 'wpvue_vuejs', true );
}

add_action('wp_enqueue_scripts', 'estilos_plugin');

//Add shortscode
function func_wp_vue(){
  wp_enqueue_script('wpvue_vuejs');
  wp_enqueue_script('my_vuecode');
  //Build String
  /* $str= "<div id='divWpVue' v-cloak>"
  ."Vue code here: {{ message }}"
  ."<img src='https://portamor.files.wordpress.com/2021/06/bwa5yio-imgur.jpg'/>"
  ."<div v-for='specialist in specialists'>
      <img :src='specialist.img'/>
      <h1>{{ specialist.name }}</h1>
    </div>
    "
  ."</div>"; */

  $str = "<div id='app'>
  <div class='btns-container'>
    <div class='header-items'>
      <h2 style='font-size: 2rem;'>Servicios especializados</h2>
      <h2 style='font-size: 3rem;'>Profesionales especialistas en el adulto mayor</h2>
      <p style='font-size: 2rem;'>Te atenderemos con toda la experiencia, amor en lo que necesites para seguir fortaleciendo tu bienestar mental, participación social, alimentación saludable y ejercicio físico por un envejecimiento activo.</p>
    </div>
    <div class='btns-group'>
        <button class='btn-actions' @click='filterby(1)'>Bienestar mental 🧘</button>
        <button class='btn-actions' @click='filterby(2)'>Activación fisíca 🤍</button>
        <button class='btn-actions' @click='filterby(3)'>Alimentación saludable 🍇</button>
        <button class='btn-actions' @click='filterby(4)'>Participación social 📱</button>
    </div>
  </div>
  <div class='card-container' >
    <div class='card' v-for='specialist in specialists' :key='specialist'>
      <div class='specialist-img'>
        <img src='https://firebasestorage.googleapis.com/v0/b/mentebonita-prod.appspot.com/o/photos%2Fphoto_1672946321947.png?alt=media&token=b9234a32-eba9-4886-a2ca-f3e64cc8d9ff' alt=''>
      </div>
      <div class='specialist-info'>
        <h2 class='item-full specialist-name'>{{ specialist.name }}</h2>
        <p class='item-full specialist-profession'>{{ specialist.proffession }}</p>
        <div class='item-full specialist-stars'>
          ★★★★★
        </div>
        <div class='item-full'>
          <button class='btn-ask'>Consultar</button>
        </div>  
      </div>
      </div>
    </div>
  </div>";

  //Return
  return $str;
} // end function

//Add shortcode to WordPress
add_shortcode('wpvue', 'func_wp_vue' );