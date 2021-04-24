<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    static public function getUpdates()
    {
        $offset = 0;

        $tg_token = env('TG_TOKEN');
        while (true) {

            $request = Http::get("https://api.telegram.org/bot" . $tg_token . "/getUpdates?offset=" . $offset + 1);

            $response = $request->json();

            foreach ($response['result'] as $message) {
                $offset = $message['update_id'];

                settype($message['message']['text'], 'int');


                if ($message['message']['text'] == 0) {
                    $data = [
                        'chat_id' => $message['message']['chat']['id'],
                        'text' => 'Вы ввели неправильные данные.'
                    ];
                } else {
                    if ($order = \App\Models\Order::find($message['message']['text'])) {
                        if ($order->is_completed === 1) {
                            $isCompleteText = 'выполнен.';
                        } else {
                            $isCompleteText = 'в работе.';
                        }

                        $data = [
                            'chat_id' => $message['message']['chat']['id'],
                            'text' => 'Статус вашего заказа "' . ucfirst($order->title) . '" - ' . $isCompleteText
                        ];
                    } else {
                        $data = [
                            'chat_id' => $message['message']['chat']['id'],
                            'text' => 'Заказ не был найден.'
                        ];
                    }
                }


                Http::get("https://api.telegram.org/bot" . $tg_token . "/sendMessage?" . http_build_query($data));
            }
            sleep(1);
        }


    }


    static public function listOrders()
    {
        $orders = \App\Models\Order::all();

        print 'id' . "\t" . "|  " . 'title' . PHP_EOL;
        print "---------------------------" . PHP_EOL;
        foreach ($orders as $order) {
            print $order->id . "\t" . "|  " . $order->title . PHP_EOL;
        }
        print "---------------------------" . PHP_EOL;
    }


}
