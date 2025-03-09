<?php

/*
  
  Rajador Developer

  ▒█▀▀█ ░█▀▀█ ░░░▒█ ░█▀▀█ ▒█▀▀▄ ▒█▀▀▀█ ▒█▀▀█ 
  ▒█▄▄▀ ▒█▄▄█ ░▄░▒█ ▒█▄▄█ ▒█░▒█ ▒█░░▒█ ▒█▄▄▀ 
  ▒█░▒█ ▒█░▒█ ▒█▄▄█ ▒█░▒█ ▒█▄▄▀ ▒█▄▄▄█ ▒█░▒█

  GitHub: https://github.com/RajadorDev
  Discord: rajadortv


*/

namespace KingDbrm;

use pocketmine\plugin\PluginBase;

use pocketmine\item\{Item, StringToItemParser, LegacyStringToItemParser};

use pocketmine\item\enchantment\EnchantmentInstance;

use pocketmine\data\bedrock\EnchantmentIdMap;

use pocketmine\data\bedrock\item\SavedItemData as Data;

use pocketmine\world\format\io\GlobalItemDataHandlers;

use pocketmine\nbt\tag\
{
	CompoundTag,
	StringTag,
	IntTag,
	FloatTag,
	ByteTag,
	ShortTag,
	LongTag,
	ListTag
};

final class API extends PluginBase 
{
	
	public static function serialize(Item $item) : array 
	{
		$id = StringToItemParser::getInstance()->lookupAliases($item)[0] ?? null;
		if ($id === null)
		{
			throw new \Exception('NÃO FOI POSSIVEL SALVAR O ID DO ITEM ' . $item->getName() . ' ID NÃO ENCONTRADO!');
		}
		$data = array 
		(
			'id' => $id,
			'count' => $item->getCount(),
			'lore' => $item->getLore(),
			'compound' => serialize($item->getNamedTag())
		);
		if ($item->hasCustomName())
		{
			$data['name'] = $item->getCustomName();
		}
		$enchantments = array();
		foreach ($item->getEnchantments() as $enchantment) 
		{
			$id = EnchantmentIdMap::getInstance()->toId($enchantment->getType());
			if (!is_null($id))
			{
				$enchantments[$id] = $enchantment->getLevel();
			}
		}
		if (!empty($enchantments))
		{
			$data['enchantments'] = $enchantments;
		}
		return $data;
	}
	
	public static function unserializeAll(array $allItems) : array 
	{
		return array_map 
		(
			function (array $data) : Item 
			{
				return self::unserialize($data);
			},
			array_filter 
			(
				$allItems,
				function (array $data) : bool 
				{
					return isset($data['id']);
				}
			)
		);
	}
	
	public static function serializeAll(array $list) : array 
	{
		return array_map 
		(
			function (Item $item) : array 
			{
				return self::serialize($item);
			},
			array_filter 
			(
				$list,
				function (mixed $type) : bool 
				{
					return $type instanceof Item;
				}
			)
		);
	}
	
	public static function unserialize(array $data) : Item 
	{
		
		$id = $data['id'];
		if (is_numeric(str_replace(':', '', (string) $id)))
		{
			foreach (['meta', 'damage'] as $metaId)
			{
				if (isset($data[$metaId]))
				{
					$id = $id . ':' . $data[$metaId];
					break;
				}
			}
			$item = LegacyStringToItemParser::getInstance()->parse($id);
		}
		
		if (!isset($item) || !($item instanceof Item))
			$item = StringToItemParser::getInstance()->parse($data['id']) ?? LegacyStringToItemParser::getInstance()->parse($data['id']);
		
		if (!($item instanceof Item))
		{
			throw new \Exception('Item invalido com id ' . $data['id'] ?? 'unknow');
		}
		
		$item->setCount($data['count'] ?? 1);
		if (isset($data['compound']))
		{
			$nbt = unserialize($data['compound']);
			$item->setNamedTag($nbt);
		}
		if (isset($data['name']))
		{
			$item->setCustomName($data['name']);
		}
		$item->setLore($data['lore'] ?? []);
		if (isset($data['enchantments']))
		{
			$enchantments = array();
			foreach ($data['enchantments'] as $enchantmentId => $level)
			{
				$id = (int) $enchantmentId;
				$level = (int) $level;
				$enchant = self::unserializeEnchantment($id, $level);
				if (!is_null($enchant))
				{
					$item->addEnchantment($enchant);
				}
			}
		}
		return $item;
	}
	
	public static function unserializeEnchantment(int $id, int $level) : ? EnchantmentInstance
	{
		$enchant = EnchantmentIdMap::getInstance()->fromId($id);
		if (!is_null($enchant))
		{
			return new EnchantmentInstance($enchant, $level);
		}
		return null;
	}
	
	public static function register(String $id, Item $item, String $customId = null) : void 
	{
		$stringId = $id;
		$id = 'minecraft:' . $id;
		GlobalItemDataHandlers::getDeserializer()->map($id, fn() => clone $item);
		GlobalItemDataHandlers::getSerializer()->map($item, fn() => new Data($id));
		StringToItemParser::getInstance()->register($stringId, fn() => $item);
		if (is_string($customId))
		{
			StringToItemParser::getInstance()->override($customId, fn() => $item);
		}
	}
	
}

?>