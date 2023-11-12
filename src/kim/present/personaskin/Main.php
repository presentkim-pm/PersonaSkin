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

use kim\present\traits\removeplugindatadir\RemovePluginDataDirTrait;
use pocketmine\network\mcpe\convert\SkinAdapter;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{
    use RemovePluginDataDirTrait;

    private ?SkinAdapter $originalAdaptor = null;

    protected function onEnable() : void{
        $this->originalAdaptor = TypeConverter::getInstance()->getSkinAdapter();
        TypeConverter::getInstance()->setSkinAdapter(new PersonaSkinAdapter());
    }

    protected function onDisable() : void{
        if(!is_null($this->originalAdaptor)){
            TypeConverter::getInstance()->setSkinAdapter($this->originalAdaptor);
        }
    }
}
