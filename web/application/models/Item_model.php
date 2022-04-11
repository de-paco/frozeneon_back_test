<?php

namespace Model;

use App;
use Cake\Database\Exception;
use System\Emerald\Emerald_model;
use stdClass;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Item_model extends Emerald_model {
    const CLASS_TABLE = 'items';

    protected $price;
    protected $name;

    /**
     * @return int
     */
    public function get_price(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return bool
     * @throws \ShadowIgniterException
     */
    public function set_price(int $price): bool
    {
        $this->price = $price;

        return $this->save('price', $price);
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function set_name(string $name): bool
    {
        $this->name = $name;

        return $this->save('name', $name);
    }

    function __construct($id = NULL)
    {
        parent::__construct();

        $this->set_id($id);
    }

    public function reload()
    {
        parent::reload();

        return $this;
    }

    public static function create(array $data)
    {
        App::get_s()->from(self::CLASS_TABLE)->insert($data)->execute();

        return new static(App::get_s()->get_insert_id());
    }

    public function delete(): bool
    {
        $this->is_loaded(TRUE);
        App::get_s()->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();

        return App::get_s()->is_affected();
    }

    /**
     * @param int $boosterpack_id
     *
     * @return self[]
     */
    public static function get_by_boosterpack_id(int $boosterpack_id): array
    {
        $boosterpack_info = Boosterpack_info_model::get_by_boosterpack_id($boosterpack_id);
        $boosterpack_items = array_map(static function (Boosterpack_info_model $info) {
            return $info->get_item();
        }, $boosterpack_info);

        return $boosterpack_items;
    }

    /**
     * @param self $data
     * @param string $preparation
     * @return stdClass
     * @throws Exception
     */
    public static function preparation(Item_model $data, string $preparation = 'default')
    {
        switch ($preparation) {
            case 'default':
                return self::_preparation_default($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param self $data
     * @return stdClass
     */
    private static function _preparation_default(Item_model $data): stdClass
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->name = $data->get_name();

        return $o;
    }
}
