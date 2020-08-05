<?php

/*
 *
 *  ____  _             _         _____
 * | __ )| |_   _  __ _(_)_ __   |_   _|__  __ _ _ __ ___
 * |  _ \| | | | |/ _` | | '_ \    | |/ _ \/ _` | '_ ` _ \
 * | |_) | | |_| | (_| | | | | |   | |  __/ (_| | | | | | |
 * |____/|_|\__,_|\__, |_|_| |_|   |_|\___|\__,_|_| |_| |_|
 *                |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  Blugin team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace blugin\support;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\plugin\PluginBase;

class SupportCharacterCreator extends PluginBase implements Listener{
    private $skinData = [];

    public function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @priority HIGHEST
     *
     * @param DataPacketReceiveEvent $event
     */
    public function onDataPacketReceiveEvent(DataPacketReceiveEvent $event) : void{
        $packet = $event->getPacket();
        if($packet instanceof LoginPacket){
            $skinData = JwtParser::getSkinDataFromJwtString($packet->clientDataJwt);
            $uuid = JwtParser::getUUIDFromJwtChain($packet->chainDataJwt);
            if($skinData !== null && $uuid !== null){
                $this->skinData[$uuid->toString()] = $skinData;
            }
        }elseif($packet instanceof PlayerSkinPacket){
            $this->skinData[$event->getOrigin()->getPlayer()->getUniqueId()->toString()] = $packet->skin;
        }
    }

    /**
     * @priority HIGHEST
     *
     * @param DataPacketSendEvent $event
     */
    public function onDataPacketSendEvent(DataPacketSendEvent $event) : void{
        foreach($event->getPackets() as $packet){
            if($packet instanceof PlayerListPacket){
                foreach($packet->entries as $entry){
                    $entry->skinData = $this->skinData[$entry->uuid->toString()] ?? $entry->skinData;
                }
            }elseif($packet instanceof PlayerSkinPacket){
                $packet->skin = $this->skinData[$packet->uuid->toString()] ?? $packet->skin;
            }
        }
    }
}
