<?php

function callback_callback()
{
  $response = array();
  $response['errors'] = array();

  $fields = array(
      'required' => array(
          'tel' => $_POST['tel']
      ),
      'optional' => array(
          'name' => $_POST['name'],
          'message' => $_POST['message']
      )
  );
  
  $secret = "6LeyfCgUAAAAAFjA82CpiN9QTTsVWvMM0zkeZLGN";

//  $reCaptcha = new ReCaptcha($secret);
//  
//  $resp = null;
//  
//  if ($_POST["g-recaptcha-response"]) {
//    $resp = $reCaptcha->verifyResponse(
//        $_SERVER["REMOTE_ADDR"],
//        $_POST["g-recaptcha-response"]
//    );
//  }
//
//  if (!($resp != null && $resp->success)) {
//    return;
//  }
  
  
  $response['errors'] = Validator::validate_fields($fields, $response['errors']);
  
  if (count($response['errors'])) {
    wp_send_json_error($response);
    return;
  }

  acf_set_language_to_default();
  $emails = get_field('email', 'option');
  acf_unset_language_to_default();

  $subject = 'Вам сообщение с сайта ' . get_bloginfo('name');
  $message = 'Имя: ' . $_POST['name'] . "\r\n";
  $message .= 'Почта: ' . $_POST['email'] . "\r\n";
  $message .= 'Телефон: ' . $_POST['tel'] . "\r\n";

  $headers = "MIME-Version: 1.0\r\n" . "Content-type: text/plain; charset=utf-8";

  $sendSuccess = wp_mail($emails, $subject, $message, $headers);

  if (!$sendSuccess) {
    wp_send_json_error("WP_mail not send");
  }

  wp_send_json_success($response);

  // выход нужен для того, чтобы в ответе не было ничего лишнего, только то что возвращает функция
  wp_die();
}

add_action('wp_ajax_callback', 'callback_callback');
add_action('wp_ajax_nopriv_callback', 'callback_callback');
