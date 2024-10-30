<?php
 
/*
Plugin Name: List Video Youtube
Plugin URI: http://www.josejavierfm.es/gsllv
Description: Widget con la lista de videos de un canal de youtube
Version: 1.0
Author: José Javier Fernández Mendoza
Author URI: http://www.josejavierfm.es/
*/
 
/**
 * Función que instancia el Widget
 */
function gsliv_create_widget(){    
    include_once(plugin_dir_path( __FILE__ ).'/gsliv-widget.php');
    register_widget('gsliv_widget');
}
add_action('widgets_init','gsliv_create_widget'); 
 

add_action('init', 'gsliv_language'); 

function gsliv_language() {

        load_plugin_textdomain( 'messages', false, dirname(plugin_basename(__FILE__)).'/languages/' );

}

 
class gsliv_widget extends WP_Widget {
 
    function gsliv_widget(){
        // Constructor del Widget.
         $widget_ops = array('classname' => 'gsliv_widget', 'description' => "List with lastest videos of Youtube channel" );
        $this->WP_Widget('gsliv_widget', "List Video Youtube", $widget_ops);
    }
	function getXML($channel_id){
		
		$url=sprintf('https://www.youtube.com/feeds/videos.xml?channel_id=%s', $channel_id);
		$xml="";
		$xml = @simplexml_load_file($url);
		if ($xml==""){
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($curl);
			$xml = @simplexml_load_string($data);
		}
		
		return $xml;
	}
    function widget($args,$instance){
        // Contenido del Widget que se mostrará en la Sidebar
		$error=false;
        $id = NULL;
		$title=NULL;
		$xml="";
        $channel_id = $instance["gsliv_idcanal"];
        $limite = $instance["gsliv_limite"];
        if ($channel_id!=""){
			$xml=$this->getXML($channel_id);
			if ($xml==""){
				$error=true;
			}
			///echo count($xml->entry);
            if ($xml!="" && !$error && $xml->entry[0] && !empty($xml->entry[0]->children('yt', true)->videoId[0])){
                $id = $xml->entry[0]->children('yt', true)->videoId[0];
				$title= (string)$xml->entry[0]->children()->title[0];
            }
        }
       
         echo $before_widget;    
        ?>
        <aside id='gsliv_widget' class='widget mpw_widget'>
            <h3 class='widget-title'><?=__("GSLLV_ultimo_video_titulo", "messages")?></h3>
            <? 
			if (!$error){
				if ($channel_id!=""){
					if (count($xml->entry)>0){
						$contador=0;
						
						echo "<ul>";
						foreach($xml->entry as $yutube){
							if ($contador<$limite && $xml->entry[$contador] && !empty($xml->entry[$contador]->children('yt', true)->videoId[0])){
								$id = $xml->entry[$contador]->children('yt', true)->videoId[0];
								$title= (string)$xml->entry[$contador]->children()->title[0];
								echo '<li><a href="http://www.youtube.com/watch?v='.$id.'" target="_blank">'.$title.'</a></li>';
							}
							$contador++;
						}
						echo "</ul>";
						
						
					}else{
					?>
						<p><?=__("GSLLV_no_video", "messages")?></p>
					<?}
				}else{
				?>
					<p><?=__("GSLLV_canal_sin_configurar", "messages")?></p>
				<?}
			}else{?>
				<p><?=__("GSLLV_sin_permiso_php", "messages")?></p>
			<?}?>
             
        </aside>
        <?php
        echo $after_widget;
    }
 
    function update($new_instance, $old_instance){
        // Función de guardado de opciones   
         $instance = $old_instance;
        $instance["gsliv_idcanal"] = strip_tags($new_instance["gsliv_idcanal"]);
        $instance["gsliv_limite"] = strip_tags($new_instance["gsliv_limite"]);
        // Repetimos esto para tantos campos como tengamos en el formulario.
        return $instance;
    }
 
    function form($instance){
        // Formulario de opciones del Widget, que aparece cuando añadimos el Widget a una Sidebar
         ?>
         <p>
            <label for="<?php echo $this->get_field_id('gsliv_idcanal'); ?>"><?=__("GSLLV_id_del_canal", "messages")?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('gsliv_idcanal'); ?>" name="<?php echo $this->get_field_name('gsliv_idcanal'); ?>" type="text" value="<?php echo esc_attr($instance["gsliv_idcanal"]); ?>" />
         </p> 
		  <p>
            <label for="<?php echo $this->get_field_id('gsliv_limite'); ?>"><?=__("GSLLV_limite_videos", "messages")?></label>
			<select id="<?php echo $this->get_field_id('gsliv_limite'); ?>" name="<?php echo $this->get_field_name('gsliv_limite'); ?>" class="widefat" style="width:100%;"> 
				<option <?php selected( $instance['gsliv_limite'], '1'); ?> value="1">1</option>
				<option <?php selected( $instance['gsliv_limite'], '2'); ?> value="2">2</option>
				<option <?php selected( $instance['gsliv_limite'], '3'); ?> value="3">3</option>
				<option <?php selected( $instance['gsliv_limite'], '4'); ?> value="4">4</option>
				<option <?php selected( $instance['gsliv_limite'], '5'); ?> value="5">5</option>
				<option <?php selected( $instance['gsliv_limite'], '10'); ?> value="10">10</option>
			</select>
           
         </p>  
         <?php
    }    
} 
 

?>