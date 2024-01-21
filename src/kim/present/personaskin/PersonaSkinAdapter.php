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

use JsonException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;

class PersonaSkinAdapter extends LegacySkinAdapter{
    /**
     * @var SkinData[]
     * @phpstan-var array<int, SkinData>
     */
    private array $personaSkinData = [];

    public function fromSkinData(SkinData $data) : Skin{
        $skin = parent::fromSkinData($data);

        if($data->isPersona()){
            $this->personaSkinData[spl_object_id($skin)] = $data;
        }
        return $skin;
    }

    /** @throws JsonException */
    public function toSkinData(Skin $skin) : SkinData{
        return $this->personaSkinData[spl_object_id($skin)] ?? parent::toSkinData($skin);
    }
}