<?php

/*
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

use kim\present\converter\png\PngConverter;
use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\types\SkinData;

class PersoanSkin extends Skin{
    /** @var SkinData */
    protected $personaSkinData;

    public function __construct(SkinData $data){
        if(!$data->isPersona())
            throw new \RuntimeException("Must be given Persona skin.");

        $this->personaSkinData = $data;
        $originSkin = PngConverter::toPng($data->getSkinImage());
        $cropedSkin = imagecreatetruecolor(128, 128);
        imagefill($cropedSkin, 0, 0, imagecolorallocatealpha($cropedSkin, 0, 0, 0, 127));
        imagesavealpha($cropedSkin, true);

        $width = imagesx($originSkin);
        $height = imagesy($originSkin);
        imagealphablending($originSkin, false);
        imagecopy($cropedSkin, $originSkin, 0, 0, 0, 0, $width, $height);
        $skinImage = PngConverter::toSkinImage($cropedSkin);

        $capeData = $data->isPersonaCapeOnClassic() ? "" : $data->getCapeImage()->getData();

        $resourcePatch = json_decode($data->getResourcePatch(), true);
        if(is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])){
            $geometryName = $resourcePatch["geometry"]["default"];
        }else{
            throw new InvalidSkinException("Missing geometry name field");
        }
        parent::__construct($data->getSkinId(), $skinImage->getData(), $capeData, $geometryName, $data->getGeometryData());
    }

    public function getPersonaSkinData() : SkinData{
        return $this->personaSkinData;
    }
}