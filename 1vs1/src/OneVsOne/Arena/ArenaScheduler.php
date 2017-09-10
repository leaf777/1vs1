<?php

namespace OneVsOne\Arena;

use pocketmine\level\Position;
use pocketmine\scheduler\Task;
use pocketmine\tile\Sign;

/**
 * Class ArenaScheduler
 * @package OneVsOne\Arena
 */
class ArenaScheduler extends Task {

    /** @var  Arena $plugin */
    public $plugin;

    /**
     * ArenaScheduler constructor.
     * @param Arena $plugin
     */
    public function __construct(Arena $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        switch ($this->plugin->phase) {
            case 0:
                $this->updateSigns();
                break;
            // lobby
            case 1:
                $this->updateSigns();
                $this->countdown();
                $this->sendInfo();
                break;
            // full
            case 2:
                $this->updateSigns();
                $this->countdown();
                break;
            case 3:
                $this->updateSigns();
                $this->countdown();
                break;
            case 4:
                $this->updateSigns();
                $this->countdown();
                break;
        }
    }

    function countdown() {
        switch ($this->plugin->phase) {
            case 0:
                break;
            case 1:
                // lobby
                if(count($this->plugin->players) > 1) {
                    $this->plugin->phase = 2;
                }
                break;
            case 2:
                // full
                if(count($this->plugin->players) > 1) {
                    $this->plugin->startTime = $this->plugin->startTime-1;
                }
                break;
            case 3:
                // ingame
                if(count($this->plugin->players) > 1) {
                    $this->plugin->gameTime = $this->plugin->gameTime-1;
                }
                break;
            case 4:
                // restart
                if(count($this->plugin->players) > 1) {
                    $this->plugin->restartTime = $this->plugin->restartTime-1;
                }
                break;
        }
    }

    function sendInfo() {
        foreach ($this->plugin->players as $player) {
            switch ($this->plugin->phase) {
                case 0:
                    // setup
                    break;
                case 1:
                    // lobby
                    $player->setXpLevel($this->plugin->startTime);
                    if(count($this->plugin->players) <= 1) {
                        $player->sendPopup("§7You need more players...");
                    }
                    break;
                case 2:
                    $startTime = intval($this->plugin->startTime);
                    switch ($startTime) {
                        case 30:
                        case 25:
                        case 20:
                        case 15:
                        case 10:
                        case 5:
                        case 3:
                        case 2:
                        case 1:
                            $player->sendMessage("§7Battle starts in {$startTime}");
                            break;
                        case 0:
                            $player->addTitle("§aBattle started!");
                            break;
                    }
                    // full (countdown)
                    break;
            }
        }
    }

    /**
     * @param string $text
     * @return string
     */
    function translateSigns(string $text):string {
        $text = str_replace("%count", count($this->plugin->players), $text);
        $text = str_replace("%phase", $this->getPhase(), $text);
        $text = str_replace("%arena", $this->plugin->name, $text);
        $text = str_replace("&", "§", $text);
        return $text;
    }

    function getPhase() {
        switch ($this->plugin->phase) {
            case 0:
                return "§4Setup";
            case 1:
                return "§aLobby";
            case 2:
                return "§6Full";
            case 3:
                return "§5InGame";
            case 4:
                return "§3Restarting...";
            default:
                return "§aLobby";
        }
    }

    function updateSigns() {
        $signPos = $this->plugin->signpos;
        if($signPos instanceof Position) {
            $level = $signPos->getLevel();
            $tile = $level->getTile($signPos->asVector3());
            if($tile instanceof Sign) {
                $configManager = $this->plugin->plugin->configManager;
                $tile->setText($this->translateSigns($configManager->getConfigData("SignLine-1")),
                    $this->translateSigns($configManager->getConfigData("SignLine-2")),
                    $this->translateSigns($configManager->getConfigData("SignLine-3")),
                    $this->translateSigns($configManager->getConfigData("SignLine-4")));
            }
        }
    }
}