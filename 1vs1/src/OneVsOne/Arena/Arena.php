<?php

namespace OneVsOne\Arena;

use OneVsOne\Main;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;

/**
 * Class Arena
 * @package OneVsOne\Arena
 */
class Arena {

    /** @var Main $plugin */
    public $plugin;

    /** @var  ArenaListener $arenaListener */
    public $arenaListener;

    /** @var  ArenaScheduler $arenaScheduler */
    public $arenaScheduler;

    /** @var  string $name */
    public $name;

    /** @var  Position|null $signpos */
    public $signpos;

    /**
     * @var $pos1 Position
     * @var $pos2 Position
     */
    public $pos1, $pos2;

    /** @var  Player[] $players */
    public $players = [];

    /**
     * @var int $phase
     *
     * 0 => setup
     * 1 => lobby
     * 2 => (full)
     * 3 => ingame
     * 4 => (restart)
     */
    public $phase = 1;

    /** @var  int $time */
    public $startTime = 31, $gameTime = 301, $restartTime = 16;

    /**
     * Arena constructor.
     * @param Main $plugin
     * @param string $name
     * @param Position $pos1
     * @param Position $pos2
     * @param int $phase
     */
    public function __construct(Main $plugin, string $name, Position $pos1, Position $pos2, int $phase = 0) {
        $this->phase = $phase;
        $this->plugin = $plugin;
        $this->name = $name;
        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
        $this->players = [];
        $this->plugin->getServer()->getPluginManager()->registerEvents($this->arenaListener = new ArenaListener($this), $this->plugin);
        $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($this->arenaScheduler = new ArenaScheduler($this), 20);
    }

    /**
     * @param Player $player
     */
    public function teleportToArena(Player $player) {
        switch ($this->phase) {
            case 0:
                $player->sendMessage("§cArena is in setup mode!");
                return;
            case 2:
                $player->sendMessage("§cArena is full");
                return;
            case 3:
                $player->sendMessage("§cArena is InGame");
                return;
            case 4:
                $player->sendMessage("§cArena restarting...");
                return;
        }
        $this->players[strtolower($player->getName())] = $player;
        $count = count($this->players);
        if($count == 1) {
            $player->teleport($this->pos2);
        }
        else {
            $player->teleport($this->pos1);
        }
        foreach ($this->players as $players) {
            $players->sendMessage("§7[{$count}/2] §aPlayer {$player->getName()} joined.");
        }
        $player->setGamemode($player::ADVENTURE);
        $player->setHealth(20);
        $player->setMaxHealth(20);
        $player->removeAllEffects();
        $player->setFood(20);
        $player->getInventory()->clearAll();
        $inv = $player->getInventory();
        $inv->setHelmet(Item::get(Item::IRON_HELMET));
        $inv->setChestplate(Item::get(Item::IRON_CHESTPLATE));
        $inv->setLeggings(Item::get(Item::IRON_LEGGINGS));
        $inv->setBoots(Item::get(Item::IRON_BOOTS));
        $inv->setItem(0, Item::get(Item::STONE_SWORD));
    }
}