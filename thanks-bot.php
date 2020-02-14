<?php

//Код обработчика бота ВКонтакте с функцией приветственного сообщения:

if (!isset($_REQUEST)) {
    return;
}

//Строка для подтверждения адреса сервера из настроек Callback API
$confirmation_token = 'ваш_токен';

//Ключ доступа сообщества
$token = 'ваш ключ доступа';

// Secret key
$secretKey = 'ваш секретный ключ';

//Получаем и декодируем уведомление
$data = json_decode(file_get_contents('php://input'));

// проверяем secretKey
if(strcmp($data->secret, $secretKey) !== 0 && strcmp($data->type, 'confirmation') !== 0)
    return;

//Проверяем, что находится в поле "type"
switch ($data->type) {
    //Если это уведомление для подтверждения адреса сервера...
    case 'confirmation':
        //...отправляем строку для подтверждения адреса
        echo $confirmation_token;
        break;

    //Если это уведомление о новом сообщении...
    case 'message_new':
        //...получаем id его автора
        $userId = $data->object->user_id;
        //затем с помощью users.get получаем данные об авторе
        $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&access_token={$token}&v=5.0"));

        //и извлекаем из ответа его имя
        $user_name = $userInfo->response[0]->first_name;

        //С помощью messages.send и токена сообщества отправляем ответное сообщение
        $request_params = array(
            'message' => "{$user_name}, ваше сообщение зарегистрировано!<br>".
                            "Мы постараемся ответить в ближайшее время.",
            'user_id' => $userId,
            'access_token' => $token,
            'v' => '5.0'
        );

        $get_params = http_build_query($request_params);

        file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);

        //Возвращаем "ok" серверу Callback API
        echo('ok');

        break;

    // Если это уведомление о вступлении в группу
    case 'group_join':
        //...получаем id нового участника
        $userId = $data->object->user_id;

        //затем с помощью users.get получаем данные об авторе
        $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&access_token={$token}&v=5.0"));

        //и извлекаем из ответа его имя
        $user_name = $userInfo->response[0]->first_name;

        //С помощью messages.send и токена сообщества отправляем ответное сообщение
        $request_params = array(
            'message' => "Добро пожаловать в наше сообщество, {$user_name}!<br>" .
                            "Если у Вас возникнут вопросы, то вы всегда можете обратиться к администраторам сообщества.<br>" .
                            "Их контакты можно найти в соответсвующем разделе группы.<br>" .
                            "Успехов!",
            'user_id' => $userId,
            'access_token' => $token,
            'v' => '5.0'
        );

        $get_params = http_build_query($request_params);

        file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);

        //Возвращаем "ok" серверу Callback API
        echo('ok');

        break;
}
?>
