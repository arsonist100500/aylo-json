<?php

$root = dirname(__FILE__, 3);

Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@backend', $root . '/backend');
Yii::setAlias('@console', $root . '/console');
