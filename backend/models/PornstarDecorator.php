<?php

namespace backend\models;

use rmrevin\yii\fontawesome\FAS;
use yii\base\BaseObject;
use yii\helpers\Html;

class PornstarDecorator extends BaseObject
{
    /**
     * @param Pornstar $owner
     * @param array $config
     */
    public function __construct(protected Pornstar $owner, array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        $id = $this->owner->id;
        return Html::a($id, ['/pornstar/view', 'id' => $id]);
    }

    /**
     * @return string
     */
    public function getAliases(): string
    {
        $aliases = $this->owner->aliases;
        return Html::ul($aliases);
    }

    /**
     * @param bool $small
     * @return string
     */
    public function getLink(bool $small = false): string
    {
        $link = $this->owner->link;
        $text = $small ? FAS::icon('external-link-alt') : $link;
        return Html::a($text, $link);
    }

    /**
     * @return string
     */
    public function getSmallLink(): string
    {
        return $this->getLink(true);
    }

    /**
     * @return string
     */
    public function getImages(): string
    {
        $images = $this->owner->images;
        $tags = [];
        foreach ($images as $image) {
            $tags[] = $image->getDecorator()->getImg();
        }

        return implode('<br/>', $tags);
    }
}
