<?php
include "vk_api.php"; //Подключаем нашу волшебную библиотеку для работы с api vk

//**********CONFIG**************
$vk_key = ""; //тот самый длинный ключ доступа сообщества
$access_key = ""; //например c40b9576, введите свой
$uploaddir = __DIR__ . "/upload/"; //Путь к каталогу с картинками
//******************************

$vk_api = new vk_api($vk_key); //Ключ сообщества VK

$data = json_decode(file_get_contents('php://input')); //Получает и декодирует JSON пришедший из ВК

if (isset($data->type) and $data->type == 'confirmation') { //Если vk запрашивает ключ
	echo $access_key; //Отправляем ключ
	exit(0); //Завершаем скрипт
}

echo 'ok'; //Говорим vk, что мы приняли callback

if (isset($data->type) and $data->type == 'message_new') { //Проверяем, если это сообщение от пользователя

  $id = $data->object->user_id; //Получаем id пользователя, который написал сообщение

  $send = 0; //Флаг 0

  $message = $data->object->body; //Получаем тест сообщение пользователя(в этом скрипте не используется, но вам может понадобится)
  if (!isset($data->object->payload)){ //Если кнопка не нажата
  
    $button1_1 = [["start" => 'menu'], "Меню", "red"]; //Генерируем кнопку 'Меню'

    $vk_api->sendButton($id, 'Для вызова меню, нажми на соответствующую кнопку ниже', 
	[ //Отправляем кнопки пользователю
		[
			$button1_1
			/*, 
			$button1_2*/
		]
    ]);
	
  } else {
  
    $payload = json_decode($data->object->payload, True); //Получаем её payload
	
  //  $button2_1 = [null, "<< Назад", "red"]; // Код кнопки "<< Back"

    switch ($payload['start']) { //Смотрим что в payload кнопках
      case 'menu': //Если это Меню
        $button1_1 = [["start" => 'how_to'], "Как?", "red"];
        $button1_2 = [["start" => 'help'], "Помощь", "red"];
        $button1_3 = [["start" => 'goto'], "Что то еще", "red"];
        $send = 1; //Флаг 1
        break;
		
		
      case 'how_to':
        $vk_api->sendMessage($id, "
			Тут какое то сообщение, которое отобразим пользователю, когда он нажмет на кнопку 'how_to'
		"); //отправляем сообщение
        //$vk_api->sendImage($id, $uploaddir."logo.png", "logo.png"); //отправляем картинку
        break;
		
		
      case 'help': 
        $vk_api->sendMessage($id, "
			Тут какое то сообщение, которое отобразим пользователю, когда он нажмет на кнопку 'help'
		");
        break;
		
		
		
      case 'goto': //Если это goto_joke
        $vk_api->sendMessage($id, "
			Тут какое то сообщение, которое отобразим пользователю, когда он нажмет на кнопку 'goto'
		");
        break;

      default:
        break;
    }

    if ($send) { //Если флаг = 1, отправить сформированные кнопки
      $vk_api->sendButton($id, 'Выбери пункт меню', [ //Отправляем кнопки пользователю
        [$button1_1, $button1_2], [$button1_3]
       /* [$button2_1]*/
      ]);
    }
  }
}

// Тут метод, который отправляет сообщение пользователю, вступившему в группу (ЕЩЕ НЕ ПРОТЕСТИРОВАН)
if (isset($data->type) and $data->type == 'group_join') {
    
    $userId = $data->object->user_id;
    $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&fields=first_name&access_token={$vk_key}&v=5.78"));
    // Вытаскиваем имя отправителя
    $user_name = $userInfo->response[0]->first_name;
    // Через messages.send используя токен сообщества отправляем ответ
        $request_params = array(
        'message' => "Мы рады приветствовать Вас, {$user_name}!",
        'user_id' => $userId,
        'access_token' => $token,
        'v' => '5.78'
        );
    $get_params = http_build_query($request_params);
    file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
    echo('ok'); // Отправляем "ok" серверу Callback API
    
}

?>
