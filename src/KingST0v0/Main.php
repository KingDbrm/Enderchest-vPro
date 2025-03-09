<?php

/*
 *
 * Copyright (C) 2022 Muqsit Rayyan KingDbrm
 *  _    _   _   _      _   __________
 * | |  / / |_| | \    | | |  ________|
 * | | / /   _  |  \   | | | |
 * | |/ /   | | |   \  | | | |   _____
 * |    /   | | | |\ \ | | | |  |___  |
 * | |\ \   | | | | \ \| | | |      | |
 * | | \ \  | | | |  \   | | |______| |
 * |_|  \_\ |_| |_|   \__| |__________|
 * 
 * Author: KingDbrm
 * Discord: (KingDbrm#8823)
 * github.com/KingDbrm
 * Plugin pocketmine
 * 
 */

 namespace KingST0v0;
 
 use muqsit\invmenu\InvMenu;
 use muqsit\invmenu\InvMenuHandler;
 use muqsit\invmenu\type\InvMenuTypeIds;
 use pocketmine\block\EnderChest;
 use pocketmine\command\Command;
 use pocketmine\command\CommandSender;
 use pocketmine\block\VanillaBlocks;
 use pocketmine\event\Listener;
 use pocketmine\event\player\PlayerInteractEvent;
 use pocketmine\event\player\PlayerQuitEvent;
 use pocketmine\inventory\Inventory;
 use pocketmine\player\Player;
 use pocketmine\plugin\PluginBase;
 use pocketmine\utils\Config;
 use ItemUtils\API;
 use window\WindowViewer;
 
 class Main extends PluginBase implements Listener
 {
     private static Main $instance;
     private Config $playerData;
 
     public function onEnable(): void
     {
         self::$instance = $this;
         if (!InvMenuHandler::isRegistered()) {
             InvMenuHandler::register($this);
         }
         $this->playerData = new Config($this->getDataFolder() . "playerdata.json", Config::JSON);
         $this->getServer()->getPluginManager()->registerEvents($this, $this);
     }
 
     public function onDisable(): void
     {
         $this->playerData->save();
     }
 
     public static function getInstance(): Main
     {
         return self::$instance;
     }
 
     public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
     {
         if ($sender instanceof Player && $command->getName() === "enderchest") {
             $this->sendEnderchest($sender);
         }
         return true;
     }
 
     public function convertPermToSize(Player $player): int
     {
          $size = 27;
          $permissions = [
             "36.echest.slots" => 36, 
             "45.echest.slots" => 45, 
             "54.echest.slots" => 54,  
             "63.echest.slots" => 63
          ];
          foreach ($permissions as $permission => $slots) {
               if ($player->hasPermission($permission)) {
                    $size = $slots;
                    break;
               } else {
                    continue;
               }
          }
          return $size;
     }
 
     public function getEnderPlayerData(Player $player): PlayerData
     {
         return new PlayerData($player, $this->playerData, $this->createInvMenu($player)->getInventory());
     }
 
     public function sendEnderchest(Player $player): void
     {
         $menu = $this->createInvMenu($player);
         $menu->setName("Enderchest de @" . $player->getName());
         $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory): void {
             $this->getEnderPlayerData($player)->saveContents($inventory->getContents(true));
         });
         $inventory = $menu->getInventory();
         $inventory->setContents($this->getEnderPlayerData($player)->getData());
         $menu->send($player);
     }
 
     public function createInvMenu(Player $player): InvMenu
     {
         $size = $this->convertPermToSize($player);
         return $size !== 27 ? WindowViewer::create($size) : InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
     }
 
    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        if (($event->getBlock() instanceof EnderChest) and $event->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            $event->cancel();
            $this->sendEnderchest($player);
        }
    }
 
     public function onQuit(PlayerQuitEvent $event): void
     {
         $player = $event->getPlayer();
         $this->getEnderPlayerData($player)->saveContents($this->getEnderPlayerData($player)->getData());
     }
 }