<?php

namespace app\properties\url;

use app\models\Route;
use Yii;
use yii\base\Object;

/**
 * Абстрактный класс части урла.
 */
abstract class UrlPart extends Object
{

    /**
     * Часть урла, которую мы взяли.
     * На конце обязательно слеш, если это не последняя часть
     * Строка.
     * Например: bu/ или category/subcategory/
     * @var string
     */
    public $gathered_part = null;

    /**
     * Остаток урла после взятия gathered_part
     */
    public $rest_part = null;

    /**
     * Параметры, которые передаёт наша часть урла в контроллер
     */
    public $parameters = [];

    /**
     * Эта часть урла опциональна
     *
     * На стадии парсинга урла
     * обработка этого параметра происходит вне этого класса.
     *
     * На стадии формирования урла
     * обработка этого параметра должна происходить внутри этого класса.
     */
    public $optional = false;

    /**
     * Массив ссылок на предыдущие части урла
     * Пока не убираем - может быть пригодится.... =(
     */
    public $previous_parts = [];

    /**
     * Ссылка на Object, который обрабатывает эта инстанция UrlPart
     */
    public $object = null;

    /**
     * Ссылка на модель Object-a
     */
    public $model = null;

    /** @var array Cache tags to append */
    public $cacheTags = [];

    /**
     * Gathers next url part
     * @param $full_url
     * @param $next_part
     * @param $previous_parts
     * @return false|UrlPart UrlPart instance if url part matched, false if not
     */
    abstract public function getNextPart($full_url, $next_part, &$previous_parts);

    /**
     * Добавить к урлу эту инстанцию UrlPart
     * @param string $route Route ссылка на Route
     * @param $parameters array Параметры, применимые к урлу(например фильтры),
     * может содержать модели(например продукт или категорию)
     * @return string|false если надо добавить, или false если не надо
     */
    abstract public function appendPart($route, $parameters = [], &$used_params = [], &$cacheTags = []);
}
