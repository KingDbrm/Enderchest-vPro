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
 * PocketMine-MP plugin, I made this plugin for 
 * Rankup Sky from Classic Network let's make a server
 * innovative, in case this plugin is with you,
 * Rankup Sky HAS BEEN FINISHED!
 * 
 * Author: KingDbrm
 * Discord: (KingDbrm#8823)
 * github.com/Ki
 * Plugin pocketmine
 * 
 */
 
 namespace KingDbrm;

 use pocketmine\inventory\Inventory;
 use pocketmine\player\Player;
 use pocketmine\utils\Config;
 
 class PlayerData
 {
     private Config $config;
     private Player $player;
     private Inventory $inventory;
 
     public function __construct(Player $player, Config $config, Inventory $inventory){
         $this->player = $player;
         $this->config = $config;
         $this->inventory = $inventory;
     }
 
     public function getEnderInventory(): Inventory
     {
         return $this->inventory;
     }
 
     public function getData(): array
     {
         $playerName = $this->player->getName();
         $data = $this->config->getAll();
         
         if (isset($data[$playerName])) {
             return API::unserializeAll($data[$playerName]);
         }
         
         return [];
     }
 
     public function saveContents(array $contents): void
     {
         $playerName = $this->player->getName();
         $serializedItems = API::serializeAll($contents);
         
         $this->config->set($playerName, $serializedItems);
         $this->config->save();
     }
 }