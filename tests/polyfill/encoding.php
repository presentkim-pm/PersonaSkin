<?php

/**
 * Test-only, minimal pure-PHP stand-in for the pmmp/ext-encoding extension.
 *
 * It implements the byte buffer reader/writer and the Byte / LE / BE / VarInt codecs that the bedrock-protocol packet
 * (de)serialization uses, which is enough to encode and decode the packets built by this plugin. It is NEVER loaded
 * when the real extension is available and favours simplicity over speed.
 */

declare(strict_types=1);

namespace pmmp\encoding;

final class DataDecodeException extends \RuntimeException{
}

final class ByteBufferWriter{
    private string $data = "";

    public function getData() : string{ return $this->data; }

    public function getOffset() : int{ return strlen($this->data); }

    public function clear() : void{ $this->data = ""; }

    public function writeByteArray(string $bytes) : void{ $this->data .= $bytes; }
}

final class ByteBufferReader{
    public function __construct(private string $data, private int $offset = 0){}

    public function getData() : string{ return $this->data; }

    public function getOffset() : int{ return $this->offset; }

    public function setOffset(int $offset) : void{ $this->offset = $offset; }

    public function getUnreadLength() : int{ return strlen($this->data) - $this->offset; }

    public function readByteArray(int $length) : string{
        if($length < 0 || $this->getUnreadLength() < $length){
            throw new DataDecodeException("Not enough bytes: need $length, have " . $this->getUnreadLength());
        }
        $result = substr($this->data, $this->offset, $length);
        $this->offset += $length;
        return $result;
    }
}

/** @internal */
abstract class FixedWidthPolyfill{
    /** @var array<string, array{int, bool, string}> type => [size, float, unsigned format] */
    private const TYPES = [
        'Short' => [2, false], 'Int' => [4, false], 'Long' => [8, false],
        'Float' => [4, true], 'Double' => [8, true],
    ];

    abstract protected static function bigEndian() : bool;

    /** @param mixed[] $args */
    public static function __callStatic(string $name, array $args) : mixed{
        if(preg_match('/^(read|write|pack|unpack)(Signed|Unsigned)?(Short|Int|Long|Float|Double)$/', $name, $m) !== 1){
            throw new \BadMethodCallException("pmmp\\encoding polyfill does not implement " . static::class . "::$name()");
        }
        [$op, $sign, $type] = [$m[1], $m[2], $m[3]];
        [$size, $isFloat] = self::TYPES[$type];

        if($op === 'write' || $op === 'pack'){
            $value = $args[$op === 'write' ? 1 : 0];
            if($isFloat){
                $bytes = pack($type === 'Float' ? 'g' : 'e', $value);
            }else{
                self::checkRange($value, $size, $sign === 'Signed');
                $bytes = substr(pack('P', $value), 0, $size);
            }
            if(static::bigEndian() && $size > 1){
                $bytes = strrev($bytes);
            }
            if($op === 'pack'){
                return $bytes;
            }
            $args[0]->writeByteArray($bytes);
            return null;
        }

        if($op === 'read'){
            $bytes = $args[0]->readByteArray($size);
        }else{
            $bytes = (string) $args[0];
            if(strlen($bytes) < $size){
                throw new DataDecodeException("Not enough bytes");
            }
            $bytes = substr($bytes, 0, $size);
        }
        if(static::bigEndian() && $size > 1){
            $bytes = strrev($bytes);
        }
        if($isFloat){
            return unpack($type === 'Float' ? 'g' : 'e', $bytes)[1];
        }
        $unsigned = unpack('P', str_pad($bytes, 8, "\0"))[1]; //for 8 bytes, this is already the (wrapped) signed value
        if($sign === 'Signed' && $size < 8 && ($unsigned >> ($size * 8 - 1)) & 1){
            $unsigned -= 1 << ($size * 8);
        }
        return $unsigned;
    }

    /** @internal */
    public static function checkByte(int $value, bool $signed) : void{
        self::checkRange($value, 1, $signed);
    }

    private static function checkRange(int $value, int $size, bool $signed) : void{
        if($size === 8){
            return;
        }
        $bits = $size * 8;
        [$min, $max] = $signed ? [-(1 << ($bits - 1)), (1 << ($bits - 1)) - 1] : [0, (1 << $bits) - 1];
        if($value < $min || $value > $max){
            throw new \InvalidArgumentException("Value $value out of range for a " . ($signed ? "signed" : "unsigned") . " $bits-bit integer");
        }
    }
}

final class LE extends FixedWidthPolyfill{
    protected static function bigEndian() : bool{ return false; }
}

final class BE extends FixedWidthPolyfill{
    protected static function bigEndian() : bool{ return true; }
}

final class Byte{
    public static function writeUnsigned(ByteBufferWriter $out, int $value) : void{
        FixedWidthPolyfill::checkByte($value, false);
        $out->writeByteArray(chr($value));
    }

    public static function writeSigned(ByteBufferWriter $out, int $value) : void{
        FixedWidthPolyfill::checkByte($value, true);
        $out->writeByteArray(chr($value & 0xff));
    }

    public static function readUnsigned(ByteBufferReader $in) : int{
        return ord($in->readByteArray(1));
    }

    public static function readSigned(ByteBufferReader $in) : int{
        $value = ord($in->readByteArray(1));
        return $value >= 0x80 ? $value - 0x100 : $value;
    }
}

final class VarInt{
    public static function writeUnsignedInt(ByteBufferWriter $out, int $value) : void{
        if($value < 0 || $value > 0xffffffff){
            throw new \InvalidArgumentException("Value $value out of range for an unsigned 32-bit VarInt");
        }
        self::writeRaw($out, $value);
    }

    public static function writeSignedInt(ByteBufferWriter $out, int $value) : void{
        if($value < -0x80000000 || $value > 0x7fffffff){
            throw new \InvalidArgumentException("Value $value out of range for a signed 32-bit VarInt");
        }
        self::writeRaw($out, (($value << 1) ^ ($value >> 63)) & 0xffffffff);
    }

    public static function writeUnsignedLong(ByteBufferWriter $out, int $value) : void{
        self::writeRaw($out, $value);
    }

    public static function writeSignedLong(ByteBufferWriter $out, int $value) : void{
        self::writeRaw($out, ($value << 1) ^ ($value >> 63));
    }

    public static function readUnsignedInt(ByteBufferReader $in) : int{
        return self::readRaw($in, 5);
    }

    public static function readSignedInt(ByteBufferReader $in) : int{
        $raw = self::readRaw($in, 5);
        return ($raw >> 1) ^ -($raw & 1);
    }

    public static function readUnsignedLong(ByteBufferReader $in) : int{
        return self::readRaw($in, 10);
    }

    public static function readSignedLong(ByteBufferReader $in) : int{
        $raw = self::readRaw($in, 10);
        return (($raw >> 1) & PHP_INT_MAX) ^ -($raw & 1);
    }

    /** Writes the value as a base-128 varint, treating it as an unsigned 64-bit quantity. */
    private static function writeRaw(ByteBufferWriter $out, int $value) : void{
        $bytes = "";
        for($i = 0; $i < 10; ++$i){
            $byte = $value & 0x7f;
            $value = ($value >> 7) & (PHP_INT_MAX >> 6); //logical shift
            if($value !== 0){
                $bytes .= chr($byte | 0x80);
            }else{
                $bytes .= chr($byte);
                break;
            }
        }
        $out->writeByteArray($bytes);
    }

    private static function readRaw(ByteBufferReader $in, int $maxBytes) : int{
        $value = 0;
        for($i = 0; $i < $maxBytes; ++$i){
            $byte = ord($in->readByteArray(1));
            $value |= ($byte & 0x7f) << (7 * $i);
            if(($byte & 0x80) === 0){
                return $value;
            }
        }
        throw new DataDecodeException("VarInt did not terminate after $maxBytes bytes");
    }
}
