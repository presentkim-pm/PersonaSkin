<?php

/**
 *
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\personaskin;

use pocketmine\network\mcpe\convert\SkinAdapter;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

    private ?SkinAdapter $originalAdaptor = null;
    private ?PersonaSkinAdapter $adapter = null;

    protected function onEnable() : void{
        /**
         * This is a plugin that does not use data folders.
         * Delete the unnecessary data folder of this plugin for users.
         */
        $dataFolder = $this->getDataFolder();
        if(is_dir($dataFolder) && count(scandir($dataFolder)) <= 2){
            rmdir($dataFolder);
        }

        $typeConverter = TypeConverter::getInstance();

        $this->originalAdaptor = $typeConverter->getSkinAdapter();
        $this->adapter = new PersonaSkinAdapter();
        $typeConverter->setSkinAdapter($this->adapter);
    }

    protected function onDisable() : void{
        $typeConverter = TypeConverter::getInstance();
        //Do not overwrite the adapter if another plugin has replaced ours in the meantime
        if($this->originalAdaptor !== null && $typeConverter->getSkinAdapter() === $this->adapter){
            $typeConverter->setSkinAdapter($this->originalAdaptor);
        }
        $this->originalAdaptor = $this->adapter = null;
    }
}