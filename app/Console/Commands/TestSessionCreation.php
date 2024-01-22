<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SpinController; 
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Http;

class TestSessionCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-session-creation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
        $this->comment("Test Session Creation");

        $games = $this->selectTestGames();
        foreach($games as $game) {
            $this->tries = 0;
            $gameExplode = explode("/", $game[0])[0];
            if(!in_array($gameExplode, config("games.exemptProviders"))) {
                $this->createSession($game[0]);
            }
        }
        } catch(\Exception $e) {
            $this->genericError($e->getMessage());
        }
    }

    public function enterSession($game_id, $url) {
        $response = Http::get($url);
        if(!$response->ok()) {
            $this->enterSessionError($game_id);
        } else {
            $this->comment(".. success - session enter: ".$game_id);
        }
    }

    public function createSession($game_id) {
        try {
        $this->tries = $this->tries + 1;
        $spinController = new SpinController;
        $url = json_decode($spinController->gameflow($game_id), true)['response'];
        $this->comment("... success - session create: ".$game_id);
        $this->enterSession($game_id, $url);

        } catch(\Exception $e) {
            if($this->tries < 3) {
                sleep(1);
                $this->createSession($game_id);
            } else {
                $this->sessionCreateError($game_id);
            }
        }         
    }

    public function sessionCreateError($game_id) {
        $response = Telegram::sendMessage([
            "chat_id" => env("TELEGRAM_GROUP_CHAT"),
            "text" => "[TESTSUITE] Session creation failed for game ".$game_id,
        ]);
        $this->comment("... FAILED: ".$game_id);
    }

    public function enterSessionError($game_id) {
        $response = Telegram::sendMessage([
            "chat_id" => env("TELEGRAM_GROUP_CHAT"),
            "text" => "[TESTSUITE] Session enter failed (but session creation on API was succesful) for ".$game_id,
        ]);
        $this->comment("... enter session error:".$game_id);
    }


    public function genericError($error) {
        $response = Telegram::sendMessage([
            "chat_id" => env("TELEGRAM_GROUP_CHAT"),
            "text" => "[TESTSUITE] Session create test failed completely, possibly because the games API is not reachable at all. Error:".$error,
        ]);
        $this->comment("... GENERIC ERROR");
    }

    public function selectTestGames() 
    {
        try {
        $spinController = new SpinController;
        $gamesList = $spinController->gamelist();
        $gamesList = json_decode($gamesList, true)['response'];
        $gamesList = collect($gamesList);

        $providers = $gamesList->unique("category")->all();

        foreach($providers as $provider) {
            $provider = $provider["category"];
            $count = $gamesList->where("category", $provider)->count();
            $gamePush = $gamesList->where("category", $provider)->random(1)->first()['id_hash'];
            $testGames[] = array($gamePush);
        }

        return $testGames;
        } catch(\Exception $e) {
            $this->genericError($e->getMessage());
        }
    }
}
