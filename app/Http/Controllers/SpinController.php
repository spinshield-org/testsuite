<?php
namespace App\Http\Controllers;
use spinshield\spinclient;

class SpinController
{
    protected $client;
    protected $helpers; 

    function __construct()
    {
    $this->client = new spinclient\ApiClient(array(
        "endpoint" => config("games.api.endpoint"),
        "api_login" => config("games.api.login"),
        "api_password" => config("games.api.password"),
    ));

    $this->helpers = new spinclient\Helpers();
    }
  
    public function gamelist() {
        return $this->client->getGameList("USD", 1);
    }

    public function callback() {

        return [
            "error" => 0,
            "balance" => 1000,
        ];
    }

    public function gameflow($game_id) {
        $createPlayer = $this->client->createPlayer("playerId1337", "playerPassword", "Tiernan", "USD");
        if($this->helpers->responseHasError($createPlayer)) {
        return $createPlayer;
        } else {
        $getGame = $this->client->getGame("playerId1337", "playerPassword", $game_id, "USD", "https://casino.com", "https://casino.com/deposit", 0, "en");
        return $getGame;
        }
    }
}