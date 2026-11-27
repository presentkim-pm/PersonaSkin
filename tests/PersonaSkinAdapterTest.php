<?php

declare(strict_types=1);

namespace kim\present\personaskin\tests;

use kim\present\personaskin\PersonaSkinAdapter;
use PHPUnit\Framework\TestCase;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;

final class PersonaSkinAdapterTest extends TestCase{

    private static function skinData(bool $persona, string $skinId = "test") : SkinData{
        return new SkinData(
            $skinId,
            json_encode(["geometry" => ["default" => "geometry.humanoid.custom"]], JSON_THROW_ON_ERROR),
            new SkinImage(64, 64, str_repeat("\x01\x02\x03\xff", 64 * 64)),
            persona: $persona
        );
    }

    public function testPersonaSkinDataIsPreserved() : void{
        $adapter = new PersonaSkinAdapter();
        $data = self::skinData(true);

        $skin = $adapter->fromSkinData($data);
        self::assertSame($data, $adapter->toSkinData($skin));
    }

    public function testClassicSkinGoesThroughLegacyConversion() : void{
        $adapter = new PersonaSkinAdapter();
        $data = self::skinData(false);

        $skin = $adapter->fromSkinData($data);
        self::assertSame("test", $skin->getSkinId());
        $converted = $adapter->toSkinData($skin);
        self::assertNotSame($data, $converted);
        self::assertFalse($converted->isPersona());
        self::assertSame($data->getSkinImage()->getData(), $converted->getSkinImage()->getData());
    }

    public function testPersonaSkinsAreIndependentPerSkin() : void{
        $adapter = new PersonaSkinAdapter();
        $a = self::skinData(true, "a");
        $b = self::skinData(true, "b");

        $skinA = $adapter->fromSkinData($a);
        $skinB = $adapter->fromSkinData($b);
        self::assertSame($a, $adapter->toSkinData($skinA));
        self::assertSame($b, $adapter->toSkinData($skinB));
    }

    public function testSkinDataIsReleasedWithTheSkin() : void{
        $adapter = new PersonaSkinAdapter();
        $data = self::skinData(true);
        $ref = \WeakReference::create($data);

        $skin = $adapter->fromSkinData($data);
        unset($data);
        self::assertNotNull($ref->get(), "The adapter must keep the data while the skin is alive");

        unset($skin);
        self::assertNull($ref->get(), "The adapter must not keep the data after the skin is gone");
    }
}
