<?php

namespace app\properties\url;

use Yii;

class StaticPart extends UrlPart
{
    public $static_part = 'dummy_static_part';

    public $parameters = [
        'last_category_id' => null,
        'category_group_id' => null,
    ];

    public function getNextPart($full_url, $next_part, &$previous_parts)
    {
        if (mb_strpos($next_part, $this->static_part) === 0) {
            // наша подстрока всегда начинается с нуля
            // тот, кто забивает правила урлов
            // должен сам беспокоиться о trailing slash и его необходимости

            // заполним parameters
            if (count($this->parameters) === 0) {
                $this->parameters = ['static_part' => $this->static_part,];
            }

            // создадим объект части урла
            $part = new self([
                'gathered_part' => $this->static_part,
                'rest_part' => mb_substr($next_part, mb_strlen($this->static_part)),
                'parameters' => $this->parameters,
            ]);

            return $part;
        } else {
            return false;
        }
    }

    public function appendPart($route, $parameters = [], &$used_params = [])
    {
        if (isset($this->parameters['category_group_id'], $parameters['category_group_id'])) {
            $used_params[] = 'category_group_id';
            $used_params[] = 'last_category_id';
            if ($this->parameters['category_group_id'] != $parameters['category_group_id']) {
                return false;
            }
        }
        return $this->static_part;
    }
}
