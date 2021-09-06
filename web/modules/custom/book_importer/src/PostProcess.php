<?php

namespace Drupal\book_importer;

/**
 * Process text after translation.
 */
class PostProcess {

  /**
   * Post process after translation.
   */
  public function processText($translated_text) {
    // Replaced with regex below.
//    $translated_text = str_replace(' / ', '/', $translated_text);
//    $translated_text = str_replace('/ ', '/', $translated_text);
//
//    $translated_text = str_replace('0 -0', '0-0', $translated_text);
//    $translated_text = str_replace('-04 -', '-04-', $translated_text);
//    $translated_text = str_replace('2021 -0', '2021-0', $translated_text);

    //    $translated_text = str_replace(' .png', '.png', $translated_text);
//    $translated_text = str_replace('. png', '.png', $translated_text);
//    $translated_text = str_replace(' .jpg', '.jpg', $translated_text);
//    $translated_text = str_replace('. jpg', '.jpg', $translated_text);

//    $translated_text = str_replace('/content /', '/content/', $translated_text);
//    $translated_text = str_replace('/files /', '/files/', $translated_text);

    // Remove spaces from src attribute.
    $translated_text = preg_replace('!\s+!', ' ', $translated_text);
    $translated_text = preg_replace('/src\s*=\s*"[^" ]*\K\h|(?!^)\G[^" ]*\K\h/i', '', $translated_text);

    $translated_text = str_replace('alt = "', '" alt="', $translated_text);
    $translated_text = str_replace('alt ="', '" alt="', $translated_text);
    $translated_text = str_replace('alt= "', '" alt="', $translated_text);

    $translated_text = str_replace('title = "', ' title="', $translated_text);
    $translated_text = str_replace('title ="', ' title="', $translated_text);
    $translated_text = str_replace('title= "', ' title="', $translated_text);

    $translated_text = str_replace('src = "', ' src="', $translated_text);
    $translated_text = str_replace('src ="', ' src="', $translated_text);
    $translated_text = str_replace('src= "', ' src="', $translated_text);

    $translated_text = str_replace('class = "', ' class="', $translated_text);
    $translated_text = str_replace('class= "', ' class="', $translated_text);
    $translated_text = str_replace('class ="', ' class="', $translated_text);

    $translated_text = str_replace('id = "', ' class="', $translated_text);
    $translated_text = str_replace('id= "', ' class="', $translated_text);
    $translated_text = str_replace('id ="', ' class="', $translated_text);

    $translated_text = str_replace('data-entity-uuid = "', ' data-entity-uuid="', $translated_text);
    $translated_text = str_replace('data-entity-uuid= "', ' data-entity-uuid="', $translated_text);
    $translated_text = str_replace('data-entity-uuid ="', ' data-entity-uuid="', $translated_text);

    return $translated_text;
  }

}
